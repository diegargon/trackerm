<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 *
 */
!defined('IN_WEB') ? exit : true;

if (!file_exists('/etc/trackerm.conf')) {
    echo '<br> Missed config file /etc/trackerm.conf, please copy the default file (config/config.min.php) to /etc directory and rename it as trackerm.conf and configure the settings';
    exit();
}
require('/etc/trackerm.conf');

require_once('config/config.priv.php');

require_once('includes/checks.inc.php');
do_checks();

require_once('includes/db.class.php');
global $db;
$db = new DB($cfg['DB_FILE']);
$db->connect();

require_once('includes/config.class.php');
$config = new Config();

$db->checkNeedUpgrade();

if (!empty($cfg['display_errors'])) {
    ini_set('error_reporting', E_ALL);
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

if (!empty($cfg['locale'])) {
    setlocale(LC_ALL, $cfg['locale']);
}

require_once('includes/Logging.php');
global $log;
$log = new Logging($cfg);

require_once('lang/en-EN/lang.inc.php');
if (empty($cfg['LANG']) || $cfg['LANG'] != 'en-EN') {
    $lang_file = 'lang/' . $cfg['LANG'] . '/lang.inc.php';
    file_exists($lang_file) ? require_once($lang_file) : null;
}

require_once('includes/time.inc.php');
require_once('includes/filters.class.php');
require_once('includes/curl.inc.php');
require_once('includes/file.utils.php');
//require_once('includes/transmission.class.php');
require_once('class/Transmission.php');
require_once('includes/identify.inc.php');
require_once('includes/library-common.inc.php');
require_once('includes/ident-title-utils.inc.php');
require_once('includes/view.inc.php');
require_once('includes/' . $cfg['search_db'] . '.inc.php');
require_once('includes/mediadb.inc.php');
require_once('includes/jackett.inc.php');
require_once('includes/wanted.inc.php');
//require_once('vendor/autoload.php');
require_once('includes/utils.inc.php');
require_once('includes/Preferences.class.php');

!empty($cfg['localplayer']) ? require_once('includes/localplayer.inc.php') : null;

global $trans;
$trans = new Transmission($cfg);
if ($trans->valid_conn !== true) {
    $cfg['general_warn_msg'] = $LNG['L_ERR_TRANS_CONN'] . " {$trans->valid_conn}";
    $trans = false;
}
