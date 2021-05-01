<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
define('IN_WEB', true);

require_once('includes/usermode.inc.php');

//TODO move out: download link are in page view, torrents and new
if (!(empty($d_link = Filter::postUrl('download')))) {
    if (($pos = strpos($d_link, 'file=')) !== FALSE) {
        $jackett_filename = substr($d_link, $pos + 5);
        $jackett_filename = trim(str_replace('+', ' ', $jackett_filename));
    }
    !empty($trans) ? $trans_response = $trans->addUrl($d_link) : null;
    if (!empty($trans_response)) {
        foreach ($trans_response as $rkey => $rval) {
            $trans_db[0][$rkey] = $rval;
        }

        $wanted_db = [
            'tid' => $trans_db[0]['id'],
            'wanted_status' => 1,
            'jackett_filename' => !empty($jackett_filename) ? $jackett_filename : null,
            'hashString' => $trans_db[0]['hashString'],
            'themoviedb_id' => !empty($themoviedb_id) ? $wanted_db[0]['themoviedb_id'] = $themoviedb_id : null,
            'direct' => 1,
            'profile' => (int) $cfg['profile'],
        ];
        $db->addItemUniqField('wanted', $wanted_db, 'hashString');
    } else {
        $frontend->msgPage(['title' => 'L_ERROR', 'body' => 'L_SEE_ERROR_DETAILS']);
        return false;
    }
}

$web = new Web($frontend);
$web->render();
$db->close();
