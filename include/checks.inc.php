<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 *
 */
function do_checks() {
    global $cfg;

    if (empty($cfg['db_api_token'])) {
        echo "WARNING: You must set in /config/config.inc.php db_api token with your themoviedb api key\n";
    }
    if (empty($cfg['jackett_key'])) {
        echo "WARNING: You must set in /config/config.inc.php jackett_key with your jackett api key\n";
    }
    if (empty($cfg['jackett_srv'])) {
        echo "WARNING: You must set in /config/config.inc.php jackett_srv with you http://ip:port hackett server\n";
    }
    if (empty($cfg['LANG'])) {
        echo "WARNING: You must set in /config/config.inc.php LANG (only supported es-ES/en-En) \n";
    }
    if (empty($cfg['MOVIES_PATH']) || !is_dir($cfg['MOVIES_PATH'])) {
        echo "WARNING: You must set in /config/config.inc.php MOVIES_PATH where your movies reside \n";
    }

    if (empty($cfg['SHOWS_PATH']) || !is_dir($cfg['MOVIES_PATH'])) {
        echo "WARNING: You must set in /config/config.inc.php SHOWS_PATH where your shows reside \n";
    }
    if (empty($cfg['REL_PATH'])) {
        echo "WARNING: You must set in /config/config.inc.php REL_PATH \n";
    }
    if (empty($cfg['TORRENT_FINISH_PATH']) || !is_dir($cfg['TORRENT_FINISH_PATH'])) {
        echo "WARNING: You must set in /config/config.inc.php TORRENT_FINISH_PATH to where torrent put your files (separate from temporal directory) \n";
    }
    if (empty($cfg['ROOT_PATH']) || !is_dir($cfg['ROOT_PATH'])) {
        echo "WARNING: You must set in /config/config.inc.php ROOT_PATH \n";
    }
    if (!is_writable($cfg['ROOT_PATH'] . '/cache')) {
        echo "WARNING: Your cache directory must be writable: {$cfg['ROOT_PATH']}/cache \n";
    }
    if (!is_writable($cfg['TORRENT_FINISH_PATH'])) {
        echo "WARNING: Your \"torrent finish path\" directory must be writable: {$cfg['TORRENT_FINISH_PATH']} \n";
    }
    if (!is_writable($cfg['MOVIES_PATH'])) {
        echo "WARNING: Your \"MOVIES_PATH \" directory must be writable: {$cfg['MOVIES_PATH']} \n";
    }
    if (!is_writable($cfg['SHOWS_PATH'])) {
        echo "WARNING: Your \"SHOWS_PATH \" directory must be writable: {$cfg['SHOWS_PATH'] }\n";
    }
    if (empty($cfg['trans_hostname'])) {
        echo "WARNING: You must set in /config/config.inc.php trans_hostname with the ip of transmission-daemon server ex: \"192.168.1.1\"\n";
    }
    if (empty($cfg['trans_port'])) {
        echo "WARNING: You must set in /config/config.inc.php trans_port with the port of transmission-daemon server ex: \"9091\"\n";
    }
}
