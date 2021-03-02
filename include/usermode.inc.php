<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

require_once('include/common.inc.php');

if (!empty($cfg['only_local_net']) && !is_local_ip()) {
    exit("ip not allowed");
}

require_once('include/user.inc.php');
require_once('include/session.inc.php');
loadUserPrefs();
require_once('libs/html.class.php');
require_once('include/pages.inc.php');
require_once('include/html.common.php');
require_once('include/library.inc.php');
require_once('include/new_media.inc.php');
require_once('include/user_management.inc.php');
