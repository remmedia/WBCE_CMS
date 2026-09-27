<?php
final class WbceAddonUploadLanguage
{
    private static $text;
    public static function get($key)
    {
        if(self::$text===null){
            $language=defined('LANGUAGE')?strtoupper((string)LANGUAGE):(defined('DEFAULT_LANGUAGE')?strtoupper((string)DEFAULT_LANGUAGE):'EN');
            $language=preg_replace('/[^A-Z].*$/','',$language);
            $file=__DIR__.'/languages/'.$language.'.php';
            if(!is_file($file))$file=__DIR__.'/languages/EN.php';
            $loaded=require $file;
            self::$text=is_array($loaded)?$loaded:array();
        }
        return isset(self::$text[$key])?self::$text[$key]:$key;
    }
}
