<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

require_once('includes/common.inc.php');

session_name('trackerm');
session_start();

if (!empty($cfg['only_local_net']) && !is_local_ip()) {
    exit("ip not allowed");
}

require_once('includes/User.class.php');
$user = new Users();

$prefs = new Preferences($user->getId());
require_once('includes/pages.inc.php');
require_once('includes/html.common.php');
require_once('includes/library.inc.php');
require_once('includes/new_media.inc.php');
require_once('includes/user_management.inc.php');
require_once('includes/Web.php');
require_once('includes/BackEnd.php');
require_once('includes/FrontEnd.php');
require_once('includes/torrents.inc.php');
require_once('includes/strings.inc.php');
