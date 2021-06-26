<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function loadUserPrefs() {
    global $cfg, $db, $user;

    if (empty($user) || empty($user['id'])) {
        return false;
    }
    $where['uid'] = ['value' => $user['id']];
    $results = $db->select('preferences', null, $where, 'LIMIT 1');

    if (valid_array($user_prefs = $db->fetchAll($results))) {
        foreach ($user_prefs as $pref) {
            if (!empty($pref['pref_name']) && isset($pref['pref_value'])) {
                $cfg[$pref['pref_name']] = $pref['pref_value'];
            }
        }
    }
}

function getPrefsItem(string $r_key, bool $system = false) {
    global $db, $user;

    ($system) ? $where['uid'] = ['value' => 0] : $where['uid'] = ['value' => $user['id']];

    $where['pref_name'] = ['value' => $r_key];
    $results = $db->select('preferences', null, $where, 'LIMIT 1');
    $user_prefs = $db->fetchAll($results);


    if (valid_array($user_prefs)) {
        return $user_prefs[0]['pref_value'];
    }
    return false;
}

/* UNUSED */

function getUidWithPref(string $r_key, string $r_value) {
    global $db;

    $where['pref_name'] = $r_key;
    $where['pref_value'] = $r_value;
    $results = $db->select('preferences', 'uid', $where, 'LIMIT 1');

    return $results ? $db->fetchAll($results) : false;
}

function getPrefValueByUid(int $id, string $r_key) {
    global $db;

    $where = ['pref_name' => ['value' => $r_key], 'uid' => ['value' => $id]];

    $results = $db->select('preferences', null, $where, 'LIMIT 1');
    $user_prefs = $db->fetchAll($results);
    if (valid_array($user_prefs)) {
        return $user_prefs[0]['pref_value'];
    }
    return false;
}

function setPrefsItem(string $key, string $value, bool $system = false) {
    global $db, $user;

    ($system) ? $uid = 0 : $uid = $user['id'];

    $newitem = [
        'uid' => $uid,
        'pref_name' => $key,
        'pref_value' => $value,
    ];

    $where['uid'] = ['value' => $uid];
    $where['pref_name'] = ['value' => $key];

    $result = $db->select('preferences', null, $where, 'LIMIT 1');
    $db->finalize($result);
    $prefs = $db->fetch($result);

    if (valid_array($prefs)) {
        if ($prefs['pref_value'] != $value) {
            $set['pref_value'] = $value;
            $db->update('preferences', $set, $where, 'LIMIT 1');
        }
    } else {
        $db->addItem('preferences', $newitem);
    }
}
