<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function user_management() {
    global $LNG, $db, $cfg, $user;

    $status_msg = $LNG['L_USERS_MNGT_HELP'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user->isAdmin()) {
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

    $tpl = [
        'name' => 'admin_management',
        'tpl_pri' => 8,
        'tpl_file' => 'home-item',
        'tpl_place' => 'homepage',
        'tpl_place_var' => 'col1',
        'tpl_vars' => [
            'title' => $LNG ['L_USERS_MANAGEMENT'],
            'status_msg' => $status_msg,
        ]
    ];

    return $tpl;
}

function new_user() {

    $tpl = [
        'name' => 'newuser',
        'tpl_pri' => 9,
        'tpl_file' => 'new_user',
        'tpl_place' => 'admin_management',
        'tpl_place_var' => 'content',
    ];

    return $tpl;
}

function show_users() {
    global $user;

    $users = $user->getProfiles();

    $users_tpl = [
        'name' => 'showusers',
        'tpl_pri' => 9,
        'tpl_file' => 'delete_user',
        'tpl_place' => 'admin_management',
        'tpl_place_var' => 'content',
        'tpl_vars' => $users,
    ];

    return $users_tpl;
}

function encrypt_password($password) {
    return sha1($password);
}

function user_edit_profile() {
    global $prefs;

    $tpl_vars = [];
    $index_pref = $prefs->getPrefsItem('index_page');
    $email_notify = $prefs->getPrefsItem('email_notify');

    (empty($index_pref) || $index_pref == 'index') ? $tpl_vars['index_selected'] = 'selected' : $tpl_vars['index_selected'] = '';
    $index_pref == 'library' ? $tpl_vars['library_selected'] = 'selected' : $tpl_vars['library_selected'] = '';
    $index_pref == 'news' ? $tpl_vars['news_selected'] = 'selected' : $tpl_vars['news_selected'] = '';
    $index_pref == 'wanted' ? $tpl_vars['wanted_selected'] = 'selected' : $tpl_vars['wanted_selected'] = '';
    $index_pref == 'torrents' ? $tpl_vars['torrents_selected'] = 'selected' : $tpl_vars['torrents_selected'] = '';
    $index_pref == 'tmdb' ? $tpl_vars['tmdb_selected'] = 'selected' : $tpl_vars['tmdb_selected'] = '';
    $index_pref == 'transmission' ? $tpl_vars['transmission_selected'] = 'selected' : $tpl_vars['transmission_selected'] = '';
    $email_notify ? $tpl_vars['email_checked'] = 'checked' : $tpl_vars['email_checked'] = '';

    $tpl = [
        'name' => 'edit_profile',
        'tpl_pri' => 20,
        'tpl_file' => 'user_prefs',
        'tpl_place' => 'user_profile',
        'tpl_place_var' => 'pre_content',
        'tpl_vars' => $tpl_vars,
    ];

    return $tpl;
}

function user_change_prefs() {
    global $user, $db, $LNG, $prefs;

    $status_msg = null;

    if (isset($_POST['email']) && empty($_POST['email']) && !empty($user->getEmail())) {
        $db->updateItemById('users', $user->getId(), ['email' => '']);
        $status_msg .= $LNG['L_EMAIL_CHANGE_SUCESS'];
    } else if (!empty($_POST['email']) && ($email = Filter::postEmail('email'))) {
        if ($email && ($email != $user->getEmail())) {
            $db->updateItemById('users', $user->getId(), ['email' => $email]);
            $status_msg .= $LNG['L_EMAIL_CHANGE_SUCESS'];
        } else if ($email != $user->getEmail()) {
            $status_msg .= $LNG['L_EMAIL_INVALID'];
        }
    }
    $index_page = $prefs->getPrefsItem('index_page');

    if (isset($_POST['index_page']) && !empty($_POST['index_page']) && ($_POST['index_page'] != $index_page)) {
        $prefs->setPrefsItem('index_page', Filter::postString('index_page'));
    }
    if (isset($_POST['email_notify'])) {
        (!empty($_POST['email_notify'])) ? $prefs->setPrefsItem('email_notify', 1) : $prefs->setPrefsItem('email_notify', 0);
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
    if (!empty($user->getPassword()) && empty($cur_password)) {
        return $LNG['L_PASSWORD_INCORRECT'];
    }
    if (empty($user->getPassword()) && empty($new_password)) {
        return false;
    }
    if (!empty($user->getPassword()) && (encrypt_password($new_password) == $user->getPassword())) {
        return $LNG['L_PASSWORD_EQUAL'];
    }
    if (empty($user->getPassword()) && empty($cur_password) && !empty($new_password)) {
        !empty($new_password) ? $new_encrypted_password = encrypt_password($new_password) : $new_encrypted_password = '';
        $db->updateItemById('users', $user->getId(), ['password' => $new_encrypted_password]);
        return $LNG['L_PASSWORD_CHANGE_SUCESS'];
    }
    if (!empty($user->getPassword())) {
        $old_encrypted_password = encrypt_password($cur_password);
        if ($user->getPassword() == $old_encrypted_password) {
            !empty($new_password) ? $new_encrypted_password = encrypt_password($new_password) : $new_encrypted_password = '';
            $db->updateItemById('users', $user->getId(), ['password' => $new_encrypted_password]);
            return $LNG['L_PASSWORD_CHANGE_SUCESS'];
        } else {
            return $LNG['L_PASSWORD_INCORRECT'];
        }
    }

    return $LNG['L_PASSWORD_UNKNOWN_ERROR'];
}
