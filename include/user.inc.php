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

    if ($user['disable'] == 1) {
        return false;
    }
    if ($user && !empty($user['id'])) {
        !empty($password) ? $password_hashed = encrypt_password($password) : $password_hashed = '';

        if ($user['password'] == $password_hashed) {
            $ip = get_user_ip();
            if ($user['ip'] != $ip) {
                $db->updateItemById('users', $user['id'], ['ip' => $ip]);
            }
            return $user['id'];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function set_user($user_id) {
    global $user;

    $user['id'] = $user_id;
    $_SESSION['uid'] = $user['id'];
    setcookie("uid", $user['id'], time() + 3600000);
    setcookie("sid", session_id(), time() + 3600000);
    update_session_id();
}

function update_session_id() {
    global $db, $user;

    setcookie("sid", session_id(), time() + 3600000);
    $db->updateItemById('users', $user['id'], ['sid' => session_id()]);
}
