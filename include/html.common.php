<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function getMenu() {
    global $cfg, $LNG;

    return getTpl('menu', array_merge($cfg, $LNG));
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
    global $cfg, $LNG;

    empty($topt['columns']) ? $columns = $cfg['tresults_columns'] : $columns = $topt['columns'];
    empty($topt['max_items']) ? $max_items = $cfg['tresults_rows'] * $columns : $max_items = $topt['max_items'];
    $page = '';

    $page .= '<div class="type_head_container">';

    !empty($head) ? $page .= '<div class="type_head"><h2>' . $LNG[$head] . '</h2></div>' : null;

    /* PAGES */
    $pages = '';
    $items_per_page = $cfg['tresults_columns'] * $cfg['tresults_rows'];
    $total_items = count($db_ary);
    $num_pages = $total_items / $items_per_page;

    $iurl = basename($_SERVER['REQUEST_URI']);
    $iurl = preg_replace('/&npage=\d{1,4}/', '', $iurl);
    for ($i = 1; $i <= ceil($num_pages); $i++) {

        $link_npage_class = "num_pages_link";

        if (!isset($_GET['type']) && isset($topt['type'])) {
            if ($topt['type'] == 'movies') {
                $extra = '&type=movies';
            } else if ($topt['type'] == 'shows') {
                $extra = '&type=shows';
            }
        } else {
            $extra = '';
        }

        if (
                (isset($_GET['npage']) && ($_GET['npage'] == $i)) &&
                ((isset($_GET['type'])) && ( $_GET['type'] == 'movies_torrent' || $_GET['type'] == 'shows_torrent') ||
                (isset($_GET['type']) && ($_GET['type'] == $topt['type'])))
        ) {
            $link_npage_class .= '_selected';
        }
        $pages .= '<a class="' . $link_npage_class . '" href="' . $iurl . '&npage=' . $i . $extra . '">' . $i . '</a>';
    }

    $page .= '<div class="type_pages_numbers">' . $pages . '</div>';

    /* FIN PAGES */
    $page .= '</div>';

    $page .= '<div class="divTable">';
    $num_col_items = 0;
    $num_items = 0;

    if (
            (!empty($_GET['npage']) && !isset($_GET['type']) ) ||
            ((isset($_GET['type'])) && ( $_GET['type'] == 'movies_torrent' || $_GET['type'] == 'shows_torrent') ||
            ( (isset($topt['type']) && isset($_GET['type'])) && $topt['type'] == $_GET['type']))
    ) {
        $npage = $_GET['npage'];
        $npage_jump = ($max_items * $npage) - $max_items;
        $db_ary_slice = array_slice($db_ary, $npage_jump);
    } else {
        $db_ary_slice = $db_ary;
    }

    foreach ($db_ary_slice as $item) {
        if ($num_items >= $max_items) {
            break;
        }

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
            $item['poster'] = $cfg['img_url'] . '/not_available.jpg';
        } else {
            if ($cfg['CACHE_IMAGES']) {
                $cache_img_response = get_and_cache_img($item['poster']);
                if ($cache_img_response !== false) {
                    $item['poster'] = $cache_img_response;
                }
            }
        }
        if (!empty($item['size'])) {
            $item['hsize'] = human_filesize($item['size']);
        }
        $page .= getTpl('item_display_1', array_merge($item, $LNG));
    }

    return $page;
}

function get_and_cache_img($img_url) {
    global $cfg;

    if (!is_writeable($_SERVER['DOCUMENT_ROOT'] . $cfg['REL_PATH'] . $cfg['CACHE_IMAGES_PATH'])) {
        return false;
    }

    $file_name = basename($img_url);
    $img_path = $_SERVER['DOCUMENT_ROOT'] . $cfg['REL_PATH'] . $cfg['CACHE_IMAGES_PATH'] . '/' . $file_name;

    if (
            file_exists($img_path) ||
            file_put_contents($img_path, file_get_contents($img_url)) !== false
    ) {
        return $cfg['REL_PATH'] . $cfg['CACHE_IMAGES_PATH'] . '/' . $file_name;
    }
    return false;
}
