<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
define('IN_WEB', true);

require_once('include/usermode.inc.php');

$req_page = $filter->getString('page');
$body = getMenu();
$footer = getFooter();

if (!empty($_GET['download'])) {
    $d_link = $_GET['download'];

    $trans_response = $trans->addUrl($d_link);

    foreach ($trans_response as $rkey => $rval) {
        $trans_db[0][$rkey] = $rval;
    }

    $wanted_db[] = [
        'tid' => $trans_db[0]['id'],
        'wanted_status' => 1,
        'themoviedb_id' => !empty($themoviedb_id) ? $wanted_db[0]['themoviedb_id'] = $themoviedb_id : null,
        'direct' => 1,
        'profile' => (int) $cfg['profile'],
    ];
    $newdb->addItemUniqField('wanted', $wanted_db, 'tid');
}

if (!isset($req_page) || $req_page == '') {
    $body .= index_page();
} else if ($req_page == 'library') {
    $body .= page_library();
} else if ($req_page == 'news') {
    $body .= page_news();
} else if ($req_page == 'tmdb') {
    $body .= page_tmdb();
} else if ($req_page == 'torrents') {
    $body .= page_torrents();
} else if ($req_page == 'view') {
    $body .= page_view();
} else if ($req_page == 'wanted') {
    $body .= page_wanted();
} else if ($req_page == 'identify') {
    $body .= page_identify();
} else if ($req_page == 'download') {
    page_download();
} else if ($req_page == 'transmission') {
    $body .= page_transmission();
} else {
    $box_msg['title'] = $LNG['L_ERROR'] . ' : ' . $LNG['L_NOEXISTS'];
    $box_msg['body'] = $LNG['L_PAGE_NOEXISTS'];
    $body .= msg_box($box_msg);
}

$page = getTpl('html_mstruct', $tdata = ['body' => $body, 'footer' => $footer]);
echo $page;



