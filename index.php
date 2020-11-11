<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
include "include/common.inc.php";
include "include/html.common.php";
require __DIR__ . '/vendor/autoload.php';

isset($_GET['page']) ? $req_page = $_GET['page'] : $req_page = '';

$body = getMenu();
$footer = getFooter();

if (!is_writable($cfg['cache'])) {
    echo "<p><b>WARNING: Your cache directory must be writable:" . $cfg['cache'] . '</p></b>';
}
$trans = new TorrentServer($cfg);
$transfers = $trans->getAll();

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
    $bodu .= page_view();
}

$page = getTpl('html_mstruct', $tdata = ['body' => $body, 'footer' => $footer]);
echo $page;



