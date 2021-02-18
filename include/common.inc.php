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

if (1) {
    ini_set('error_reporting', E_ALL);
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

if (!file_exists('/etc/trackerm.conf')) {
    echo '<br> The config file /etc/trackerm.conf is missed, please copy the default file (config/config.min.php) to /etc directory and rename it as trackerm.conf and configure the settings';
    exit();
}
require('/etc/trackerm.conf');

require_once('config/config.priv.php');

require_once('include/checks.inc.php');
do_checks();

require_once('include/db.class.php');
global $db;
$db = new DB($cfg['DB_FILE']);
$db->connect();

require_once('include/config.class.php');
$config = new Config();

setlocale(LC_ALL, $cfg['locale']);

require_once('include/logging.class.php');
global $log;
$log = new Log($cfg);

require_once('lang/' . $cfg['LANG'] . '/lang.inc.php');
require_once('include/filters.class.php');
global $filter;
$filter = new Filter();

require_once('include/curl.inc.php');
require_once('include/file.utils.php');
require_once('include/transmission.class.php');
require_once('include/library-common.inc.php');
require_once('include/ident-title-utils.inc.php');
require_once('include/view.inc.php');
require_once('include/' . $cfg['search_db'] . '.inc.php');
require_once('include/mediadb.inc.php');
require_once('include/jackett.inc.php');
require_once('include/wanted.inc.php');
require_once('vendor/autoload.php');
require_once('include/utils.inc.php');

require_once('include/prefs.inc.php');

global $trans;
$trans = new TorrentServer($cfg);
if ($trans->trans_conn == false) {
    $cfg['general_warn_msg'] = $LNG['L_ERR_TRANS_CONN'];
    $trans = false;
}
