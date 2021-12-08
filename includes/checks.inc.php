<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 *
 */
!defined('IN_WEB') ? exit : true;

function do_checks() {
    global $cfg;

    if (!file_exists('/etc/trackerm.conf')) {
        echo "ERROR: trackerm ins't configure please copy & rename config/config.min.php to /etc/trackerm.conf and fill all fields\n";
    }
    if (empty($cfg['db_api_token'])) {
        echo "ERROR: You must set in /etc/trackerm.conf db_api_token with your themoviedb api key\n";
        exit();
    }
    if (empty($cfg['jackett_key'])) {
        echo "ERROR: You must set in /etc/trackerm.conf  jackett_key with your jackett api key\n";
        exit();
    }
    if (empty($cfg['jackett_srv'])) {
        echo "ERROR: You must set in /etc/trackerm.conf jackett_srv with you http://ip:port hackett server\n";
        exit();
    }
    if (empty($cfg['LANG'])) {
        echo "ERROR: You must set in /etc/trackerm.conf  LANG (only supported languages es-ES/en-En) \n";
        exit();
    }
    if (empty($cfg['TORRENT_FINISH_PATH']) || !is_dir($cfg['TORRENT_FINISH_PATH'])) {
        echo "ERROR: You must set in /etc/trackerm.conf  TORRENT_FINISH_PATH to where torrent put your files (separate from temporal directory) \n";
        exit();
    }
    if (empty($cfg['ROOT_PATH']) || !is_dir($cfg['ROOT_PATH'])) {
        echo "ERROR: You must set in /etc/trackerm.conf p ROOT_PATH \n";
        exit();
    }
    if (!is_writable($cfg['ROOT_PATH'] . '/cache')) {
        echo "ERROR: Your cache directory must be writable: {$cfg['ROOT_PATH']}/cache \n";
        exit();
    }
    if (!is_writable($cfg['ROOT_PATH'] . '/cache/images')) {
        echo "ERROR: Your cache/images directory must be writable: {$cfg['ROOT_PATH']}/cache \n";
        exit();
    }
    if (!is_writable($cfg['ROOT_PATH'] . '/cache/log')) {
        echo "ERROR: Your cache/log directory must be writable: {$cfg['ROOT_PATH']}/cache \n";
        exit();
    }
    /*
      if (!is_writable($cfg['TORRENT_FINISH_PATH'])) {
      echo "WARNING: Your \"torrent finish path\" directory must be writable: {$cfg['TORRENT_FINISH_PATH']} \n";
      }
      if (!is_array($cfg['MOVIES_PATH']) && !is_writable($cfg['MOVIES_PATH'])) {
      echo "WARNING: Your \"MOVIES_PATH \" directory must be writable: {$cfg['MOVIES_PATH']} \n";
      }
      if (!is_array($cfg['MOVIES_PATH']) && !is_writable($cfg['SHOWS_PATH'])) {
      echo "WARNING: Your \"SHOWS_PATH \" directory must be writable: {$cfg['SHOWS_PATH'] }\n";
      }

      if (is_array($cfg['MOVIES_PATH'])) {
      foreach ($cfg['MOVIES_PATH'] as $mp_key => $movies_path) {
      if (!is_writable($movies_path)) {
      echo "WARNING: Your {$movies_path} directory must be writable: {$cfg['MOVIES_PATH'][$mp_key]} \n";
      }
      }
      }
      if (is_array($cfg['SHOWS_PATH'])) {
      foreach ($cfg['SHOWS_PATH'] as $sp_key => $shows_path) {
      if (!is_writable($shows_path)) {
      echo "WARNING: Your {$shows_path} directory must be writable: {$cfg['SHOWS_PATH'][$sp_key]} \n";
      }
      }
      }
     */
    if (empty($cfg['trans_hostname'])) {
        echo "ERROR: You must set in /config/config.inc.php trans_hostname with the ip of transmission-daemon server ex: \"192.168.1.1\"\n";
        exit();
    }
    if (empty($cfg['trans_port'])) {
        echo "ERROR: You must set in /config/config.inc.php trans_port with the port of transmission-daemon server ex: \"9091\"\n";
        exit();
    }
    if (file_exists($cfg['DB_FILE']) && !is_writable($cfg['DB_FILE'])) {
        echo "ERROR: db exists but is not writable\n";
        exit();
    }
}
