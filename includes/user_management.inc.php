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
    global $LNG, $db, $cfg, $user;

    $status_msg = $LNG['L_USERS_MNGT_HELP'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user['isAdmin']) {
        if (isset($_POST['new_user'])) {
            if (!empty($new_user = Filter::postUsername('username'))) {
                if ($cfg['force_use_passwords']) {
                    if (!empty($password = Filter::postPassword('password'))) {
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
                    if (!empty($password = Filter::postPassword('password'))) {
                        $user_create['password'] = encrypt_password($password);
                    }
                    $_POST['is_admin'] == 1 ? $user_create['isAdmin'] = 1 : $user_create['isAdmin'] = 0;
                    $_POST['disable'] == 1 ? $user_create['disable'] = 1 : $user_create['disable'] = 0;
                    $_POST['hide_login'] == 1 ? $user_create['hide_login'] = 1 : $user_create['hide_login'] = 0;
                    $user_create['username'] = $new_user;
                    $db->upsertItemByField('users', $user_create, 'username');
                    $status_msg = $LNG['L_USER_CREATE_SUCCESS'];
                }
            } else {
                $status_msg = $LNG['L_USER_INCORRECT_USERNAME'];
            }
        }

        if (isset($_POST['delete_user']) && !empty($delete_user_id = Filter::postInt('delete_user_id'))) {
            $db->delete('users', ['id' => ['value' => $delete_user_id]]);
            $status_msg = $LNG['L_USER_DELETED'];
        }
    }
    $html['title'] = $LNG['L_USERS_MANAGEMENT'];
    $html['content'] = new_user() . show_users();
    $html['content'] .= '<p>' . $status_msg . '</p>';

    return $html;
}

function new_user() {
    global $frontend;

    return $frontend->getTpl('new_user');
}

function show_users() {
    global $frontend;

    $form_content = '';
    $users = get_profiles();

    foreach ($users as $_user) {
        if ($_user['id'] > 1) {
            $form_content .= $frontend->getTpl('delete_user', $_user);
        }
    }
    $form = Html::form(['id' => 'delete_user', 'method' => 'POST'], $form_content);

    return $form;
}

function encrypt_password($password) {
    return sha1($password);
}

function user_edit_profile() {
    global $frontend;

    $index_pref = getPrefsItem('index_page');
    $email_notify = getPrefsItem('email_notify');

    (empty($index_pref) || $index_pref == 'index') ? $tdata['index_selected'] = 'selected' : $tdata['index_selected'] = '';
    $index_pref == 'library' ? $tdata['library_selected'] = 'selected' : $tdata['library_selected'] = '';
    $index_pref == 'news' ? $tdata['news_selected'] = 'selected' : $tdata['news_selected'] = '';
    $index_pref == 'wanted' ? $tdata['wanted_selected'] = 'selected' : $tdata['wanted_selected'] = '';
    $index_pref == 'torrents' ? $tdata['torrents_selected'] = 'selected' : $tdata['torrents_selected'] = '';
    $index_pref == 'tmdb' ? $tdata['tmdb_selected'] = 'selected' : $tdata['tmdb_selected'] = '';
    $index_pref == 'transmission' ? $tdata['transmission_selected'] = 'selected' : $tdata['transmission_selected'] = '';
    $email_notify ? $tdata['email_checked'] = 'checked' : $tdata['email_checked'] = '';

    return $frontend->getTpl('user_prefs', $tdata);
}

function user_change_prefs() {
    global $user, $db, $LNG;

    $status_msg = null;

    if (isset($_POST['email']) && empty($_POST['email']) && !empty($user['email'])) {
        $user['email'] = '';
        $db->updateItemById('users', $user['id'], ['email' => '']);
        $status_msg .= $LNG['L_EMAIL_CHANGE_SUCESS'];
    } else if (!empty($_POST['email']) && ($email = Filter::postEmail('email'))) {
        if ($email && ($email != $user['email'])) {
            $user['email'] = $email;
            $db->updateItemById('users', $user['id'], ['email' => $email]);
            $status_msg .= $LNG['L_EMAIL_CHANGE_SUCESS'];
        } else if ($email != $user['email']) {
            $status_msg .= $LNG['L_EMAIL_INVALID'];
        }
    }
    $index_page = getPrefsItem('index_page');

    if (isset($_POST['index_page']) && !empty($_POST['index_page']) && ($_POST['index_page'] != $index_page)) {
        setPrefsItem('index_page', Filter::postString('index_page'));
    }
    if (isset($_POST['email_notify'])) {
        (!empty($_POST['email_notify'])) ? setPrefsItem('email_notify', 1) : setPrefsItem('email_notify', 0);
    }

    return $status_msg;
}

function user_change_password() {
    global $cfg, $user, $LNG, $db;

    if (isset($_POST['cur_password'])) {
        !empty(Filter::postPassword('cur_password')) ? $cur_password = Filter::postPassword('cur_password') : $cur_password = '';
    }
    if (isset($_POST['new_password'])) {
        !empty(Filter::postPassword('new_password')) ? $new_password = Filter::postPassword('new_password') : $new_password = '';
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
