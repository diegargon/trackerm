<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
require ('config/config.php');
require('includes/mysql.class.php');
global $db;

$db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
$db->connect();

require('includes/db_utils.inc.php');
setConfig();
require('lang/' . $cfg['lang'] . '/lang.inc.php');
require('includes/filters.class.php');
require('includes/file.utils.php');
require('includes/logging.class.php');
$log = new Log($cfg);
require('includes/utils.inc.php');
require('includes/vars.inc.php');
require('includes/func-common.inc.php');
require('includes/planets.class.php');
