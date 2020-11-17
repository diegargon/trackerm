<?php

if (1) {
    ini_set('error_reporting', E_ALL);
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

require('config/config.inc.php');

setlocale(LC_ALL, $cfg['LOCALE']);
$cfg['cache'] = $_SERVER['DOCUMENT_ROOT'] . $cfg['REL_PATH'] . $cfg['cache'];

require('include/db.inc.php');

require('lang/' . $cfg['LANG'] . '/lang.inc.php');
require('include/pages.inc.php');
require('include/curl.inc.php');
require('include/file.utils.php');
require('include/transmission.inc.php');
require('include/biblio-movies.inc.php');
require('include/biblio-shows.inc.php');
require('include/biblio-common.inc.php');
require('include/view.inc.php');
require('include/checks.inc.php');
require('include/' . $cfg['search_db'] . '.inc.php');
require('include/jackett.inc.php');
require('include/prefs.inc.php');
require('include/wanted.inc.php');

global $db;
$db = new DB($cfg['cache']);

loadPrefs();
