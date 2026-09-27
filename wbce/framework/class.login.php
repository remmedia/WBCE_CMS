<?php
/**
 * Compatibility adapter for legacy extensions that include
 * framework/class.login.php directly.
 *
 * The canonical Login implementation lives in Login.php. Keeping this
 * file avoids breaking older extensions while preventing two competing
 * Login class implementations from being loaded.
 */

defined('WB_PATH') or die('No direct access allowed');

require_once __DIR__ . '/Login.php';
