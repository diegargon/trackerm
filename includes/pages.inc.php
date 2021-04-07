<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function page_index() {
    global $cfg, $user, $L, $log, $frontend;

    $titems = [];
    $status_msg = '';

    // Config
    if (!empty($user->isAdmin())) {
        $tdata = [];
        $tdata['title'] = '';
        $tdata['content'] = Html::link(['class' => 'action_link'], 'index.php', $L['L_CONFIG'], ['page' => 'config']);
        $titems['col1'][] = $frontend->getTpl('home-item', $tdata);
    }
    // General Info
    $tdata = [];
    $tdata['content'] = '';
    $tdata['title'] = $L['L_IDENTIFIED'] . ': ' . strtoupper($user->username());

    if (Filter::getInt('edit_profile')) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            (isset($_POST['cur_password']) && isset($_POST['new_password'])) ? $status_msg .= user_change_password() . '<br/>' : null;
            $status_msg .= user_change_prefs();
        }
        $tdata['content'] .= user_edit_profile();
    } else {
        $tdata['content'] = Html::link(['class' => 'action_link'], '', $L['L_EDIT'], ['page' => 'index', 'edit_profile' => 1]);
    }

    $tdata['content'] .= Html::link(['class' => 'action_link'], '', $L['L_LOGOUT'], ['page' => 'logout']);
    $tdata['content'] .= $status_msg;
    $titems['col1'][] = $frontend->getTpl('home-item', $tdata);

    // User managament
    if (!empty($user->isAdmin())) {
        //$tdata = [];
        //$tdata = user_management();
        //$titems['col1'][] = $frontend->getTpl('home-item', $tdata);
    }

    // States Messages
    isset($_POST['clear_state']) ? $log->clearStateMsgs() : null;
    $tdata = [];
    $tdata['title'] = $L['L_STATE_MSG'];
    $clean_link = Html::input(['type' => 'submit', 'class' => 'submit_btn clear_btn', 'name' => 'clear_state', 'value' => $L['L_CLEAR']]);
    $tdata['content'] = Html::form(['method' => 'POST'], $clean_link);
    $state_msgs = $log->getStateMsgs();

    if (!empty($state_msgs) && (count($state_msgs) > 0)) {
        foreach ($state_msgs as $state_msg) {
            $state_msg['display_time'] = strftime("%d %h %X", strtotime($state_msg['created']));
            $tdata['content'] .= $frontend->getTpl('statemsg_item', $state_msg);
        }
    }
    $tdata['main_class'] = 'home_state_msg';
    $titems['col2'][] = $frontend->getTpl('home-item', $tdata);

    // LATEST info
    $tdata = [];
    $tdata['title'] = $L['L_NEWS'];
    $tdata['content'] = '';
    $latest_ary = getfile_ary('LATEST.' . $cfg['lang']);
    if (!empty($latest_ary)) {
        $latest_ary = array_slice($latest_ary, 2);
        foreach ($latest_ary as $latest) {
            $tdata['content'] .= Html::div(['class' => 'divBlock'], $latest);
        }
    }
    $tdata['main_class'] = 'home_news';
    $titems['col2'][] = $frontend->getTpl('home-item', $tdata);

    // Starting Info
    $tdata = [];
    $tdata['title'] = $L['L_STARTING'];
    $tdata['content'] = getfile('STARTING.' . $cfg['lang']);
    $tdata['main_class'] = 'home_starting';
    $titems['col2'][] = $frontend->getTpl('home-item', $tdata);

    //FIN
    $home = $frontend->getTpl('home-page', $titems);

    return $home;
}

function page_login() {
    global $cfg, $db, $user, $frontend;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $dologin = 0;

        $username = Filter::postUsername('username');
        $password = Filter::postUsername('password');
        if (!empty($username)) {
            if ($cfg['force_use_passwords'] && !empty($password)) {
                $dologin = 1;
            } else if (!$cfg['force_use_passwords']) {
                $dologin = 1;
            }
            if ($dologin) {
                $userid = check_user($username, $password);
                if ($userid) {
                    set_user($userid);
                    header("Location: {$cfg['REL_PATH']} ");
                    exit();
                } else {
                    $user = [];
                }
            }
        }
    }

    $tdata = [];
    $result = $db->select('users');
    $users = $db->fetchAll($result);
    $page = '';
    $tdata['profiles'] = '';
    foreach ($users as $_user) {
        if ($_user['disable'] != 1 && $_user['hide_login'] != 1) {
            $tdata['profiles'] .= $frontend->getTpl('profile_box', array_merge($tdata, $_user));
        }
    }
    $page .= $frontend->getTpl('login', $tdata);
    return $page;
}

function page_ports() {
    require('includes/ports.inc.php');

    return show_port();
}

function page_ships() {
    global $user;

    $status_msg = '';

    require('includes/ships.inc.php');

    if (!empty(Filter::postInt('ship_id'))) {
        $ship_id = Filter::postInt('ship_id');
    }

    (!empty($_POST)) ? $status_msg .= ship_control_exec() : null;

    if (empty($ship_id)) {
        $ships = $user->getShips();
        if (valid_array($ships)) {
            $ship = $ships[0];
        } else {
            return false;
        }
    } else {
        $ship = $user->getShipById($ship_id);
    }
    return show_control_ships($ship, $status_msg);
}

function page_planets() {
    require('includes/planets.inc.php');

    return show_user_planets();
}

function page_research() {

    return '';
}

function page_production() {
    return '';
}

function page_logout() {
    global $cfg;

    $_SESSION['uid'] = 0;
    ($_COOKIE) ? setcookie("uid", null, -1) : null;
    session_regenerate_id();
    session_destroy();
    header("Location: ?page=index");
    exit(0);
}
