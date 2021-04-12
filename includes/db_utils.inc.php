<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;
/*
  function rows_key_value($rows, $key, $value) {
  $array = [];
  foreach ($rows as $row) {
  $array[$row[$key]] = $row[$value];
  }

  return $array;
  }
 */

function getUniverseData() {
    global $db;

    $stmt = $db->select('universe', '*');
    $universe_rows = $db->fetchAll($stmt);
    return $universe_rows[0];
}

function setConfig() {
    global $db, $cfg;

    $stmt = $db->select('config', '*');
    $config_rows = $db->fetchAll($stmt);
    foreach ($config_rows as $config_row) {
        $cfg[$config_row['name']] = $config_row['value'];
    }
}
