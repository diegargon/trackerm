<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Web {

    private $frontend;

    function __construct($frontend) {
        $this->frontend = $frontend;
    }

    function render() {
        $this->pagesGlobal();
        $req_page = $this->getPage();

        if ($req_page === false) {
            $this->frontend->msgPage(['title' => 'L_ERROR', 'body' => 'L_PAGE_NOEXISTS']);
        }
        echo $this->frontend->buildPage($req_page);
    }

    function getPage() {
        global $cfg, $user, $prefs;

        $req_page = Filter::getString('page');

        ($user->getId() < 1) ? $req_page = 'login' : null;

        if (empty($req_page) && $user->getId() > 0) {
            $index_page = trim($prefs->getPrefsItem('index_page'));
            if (!empty($index_page) && $index_page != "index") {
                header("Location: {$cfg['REL_PATH']}/?page=$index_page");
                exit();
            }
        }
        $valid_pages = ['index', 'library', 'news', 'tmdb', 'torrents', 'view', 'view_group', 'view_genres', 'view_director', 'view_cast', 'view_writer', 'wanted', 'identify',
            'download', 'localplayer', 'identify', 'download', 'transmission', 'config', 'login', 'logout'];

        (!isset($req_page) || $req_page == '') ? $req_page = 'index' : null;
        (in_array($req_page, ['library_movies', 'library_shows'])) ? $req_page = 'library' : null;
        (in_array($req_page, ['new_movies', 'new_shows'])) ? $req_page = 'news' : null;
        ($req_page == 'config' && !$user->isAdmin()) ? $req_page = 'index' : null;
        ($req_page == 'localplayer' && !$cfg['localplayer']) ? $req_page = 'index' : null;

        if (in_array($req_page, $valid_pages)) {
            return $req_page;
        }

        return false;
    }

    function pagesGlobal() {
        global $trans, $db, $user, $log;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!(empty($d_link = Filter::postUrl('download')))) {
                if (empty($trans)) {
                    $log->err('Transmission connection fail');
                    $this->frontend->msgPage(['title' => 'L_ERROR', 'body' => 'L_SEE_ERROR_DETAILS']);
                    return false;
                }
                if (($pos = strpos($d_link, 'file=')) !== FALSE) {
                    $jackett_filename = substr($d_link, $pos + 5);
                    $jackett_filename = trim(str_replace('+', ' ', $jackett_filename));
                }
                $trans_response = $trans->addUrl($d_link);

                //Magnet Link hack: transmission fail to download magnet from url,
                //if addUrl fail we try for magnets: get with curl extract  and send the magnet
                if (empty($trans_response)) {
                    $curl_opt['headers'] = ['Accept-Encoding: gzip, deflate'];
                    $curl_opt['return_headers'] = 1;
                    $response = curl_get($d_link, $curl_opt);
                    $match = [];
                    preg_match("/Location:(.+?)\s/", $response, $match);
                    if (valid_array($match) && !empty($match[1])) {
                        !empty($trans) ? $trans_response = $trans->addUrl(trim($match[1])) : null;
                        if (!empty($trans_response)) {
                            $log->info("Magnet detected, fixed error, ignore previous related (addUrl) error");
                        }
                    }
                }

                if (!empty($trans_response)) {
                    foreach ($trans_response as $rkey => $rval) {
                        $trans_db[$rkey] = $rval;
                    }

                    $wanted_db = [
                        'tid' => $trans_db['id'],
                        'wanted_status' => 1,
                        'jackett_filename' => !empty($jackett_filename) ? $jackett_filename : null,
                        'hashString' => $trans_db['hashString'],
                        /* 'themoviedb_id' => */
                        'direct' => 1,
                        'profile' => $user->getId(),
                    ];
                    $db->addItemUniqField('wanted', $wanted_db, 'hashString');
                } else {
                    $this->frontend->msgPage(['title' => 'L_ERROR', 'body' => 'L_SEE_ERROR_DETAILS']);
                    return false;
                }
            } //END DOWNLOAD
        } //END GET
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            //VID : Mark as view entire master
            $media_type = Filter::postAzChar('media_type');
            $vid = Filter::postInt('vid');

            if (!empty($vid) && !empty($media_type)) {

                $library = 'library_' . $media_type;
                $library_master = 'library_master_' . $media_type;

                $master = $db->getItemById($library_master, $vid);

                if (valid_array($master)) {

                    $items = $db->getItemsByField($library, 'master', $master['id']);
                    $where = [
                        'uid' => ['value' => $user->getId()],
                        'themoviedb_id' => ['value' => $master['themoviedb_id']],
                    ];
                    $results = $db->select('view_media', '*', $where);
                    $view_items = $db->fetchAll($results);

                    /*
                     *  view_media can have other old  items, this items can be deleted but keep in viewmedia
                     *  with same themoviedb id. We must check the file_hash  and count, and check if count match, if count
                     * match we want "mark as unwatch all actual items" if not we wan mark as "watch" all items
                     */
                    $nfound = [];
                    foreach ($items as $item) {
                        foreach ($view_items as $view_item) {
                            if (empty($item['file_hash'])) {
                                $hash = file_hash($item['path']);
                                $db->updateItemById($library, $item['id'], ['file_hash' => $hash], 'LIMIT 1');
                                $item['file_hash'] = $hash;
                            }
                            if ($view_item['file_hash'] == $item['file_hash']) {
                                $nfound[] = $view_item['id'];
                                break;
                            }
                        }
                    }

                    if (count($items) == count($nfound)) {
                        foreach ($nfound as $found_item) {
                            $db->delete('view_media', ['id' => ['value' => $found_item]]);
                        }
                    } else {
                        foreach ($items as $item) {
                            $found = 0;
                            foreach ($view_items as $view_item) {
                                if ($view_item['file_hash'] == $item['file_hash']) {
                                    $found = $view_item['id'];
                                    break;
                                }
                            }

                            if (!$found) {
                                $values['uid'] = $user->getId();
                                $values['themoviedb_id'] = $master['themoviedb_id'];
                                $values['file_hash'] = $item['file_hash'];
                                $values['media_type'] = $media_type;
                                if ($media_type == 'shows') {
                                    $values['season'] = $item['season'];
                                    $values['episode'] = $item['episode'];
                                }
                                $db->insert('view_media', $values);
                            }
                        }
                    }
                }
            } //END VID
        } //END POST
    }

}
