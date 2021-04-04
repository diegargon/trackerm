<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function get_profiles() {
    global $db;

    $results = $db->select('users');

    return $db->fetchAll($results);
}

function get_profile($uid) {
    global $db;

    return $db->getItemById('users', $uid);
}

function check_user($username, $password) {
    global $db;

    $user = $db->getItemByField('users', 'username', $username);

    if (!valid_array($user) || empty($user['id']) || $user['disable'] == 1) {
        return false;
    }
    !empty($password) ? $password_hashed = encrypt_password($password) : $password_hashed = '';

    if (($user['password'] == $password_hashed)) {
        $ip = get_user_ip();
        if ($user['ip'] != $ip) {
            $db->updateItemById('users', $user['id'], ['ip' => $ip]);
        }
        return $user['id'];
    }
    return false;
}

function setUser($user_id) {
    global $user, $cfg;

    $_SESSION['uid'] = $user['id'] = $user_id;

    if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
        setcookie('uid', $user['id'], [
            'expires' => time() + $cfg['sid_expire'],
            'secure' => true,
            'samesite' => 'lax',
        ]);
    } else {
        setcookie("uid", $user['id'], time() + $cfg['sid_expire']);
    }
    update_session_id();
}
