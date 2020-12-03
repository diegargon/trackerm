<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

session_start();

global $user;

if (isset($_GET['userid'])) {
    $user['uid'] = $filter->getInt('userid');
    $_SESSION['uid'] = $user['uid'];
    setcookie("uid", $user['uid'], time() + 3600000);
} else if (isset($_SESSION['uid'])) {
    $user['uid'] = $_SESSION['uid'];
} else if (isset($_COOKIE['uid'])) {
    $user['uid'] = $_COOKIE['uid'];
    $_SESSION["uid"] = $user['uid'];
} else {
    $user['uid'] = 1; //DEFAULT;
    $user['username'] = 'default';
}

$user_ = get_profile($user['uid']);

($user_) ? $user = $user_ : null;


