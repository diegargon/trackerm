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

$req_page = Filter::getString('page');
($user['id'] < 1) ? $req_page = 'login' : null;

if (empty($req_page) && $user['id'] > 0) {
    $index_page = trim(getPrefsItem('index_page'));
    if (!empty($index_page) && $index_page != "index") {
        header("Location: {$cfg['REL_PATH']}/?page=$index_page");
        exit();
    }
}

if (!(empty($d_link = Filter::getUrl('download')))) {

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
        msg_page(['title' => $LNG['L_ERROR'], 'body' => $LNG['L_SEE_ERROR_DETAILS']]);
        return false;
    }
}

$req_page != 'login' ? $menu = getMenu() : $menu = '';
$body = '';
$valid_pages = ['index', 'library', 'news', 'tmdb', 'torrents', 'view', 'wanted', 'identify',
    'download', 'localplayer', 'identify', 'download', 'transmission', 'config', 'login', 'logout'];

(!isset($req_page) || $req_page == '') ? $req_page = 'index' : null;
(in_array($req_page, ['library_movies', 'library_shows'])) ? $req_page = 'library' : null;
(in_array($req_page, ['new_movies', 'new_shows'])) ? $req_page = 'news' : null;
($req_page == 'config' && $user['isAdmin'] != 1) ? $req_page = 'index' : null;
($req_page == 'localplayer' && !$cfg['localplayer']) ? $req_page = 'index' : null;

if (in_array($req_page, $valid_pages)) {
    $page_func = 'page_' . $req_page;
    $body .= $page_func();
}

$footer = getFooter();
$tdata = ['menu' => $menu, 'body' => $body, 'footer' => $footer];
$tdata['css_file'] = 'tpl/' . $cfg['theme'] . '/css/' . $cfg['css'] . '.css';
!file_exists(($tdata['css_file'])) ? $tdata['css_file'] = 'tpl/default/css/default.css' : null;
$tdata['css_file'] .= '?nocache=' . time();
$page = getTpl('html_mstruct', $tdata);
$db->close();

echo $page;



