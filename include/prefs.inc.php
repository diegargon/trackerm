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
    global $cfg, $db, $user;

    $where['uid'] = ['value' => $user['id']];
    $results = $db->select('preferences', null, $where);

    if (($user_prefs = $db->fetchAll($results))) {
        foreach ($user_prefs as $pref) {
            if (!empty($pref['pref_name']) && !empty($pref['pref_value'])) {
                $cfg[$pref['pref_name']] = $pref['pref_value'];
            }
        }
    }
}

function setPrefsItem($key, $value) {
    global $db, $user;

    $newitem = [
        'uid' => $user['id'],
        'pref_name' => $key,
        'pref_value' => $value,
    ];

    $where['uid'] = ['value' => $user['id']];
    $where['pref_name'] = ['value' => $key];

    $result = $db->select('preferences', null, $where, 'LIMIT 1');

    $prefs = $db->fetch($result);

    if ($prefs) {

        if ($prefs['pref_value'] != $value) {
            $set['pref_value'] = $value;
            $db->update('preferences', $set, $where, 'LIMIT 1');
        }
    } else {
        $db->addItem('preferences', $newitem);
    }
}
