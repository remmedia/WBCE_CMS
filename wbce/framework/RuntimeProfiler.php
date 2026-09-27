<?php
/**
 * Opt-in request runtime recorder for diagnosing slow WBCE requests.
 *
 * It intentionally uses a small JSON file instead of database settings: the
 * profiler has to start before the settings table and modules are available.
 */
final class WbceRuntimeProfiler
{
    private static $startedAt = 0.0;
    private static $lastAt = 0.0;
    private static $events = array();
    private static $active = array();
    private static $config;
    private static $requestId = '';
    private static $finished = false;

    public static function configPath() { return WB_PATH . '/temp/runtime-profiler.json'; }
    public static function logPath($type = 'request')
    {
        return WB_PATH . '/var/logs/' . ($type === 'access' ? 'access.log.php' : 'runtime.log.php');
    }

    public static function config()
    {
        if (is_array(self::$config)) return self::$config;
        $defaults = array('enabled' => false, 'trace' => false, 'access' => false, 'threshold_ms' => 250, 'access_threshold_ms' => 500, 'access_sample_percent' => 0, 'access_slow' => true, 'access_errors' => true, 'access_ajax_api_errors' => true, 'access_background' => true, 'access_sampling' => false, 'admin_display' => false, 'frontend_display' => false);
        $json = @file_get_contents(self::configPath());
        $stored = is_string($json) && strlen($json) < 8192 ? json_decode($json, true) : null;
        self::$config = is_array($stored) ? array_merge($defaults, $stored) : $defaults;
        self::$config['enabled'] = !empty(self::$config['enabled']);
        self::$config['trace'] = !empty(self::$config['trace']);
        self::$config['access'] = !empty(self::$config['access']);
        self::$config['threshold_ms'] = max(25, min(10000, (int) self::$config['threshold_ms']));
        self::$config['access_threshold_ms'] = max(50, min(60000, (int) self::$config['access_threshold_ms']));
        self::$config['access_sample_percent'] = max(0, min(100, (int) self::$config['access_sample_percent']));
        foreach (array('access_slow','access_errors','access_ajax_api_errors','access_background','access_sampling','admin_display','frontend_display') as $option) self::$config[$option] = !empty(self::$config[$option]);
        return self::$config;
    }

    public static function saveConfig(array $config)
    {
        $value = array(
            'enabled' => !empty($config['enabled']),
            'trace' => !empty($config['trace']),
            'access' => !empty($config['access']),
            'threshold_ms' => max(25, min(10000, (int) ($config['threshold_ms'] ?? 250))),
            'access_threshold_ms' => max(50, min(60000, (int) ($config['access_threshold_ms'] ?? 500))),
            'access_sample_percent' => max(0, min(100, (int) ($config['access_sample_percent'] ?? 0))),
            'access_slow' => !empty($config['access_slow']), 'access_errors' => !empty($config['access_errors']), 'access_ajax_api_errors' => !empty($config['access_ajax_api_errors']), 'access_background' => !empty($config['access_background']), 'access_sampling' => !empty($config['access_sampling']), 'admin_display' => !empty($config['admin_display']), 'frontend_display' => !empty($config['frontend_display']),
        );
        $path = self::configPath();
        $temporary = $path . '.tmp-' . bin2hex(random_bytes(4));
        if (!is_dir(dirname($path)) || file_put_contents($temporary, json_encode($value), LOCK_EX) === false || !rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('Die Laufzeitprotokoll-Konfiguration konnte nicht gespeichert werden.');
        }
        @chmod($path, 0640);
        self::$config = $value;
    }

    public static function elapsedMs() { return self::$startedAt ? (int)round((microtime(true) - self::$startedAt) * 1000) : 0; }
    public static function logCenterInstalled()
    {
        if (class_exists('Settings') && filter_var(Settings::GetDb('log_center_installed', false), FILTER_VALIDATE_BOOLEAN)) return true;
        return is_dir(WB_PATH . '/modules/log_center') && is_file(WB_PATH . '/modules/log_center/Client.php');
    }
    public static function showInAdmin()
    {
        return self::logCenterInstalled()
            && !defined('WB_FRONTEND')
            && !empty(self::config()['admin_display']);
    }
    public static function showInFrontend()
    {
        return self::logCenterInstalled()
            && defined('WB_FRONTEND') && WB_FRONTEND
            && PHP_SAPI !== 'cli'
            && !empty(self::config()['frontend_display']);
    }

    public static function start()
    {
        if (self::$startedAt || !self::config()['enabled']) return;
        self::$startedAt = self::$lastAt = microtime(true);
        if (self::showInFrontend()) {
            ob_start(array(__CLASS__, 'appendFrontendRuntime'));
        }
        try { self::$requestId = bin2hex(random_bytes(6)); } catch (Throwable $e) { self::$requestId = dechex(mt_rand()); }
        self::record('request.start', array('request' => self::requestName()), true);
        register_shutdown_function(array(__CLASS__, 'finish'));
    }

    public static function mark($name, array $context = array())
    {
        if (!self::$startedAt) return;
        $now = microtime(true);
        $event = array('name' => (string) $name, 'ms' => (int) round(($now - self::$lastAt) * 1000), 'total_ms' => (int) round(($now - self::$startedAt) * 1000), 'context' => $context);
        self::$lastAt = $now;
        self::$events[] = $event;
    }

    public static function begin($name) { if (self::$startedAt) self::$active[(string) $name] = microtime(true); }
    public static function end($name, array $context = array())
    {
        if (!self::$startedAt || !isset(self::$active[(string) $name])) return;
        $duration = (int) round((microtime(true) - self::$active[(string) $name]) * 1000);
        unset(self::$active[(string) $name]);
        $context['duration_ms'] = $duration;
        self::mark($name, $context);
    }

    public static function finish()
    {
        if (!self::$startedAt || self::$finished) return;
        self::$finished = true;
        $total = (int) round((microtime(true) - self::$startedAt) * 1000);
        $config = self::config();
        $threshold = (int)$config['access_threshold_ms'];
        $lastTotal = self::$events ? (int)(self::$events[count(self::$events) - 1]['total_ms'] ?? 0) : 0;
        if ($total > $lastTotal) self::$events[] = array('name' => 'request.untracked', 'ms' => $total - $lastTotal, 'total_ms' => $total, 'context' => array('description' => 'Zeit seit dem letzten Messpunkt'));
        $slow = array_values(array_filter(self::$events, static function ($event) use ($threshold) { return $event['ms'] >= $threshold || (!empty($event['context']['duration_ms']) && $event['context']['duration_ms'] >= $threshold); }));
        // Runtime entries are only written from the user-configured “Slow from”
        // threshold onwards. The complete trace is attached to that one entry.
        if ($total >= $threshold) {
            self::write('request', array('name' => 'request.finish', 'ms' => $total, 'total_ms' => $total, 'context' => array('request' => self::requestName(), 'memory_mb' => round(memory_get_peak_usage(true) / 1048576, 2), 'slow_steps' => $slow)), self::trace(self::$events));
        }
        $access = self::accessContext($total);
        if ($config['access'] && (($config['access_slow'] && $access['slow']) || ($config['access_errors'] && $access['error']) || ($config['access_ajax_api_errors'] && $access['error'] && ($access['ajax'] || $access['api'])) || ($config['access_background'] && $access['background']) || ($config['access_sampling'] && $access['sampled']))) {
            self::write('access', array('name' => 'request.access', 'ms' => $total, 'total_ms' => $total, 'context' => $access));
        }
    }
    public static function appendFrontendRuntime($html)
    {
        if (!is_string($html) || stripos($html, '</body>') === false) return $html;
        $label = '<span class="wbce-runtime-overlay" style="position:fixed;right:.35rem;bottom:.35rem;z-index:2147483647;padding:.12rem .35rem;border-radius:.18rem;background:rgba(20,25,30,.38);color:rgba(255,255,255,.78);font:500 clamp(.58rem,1.4vw,.7rem)/1.2 sans-serif;pointer-events:none;white-space:nowrap">Laufzeit: ' . (int)self::elapsedMs() . ' ms</span>';
        return preg_replace('~</body>~i', $label . '</body>', $html, 1) ?: $html;
    }
    private static function accessContext($total)
    {
        $status = PHP_SAPI === 'cli' ? 0 : (int)http_response_code();
        $path = self::requestName();
        $ajax = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
        $api = strpos($path, '/api/') !== false || strpos($path, '/modules/api/') !== false;
        $background = PHP_SAPI === 'cli' || strpos($path, '/worker/') !== false || strpos($path, '/cron') !== false;
        $config = self::config(); $sample = (int)$config['access_sample_percent'];
        $sampled = $status < 400 && !$ajax && !$api && !$background && $sample > 0 && random_int(1, 100) <= $sample;
        return array('request' => $path, 'method' => (string)($_SERVER['REQUEST_METHOD'] ?? (PHP_SAPI === 'cli' ? 'CLI' : 'GET')), 'status' => $status, 'ajax' => $ajax, 'api' => $api, 'background' => $background, 'slow' => $total >= $config['access_threshold_ms'], 'error' => $status >= 400, 'sampled' => $sampled);
    }

    private static function record($name, array $context, $force = false)
    {
        if ($force && self::config()['trace']) self::$events[] = array('name' => (string) $name, 'ms' => 0, 'total_ms' => 0, 'context' => $context);
    }
    private static function requestName()
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? 'cli');
        return (string) (parse_url($uri, PHP_URL_PATH) ?: $uri);
    }
    private static function trace(array $events)
    {
        $lines = array(); foreach ($events as $index => $event) { $line='#'.$index.' +'.(int)($event['ms']??0).'ms total='.(int)($event['total_ms']??0).'ms '.(string)($event['name']??''); if(!empty($event['context']))$line.=' '.json_encode($event['context'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); $lines[]=$line; }
        return implode("\n", $lines);
    }
    private static function write($type, array $event, $trace = '')
    {
        $path = self::logPath($type);
        if (!is_dir(dirname($path))) @mkdir(dirname($path), 0750, true);
        if (!file_exists($path)) @file_put_contents($path, "<?php die(); ?>\n", LOCK_EX);
        $line = gmdate('c') . ' [Runtime ' . ucfirst($type) . '] id=' . self::$requestId . ' +' . (int) $event['ms'] . 'ms total=' . (int) $event['total_ms'] . 'ms ' . str_replace(array("\r", "\n"), ' ', (string) $event['name']);
        if (!empty($event['context'])) $line .= ' ' . json_encode($event['context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($trace !== '') $line .= ' | Trace: ' . str_replace(array("\r", "\n"), array('', ' <- '), (string)$trace);
        @file_put_contents($path, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        // The local runtime file is authoritative. Remote delivery only queues
        // a compact copy and never waits for network I/O in a page request.
        $client = WB_PATH . '/modules/log_center/Client.php';
        if (is_file($client)) {
            try {
                require_once $client;
                if (class_exists('WbceLogCenterClient', false)) {
                    WbceLogCenterClient::sendEvent('runtime', $type === 'trace' ? 'Trace' : 'Runtime', (string)$event['name'], $line, (string)$trace);
                }
            } catch (Throwable $ignored) { }
        }
    }
}
