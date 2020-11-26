<?php

if (1) {
    ini_set('error_reporting', E_ALL);
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

require('config/config.inc.php');
require('config/config.priv.php');

if (file_exists('/etc/trackerm.conf')) {
    require('/etc/trackerm.conf');
} else {
    echo '<br> The config file /etc/trackerm.conf is missed, please copy the default file in config.inc.php directory to /etc  and rename it as trackerm.conf and configure the settings';
    exit();
}

setlocale(LC_ALL, $cfg['LOCALE']);

require('include/logging.class.php');
global $log;
$log = new Log($cfg);

require('include/db.inc.php');
require('lang/' . $cfg['LANG'] . '/lang.inc.php');
require('include/checks.inc.php');
require('include/curl.inc.php');
require('include/file.utils.php');
require('include/transmission.class.php');
require('include/transmission.inc.php');
require('include/library-common.inc.php');
require('include/view.inc.php');
require('include/' . $cfg['search_db'] . '.inc.php');
require('include/mediadb.inc.php');
require('include/jackett.inc.php');
require('include/wanted.inc.php');
require __DIR__ . './../vendor/autoload.php';

global $db;
$db = new DB($cfg['ROOT_PATH'] . '/cache');

do_checks();

global $trans;
$trans = new TorrentServer($cfg);


