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

if (isset($_SESSION['uid']) && $_SESSION['uid'] > 0) {
    $user = get_profile($_SESSION['uid']);
    if ($user['sid'] != session_id()) {
        $user['id'] = -1;
    }
} else if (!empty($_COOKIE['uid']) && !empty($_COOKIE['sid'])) {
    $user = get_profile($_COOKIE['uid']);
    if (empty($user['sid']) || $user['sid'] != $_COOKIE['sid']) {
        $user = [];
        $user['id'] = -1;
    } else {
        update_session_id();
    }
} else {
    $user['id'] = -1;
}

