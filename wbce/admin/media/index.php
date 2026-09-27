<?php
/**
 * WBCE CMS
 * Way Better Content Editing.
 * Visit https://wbce.org to learn more and to join the community.
 *
 * @copyright Ryan Djurovich (2004-2009)
 * @copyright WebsiteBaker Org. e.V. (2009-2015)
 * @copyright WBCE Project (2015-)
 * @license GNU GPL2 (or any later version)
 */

// Print admin header
require('../../config.php');
require_once(WB_PATH . '/framework/Admin.php');
$admin = new admin('Media', 'media');

// elFinder renders its own document instead of using the regular admin page
// template. Keep the section heading in the surrounding admin layout so the
// active backend theme can style it consistently with the other main areas.
$mediaTitle = isset($MENU['MEDIA']) && is_string($MENU['MEDIA'])
    ? $MENU['MEDIA']
    : 'Media';
$mediaDescription = (defined('LANGUAGE') && strtoupper((string) LANGUAGE) === 'DE')
    ? 'Dateien und Verzeichnisse zentral verwalten.'
    : 'Manage files and folders centrally.';
echo '<div class="wbce-section-shell wbce-media-shell">'
    . '<header class="wbce-section-hero">'
    . '<i class="fa fa-image" aria-hidden="true"></i>'
    . '<div><h2>' . htmlspecialchars($mediaTitle, ENT_QUOTES, 'UTF-8') . '</h2>'
    . '<p>' . htmlspecialchars($mediaDescription, ENT_QUOTES, 'UTF-8') . '</p></div>'
    . '</header>'
    . '</div>';

$noPage = false;
$modulePath = WB_PATH . '/modules/elfinder/';
include('../../modules/elfinder/tool.php');

$admin->print_footer();
