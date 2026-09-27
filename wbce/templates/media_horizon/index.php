<?php
if (!defined('WB_URL')) { header('Location: ../index.php', true, 302); exit; }

if (!function_exists('mh_escape')) {
    function mh_escape($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('mh_block')) {
    function mh_block($number) { ob_start(); page_content($number); return trim((string)ob_get_clean()); }
}

$language = defined('LANGUAGE') ? strtolower((string)LANGUAGE) : 'de';
$siteTitle = defined('WEBSITE_TITLE') ? WEBSITE_TITLE : 'Website';
$pageTitle = defined('PAGE_TITLE') ? PAGE_TITLE : $siteTitle;
$pageDescription = defined('PAGE_DESCRIPTION') ? trim((string)PAGE_DESCRIPTION) : '';
$hero = mh_block(2);
$content = mh_block(1);
$teasers = mh_block(3);
$footer = mh_block(4);

$mainNavigation = function_exists('show_menu2') ? show_menu2(
    1, SM2_ROOT, SM2_START + 4, SM2_ALL | SM2_PRETTY | SM2_BUFFER,
    '<li class="[class]"><a href="[url]" target="[target]">[menu_title]</a>',
    '</li>', '<ul>', '</ul>', false, '<ul class="mh-main-menu">'
) : '';
$serviceNavigation = function_exists('show_menu2') ? show_menu2(
    2, SM2_ROOT, SM2_START, SM2_ALL | SM2_PRETTY | SM2_BUFFER,
    '<li class="[class]"><a href="[url]" target="[target]">[menu_title]</a>',
    '</li>', '<ul>', '</ul>', false, '<ul class="mh-service-menu">'
) : '';
$breadcrumbs = function_exists('show_menu2') ? show_menu2(
    1, SM2_ROOT, SM2_CURR, SM2_CRUMB | SM2_BUFFER,
    '<li class="[class]"><a href="[url]">[menu_title]</a>', '</li>', '<ol>', '</ol>', false, '<ol>'
) : '';
$mainNavigation = preg_replace('/(<li class="[^"]*\bmenu-current\b[^"]*">\s*<a)(?![^>]*\baria-current=)/i', '$1 aria-current="page"', $mainNavigation);
$breadcrumbs = preg_replace('/(<li class="[^"]*\bmenu-current\b[^"]*">\s*<a)(?![^>]*\baria-current=)/i', '$1 aria-current="page"', $breadcrumbs);

$heroImagePath = WB_PATH.'/media/media-horizon-hero.jpg';
$heroStyle = is_file($heroImagePath) ? ' style="--mh-hero-image:url(\''.mh_escape(WB_URL.'/media/media-horizon-hero.jpg').'\')"' : '';
?><!doctype html>
<html lang="<?php echo mh_escape($language);?>">
<head>
    <?php if (function_exists('simplepagehead')) { simplepagehead(); } else { ?><meta charset="utf-8"><title><?php echo mh_escape($pageTitle.' – '.$siteTitle);?></title><?php } ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#5aa3e6">
    <link rel="icon" href="<?php echo mh_escape(TEMPLATE_DIR.'/favicon.svg');?>" type="image/svg+xml">
    <link rel="icon" href="<?php echo mh_escape(TEMPLATE_DIR.'/favicon-32x32.png');?>" sizes="32x32" type="image/png">
    <link rel="icon" href="<?php echo mh_escape(TEMPLATE_DIR.'/favicon-16x16.png');?>" sizes="16x16" type="image/png">
    <link rel="shortcut icon" href="<?php echo mh_escape(TEMPLATE_DIR.'/favicon.ico');?>">
    <link rel="apple-touch-icon" href="<?php echo mh_escape(TEMPLATE_DIR.'/apple-touch-icon.png');?>" sizes="180x180">
    <link rel="manifest" href="<?php echo mh_escape(TEMPLATE_DIR.'/site.webmanifest');?>">
    <link rel="mask-icon" href="<?php echo mh_escape(TEMPLATE_DIR.'/safari-pinned-tab.svg');?>" color="#5aa3e6">
    <meta name="msapplication-TileColor" content="#5aa3e6">
    <meta name="msapplication-config" content="<?php echo mh_escape(TEMPLATE_DIR.'/browserconfig.xml');?>">
    <link rel="stylesheet" href="<?php echo mh_escape(TEMPLATE_DIR.'/css/template.css');?>">
    <?php register_frontend_modfiles('css'); register_frontend_modfiles('jquery'); register_frontend_modfiles('js'); ?>
</head>
<body class="mh-page">
<a class="mh-skip-link" href="#mh-main"><?php echo $language === 'de' ? 'Direkt zum Inhalt' : 'Skip to content';?></a>

<div class="mh-topbar">
    <div class="mh-shell">
        <span><?php echo mh_escape($siteTitle);?></span>
        <?php if ($serviceNavigation !== '') { ?><nav aria-label="<?php echo $language === 'de' ? 'Servicenavigation' : 'Utility navigation';?>"><?php echo $serviceNavigation;?></nav><?php } ?>
    </div>
</div>

<header class="mh-header" data-mh-header>
    <div class="mh-shell mh-header-inner">
        <a class="mh-brand" href="<?php echo mh_escape(WB_URL.'/');?>" aria-label="<?php echo mh_escape($siteTitle.' – Startseite');?>">
            <span class="mh-brand-mark" aria-hidden="true"><span></span></span>
            <span class="mh-brand-name"><?php echo mh_escape($siteTitle);?></span>
        </a>
        <button class="mh-menu-toggle" type="button" aria-expanded="false" aria-controls="mh-navigation" data-mh-toggle>
            <span class="mh-menu-icon" aria-hidden="true"></span>
            <span><?php echo $language === 'de' ? 'Menü' : 'Menu';?></span>
        </button>
        <nav id="mh-navigation" class="mh-navigation" aria-label="<?php echo $language === 'de' ? 'Hauptnavigation' : 'Main navigation';?>" data-mh-navigation>
            <?php echo $mainNavigation !== '' ? $mainNavigation : '<p class="mh-menu-empty">'.($language === 'de' ? 'Noch keine Menüpunkte angelegt.' : 'No menu items yet.').'</p>'; ?>
        </nav>
    </div>
</header>

<section class="mh-hero"<?php echo $heroStyle;?> aria-labelledby="mh-page-title">
    <div class="mh-shell mh-hero-grid">
        <div class="mh-hero-copy">
            <p class="mh-eyebrow"><?php echo mh_escape($siteTitle);?></p>
            <h1 id="mh-page-title"><?php echo mh_escape($pageTitle);?></h1>
            <?php if ($pageDescription !== '') { ?><p class="mh-lead"><?php echo mh_escape($pageDescription);?></p><?php } ?>
        </div>
        <?php if ($hero !== '') { ?><div class="mh-hero-panel"><?php echo $hero;?></div><?php } ?>
    </div>
</section>

<main id="mh-main" class="mh-main" tabindex="-1">
    <div class="mh-shell">
        <?php if ($breadcrumbs !== '') { ?><nav class="mh-breadcrumbs" aria-label="<?php echo $language === 'de' ? 'Brotkrümelnavigation' : 'Breadcrumb';?>"><?php echo $breadcrumbs;?></nav><?php } ?>
        <div class="mh-content"><?php echo $content;?></div>
        <?php if ($teasers !== '') { ?><section class="mh-teasers" aria-label="<?php echo $language === 'de' ? 'Weitere Inhalte' : 'More content';?>"><?php echo $teasers;?></section><?php } ?>
    </div>
</main>

<footer class="mh-footer">
    <div class="mh-shell mh-footer-grid">
        <div><a class="mh-brand mh-brand-footer" href="<?php echo mh_escape(WB_URL.'/');?>"><span class="mh-brand-mark" aria-hidden="true"><span></span></span><span class="mh-brand-name"><?php echo mh_escape($siteTitle);?></span></a><p>&copy; <?php echo date('Y');?> <?php echo mh_escape($siteTitle);?></p></div>
        <?php if ($footer !== '') { ?><div class="mh-footer-content"><?php echo $footer;?></div><?php } ?>
    </div>
</footer>

<?php if (function_exists('register_frontend_modfiles_body')) { register_frontend_modfiles_body(); } ?>
<script src="<?php echo mh_escape(TEMPLATE_DIR.'/js/template.js');?>" defer></script>
</body>
</html>
