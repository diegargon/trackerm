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
        echo '<p><b>WARNING: You must set in /config/config.inc.php db_api token with your themoviedb api key</b></p>';
    }
    if (empty($cfg['jackett_key'])) {
        echo '<p><b>WARNING: You must set in /config/config.inc.php jackett_key with your jackett api key</b></p>';
    }
    if (empty($cfg['jackett_srv'])) {
        echo '<p><b>WARNING: You must set in /config/config.inc.php jackett_srv with you http://ip:port hackett server</b></p>';
    }
    if (empty($cfg['LANG'])) {
        echo '<p><b>WARNING: You must set in /config/config.inc.php LANG (only supported es-ES/en-En) </b></p>';
    }
    if (empty($cfg['MOVIES_PATH']) || !is_dir($cfg['MOVIES_PATH'])) {
        echo '<p><b>WARNING: You must set in /config/config.inc.php MOVIES_PATH where your movies reside </b></p>';
    }

    if (empty($cfg['SHOWS_PATH']) || !is_dir($cfg['MOVIES_PATH'])) {
        echo '<p><b>WARNING: You must set in /config/config.inc.php SHOWS_PATH where your shows reside </b></p>';
    }
    if (empty($cfg['REL_PATH'])) {
        echo '<p><b>WARNING: You must set in /config/config.inc.php REL_PATH </b></p>';
    }

    if (!is_writable($cfg['cache'])) {
        echo "<p><b>WARNING: Your cache directory must be writable:" . $cfg['cache'] . '</b></p>';
    }

    if (empty($cfg['trans_hostname'])) {
        echo '<p><b>WARNING: You must set in /config/config.inc.php trans_hostname with the ip of transmission-daemon server ex: "192.168.1.1"</b></p>';
    }

    if (empty($cfg['trans_port'])) {
        echo '<p><b>WARNING: You must set in /config/config.inc.php trans_port with the port of transmission-daemon server ex: "9091"</b></p>';
    }
}
