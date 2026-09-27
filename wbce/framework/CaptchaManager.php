<?php

/** Global, module-extensible CAPTCHA provider registry for WBCE 1.7. */
final class WbceCaptchaManager
{
    private static $legacyTypes = array('calc_text', 'calc_image', 'calc_ttf_image', 'ttf_image', 'old_image', 'text');

    public static function providers(array $legacyLabels = array())
    {
        $providers = array();
        foreach (self::$legacyTypes as $id) {
            $providers[$id] = array('id' => $id, 'name' => isset($legacyLabels[$id]) ? $legacyLabels[$id] : $id, 'description' => '', 'legacy' => true);
        }
        $providers = wbce_apply_array_filters('captcha.providers', $providers);
        foreach ($providers as $id => &$provider) {
            if (!is_array($provider)) {
                unset($providers[$id]);
                continue;
            }
            $provider['id'] = isset($provider['id']) ? (string)$provider['id'] : (string)$id;
            $provider['name'] = isset($provider['name']) ? (string)$provider['name'] : $provider['id'];
            $provider['description'] = isset($provider['description']) ? (string)$provider['description'] : '';
            $provider['legacy'] = !empty($provider['legacy']);
        }
        unset($provider);
        return $providers;
    }

    public static function selected(array $legacyLabels = array())
    {
        $id = defined('CAPTCHA_TYPE') ? (string)CAPTCHA_TYPE : 'calc_text';
        $providers = self::providers($legacyLabels);
        if (!isset($providers[$id])) {
            $id = isset($providers['calc_text']) ? 'calc_text' : (string)key($providers);
        }
        return isset($providers[$id]) ? $providers[$id] : null;
    }

    public static function isLegacy($id)
    {
        return in_array((string)$id, self::$legacyTypes, true);
    }

    public static function render($action = 'all', $style = '', $sectionId = '', array $context = array())
    {
        $provider = self::selected();
        if (!$provider || !empty($provider['legacy']) || empty($provider['render']) || !is_callable($provider['render'])) {
            return false;
        }
        $context = array_merge(array('action' => (string)$action, 'style' => (string)$style, 'section_id' => (string)$sectionId, 'field_name' => 'captcha'), $context);
        echo (string)call_user_func($provider['render'], $context);
        wbce_do_action('captcha.rendered', $provider['id'], $context);
        return true;
    }

    public static function verify($input = null, $sectionId = '', array $context = array())
    {
        $provider = self::selected();
        if (!$provider) {
            return false;
        }
        $context = array_merge(array('section_id' => (string)$sectionId, 'request' => $_POST, 'remote_address' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''), $context);
        if (!empty($provider['legacy'])) {
            $value = $input === null ? (isset($_POST['captcha']) ? $_POST['captcha'] : null) : $input;
            $expected = array();
            if (isset($_SESSION['captcha'])) $expected[] = $_SESSION['captcha'];
            if ($sectionId !== '' && isset($_SESSION['captcha' . $sectionId])) $expected[] = $_SESSION['captcha' . $sectionId];
            $valid = $value !== null && $value !== '' && in_array($value, $expected, false);
        } elseif (!empty($provider['verify']) && is_callable($provider['verify'])) {
            $valid = (bool)call_user_func($provider['verify'], $input, $context);
        } else {
            $valid = false;
        }
        $valid = (bool)wbce_apply_filters('captcha.validation.result', $valid, $provider['id'], $context);
        wbce_do_action($valid ? 'captcha.verified' : 'captcha.failed', $provider['id'], $context);
        return $valid;
    }
}

function wbce_captcha_providers(array $legacyLabels = array()) { return WbceCaptchaManager::providers($legacyLabels); }
function wbce_captcha_render($action = 'all', $style = '', $sectionId = '', array $context = array()) { return WbceCaptchaManager::render($action, $style, $sectionId, $context); }
function wbce_captcha_verify($input = null, $sectionId = '', array $context = array()) { return WbceCaptchaManager::verify($input, $sectionId, $context); }

/** Whether the globally selected CAPTCHA is enabled for a form purpose. */
function wbce_captcha_is_enabled($purpose)
{
    switch ((string)$purpose) {
        case 'login':
            return (string)Settings::Get('captcha_login_mode', 'after_failures') !== 'off';
        case 'password_reset':
            return (string)Settings::Get('captcha_password_reset', '1') === '1';
        case 'signup':
            return defined('ENABLED_CAPTCHA') ? (bool)ENABLED_CAPTCHA : (bool)Settings::Get('enabled_captcha', true);
        default:
            return true;
    }
}
