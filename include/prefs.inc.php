<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function loadPrefs() {
    global $db, $cfg;
    $username = 'default';
    $prefs = $db->getTableData('prefs-' . $username);

    if (!empty($prefs) && count($prefs) > 0) {
        foreach ($prefs as $key => $pref) {
            $cfg[$key] = $pref;
        }
    }
}

/*
  function setPrefsItems($items) {
  global $db;
  $db->addUniqKey('prefs', $items);
  }
 */

function setPrefsItem($key, $value) {
    global $db;
    $username = 'default';
    $item[$key] = $value;
    $db->addUniqKey('prefs-' . $username, $item);
}
