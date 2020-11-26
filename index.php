<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
require('include/common.inc.php');
require('include/session.inc.php');
require('include/pages.inc.php');
require('include/html.common.php');
require('include/library.inc.php');

isset($_GET['page']) ? $req_page = $_GET['page'] : $req_page = '';

$body = getMenu();
$footer = getFooter();

if (!empty($_GET['download'])) {
    $d_link = $_GET['download'];

    $trans_response = $trans->addUrl($d_link);

    foreach ($trans_response as $rkey => $rval) {
        $trans_db[0][$rkey] = $rval;
    }
    !empty($_GET['themoviedb_id']) ? $trans_db[0]['themoviedb_id'] = $_GET['themoviedb_id'] : null; //TODO enviar para autoidentificar
    $trans_db[0]['tid'] = $trans_db[0]['id'];
    $trans_db[0]['status'] = -1;
    $trans_db[0]['profile'] = (int) $cfg['profile'];
    $db->addUniqElements('transmission', $trans_db, 'tid');
}

if (!isset($req_page) || $req_page == '') {
    $body .= index_page();
} else if ($req_page == 'biblio') {
    $body .= page_biblio();
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



