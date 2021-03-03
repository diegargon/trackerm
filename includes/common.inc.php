<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 *
 */
!defined('IN_WEB') ? exit : true;

if (!file_exists('/etc/trackerm.conf')) {
    echo '<br> The config file /etc/trackerm.conf is missed, please copy the default file (config/config.min.php) to /etc directory and rename it as trackerm.conf and configure the settings';
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

if (!empty($cfg['display_errors'])) {
    ini_set('error_reporting', E_ALL);
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

if (!empty($cfg['locale'])) {
    setlocale(LC_ALL, $cfg['locale']);
}

require_once('includes/logging.class.php');
global $log;
$log = new Log($cfg);

require_once('lang/' . $cfg['LANG'] . '/lang.inc.php');
require_once('includes/filters.class.php');
require_once('includes/curl.inc.php');
require_once('includes/file.utils.php');
require_once('includes/transmission.class.php');
require_once('includes/library-common.inc.php');
require_once('includes/ident-title-utils.inc.php');
require_once('includes/view.inc.php');
require_once('includes/' . $cfg['search_db'] . '.inc.php');
require_once('includes/mediadb.inc.php');
require_once('includes/jackett.inc.php');
require_once('includes/wanted.inc.php');
require_once('vendor/autoload.php');
require_once('includes/utils.inc.php');

require_once('includes/prefs.inc.php');
!empty($cfg['localplayer']) ? require_once('includes/localplayer.inc.php') : null;

global $trans;
$trans = new TorrentServer($cfg);
if ($trans->trans_conn == false) {
    $cfg['general_warn_msg'] = $LNG['L_ERR_TRANS_CONN'];
    $trans = false;
}
