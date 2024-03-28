<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class BackEnd {

    public function __construct() {
        $this->prefsSet();
    }

    public function getPageData(string $req_page) {
        $page_data = [];

        $pages_global = $this->pagesGlobal();

        if ($pages_global === true) {
            $page_func = 'page_' . $req_page;
            //FIX check if page_func exists
            $page_data = $page_func();

            if (is_array($page_data)) {
                $page_data['request_page'] = $req_page;
                $page_data['request_page_func'] = $page_func;
            } else {

                exit('Backend error: getPageData not return array');
            }
        } else {
            $page_data = $pages_global;
        }

        return $page_data;
    }

    function pagesGlobal() {
        global $trans, $db, $user, $log, $LNG;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!(empty($d_link = Filter::postUrl('download')))) {
                if (empty($trans)) {
                    $log->err('Transmission connection fail');
                    $error['templates'][] = [
                        'name' => 'msgbox', 'tpl_file' => 'msgbox', 'tpl_pri' => 4,
                        'tpl_vars' => [
                            'title' => $LNG['L_ERROR'],
                            'body' => $LNG['L_SEE_ERROR_DETAILS'],
                        ]
                    ];
                    $error['request_page'] = 'error';
                    return $error;
                }
                if (($pos = strpos($d_link, 'file=')) !== FALSE) {
                    $jackett_filename = substr($d_link, $pos + 5);
                    $jackett_filename = trim(str_replace('+', ' ', $jackett_filename));
                }
                $trans_response = $trans->addUrl($d_link);

                //NOT Checked if still a problem with the new transmission class
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

                if (!empty($trans_response) || $trans_response['result'] === 'success') {

                    $wanted_db = [
                        'tid' => $trans_response['arguments']['torrent-added']['id'],
                        'wanted_status' => 1,
                        'jackett_filename' => !empty($jackett_filename) ? $jackett_filename : null,
                        'hashString' => $trans_response['arguments']['torrent-added']['hashString'],
                        /* 'themoviedb_id' => */
                        'direct' => 1,
                        'profile' => $user->getId(),
                    ];
                    $db->addItemUniqField('wanted', $wanted_db, 'hashString');
                } else {
                    $error['templates'][] = [
                        'name' => 'msgbox', 'tpl_file' => 'msgbox', 'tpl_pri' => 4,
                        'tpl_vars' => [
                            'title' => $LNG['L_ERROR'],
                            'body' => $LNG['L_SEE_ERROR_DETAILS'],
                        ]
                    ];
                    $error['request_page'] = 'error';
                    return $error;
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

        return true;
    }

    function prefsSet() {
        global $prefs;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return false;
        }

        if (isset($_POST['num_ident_toshow']) &&
                ($prefs->getPrefsItem('max_identify_items') != $_POST['num_ident_toshow'])
        ) {
            $num_ident_toshow = Filter::postInt('num_ident_toshow');
            $prefs->setPrefsItem('max_identify_items', $num_ident_toshow);
        }
        if (isset($_POST['new_ignore_keywords'])) {
            $prefs->setPrefsItem('new_ignore_keywords', Filter::postString('new_ignore_keywords'));
        }
        if (isset($_POST['only_freelech'])) {
            $prefs->setPrefsItem('only_freelech', Filter::postString('only_freelech'));
        }
        if (isset($_POST['movies_cached'])) {
            $prefs->setPrefsItem('movies_cached', Filter::postString('movies_cached'));
        }
        if (isset($_POST['shows_cached'])) {
            $prefs->setPrefsItem('shows_cached', Filter::postString('shows_cached'));
        }
        if (isset($_POST['view_mode'])) {
            $prefs->setPrefsItem('view_mode', Filter::postString('view_mode'));
        }
        if (isset($_POST['new_ignore_size_max'])) {
            $prefs->setPrefsItem('new_ignore_size_max', Filter::postInt('new_ignore_size_max'));
        }
        if (isset($_POST['new_ignore_size_min'])) {
            $prefs->setPrefsItem('new_ignore_size_min', Filter::postInt('new_ignore_size_min'));
        }
        if (isset($_POST['new_ignore_size_enable'])) {
            $prefs->setPrefsItem('new_ignore_size_enable', Filter::postString('new_ignore_size_enable'));
        }
        if (isset($_POST['new_ignore_words_enable'])) {
            $prefs->setPrefsItem('new_ignore_words_enable', Filter::postString('new_ignore_words_enable'));
        }
        if (isset($_POST['sel_indexer'])) {
            $prefs->setPrefsItem('sel_indexer', Filter::postString('sel_indexer'));
        }
        if (isset($_POST['expand_all'])) {
            $prefs->setPrefsItem('expand_all', Filter::postString('expand_all'));
        }
        (isset($_POST['show_trending'])) ? $prefs->setPrefsItem('show_trending', Filter::postString('show_trending')) : null;
        (isset($_POST['show_popular'])) ? $prefs->setPrefsItem('show_popular', Filter::postString('show_popular')) : null;
        (isset($_POST['show_today_shows'])) ? $prefs->setPrefsItem('show_today_shows', Filter::postString('show_today_shows')) : null;

        // Collections
        if (isset($_POST['show_collections'])) {
            $prefs->setPrefsItem('show_collections', Filter::postString('show_collections'));
        }
        // ROWS
        if (isset($_POST['num_rows_results'])) {
            $prefs->setPrefsItem('tresults_rows', Filter::postInt('num_rows_results'));
        }
        // Columns
        if (isset($_POST['num_columns_results'])) {
            $prefs->setPrefsItem('tresults_columns', Filter::postInt('num_columns_results'));
        }
    }
}
