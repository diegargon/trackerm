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

if (!empty($cfg['only_local_net']) && !is_local_ip()) {
    exit("ip not allowed");
}

require_once('includes/user.inc.php');
require_once('includes/session.inc.php');
loadUserPrefs();
require_once('includes/html.class.php');
require_once('includes/pages.inc.php');
require_once('includes/html.common.php');
require_once('includes/library.inc.php');
require_once('includes/new_media.inc.php');
require_once('includes/user_management.inc.php');
