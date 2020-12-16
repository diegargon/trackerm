<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function getMenu() {
    global $cfg, $LNG, $user, $filter;

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
        $user['menu_opt'] = getOptions();
        $tdata['arrow'] = '&uarr;';
    } else {
        $tdata['arrow'] = '&darr;';
    }


    return getTpl('menu', array_merge($cfg, $LNG, $user, $tdata));
}

function getFooter() {
    global $cfg, $LNG;

    return getTpl('footer', array_merge($cfg, $LNG));
}

function getTpl($tpl, $tdata) {
    global $cfg;
    ob_start();
    include('tpl/' . $cfg['theme'] . '/' . $tpl . '.tpl.php');

    return ob_get_clean();
}

function buildTable($head, $db_ary, $topt = null) {
    global $cfg, $LNG, $filter;
    //TODO FILTERS
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

    if (!isset($topt['nopages'])) {
        $page .= pager($npage, count($db_ary), $topt);
    }
    $page .= '</div>';

    $page .= '<div class="divTable">';
    $num_col_items = 0;
    $num_items = 0;

    if (
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

            $item['episode_count'] = $topt['episode_count'][$item['themoviedb_id']];
        }
        !empty($topt['search_type']) ? $item['media_type'] = $topt['search_type'] : null;

        $num_col_items == 0 ? $page .= '<div class="divTableRow">' : null;
        $page .= '<div class="divTableCell">';
        $page .= build_item($item);
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

function build_item($item, $detail = null) {
    global $cfg, $LNG;

    !isset($detail) ? $details = $cfg['tresults_details'] : $details = $detail;
    $page = '';

    if ($details == 0) {
        $page .= '<a href="?page=view&id=' . $item['id'] . '&type=' . $item['ilink'] . '">' . $item['title'] . '</a>';
    } else if ($details == 1) {
        if (empty($item['poster'])) {
            //TODO: Search in imdb for poster if is torrent
            $item['poster'] = $cfg['img_url'] . '/not_available.jpg';
        } else {
            if ($cfg['CACHE_IMAGES']) {
                $cache_img_response = cacheImg($item['poster']);
                if ($cache_img_response !== false) {
                    $item['poster'] = $cache_img_response;
                }
            }
        }

        $page .= getTpl('item_display_1', array_merge($item, $LNG));
    }

    return $page;
}

function msg_box($msg) {
    global $LNG;

    return getTpl('msgbox', array_merge($LNG, $msg));
}

function pager($npage, $nitems, &$topt) {
    global $cfg, $filter;

    /* PAGES */
    $pages = '';
    $items_per_page = $cfg['tresults_columns'] * $cfg['tresults_rows'];
    $num_pages = $nitems / $items_per_page;
    $search_type = $filter->getUtf8('search_type');

    $page = $filter->getString('page');

    if ($num_pages > 1) {
        $iurl = '?page=' . $page;

        (!empty($filter->getString('type'))) ? $iurl .= '&type=' . $filter->getString('type') : null;
        (!empty($filter->getInt('id'))) ? $iurl .= '&id=' . $filter->getInt('type') : null;
        (!empty($filter->getUtf8('search_shows_torrents'))) ? $iurl .= '&search_shows_torrents=' . $filter->getUtf8('search_shows_torrents') : null;
        (!empty($filter->getUtf8('search_movies_torrents'))) ? $iurl .= '&search_movies_torrents=' . $filter->getUtf8('search_movies_torrents') : null;
        (!empty($_GET['more_movies'])) ? $iurl .= '&more_movies=1' : null;
        (!empty($_GET['more_movies'])) ? $iurl .= '&more_torrents=1' : null;
        (!empty($filter->getUtf8('search_movie_db'))) ? $iurl .= '&search_movie_db=' . $filter->getUtf8('search_movie_db') : null;
        (!empty($filter->getUtf8('search_movies'))) ? $iurl .= '&search_movies=' . $filter->getUtf8('search_movies') : null;
        (!empty($filter->getUtf8('search_shows'))) ? $iurl .= '&search_shows=' . $filter->getUtf8('search_movies') : null;

        for ($i = 1; $i <= ceil($num_pages); $i++) {
            if (
                    (($i <= ($npage + 3)) && $i >= ($npage - 1) ||
                    ($i >= (ceil($num_pages) - 3 )) ||
                    ($i == 1) || ($i == ceil($num_pages) ))
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
    global $cfg, $filter, $LNG, $log;

    (isset($_POST['rebuild_movies'])) ? rebuild('movies', $cfg['MOVIES_PATH']) . $log->addStateMsg('Rebuild movies called') : null;
    (isset($_POST['rebuild_shows'])) ? rebuild('shows', $cfg['SHOWS_PATH']) . $log->addStateMsg('Rebuild shows called') : null;

    $tdata['page'] = $filter->getString('page');

    if (
            isset($_POST['num_ident_toshow']) &&
            ($cfg['max_identify_items'] != $_POST['num_ident_toshow'])
    ) {
        $num_ident_toshow = $filter->postInt('num_ident_toshow');
        $cfg['max_identify_items'] = $num_ident_toshow;
        setPrefsItem('max_identify_items', $num_ident_toshow);
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
    /* FIN */

    return getTpl('menu_options', array_merge($tdata, $LNG, $cfg));
}
