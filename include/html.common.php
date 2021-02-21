<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function getMenu() {
    global $cfg, $LNG, $user, $filter;

    if (empty($user) || empty($user['username']) || empty($user['id'])) {
        $user['id'] = 0;
        $user['username'] = $LNG['L_ANONYMOUS'];
    }
    if (isset($_GET['sw_opt'])) {
        $value = getPrefsItem('hide_opt');
        if ($value == 0) {
            setPrefsItem('hide_opt', 1);
            $cfg['hide_opt'] = 1;
        } else {
            setPrefsItem('hide_opt', 0);
            $cfg['hide_opt'] = 0;
        }
    }

    if (!empty($filter->getString('page'))) {
        $tdata['menu_opt_link'] = str_replace('&sw_opt=1', '', basename($_SERVER['REQUEST_URI'])) . '&sw_opt=1';
    } else {
        $tdata['menu_opt_link'] = "?page=index&sw_opt=1";
    }

    if (empty($cfg['hide_opt'])) {
        $tdata['menu_opt'] = getOptions();
        $tdata['arrow'] = '&uarr;';
    } else {
        $tdata['arrow'] = '&darr;';
    }

    return getTpl('menu', $tdata);
}

function getFooter() {
    return getTpl('footer');
}

function getTpl($tpl, $tdata = []) {
    global $cfg, $LNG, $user; //NO delete work for templates
    ob_start();
    include('tpl/' . $cfg['theme'] . '/' . $tpl . '.tpl.php');

    return ob_get_clean();
}

function buildTable($head, $db_ary, $topt = null) {
    global $cfg, $LNG, $filter;

    $npage = $filter->getInt('npage');

    if (isset($_GET['search_type']) && isset($topt['search_type']) && ($_GET['search_type'] == $topt['search_type'])) {
        empty($npage) ? $npage = 1 : null;
    } else {
        $npage = 1;
    }

    empty($topt['columns']) ? $columns = $cfg['tresults_columns'] : $columns = $topt['columns'];
    empty($topt['max_items']) ? $max_items = $cfg['tresults_rows'] * $columns : $max_items = $topt['max_items'];

    $page = '<div class="type_head_container">';

    !empty($head) ? $page .= '<div class="type_head"><h2>' . $LNG[$head] . '</h2></div>' : null;

    if (!isset($topt['no_pages'])) {
        empty($topt['num_table_rows']) ? $npages = count($db_ary) : $npages = $topt['num_table_rows'];
        $page .= pager($npage, $npages, $topt);
    }
    $page .= '</div>';

    $page .= '<div class="divTable">';
    $num_col_items = 0;
    $num_items = 0;

    if (isset($topt['num_table_rows']) ||
            $npage == 1 ||
            (isset($topt['search_type']) && ($_GET['search_type'] != $topt['search_type']))
    ) {
        $db_ary_slice = $db_ary;
    } else {
        $npage_jump = ($max_items * $npage) - $max_items;
        $db_ary_slice = array_slice($db_ary, $npage_jump);
    }

    foreach ($db_ary_slice as $item) {
        if ($num_items >= $max_items) {
            break;
        }

        if (empty($topt['sizes']) && !empty($item['size'])) {
            $item['size'] = human_filesize($item['size']);
        } else if (!empty($topt['sizes'])) {
            $item['size'] = human_filesize($topt['sizes'][$item['themoviedb_id']]);
        }

        if (!empty($item['themoviedb_id']) && !empty($topt['episode_count'][$item['themoviedb_id']])) {

            $topt['num_episodes'] = $topt['episode_count'][$item['themoviedb_id']];
        }
        !empty($topt['search_type']) ? $item['media_type'] = $topt['search_type'] : null;

        $num_col_items == 0 ? $page .= '<div class="divTableRow">' : null;
        $page .= '<div class="divTableCell">';
        $page .= build_item($item, $topt);
        $page .= '</div>';

        $num_col_items++;
        if ($num_col_items == $columns) {
            $page .= '</div>';
            $num_col_items = 0;
        }
        $num_items++;
    }
    $num_col_items != 0 ? $page .= '</div>' : false;
    $page .= '</div>';

    return $page;
}

function build_item($item, $topt) {
    global $cfg, $db;

    $page = '';

    if (!empty($topt['view_type']) &&
            ($topt['view_type'] == 'movies_library' || $topt['view_type'] == 'shows_library' || $topt['view_type'] == 'shows_db' || $topt['view_type'] == 'movies_db')
    ) {
        $item['title'] = $item['title'] . ' (' . strftime("%Y", strtotime($item['release'])) . ')';
    }
    if (empty($item['poster'])) {

        $item['poster'] = $cfg['img_url'] . '/not_available.jpg';
        if (!isset($item['themoviedb_id'])) {
            if (!empty($item['guessed_poster']) && $item['guessed_poster'] != -1) {
                $poster = $item['guessed_poster'];
            } else if (empty($item['guessed_poster'])) {
                $poster = mediadb_guessPoster($item);
            }
            if (!empty($poster)) {
                if ($cfg['cache_images']) {
                    $cache_img_response = cacheImg($poster);
                    if ($cache_img_response !== false) {
                        $item['poster'] = $cache_img_response;
                    }
                }
                $item['guessed_poster'] = 1;
                $values['guessed_poster'] = $poster;
            } else {
                $values['guessed_poster'] = -1;
            }
            if (!empty($topt['view_type']) && $topt['view_type'] == 'movies_torrent') {
                $db->updateItemById('jackett_movies', $item['id'], $values);
            } else if (!empty($topt['view_type']) && $topt['view_type'] == 'shows_torrent') {
                $db->updateItemById('jackett_shows', $item['id'], $values);
            }
        }
    } else {
        if ($cfg['cache_images']) {
            $cache_img_response = cacheImg($item['poster']);
            if ($cache_img_response !== false) {
                $item['poster'] = $cache_img_response;
            }
        }
    }

    if (!isset($item['themoviedb_id']) && empty($item['trailer'])) {
        if (!empty($item['guessed_trailer']) && $item['guessed_trailer'] != -1) {
            $trailer = $item['guessed_trailer'];
        } else if (empty($item['guessed_trailer'])) {
            $trailer = mediadb_guessTrailer($item);
        }
        if (!empty($trailer)) {
            $item['trailer'] = trim($trailer);
            if (substr($trailer, 0, 4) == 'http:') {
                $values['guessed_trailer'] = str_replace('http', 'https', $trailer);
            } else {
                $values['guessed_trailer'] = $trailer;
            }
        } else {
            $values['guessed_trailer'] = -1;
        }
        if (!empty($topt['view_type']) && $topt['view_type'] == 'movies_torrent') {
            $db->updateItemById('jackett_movies', $item['id'], $values);
        } else if (!empty($topt['view_type']) && $topt['view_type'] == 'shows_torrent') {
            $db->updateItemById('jackett_shows', $item['id'], $values);
        }
    }

    $page .= getTpl('item_display', array_merge($item, $topt));

    return $page;
}

function msg_box($msg) {
    return getTpl('msgbox', $msg);
}

function msg_page($msg) {
    $footer = getFooter();
    $menu = getMenu();
    $body = msg_box($msg = ['title' => $msg['title'], 'body' => $msg['body']]);
    $tdata = ['menu' => $menu, 'body' => $body, 'footer' => $footer];
    echo getTpl('html_mstruct', $tdata);

    exit();
}

function pager($npage, $nitems, &$topt) {
    global $cfg, $filter;

    /* PAGES */
    $pages = '';
    $items_per_page = $cfg['tresults_columns'] * $cfg['tresults_rows'];
    $num_pages = ceil($nitems / $items_per_page);
    $search_type = $filter->getUtf8('search_type');

    $page = $filter->getString('page');

    if ($num_pages > 1) {
        $iurl = '?page=' . $page;

        (!empty($filter->getString('view_type'))) ? $iurl .= '&view_type=' . $filter->getString('view_type') : null;
        (!empty($filter->getInt('id'))) ? $iurl .= '&id=' . $filter->getInt('id') : null;
        (!empty($filter->getUtf8('search_shows_torrents'))) ? $iurl .= '&search_shows_torrents=' . $filter->getUtf8('search_shows_torrents') : null;
        (!empty($filter->getUtf8('search_movies_torrents'))) ? $iurl .= '&search_movies_torrents=' . $filter->getUtf8('search_movies_torrents') : null;
        (!empty($_GET['more_movies'])) ? $iurl .= '&more_movies=1' : null;
        (!empty($_GET['more_torrents'])) ? $iurl .= '&more_torrents=1' : null;
        (!empty($filter->getUtf8('search_movie_db'))) ? $iurl .= '&search_movie_db=' . $filter->getUtf8('search_movie_db') : null;
        (!empty($filter->getUtf8('search_movies'))) ? $iurl .= '&search_movies=' . $filter->getUtf8('search_movies') : null;
        (!empty($filter->getUtf8('search_shows'))) ? $iurl .= '&search_shows=' . $filter->getUtf8('search_shows') : null;

        for ($i = 1; $i <= ceil($num_pages); $i++) {
            if (($i == 1 || $i == $num_pages || $i == $npage) ||
                    in_range($i, ($npage - 3), ($npage + 3), TRUE)
            ) {
                $extra = '';
                $link_npage_class = "num_pages_link";

                if (!empty($topt['search_type'])) {
                    $extra = '&search_type=' . $topt['search_type'];
                }

                if (isset($npage) && ($npage == $i)) {
                    if (isset($topt['search_type']) && ($search_type != $topt['search_type'])) {

                    } else {
                        $link_npage_class .= '_selected';
                    }
                }
                $pages .= '<a onClick="show_loading()"  class="' . $link_npage_class . '" href="' . $iurl . '&npage=' . $i . $extra . '">' . $i . '</a>';
            }
        }
    }

    return '<div class="type_pages_numbers">' . $pages . '</div>';
}

function getOptions() {
    global $cfg, $filter, $LNG;

    (isset($_POST['rebuild_movies'])) ? rebuild('movies', $cfg['MOVIES_PATH']) : null;
    (isset($_POST['rebuild_shows'])) ? rebuild('shows', $cfg['SHOWS_PATH']) : null;

    $tdata['page'] = $filter->getString('page');

    if (
            isset($_POST['num_ident_toshow']) &&
            ($cfg['max_identify_items'] != $_POST['num_ident_toshow'])
    ) {
        $num_ident_toshow = $filter->postInt('num_ident_toshow');
        $cfg['max_identify_items'] = $num_ident_toshow;
        setPrefsItem('max_identify_items', $num_ident_toshow);
    }

    if (isset($_POST['new_ignore_keywords'])) {
        $cfg['new_ignore_keywords'] = $filter->postString('new_ignore_keywords');
        setPrefsItem('new_ignore_keywords', $cfg['new_ignore_keywords']);
    }

    if (isset($_POST['new_ignore_size'])) {
        $cfg['new_ignore_size'] = $filter->postString('new_ignore_size');
        setPrefsItem('new_ignore_size', $cfg['new_ignore_size']);
    }

    if (isset($_POST['new_ignore_words_enable'])) {
        $cfg['new_ignore_words_enable'] = $filter->postString('new_ignore_words_enable');
        setPrefsItem('new_ignore_words_enable', $cfg['new_ignore_words_enable']);
    }

    if (isset($_POST['new_ignore_size_enable'])) {
        $cfg['new_ignore_size_enable'] = $filter->postString('new_ignore_size_enable');
        setPrefsItem('new_ignore_size_enable', $cfg['new_ignore_size_enable']);
    }

    if (isset($_POST['sel_indexer'])) {
        $cfg['sel_indexer'] = $filter->postString('sel_indexer');
        setPrefsItem('sel_indexer', $cfg['sel_indexer']);
    }

    ($cfg['max_identify_items'] == 0) ? $max_id_sel_0 = 'selected' : $max_id_sel_0 = '';
    ($cfg['max_identify_items'] == 5) ? $max_id_sel_5 = 'selected' : $max_id_sel_5 = '';
    ($cfg['max_identify_items'] == 10) ? $max_id_sel_10 = 'selected' : $max_id_sel_10 = '';
    ($cfg['max_identify_items'] == 20) ? $max_id_sel_20 = 'selected' : $max_id_sel_20 = '';
    ($cfg['max_identify_items'] == 50) ? $max_id_sel_50 = 'selected' : $max_id_sel_50 = '';

    $tdata['max_id_sel_0'] = $max_id_sel_0;
    $tdata['max_id_sel_5'] = $max_id_sel_5;
    $tdata['max_id_sel_10'] = $max_id_sel_10;
    $tdata['max_id_sel_20'] = $max_id_sel_20;
    $tdata['max_id_sel_50'] = $max_id_sel_50;

    /* ROWS */
    $max_rows_sel_none = '';

    if (isset($_POST['num_rows_results'])) {
        if ($_POST['num_rows_results'] == $LNG['L_DEFAULT']) {
            $max_rows_sel_none = 'selected';
        } else {
            $num_rows_results = $filter->postInt('num_rows_results');
            $cfg['tresults_rows'] = $num_rows_results;
            setPrefsItem('tresults_rows', $num_rows_results);
        }
    }

    ($cfg['tresults_rows'] == 1) ? $tdata['max_rows_sel_1'] = 'selected' : $tdata['max_rows_sel_1'] = '';
    ($cfg['tresults_rows'] == 2) ? $tdata['max_rows_sel_2'] = 'selected' : $tdata['max_rows_sel_2'] = '';
    ($cfg['tresults_rows'] == 4) ? $tdata['max_rows_sel_4'] = 'selected' : $tdata['max_rows_sel_4'] = '';
    ($cfg['tresults_rows'] == 6) ? $tdata['max_rows_sel_6'] = 'selected' : $tdata['max_rows_sel_6'] = '';
    ($cfg['tresults_rows'] == 8) ? $tdata['max_rows_sel_8'] = 'selected' : $tdata['max_rows_sel_8'] = '';
    ($cfg['tresults_rows'] == 10) ? $tdata['max_rows_sel_10'] = 'selected' : $tdata['max_rows_sel_10'] = '';
    $tdata['max_rows_sel_none'] = $max_rows_sel_none;

    /* COLUMNS */

    $max_columns_sel_none = '';

    if (isset($_POST['num_columns_results'])) {
        if ($_POST['num_columns_results'] == $LNG['L_DEFAULT']) {
            $max_columns_sel_none = 'selected';
        } else {
            $num_columns_results = $filter->postInt('num_columns_results');
            $cfg['tresults_columns'] = $num_columns_results;
            setPrefsItem('tresults_columns', $num_columns_results);
        }
    }

    ($cfg['tresults_columns'] == 1) ? $tdata['max_columns_sel_1'] = 'selected' : $tdata['max_columns_sel_1'] = '';
    ($cfg['tresults_columns'] == 2) ? $tdata['max_columns_sel_2'] = 'selected' : $tdata['max_columns_sel_2'] = '';
    ($cfg['tresults_columns'] == 4) ? $tdata['max_columns_sel_4'] = 'selected' : $tdata['max_columns_sel_4'] = '';
    ($cfg['tresults_columns'] == 6) ? $tdata['max_columns_sel_6'] = 'selected' : $tdata['max_columns_sel_6'] = '';
    ($cfg['tresults_columns'] == 8) ? $tdata['max_columns_sel_8'] = 'selected' : $tdata['max_columns_sel_8'] = '';
    ($cfg['tresults_columns'] == 10) ? $tdata['max_columns_sel_10'] = 'selected' : $tdata['max_columns_sel_10'] = '';
    $tdata['max_columns_sel_none'] = $max_columns_sel_none;

    //new filters
    (!empty($cfg['sel_indexer']) && $cfg['sel_indexer'] == 'sel_indexer_none') ? $selected_idx_none = 'selected' : $selected_idx_none = '';

    $tdata['sel_indexers'] = '<option ' . $selected_idx_none . ' value="sel_indexer_none">' . $LNG['L_ALL'] . '</option>';

    foreach ($cfg['jackett_indexers'] as $indexer) {
        (isset($cfg['sel_indexer']) && $cfg['sel_indexer'] == $indexer) ? $selected_indexer = 'selected="selected"' : $selected_indexer = '';
        $tdata['sel_indexers'] .= '<option ' . $selected_indexer . ' value="' . $indexer . '">' . $indexer . '</option>';
    }
    /* FIN */

    return getTpl('menu_options', $tdata);
}

function html_mediainfo_tags($mediainfo, $tags = null) {
    global $LNG;

    $general_tags = $video_tags = $audio_tags = $text_tags = '';
    $tags = [
        'General' => ['Format', 'FrameRate', 'AudioCount', 'VideoCount', 'TextCount'],
        'Video' => ['FrameRate_Mode', 'ColorSpace', 'Encoded_Library_Name'],
        'Audio' => ['Format', 'BitRate_Mode', 'Format_Commercial_IfAny', 'Channels', 'BitRate', 'Compression_Mode'],
//        'Text' => '',
    ];
    foreach ($tags as $tag_ary_key => $tag_ary) {
        if ($tag_ary_key == 'General') {
            foreach ($tag_ary as $tag_value) {
                isset($mediainfo[$tag_ary_key][$tag_value]) ? $general_tags .= '<div  title="' . $tag_value . '" class="mediainfo_tag">' . $mediainfo[$tag_ary_key][$tag_value] . '</div>' : null;
            }
        }
        if ($tag_ary_key == 'Video') {
            foreach ($tag_ary as $tag_value) {
                isset($mediainfo[$tag_ary_key][0][$tag_value]) ? $video_tags .= '<div  title="' . $tag_value . '" class="mediainfo_tag">' . $mediainfo[$tag_ary_key][0][$tag_value] . '</div>' : null;
            }
        }
        if ($tag_ary_key == 'Audio') {
            foreach ($tag_ary as $tag_value) {
                isset($mediainfo[$tag_ary_key][1][$tag_value]) ? $audio_tags .= '<div title="' . $tag_value . '" class="mediainfo_tag">' . $mediainfo[$tag_ary_key][1][$tag_value] . '</div>' : null;
            }
        }
        if ($tag_ary_key == 'Text') {
            foreach ($tag_ary as $tag_value) {
                isset($mediainfo[$tag_ary_key][1][$tag_value]) ? $text_tags .= '<div  title="' . $tag_value . '" class="mediainfo_tag">' . $mediainfo[$tag_ary_key][1][$tag_value] . '</div>' : null;
            }
        }
    }

    if (($mediainfo['Video'][0]['Width']) && isset($mediainfo['Video'][0]['Height'])) {
        $video_tags .= '<div class="mediainfo_tag">' . $mediainfo['Video'][0]['Width'] . 'x' . $mediainfo['Video'][0]['Height'] . 'p</div>';
    }
    if (isset($mediainfo['Audio'])) {
        foreach ($mediainfo['Audio'] as $audio) {
            isset($audio['Language']) ? $audio_tags .= '<div title="' . $LNG['L_AUDIO'] . '" class="mediainfo_tag">' . $audio['Language'] . '</div>' : null;
        }
    }
    /*
      if (isset($mediainfo['Text'])) {
      $subs_langs = '';
      foreach ($mediainfo['Text'] as $text) {
      if (isset($text['Language'])) {
      empty($subs_langs) ? $subs_langs .= $text['Language'] : $subs_langs .= ':' . $text['Language'];
      }
      }
      !empty($subs_langs) ? $text_tags .= '<span title="' . $LNG['L_SUBS'] . '" class="mediainfo_tag">' . $subs_langs . '</span>' : null;
      }
     *
     */
    return $general_tags . $video_tags . $audio_tags . $text_tags;
}
