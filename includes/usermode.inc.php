<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

require_once('includes/common.inc.php');

session_start();

if (!empty($cfg['only_local_net']) && !is_local_ip()) {
    exit("ip not allowed");
}

require_once('includes/User.class.php');
$user = new Users();

$prefs = new Preferences($user->getId());
require_once('includes/html.class.php');
require_once('includes/pages.inc.php');
require_once('includes/html.common.php');
require_once('includes/library.inc.php');
require_once('includes/new_media.inc.php');
require_once('includes/user_management.inc.php');
require_once('includes/web.class.php');
require_once('includes/frontend.class.php');
require_once('includes/torrents.inc.php');
$frontend = new FrontEnd();
