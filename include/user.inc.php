<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
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

    if ($user && !empty($user['id'])) {
        return $user['id'];
    } else {
        return false;
    }
}

function set_user($user_id) {
    global $user;

    $user['id'] = $user_id;

    $_SESSION['uid'] = $user['id'];
    setcookie("uid", $user['id'], time() + 3600000);
}
