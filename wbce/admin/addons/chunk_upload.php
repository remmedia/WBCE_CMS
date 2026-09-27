<?php
require '../../config.php';
require_once WB_PATH . '/framework/Admin.php';
require_once __DIR__.'/UploadLanguage.php';
header('Content-Type: application/json; charset=utf-8');
$admin = new admin('Addons', 'addons', false, false);

function wbce_addon_chunk_response(bool $ok, string $message = '', array $data = array(), int $status = 200): void
{
    http_response_code($status);
    echo json_encode(array_merge(array('ok' => $ok, 'message' => $message), $data));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('post_only'),array(),405);
$token = (string) ($_POST['token'] ?? '');
$sessionToken = (string) ($_SESSION['wbce_addon_chunk_token'] ?? '');
if ($sessionToken === '' || !hash_equals($sessionToken, $token)) wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('session_expired'),array(),403);

$maxSize = 512 * 1024 * 1024;
$baseDir = WB_PATH . '/temp/addon_chunks';
if (!is_dir($baseDir) && !mkdir($baseDir,0750,true) && !is_dir($baseDir))wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('temp_directory_failed'),array(),500);
foreach (glob($baseDir . '/*') ?: array() as $oldFile) if (is_file($oldFile) && filemtime($oldFile) < time() - 21600) @unlink($oldFile);

$action = (string) ($_POST['action'] ?? '');
if ($action === 'init') {
    $name = basename((string) ($_POST['name'] ?? ''));
    $size = filter_var($_POST['size'] ?? null, FILTER_VALIDATE_INT);
    $chunks = filter_var($_POST['chunks'] ?? null, FILTER_VALIDATE_INT);
    if (!preg_match('/\.zip$/i',$name)||$size===false||$size<1||$size>$maxSize||$chunks===false||$chunks<1)wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('invalid_package'),array(),422);
    $id = bin2hex(random_bytes(20));
    $path = $baseDir . '/' . $id . '.part';
    if(file_put_contents($path,'')===false)wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('temp_file_failed'),array(),500);
    $_SESSION['wbce_addon_chunk_uploads'][$id] = array('path'=>$path,'name'=>$name,'size'=>$size,'chunks'=>$chunks,'next'=>0,'complete'=>false);
    wbce_addon_chunk_response(true, '', array('upload_id'=>$id));
}
$id = (string) ($_POST['upload_id'] ?? '');
if(!preg_match('/^[a-f0-9]{40}$/',$id)||empty($_SESSION['wbce_addon_chunk_uploads'][$id]))wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('unknown_upload'),array(),404);
$upload =& $_SESSION['wbce_addon_chunk_uploads'][$id];
if ($action === 'chunk') {
    $index = filter_var($_POST['index'] ?? null, FILTER_VALIDATE_INT);
    if($index===false||$index!==(int)$upload['next']||!isset($_FILES['chunk'])||$_FILES['chunk']['error']!==UPLOAD_ERR_OK||!is_uploaded_file($_FILES['chunk']['tmp_name']))wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('chunk_invalid'),array(),409);
    $current = is_file($upload['path']) ? filesize($upload['path']) : 0;
    $size = (int)$_FILES['chunk']['size'];
    if($size<1||$current+$size>(int)$upload['size'])wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('chunk_size_invalid'),array(),413);
    $source=fopen($_FILES['chunk']['tmp_name'],'rb');$target=fopen($upload['path'],'ab');$copied=($source&&$target)?stream_copy_to_stream($source,$target):false;if(is_resource($source))fclose($source);if(is_resource($target))fclose($target);
    if($copied!==$size)wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('chunk_write_failed'),array(),500);
    $upload['next']++; wbce_addon_chunk_response(true);
}
if ($action === 'complete') {
    clearstatcache(true,$upload['path']);
    if((int)$upload['next']!==(int)$upload['chunks']||!is_file($upload['path'])||filesize($upload['path'])!==(int)$upload['size'])wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('package_incomplete'),array(),409);
    $complete=$baseDir.'/'.$id.'.zip';if(!rename($upload['path'],$complete))wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('package_prepare_failed'),array(),500);
    $upload['path']=$complete;$upload['complete']=true;
    $type=wbce_addon_chunk_package_type($complete);
    if($type==='module'&&!$admin->get_permission('modules_install'))wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('permission_denied'),array(),403);
    if(($type==='template'||$type==='admin-template')&&!$admin->get_permission('templates_install'))wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('permission_denied'),array(),403);
    if($type==='language'&&!$admin->get_permission('languages_install'))wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('permission_denied'),array(),403);
    $upload['package_type']=$type;
    $validation = function_exists('wbce_apply_array_filters') ? wbce_apply_array_filters('addon.install.validation', array(
        'valid' => true, 'archive' => $complete, 'type' => $type, 'name' => $upload['name'], 'action' => 'install',
    ), $admin) : array('valid' => true);
    if (empty($validation['valid'])) wbce_addon_chunk_response(false, (string)($validation['message'] ?? WbceAddonUploadLanguage::get('invalid_package')), array(), 422);
    $installUrl=$type==='module'?ADMIN_URL.'/modules/install.php':($type==='language'?ADMIN_URL.'/languages/install.php':ADMIN_URL.'/templates/install.php');
    wbce_addon_chunk_response(true,'',array('package_type'=>$type,'install_url'=>$installUrl));
}
wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('action_unknown'),array(),400);

function wbce_addon_chunk_package_type($archive)
{
    $source='';$infoCount=0;$languageSources=array();
    if(class_exists('ZipArchive')){
        $zip=new ZipArchive();if($zip->open($archive)!==true)wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('package_type_unknown'),array(),422);
        if($zip->numFiles<1||$zip->numFiles>10000){$zip->close();wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('package_type_unknown'),array(),422);}
        for($index=0;$index<$zip->numFiles;$index++){$name=(string)$zip->getNameIndex($index);if(substr($name,-1)==='/')continue;$stat=$zip->statIndex($index);if(!is_array($stat)||(int)($stat['size']??0)>1048576)continue;$content=(string)$zip->getFromIndex($index);if(strtolower(basename($name))==='info.php'){$source=$content;$infoCount++;}if(preg_match('/^[A-Z]{2}\.php$/',basename($name))&&preg_match('/\$language_name\s*=/',$content))$languageSources[]=$name;}
        $zip->close();
    }else{
        require_once WB_PATH.'/include/pclzip/pclzip.lib.php';$zip=new PclZip($archive);$entries=$zip->listContent();
        if(!is_array($entries)||count($entries)<1||count($entries)>10000)wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('package_type_unknown'),array(),422);
        $infoName='';foreach($entries as $entry){$name=(string)($entry['filename']??'');if(!empty($entry['folder']))continue;if((int)($entry['size']??0)>1048576)continue;if(strtolower(basename($name))==='info.php'){$infoName=$name;$infoCount++;}if(preg_match('/^[A-Z]{2}\.php$/',basename($name))){$content=$zip->extract(PCLZIP_OPT_BY_NAME,$name,PCLZIP_OPT_EXTRACT_AS_STRING);$content=is_array($content)&&isset($content[0]['content'])?(string)$content[0]['content']:'';if(preg_match('/\$language_name\s*=/',$content))$languageSources[]=$name;}}
        if($infoCount===1){$extracted=$zip->extract(PCLZIP_OPT_BY_NAME,$infoName,PCLZIP_OPT_EXTRACT_AS_STRING);$source=is_array($extracted)&&isset($extracted[0]['content'])?(string)$extracted[0]['content']:'';}
    }
    if($infoCount===0&&count($languageSources)===1)return 'language';
    if($infoCount!==1)wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get($infoCount>1?'package_info_ambiguous':'package_type_unknown'),array(),422);
    if($source==='')wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('package_type_unknown'),array(),422);
    if(preg_match('/\$module_directory\s*=/', $source))return 'module';
    if(preg_match('/\$template_directory\s*=/', $source))return preg_match('/\$template_function\s*=\s*[\'\"]theme[\'\"]/', $source)?'admin-template':'template';
    wbce_addon_chunk_response(false,WbceAddonUploadLanguage::get('package_type_unknown'),array(),422);
}
