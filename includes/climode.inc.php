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
$prefs = new Preferences(0);
require_once('includes/trackerm-cli.inc.php');
require_once('cronjobs.inc.php');

