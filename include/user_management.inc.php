<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function user_management() {
    global $LNG, $filter, $db, $cfg, $user;

    $status_msg = $LNG['L_USERS_MNGT_HELP'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user['isAdmin']) {
        if (isset($_POST['new_user']) && !empty($new_user = $filter->postUsername('username'))) {
            if ($cfg['force_use_passwords']) {
                if (!empty($password = $filter->postPassword('password'))) {
                    $user_create['username'] = $new_user;
                    $user_create['password'] = encrypt_password($password);
                    $_POST['is_admin'] == 1 ? $user_create['isAdmin'] = 1 : $user_create['isAdmin'] = 0;
                    $_POST['disable'] == 1 ? $user_create['disable'] = 1 : $user_create['disable'] = 0;
                    $_POST['hide_login'] == 1 ? $user_create['hide_login'] = 1 : $user_create['hide_login'] = 0;
                    $db->upsertItemByField('users', $user_create, 'username');
                    $status_msg = $LNG['L_USER_CREATE_SUCCESS'];
                } else {
                    $status_msg = $LNG['L_USER_INCORRECT_PASSWORD'];
                }
            } else {
                if (!empty($password = $filter->postPassword('password'))) {
                    $user_create['password'] = encrypt_password($password);
                }
                $_POST['is_admin'] == 1 ? $user_create['isAdmin'] = 1 : $user_create['isAdmin'] = 0;
                $_POST['disable'] == 1 ? $user_create['disable'] = 1 : $user_create['disable'] = 0;
                $_POST['hide_login'] == 1 ? $user_create['hide_login'] = 1 : $user_create['hide_login'] = 0;
                $user_create['username'] = $new_user;
                $db->upsertItemByField('users', $user_create, 'username');
                $status_msg = $LNG['L_USER_CREATE_SUCCESS'];
            }
        } else if (isset($_POST['delete_user']) && !empty($delete_user_id = $filter->postInt('delete_user_id'))) {
            $db->delete('users', ['id' => ['value' => $delete_user_id]]);
            $status_msg = $LNG['L_USER_DELETED'];
        } else {
            $status_msg = $LNG['L_USER_INCORRECT_USERNAME'];
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

    $html = '<div class="new_user_box">';
    $html .= '<form id="new_user" method="POST" >';
    $html .= '<span>' . $LNG['L_USERNAME'] . '<span><input size="8" type="text" name="username" value=""/>';
    $html .= '<span>' . $LNG['L_PASSWORD'] . '<span><input size="8" type="password" name="password" value=""/>';
    //Admin
    $html .= '<input type="hidden" name="is_admin" value="0">';
    $html .= '<label for="is_admin">' . $LNG['L_ADMIN'] . ' </label>';
    $html .= '<input id="is_admin" type="checkbox" name="is_admin" value="1">';
    //disable
    $html .= '<input type="hidden" name="disable" value = "0">';
    $html .= '<label for="disable">' . $LNG['L_DISABLED'] . ' </label>';
    $html .= '<input id="disable" type="checkbox" name="disable" value="1">';
    //hide login
    $html .= '<input type="hidden" name="hide_login" value="0">';
    $html .= '<label for="hide_login">' . $LNG['L_HIDE_LOGIN'] . ' </label>';
    $html .= '<input id="hide_login" type="checkbox" name="hide_login" value="1">';

    //Submit
    $html .= '<input class="submit_btn" type="submit" name="new_user" value="' . $LNG['L_CREATE'] . '/' . $LNG['L_MODIFY'] . '"/>';
    $html .= '</form>';
    $html .= '</div>';

    return $html;
}

function show_users() {
    global $LNG;

    $html = '<div class="delete_user_box">';
    $html .= '<form id = "delete_user" method = "POST">';
    $users = get_profiles();
    foreach ($users as $user) {
        if ($user['id'] > 1) {
            $html .= '<div class="delete_user"><input type="hidden" name="delete_user_id" value="' . $user['id'] . '"/>';
            $html .= '<input class="submit_btn" onclick="return confirm(\'Are you sure?\')" type="submit" name="delete_user" value="' . $LNG['L_DELETE'] . '"/>';
            $html .= '<span>' . $user['username'] . '<span></div>';
        }
    }
    $html .= '</form>';
    $html .= '</div>';

    return $html;
}

function encrypt_password($password) {

    return sha1($password);
}

function user_edit_profile() {
    global $LNG, $user;

    $index_pref = getPrefsItem('index_page');
    $email_notify = getPrefsItem('email_notify');

    (empty($index_pref) || $index_pref == 'index') ? $index_selected = 'selected' : $index_selected = '';
    (!empty($index_pref) && $index_pref == 'library') ? $library_selected = 'selected' : $library_selected = '';
    (!empty($index_pref) && $index_pref == 'news') ? $news_selected = 'selected' : $news_selected = '';
    (!empty($index_pref) && $index_pref == 'wanted') ? $wanted_selected = 'selected' : $wanted_selected = '';
    (!empty($index_pref) && $index_pref == 'torrents') ? $torrents_selected = 'selected' : $torrents_selected = '';
    (!empty($index_pref) && $index_pref == 'tmdb') ? $tmdb_selected = 'selected' : $tmdb_selected = '';
    (!empty($index_pref) && $index_pref == 'transmission') ? $transmission_selected = 'selected' : $transmission_selected = '';
    ($email_notify) ? $email_checked = 'checked' : $email_checked = '';

    $html = '<span>' . $LNG['L_PASSWORD'] . '</span><input size="8" type="text" name="cur_password" value=""/>';
    $html .= '<span>' . $LNG['L_NEW_PASSWORD'] . '</span><input size="8" type="text" name="new_password" value=""/>';
    $html .= '<span>' . $LNG['L_EMAIL_NOTIFY'] . ' </span>';
    $html .= '<input type="hidden" name="email_notify" value="0"/>';
    $html .= '<input type="checkbox" ' . $email_checked . ' name="email_notify" value="1"/>';
    $html .= '<span>' . $LNG['L_EMAIL'] . '</span><input size="15" type="text" name="email" value="' . $user['email'] . '"/>';
    $html .= '<br/><span>' . $LNG['L_INDEX_SELECT'] . '</span>';
    $html .= '<select name="index_page">';
    $html .= '<option ' . $index_selected . ' value="index">index</option>';
    $html .= '<option ' . $library_selected . ' value="library">' . $LNG['L_LIBRARY'] . '</option>';
    $html .= '<option ' . $news_selected . ' value="news">' . $LNG['L_NEWS'] . '</option>';
    $html .= '<option ' . $wanted_selected . ' value="wanted">' . $LNG['L_WANTED'] . '</option>';
    $html .= '<option ' . $torrents_selected . ' value="torrents">' . $LNG['L_TORRENTS'] . '</option>';
    $html .= '<option ' . $tmdb_selected . ' value="tmdb">tmdb</option>';
    $html .= '<option ' . $transmission_selected . ' value="transmission">Transmission</option>';
    $html .= '</select>';
    //$html .= '</form>';

    return $html . '<br/>';
}

function user_change_prefs() {
    global $filter, $user, $db, $LNG;

    $status_msg = null;

    if (empty($_POST['email']) && !empty($user['email'])) {
        $user['email'] = '';
        $db->updateItemById('users', $user['id'], ['email' => '']);
        $status_msg .= $LNG['L_EMAIL_CHANGE_SUCESS'];
    } else if (($email = $filter->postEmail('email'))) {
        if ($email && ($email != $user['email'])) {
            $user['email'] = $email;
            $db->updateItemById('users', $user['id'], ['email' => $email]);
            $status_msg .= $LNG['L_EMAIL_CHANGE_SUCESS'];
        } else if ($email != $user['email']) {
            $status_msg .= $LNG['L_EMAIL_INVALID'];
        }
    }
    $index_page = getPrefsItem('index_page');
    if (!empty($_POST['index_page']) && ($_POST['index_page'] != $index_page)) {
        setPrefsItem('index_page', $filter->postString('index_page'));
    }

    (!empty($_POST['email_notify'])) ? setPrefsItem('email_notify', 1) : setPrefsItem('email_notify', 0);

    return $status_msg;
}

function user_change_password() {
    global $cfg, $user, $LNG, $filter, $db;

    if (isset($_POST['cur_password'])) {
        !empty($filter->postPassword('cur_password')) ? $cur_password = $filter->postPassword('cur_password') : $cur_password = '';
    }
    if (isset($_POST['new_password'])) {
        !empty($filter->postPassword('new_password')) ? $new_password = $filter->postPassword('new_password') : $new_password = '';
    }

    if ($cfg['force_use_passwords'] && empty($new_password)) {
        return $LNG['L_PASSWORD_CANT_EMPTY'];
    }

    if (!empty($user['password']) && empty($cur_password)) {
        return $LNG['L_PASSWORD_INCORRECT'];
    }

    if (empty($user['password']) && empty($new_password)) {
        return false;
    }
    if (!empty($user['password']) && (encrypt_password($new_password) == $user['password'])) {
        return $LNG['L_PASSWORD_EQUAL'];
    }
    if (empty($user['password']) && empty($cur_password) && !empty($new_password)) {
        !empty($new_password) ? $new_encrypted_password = encrypt_password($new_password) : $new_encrypted_password = '';
        $db->updateItemById('users', $user['id'], ['password' => $new_encrypted_password]);
        return $LNG['L_PASSWORD_CHANGE_SUCESS'];
    }
    if (!empty($user['password'])) {
        $old_encrypted_password = encrypt_password($cur_password);
        if ($user['password'] == $old_encrypted_password) {
            !empty($new_password) ? $new_encrypted_password = encrypt_password($new_password) : $new_encrypted_password = '';
            $db->updateItemById('users', $user['id'], ['password' => $new_encrypted_password]);
            return $LNG['L_PASSWORD_CHANGE_SUCESS'];
        } else {
            return $LNG['L_PASSWORD_INCORRECT'];
        }
    }

    return $LNG['L_PASSWORD_UNKNOWN_ERROR'];
}
