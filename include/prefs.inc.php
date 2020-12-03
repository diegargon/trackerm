<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function loadPrefs() {
    global $cfg, $newdb, $user;

    $where['uid'] = ['value' => $user['id']];
    $results = $newdb->select('preferences', null, $where);

    if (($user_prefs = $newdb->fetchAll($results))) {
        foreach ($user_prefs as $pref) {
            if (!empty($pref['pref_name']) && !empty($pref['pref_value'])) {
                $cfg[$pref['pref_name']] = $pref['pref_value'];
            }
        }
    }
}

function setPrefsItem($key, $value) {
    global $newdb, $user;

    $newitem = [
        'uid' => $user['id'],
        'pref_name' => $key,
        'pref_value' => $value,
    ];

    $where['uid'] = ['value' => $user['id']];
    $where['pref_name'] = ['value' => $key];

    $result = $newdb->select('preferences', null, $where, 'LIMIT 1');

    $prefs = $newdb->fetch($result);

    if ($prefs) {

        if ($prefs['pref_value'] != $value) {
            $set['pref_value'] = $value;
            $newdb->update('preferences', $set, $where, 'LIMIT 1');
        }
    } else {
        $newdb->addItem('preferences', $newitem);
    }
}
