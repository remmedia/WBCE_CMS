<?php
$template_directory = 'media_horizon';
$template_name = 'MEDIA Horizon';
$template_function = 'template';
$template_version = '1.0.8';
$template_platform = '1.6.8';
$template_author = 'MEDIA CMS';
$template_license = 'GNU GPL2 or later';
$template_description = 'Helles, vollständig responsives und barrierearmes Frontend-Template mit großzügiger Bühne, Kartenlayout und zugänglicher Mehrstufen-Navigation.';

$menu[1] = 'Hauptnavigation';
$menu[2] = 'Servicenavigation';
$menu[99] = 'Nicht anzeigen';

$block[1] = 'Hauptinhalt';
$block[2] = 'Bühne / Hero';
$block[3] = 'Teaser und Karten';
$block[4] = 'Footer';
$block[99] = 'Nicht anzeigen';

if (defined('LANGUAGE') && LANGUAGE !== 'DE') {
    $menu[1] = 'Main navigation';
    $menu[2] = 'Utility navigation';
    $menu[99] = 'Hidden';
    $block[1] = 'Main content';
    $block[2] = 'Hero';
    $block[3] = 'Teasers and cards';
    $block[4] = 'Footer';
    $block[99] = 'Hidden';
}
