<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function user_management() {
    global $LNG, $filter, $db, $cfg;

    $status_msg = $LNG['L_USERS_MNGT_HELP'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['new_user']) && !empty($new_user = $filter->postUsername('username'))) {
            if ($cfg['force_use_passwords']) {
                if (!empty($password = $filter->postPassword('password'))) {
                    $user_create['username'] = $new_user;
                    $user_create['password'] = encrypt_password($password);
                    //$_POST['is_admin'] == 1 ? $user_create['is_admin'] = 1 : $user_create['us_admin'] = 0;
                    $db->upsertItemByField('users', $user_create, 'username');
                    $status_msg = $LNG['L_USER_CREATE_SUCCESS'];
                } else {
                    $status_msg = $LNG['L_USER_INCORRECT_PASSWORD'];
                }
            } else {
                if (!empty($password = $filter->postPassword('password'))) {
                    $user_create['password'] = encrypt_password($password);
                }
                //$_POST['is_admin'] == 1 ? $user_create['is_admin'] = 1 : $user_create['us_admin'] = 0;
                $user_create['username'] = $new_user;
                $db->upsertItemByField('users', $user_create, 'username');
                $status_msg = $LNG['L_USER_CREATE_SUCCESS'];
            }
        } else {
            $status_msg = $LNG['L_USER_INCORRECT_USERNAME'];
        }
        if (isset($_POST['delete_user']) && !empty($delete_user_id = $filter->postInt('delete_user_id'))) {
            $db->delete('users', ['id' => ['value' => $delete_user_id]]);
        }
    }

    $html['title'] = $LNG['L_USERS_MANAGEMENT'];
    $html['content'] = new_user();
    $html['content'] .= show_users();
    $html['content'] .= '<p>' . $status_msg . '</p>';

    return $html;
}

function new_user() {
    global $LNG;

    $html = '<form id = "new_user" method = "POST" >';
    $html .= '<span>' . $LNG['L_USERNAME'] . '<span><input size = "8" type = "text" name = "username" value = ""/>';
    $html .= '<span>' . $LNG['L_PASSWORD'] . '<span><input size = "8" type = "password" name = "password" value = ""/>';
    $html .= '<input type = "hidden" name = "is_admin" value = "0">';
    //$html .= '<label for = "is_admin">' . $LNG['L_ADMIN'] . ' </label>';
    //$html .= '<input id = "is_admin" type = "checkbox" name = "is_admin" value = "1">';
    $html .= '<input class = "submit_btn" type = "submit" name = "new_user" value = "' . $LNG['L_CREATE'] . '/' . $LNG['L_MODIFY'] . '"/>';
    $html .= '</form>';

    return $html;
}

function show_users() {
    global $LNG;
    $html = '<form id = "delete_user" method = "POST">';
    $users = get_profiles();
    foreach ($users as $user) {
        if ($user['id'] > 1) {
            $html .= '<span>' . $user['username'] . '<span>';
            $html .= '<input type = "hidden" name = "delete_user_id" value = "' . $user['id'] . '"/>';
            $html .= '<input class = "submit_btn" type = "submit" name = "delete_user" value = "' . $LNG['L_DELETE'] . '"/>';
        }
    }

    $html .= '</form>

    ';

    return $html;
}

function encrypt_password($password) {

    return password_hash($password, PASSWORD_DEFAULT);
}
