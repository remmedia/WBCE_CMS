<?php
declare(strict_types=1);
/* WBCE Bootstrap Installer: place this single file in the web root. */
const WBCE_BOOTSTRAP_VERSION = '1.0.8';
const WBCE_BOOTSTRAP_MAX_BYTES = 536870912;
const WBCE_BOOTSTRAP_MAX_RESTORE_BYTES = 2147483648;

/* Configure trusted Store endpoints here. Tokens are never accepted via GET. */
$wbce_bootstrap_stores = array(
    // array('name' => 'WBCE Store', 'url' => 'https://store.example.org/modules/api/store/', 'token' => ''),
);
/* Optional password for a locally placed encrypted Backup-Center backup.
 * Leave empty to ask for it in the restore form. */
$wbce_bootstrap_backup_password = '';
/* Packages to install after the CMS setup. Every entry accepts either a UUID
 * or legacy_key/type+slug; version is optional and then selects the newest
 * compatible package. Examples:
 * array('uuid' => 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx', 'version' => '1.2.0'),
 * array('legacy_key' => 'module:log_center'),
 */
$wbce_bootstrap_packages = array();

function wbce_bootstrap_page(string $title, string $body, int $status = 200): never {
    http_response_code($status);
    echo '<!doctype html><html lang="de"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</title><style>body{margin:3rem auto;max-width:44rem;padding:0 1rem;font:16px/1.5 system-ui;color:#17233b;background:#eef4f8}.card{background:#fff;padding:2rem;border-radius:10px;box-shadow:0 2px 8px #0002}label,input,select,button{font:inherit;padding:.65rem;width:100%;box-sizing:border-box}label{display:block;margin-top:1rem;font-weight:600}button{margin-top:1rem;background:#2271b1;color:#fff;border:0;border-radius:5px;cursor:pointer}.error{color:#a12622}.notice{padding:.8rem 1rem;background:#edf6ff;border-left:4px solid #2271b1;border-radius:4px}</style><main class="card"><h1>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</h1>'.$body.'</main></html>';
    exit;
}
function wbce_bootstrap_fail(string $message): never { wbce_bootstrap_page('WBCE installieren', '<p class="error">'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</p><p><a href="install.php">Zurück</a></p>', 400); }
function wbce_bootstrap_h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function wbce_bootstrap_url(string $url): array { $parts=parse_url($url); if(!$parts||($parts['scheme']??'')!=='https'||empty($parts['host'])||isset($parts['user'])||isset($parts['pass'])) wbce_bootstrap_fail('Die Store-Adresse muss eine vollständige HTTPS-Adresse sein.'); return $parts; }
function wbce_bootstrap_fetch(string $url, string $token = ''): ?string { $ch=curl_init($url); if(!$ch)return null; curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_USERAGENT=>'WBCE-Bootstrap/1.1',CURLOPT_HTTPHEADER=>$token!==''?['Authorization: Bearer '.$token]:[]]); $body=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);return is_string($body)&&$status>=200&&$status<300?$body:null; }
function wbce_bootstrap_get(string $url, string $token = ''): string { $body=wbce_bootstrap_fetch($url,$token);if($body===null)wbce_bootstrap_fail('Der Store konnte nicht sicher erreicht werden.');return $body; }
function wbce_bootstrap_encrypted(string $path): bool { $handle=@fopen($path,'rb'); if(!$handle)return false; $magic=fread($handle,8);fclose($handle);return $magic==="BPENC01\n"; }
function wbce_bootstrap_write_all($handle, string $data): void { for($offset=0,$length=strlen($data);$offset<$length;){$written=fwrite($handle,substr($data,$offset));if($written===false||$written===0)throw new RuntimeException('Die Wiederherstellungsdatei konnte nicht geschrieben werden.');$offset+=$written;} }
function wbce_bootstrap_decrypt(string $sourcePath, string $destinationPath, string $passphrase): void {
    if($passphrase==='')throw new RuntimeException('Für dieses verschlüsselte Backup wird ein Passwort benötigt.');
    if(!function_exists('openssl_decrypt'))throw new RuntimeException('OpenSSL wird für das verschlüsselte Backup benötigt.');
    $source=@fopen($sourcePath,'rb');if(!$source)throw new RuntimeException('Das Backup kann nicht gelesen werden.');
    if(fread($source,8)!=="BPENC01\n"){fclose($source);if(!copy($sourcePath,$destinationPath))throw new RuntimeException('Das Backup kann nicht vorbereitet werden.');return;}
    $salt=fread($source,16);if(strlen($salt)!==16){fclose($source);throw new RuntimeException('Der Verschlüsselungskopf des Backups ist ungültig.');}
    $key=hash_pbkdf2('sha256',$passphrase,$salt,210000,32,true);$target=@fopen($destinationPath,'wb');if(!$target){fclose($source);throw new RuntimeException('Die temporäre Wiederherstellungsdatei kann nicht erstellt werden.');}
    try { while(!feof($source)){$lengthData=fread($source,4);if($lengthData==='')break;if(strlen($lengthData)!==4)throw new RuntimeException('Das verschlüsselte Backup ist beschädigt.');$length=(int)(unpack('Nlength',$lengthData)['length']??0);if($length<1||$length>1048608)throw new RuntimeException('Das verschlüsselte Backup ist beschädigt.');$iv=fread($source,12);$tag=fread($source,16);$cipher='';while(strlen($cipher)<$length&&!feof($source)){$part=fread($source,$length-strlen($cipher));if($part===false)throw new RuntimeException('Das Backup kann nicht gelesen werden.');$cipher.=$part;}$plain=openssl_decrypt($cipher,'aes-256-gcm',$key,OPENSSL_RAW_DATA,$iv,$tag);if($plain===false)throw new RuntimeException('Das Backup-Passwort ist nicht korrekt.');wbce_bootstrap_write_all($target,$plain);} } catch(Throwable $exception){fclose($source);fclose($target);@unlink($destinationPath);throw $exception;}
    fclose($source);fclose($target);
}
function wbce_bootstrap_local_backups(string $directory): array {
    $sets=array();foreach(glob($directory.'/*.zip') ?: array() as $zip){$sql=preg_replace('/\.zip$/i','.sql',$zip);if(!is_string($sql)||!is_file($sql)||filesize($zip)<1||filesize($sql)<1)continue;$name=basename($zip);$sets[$name]=array('zip'=>$zip,'sql'=>$sql,'name'=>$name,'encrypted'=>wbce_bootstrap_encrypted($zip)||wbce_bootstrap_encrypted($sql),'time'=>max((int)filemtime($zip),(int)filemtime($sql)),'size'=>(int)filesize($zip)+(int)filesize($sql));}usort($sets,static fn(array $a,array $b):int=>$b['time']<=>$a['time']);return $sets;
}
function wbce_bootstrap_safe_extract(string $archive, string $root, string $requiredPrefix = '', bool $stripPrefix = false): void {
    $zip = new ZipArchive();
    if ($zip->open($archive) !== true) throw new RuntimeException('Das Archiv ist kein gültiges ZIP-Archiv.');
    $bytes = 0;
    $entries = array();
    try {
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);
            $stat = $zip->statIndex($index);
            $attributes = 0;
            $unsafe = !is_string($name) || $name === '' || str_contains($name, "\0")
                || str_contains($name, '../') || str_starts_with($name, '/')
                || preg_match('~^[A-Za-z]:/~', $name)
                || ($requiredPrefix !== '' && !str_starts_with((string) $name, $requiredPrefix));
            if (method_exists($zip, 'getExternalAttributesIndex')
                && $zip->getExternalAttributesIndex($index, $opsys, $attributes)
                && (($attributes >> 16) & 0170000) === 0120000) {
                $unsafe = true;
            }
            $bytes += (int) ($stat['size'] ?? 0);
            if ($unsafe || $bytes > WBCE_BOOTSTRAP_MAX_RESTORE_BYTES) {
                throw new RuntimeException('Das Archiv enthält unzulässige oder zu große Dateien.');
            }
            $target = $stripPrefix ? substr((string) $name, strlen($requiredPrefix)) : (string) $name;
            if ($target === '') continue;
            $entries[] = array('source' => (string) $name, 'target' => $target, 'directory' => str_ends_with((string) $name, '/'));
        }
        foreach ($entries as $entry) {
            $target = $root . '/' . $entry['target'];
            if ($entry['directory']) {
                if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
                    throw new RuntimeException('Ein Zielverzeichnis konnte nicht erstellt werden.');
                }
                continue;
            }
            $directory = dirname($target);
            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new RuntimeException('Ein Zielverzeichnis konnte nicht erstellt werden.');
            }
            $input = $zip->getStream($entry['source']);
            $output = @fopen($target, 'wb');
            if ($input === false || $output === false) {
                if (is_resource($input)) fclose($input);
                if (is_resource($output)) fclose($output);
                throw new RuntimeException('Eine Archivdatei konnte nicht wiederhergestellt werden.');
            }
            try {
                if (stream_copy_to_stream($input, $output) === false) {
                    throw new RuntimeException('Eine Archivdatei konnte nicht wiederhergestellt werden.');
                }
            } finally {
                fclose($input);
                fclose($output);
            }
        }
    } finally {
        $zip->close();
    }
}
function wbce_bootstrap_package_request(array|string $request): array {
    if (is_string($request)) $request = array('uuid' => $request);
    if (!is_array($request)) throw new RuntimeException('Ungültiger Paketwunsch.');
    $uuid = strtolower(trim((string)($request['uuid'] ?? '')));
    $legacy = strtolower(trim((string)($request['legacy_key'] ?? '')));
    $type = strtolower(trim((string)($request['type'] ?? 'module')));
    $slug = trim((string)($request['slug'] ?? ''));
    $version = trim((string)($request['version'] ?? ''));
    if ($uuid !== '' && !preg_match('/^[a-f0-9]{8}-(?:[a-f0-9]{4}-){3}[a-f0-9]{12}$/', $uuid)) throw new RuntimeException('Ungültige Paket-UUID.');
    if ($legacy === '' && $slug !== '') $legacy = $type.':'.$slug;
    if ($legacy !== '' && !preg_match('~^(module|template|language):[a-z0-9_.-]+$~', $legacy)) throw new RuntimeException('Ungültiger Legacy-Paketschlüssel.');
    if ($uuid === '' && $legacy === '') throw new RuntimeException('Ein Paket braucht eine UUID oder einen Legacy-Schlüssel.');
    if ($version !== '' && !preg_match('/^[0-9][0-9A-Za-z.+_-]*$/', $version)) throw new RuntimeException('Ungültige Paketversion.');
    return array('uuid'=>$uuid, 'legacy_key'=>$legacy, 'version'=>$version);
}
function wbce_bootstrap_catalog_pick(array $catalog, array $request, string $minimum = ''): ?array {
    $best = null;
    foreach (($catalog['packages'] ?? array()) as $candidate) {
        if (!is_array($candidate)) continue;
        $type = strtolower((string)($candidate['type'] ?? ''));
        $slug = (string)($candidate['slug'] ?? '');
        $uuid = strtolower((string)($candidate['uuid'] ?? ''));
        $legacy = strtolower((string)($candidate['legacy_key'] ?? ($type.':'.$slug)));
        $version = (string)($candidate['version'] ?? '');
        if (!in_array($type, array('module','template','language'), true) || !preg_match('/^[a-z0-9_.-]+$/i', $slug) || $version === '') continue;
        if ($request['uuid'] !== '' ? $uuid !== $request['uuid'] : $legacy !== $request['legacy_key']) continue;
        if ($request['version'] !== '' && in_array(($request['_operator'] ?? '='), array('=', '=='), true) && $version !== $request['version']) continue;
        if ($minimum !== '' && version_compare($version, $minimum, '<')) continue;
        if ($best === null || version_compare($version, (string)$best['version'], '>')) $best = $candidate;
    }
    return $best;
}
function wbce_bootstrap_dependency_request(string $dependency): ?array {
    if (!preg_match('/^([a-z0-9_.-]+)\s*(?:(>=|=|==)\s*([0-9][0-9A-Za-z.+_-]*))?$/i', trim($dependency), $match)) return null;
    return array('uuid'=>'', 'legacy_key'=>'module:'.strtolower($match[1]), 'version'=>isset($match[3]) ? $match[3] : '', '_operator'=>$match[2] ?? '>=');
}
function wbce_bootstrap_resolve_packages(array $catalog, array $requested): array {
    $resolved = array(); $visiting = array();
    $visit = static function(array $request) use (&$visit, &$resolved, &$visiting, $catalog): void {
        $minimum = ($request['_operator'] ?? '>=') === '>=' ? (string)($request['version'] ?? '') : '';
        $candidate = wbce_bootstrap_catalog_pick($catalog, $request, $minimum);
        if ($candidate === null) throw new RuntimeException('Das angeforderte Paket oder seine Version ist im Store nicht verfügbar.');
        $identity = strtolower((string)($candidate['uuid'] ?? ''));
        if ($identity === '') $identity = strtolower((string)($candidate['legacy_key'] ?? (($candidate['type'] ?? '').':'.($candidate['slug'] ?? ''))));
        if (isset($resolved[$identity])) return;
        if (isset($visiting[$identity])) throw new RuntimeException('Zyklische Paketabhängigkeit: '.$identity);
        $visiting[$identity] = true;
        foreach (explode(',', (string)($candidate['dependencies'] ?? '')) as $dependency) {
            $dependency = trim($dependency); if ($dependency === '') continue;
            $dependencyRequest = wbce_bootstrap_dependency_request($dependency);
            if ($dependencyRequest === null) throw new RuntimeException('Ungültige Paketabhängigkeit: '.$dependency);
            $visit($dependencyRequest);
        }
        unset($visiting[$identity]); $resolved[$identity] = $candidate;
    };
    foreach ($requested as $request) $visit(wbce_bootstrap_package_request($request));
    return array_values($resolved);
}
function wbce_bootstrap_queue_package(array $candidate, array $catalogParts): array {
    $type = strtolower((string)($candidate['type'] ?? ''));
    $slug = (string)($candidate['slug'] ?? '');
    $url = (string)($candidate['download_url'] ?? '');
    $parts = wbce_bootstrap_url($url);
    if (!in_array($type, array('module','template','language'), true) || !preg_match('/^[a-z0-9_.-]+$/i', $slug)
        || strcasecmp((string)$parts['host'], (string)$catalogParts['host']) !== 0
        || !preg_match('/^[a-f0-9]{64}$/i', (string)($candidate['sha256'] ?? ''))
        || (int)($candidate['size'] ?? 0) < 1 || (int)$candidate['size'] > WBCE_BOOTSTRAP_MAX_BYTES) {
        throw new RuntimeException('Der Store liefert nicht vertrauenswürdige Paketdaten.');
    }
    return array('uuid'=>(string)($candidate['uuid'] ?? ''), 'legacy_key'=>(string)($candidate['legacy_key'] ?? ($type.':'.$slug)), 'type'=>$type, 'slug'=>$slug, 'version'=>(string)$candidate['version'], 'download_url'=>$url, 'sha256'=>strtolower((string)$candidate['sha256']), 'size'=>(int)$candidate['size']);
}
function wbce_bootstrap_write_queue(string $root, array $catalog, array $catalogParts, string $token, array $requests): void {
    if ($requests === array()) return;
    $packages = array_map(static fn(array $p): array => wbce_bootstrap_queue_package($p, $catalogParts), wbce_bootstrap_resolve_packages($catalog, $requests));
    $directory = $root.'/var';
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) throw new RuntimeException('Die Paketwarteschlange kann nicht vorbereitet werden.');
    $payload = json_encode(array('schema'=>1, 'store_url'=>(string)$catalogParts['scheme'].'://'.(string)$catalogParts['host'].(isset($catalogParts['port']) ? ':'.$catalogParts['port'] : '').(string)($catalogParts['path'] ?? '/'), 'access_token'=>$token, 'packages'=>$packages), JSON_UNESCAPED_SLASHES);
    if (!is_string($payload) || file_put_contents($directory.'/bootstrap-package-queue.json', $payload, LOCK_EX) === false) throw new RuntimeException('Die Paketwarteschlange kann nicht gespeichert werden.');
    @chmod($directory.'/bootstrap-package-queue.json', 0600);
}
function wbce_bootstrap_mark_self_delete(string $root): void {
    $directory = $root.'/var';
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) throw new RuntimeException('Der Installer kann nicht für die Bereinigung vorbereitet werden.');
    $hash = hash_file('sha256', __FILE__);
    if (!is_string($hash) || file_put_contents($directory.'/bootstrap-installer.json', json_encode(array('schema'=>1, 'installer'=>'install.php', 'sha256'=>$hash), JSON_UNESCAPED_SLASHES), LOCK_EX) === false) throw new RuntimeException('Der Installer kann nicht für die Bereinigung vorbereitet werden.');
    @chmod($directory.'/bootstrap-installer.json', 0600);
}
function wbce_bootstrap_config_value(string $config, string $name): string { $pattern="~define\\s*\\(\\s*['\\\"]".preg_quote($name,'~')."['\\\"]\\s*,\\s*'((?:\\\\.|[^'])*)'\\s*\\)~";if(!preg_match($pattern,$config,$match))throw new RuntimeException('Die Datenbankdaten im Backup konnten nicht gelesen werden.');return stripcslashes($match[1]); }
function wbce_bootstrap_import_sql(string $sqlFile, string $root): void {
    $config=$root.'/config.php';if(!is_file($config))throw new RuntimeException('Das Backup enthält keine WBCE-Konfiguration.');$content=(string)file_get_contents($config);$type=strtolower(wbce_bootstrap_config_value($content,'DB_TYPE'));if(!in_array($type,array('mysql','mysqli'),true)||!class_exists('mysqli'))throw new RuntimeException('Die Datenbank dieses Backups wird vom Bootstrap-Installer nicht unterstützt.');
    mysqli_report(MYSQLI_REPORT_OFF);$db=@new mysqli(wbce_bootstrap_config_value($content,'DB_HOST'),wbce_bootstrap_config_value($content,'DB_USERNAME'),wbce_bootstrap_config_value($content,'DB_PASSWORD'),wbce_bootstrap_config_value($content,'DB_NAME'));if($db->connect_errno)throw new RuntimeException('Die Datenbankverbindung für das Backup ist nicht möglich.');$charset=wbce_bootstrap_config_value($content,'DB_CHARSET');if($charset!=='')$db->set_charset($charset);
    $handle=@fopen($sqlFile,'rb');if(!$handle){$db->close();throw new RuntimeException('Der Datenbankexport kann nicht gelesen werden.');}$statement='';try{while(($line=fgets($handle))!==false){if($statement===''&&(trim($line)===''||str_starts_with(ltrim($line),'#')))continue;$statement.=$line;if(!str_ends_with(rtrim($line),';'))continue;if(!$db->query($statement))throw new RuntimeException('Der Datenbankimport ist fehlgeschlagen: '.$db->error);$statement='';}}finally{fclose($handle);$db->close();}
}

if(!class_exists('ZipArchive'))wbce_bootstrap_fail('Für die Installation wird ZipArchive benötigt.');
$requestedStoreUrl=isset($_GET['store_url'])&&is_string($_GET['store_url'])?trim($_GET['store_url']):'';$stores=array();foreach($wbce_bootstrap_stores as $store){if(!is_array($store)||!is_string($store['url']??null)||trim($store['url'])==='')continue;$stores[]=array('name'=>is_string($store['name']??null)&&trim($store['name'])!==''?trim($store['name']):'Store '.(count($stores)+1),'url'=>trim($store['url']),'token'=>is_string($store['token']??null)?$store['token']:'');}if($requestedStoreUrl!=='')$stores[]=array('name'=>'Temporärer Store','url'=>$requestedStoreUrl,'token'=>'');
$installedVersion='';$installedVersionFile=__DIR__.'/wbce/admin/interface/version.php';if(is_file($installedVersionFile)&&preg_match("/NEW_WBCE_VERSION',\\s*'([^']+)'/",(string)file_get_contents($installedVersionFile),$installedMatch))$installedVersion=$installedMatch[1];$backups=wbce_bootstrap_local_backups(__DIR__);

if($_SERVER['REQUEST_METHOD']!=='POST'){
    $bestStore=0;$bestVersion=null;if(extension_loaded('curl'))foreach($stores as $index=>&$store){$store['token_valid']=false;$body=wbce_bootstrap_fetch($store['url'],$store['token']);if($body===null){$store['version']='';continue;}$store['token_valid']=trim($store['token'])!=='';$catalog=json_decode($body,true);$version='';foreach(($catalog['packages']??array())as$candidate)if(is_array($candidate)&&($candidate['type']??'')==='cms'&&($candidate['slug']??'')==='wbce-cms'&&is_string($candidate['version']??null)&&($installedVersion===''||version_compare($candidate['version'],$installedVersion,'>'))&&($version===''||version_compare($candidate['version'],$version,'>')))$version=$candidate['version'];$store['version']=$version;if($version!==''&&($bestVersion===null||version_compare($version,$bestVersion,'>'))){$bestVersion=$version;$bestStore=$index;}}unset($store);
    $options='';foreach($stores as $index=>$store){$label=$store['name'].(!empty($store['version'])?' · '.$store['version']:' · nicht erreichbar');$options.='<option value="'.$index.'" data-token-valid="'.(!empty($store['token_valid'])?'1':'0').'"'.($index===$bestStore?' selected':'').'>'.wbce_bootstrap_h($label).'</option>';}
    $backupOptions='';foreach($backups as $index=>$backup){$label=$backup['name'].' · '.date('d.m.Y H:i',$backup['time']).' · '.round($backup['size']/1048576,1).' MB'.($backup['encrypted']?' · verschlüsselt':'');$backupOptions.='<option value="'.$index.'">'.wbce_bootstrap_h($label).'</option>';}
    $storeFields=$stores?'<label>Store<select name="store_id" id="store_id">'.$options.'</select></label><label id="store_token_field">Store-Token<input type="password" name="access_token" autocomplete="off"></label><script>(function(){var store=document.getElementById("store_id"),field=document.getElementById("store_token_field");if(!store||!field)return;function update(){field.hidden=store.options[store.selectedIndex].dataset.tokenValid==="1";}store.addEventListener("change",update);update();})();</script>':'<label>Store-API-Adresse<input required type="url" name="catalog_url" placeholder="https://example.org/modules/api/store/"></label><label>Store-Token (optional)<input type="password" name="access_token" autocomplete="off"></label>';
    $restore=$backups?'<hr><h2>Lokales Backup wiederherstellen</h2><p class="notice">Ein vollständiges Backup-Set im selben Verzeichnis wurde erkannt. Dateien und Datenbank werden wiederhergestellt.</p><form method="post"><input type="hidden" name="action" value="restore"><label>Backup<select name="backup_id">'.$backupOptions.'</select></label><label>Backup-Passwort (nur bei verschlüsselten Backups)<input type="password" name="backup_password" autocomplete="current-password"></label><button>Backup wiederherstellen</button></form>':'';
    wbce_bootstrap_page('WBCE installieren','<p>Das CMS-Paket wird geprüft vom Store geladen. Danach startet der normale WBCE-Installer.</p><form method="post"><input type="hidden" name="action" value="install">'.$storeFields.'<button>WBCE laden und installieren</button></form>'.$restore);
}

if(($_POST['action']??'')==='restore'){
    $id=isset($_POST['backup_id'])?(int)$_POST['backup_id']:-1;if(!isset($backups[$id]))wbce_bootstrap_fail('Das gewählte lokale Backup ist nicht verfügbar.');$backup=$backups[$id];$password=(string)$wbce_bootstrap_backup_password;if($password==='')$password=trim((string)($_POST['backup_password']??''));$temporary=array();
    try{$zip=$backup['zip'];$sql=$backup['sql'];if($backup['encrypted']){$zipTemp=tempnam(sys_get_temp_dir(),'wbce-restore-zip-');$sqlTemp=tempnam(sys_get_temp_dir(),'wbce-restore-sql-');if($zipTemp===false||$sqlTemp===false)throw new RuntimeException('Temporäre Wiederherstellungsdateien können nicht erstellt werden.');$temporary=array($zipTemp,$sqlTemp);wbce_bootstrap_decrypt($zip,$zipTemp,$password);wbce_bootstrap_decrypt($sql,$sqlTemp,$password);$zip=$zipTemp;$sql=$sqlTemp;}wbce_bootstrap_safe_extract($zip,__DIR__);wbce_bootstrap_import_sql($sql,__DIR__);}catch(Throwable $exception){foreach($temporary as $file)@unlink($file);wbce_bootstrap_fail($exception->getMessage());}foreach($temporary as $file)@unlink($file);header('Location: admin/login/');exit;
}

if(!extension_loaded('curl'))wbce_bootstrap_fail('Für die Store-Installation wird cURL benötigt.');$selectedStore=isset($_POST['store_id'])?(int)$_POST['store_id']:0;$storeCandidates=$stores&&isset($stores[$selectedStore])?array($stores[$selectedStore]):array(array('name'=>'Temporärer Store','url'=>trim((string)($_POST['catalog_url']??'')),'token'=>trim((string)($_POST['access_token']??''))));$manualToken=trim((string)($_POST['access_token']??''));if($manualToken!=='')$storeCandidates[0]['token']=$manualToken;$package=null;$catalogParts=null;$accessToken='';
foreach($storeCandidates as $store){try{$parts=wbce_bootstrap_url($store['url']);$catalog=json_decode(wbce_bootstrap_get($store['url'],$store['token']),true);if(!is_array($catalog)||!is_array($catalog['packages']??null))throw new RuntimeException('ungültiger Katalog');foreach($catalog['packages']as$candidate){if(!is_array($candidate)||($candidate['type']??'')!=='cms'||($candidate['slug']??'')!=='wbce-cms'||!is_string($candidate['version']??null))continue;if($installedVersion!==''&&version_compare($candidate['version'],$installedVersion,'<='))continue;if($package===null||version_compare($candidate['version'],$package['version'],'>')){$package=$candidate;$catalogParts=$parts;$accessToken=$store['token'];}}}catch(Throwable $ignored){}}
if (!$package) wbce_bootstrap_fail('Keiner der hinterlegten Stores liefert ein freigegebenes WBCE-CMS-Paket.');
foreach (array('download_url','sha256','size') as $key) if (!isset($package[$key])) wbce_bootstrap_fail('Die CMS-Paketdaten sind unvollständig.');
$download = (string)$package['download_url']; $downloadParts = wbce_bootstrap_url($download);
if (strcasecmp((string)$catalogParts['host'], (string)$downloadParts['host']) !== 0 || !preg_match('/^[a-f0-9]{64}$/i', (string)$package['sha256']) || (int)$package['size'] < 1 || (int)$package['size'] > WBCE_BOOTSTRAP_MAX_BYTES) wbce_bootstrap_fail('Die CMS-Paketdaten sind nicht vertrauenswürdig.');
try { wbce_bootstrap_write_queue(__DIR__, $catalog, $catalogParts, $accessToken, $wbce_bootstrap_packages); wbce_bootstrap_mark_self_delete(__DIR__); } catch (Throwable $exception) { wbce_bootstrap_fail($exception->getMessage()); }
$tmp=tempnam(sys_get_temp_dir(),'wbce-bootstrap-'); $out=fopen($tmp,'wb'); $ch=curl_init($download);
curl_setopt_array($ch,[CURLOPT_FILE=>$out,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>15,CURLOPT_TIMEOUT=>600,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_HTTPHEADER=>$accessToken!==''?['Authorization: Bearer '.$accessToken]:[]]);
$ok=curl_exec($ch); $status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE); curl_close($ch); fclose($out);
if(!$ok||$status<200||$status>=300||filesize($tmp)!==(int)$package['size']||!hash_equals(strtolower((string)$package['sha256']),hash_file('sha256',$tmp))){@unlink($tmp);wbce_bootstrap_fail('Das geladene CMS-Paket konnte nicht geprüft werden.');}
try { wbce_bootstrap_safe_extract($tmp,__DIR__,'wbce/',true); } catch(Throwable $exception){@unlink($tmp);wbce_bootstrap_fail($exception->getMessage());}
@unlink($tmp); header('Location: install/index.php');

