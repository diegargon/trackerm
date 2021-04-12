<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

require('includes/common.inc.php');


require('includes/User.class.php');

$user = new User();


require('includes/frontend.class.php');

$frontend = new FrontEnd();

require('includes/web.class.php');
$web = new Web($frontend);
require('includes/html.class.php');
$html = new Html();

require('includes/pages.inc.php');

