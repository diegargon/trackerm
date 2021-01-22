<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

session_start();

global $user;

if (isset($_GET['userid'])) {
    $user['id'] = $filter->getInt('userid');
    $_SESSION['uid'] = $user['id'];
    setcookie("uid", $user['id'], time() + 3600000);
} else if (isset($_SESSION['uid'])) {
    $user['id'] = $_SESSION['uid'];
} else if (isset($_COOKIE['uid'])) {
    $user['id'] = $_COOKIE['uid'];
    $_SESSION["uid"] = $user['id'];
} else {
    $user['id'] = 0;
    $user['username'] = $LNG['L_ANONYMOUS'];
}

$user_ = get_profile($user['id']);

($user_) ? $user = $user_ : null;


