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

    if (isset($_GET['search_type']) && isset($topt['search_type']) && ($_GET['search_type'] == $topt['search_type'])) {
        isset($_GET['npage']) ? $npage = $_GET['npage'] : $npage = 1;
    } else {
        $npage = 1;
    }
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

    if ($num_pages > 1) {
        $iurl = basename($_SERVER['REQUEST_URI']);
        //Avoid duplicate
        $iurl = preg_replace('/&npage=\d{1,4}/', '', $iurl);
        $iurl = preg_replace('/&search_type=shows/', '', $iurl);
        $iurl = preg_replace('/&search_type=movies/', '', $iurl);
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

                if (isset($_GET['npage']) && ($_GET['npage'] == $i)) {
                    if (isset($topt['search_type']) && ($_GET['search_type'] != $topt['search_type'])) {

                    } else {
                        $link_npage_class .= '_selected';
                    }
                }
                $pages .= '<a class="' . $link_npage_class . '" href="' . $iurl . '&npage=' . $i . $extra . '">' . $i . '</a>';
            }
        }

        $page .= '<div class="type_pages_numbers">' . $pages . '</div>';
    }

    /* FIN PAGES */

    $page .= '</div>';

    $page .= '<div class="divTable">';
    $num_col_items = 0;
    $num_items = 0;

    if (
            empty($_GET['npage']) ||
            (isset($topt['search_type']) && ($_GET['search_type'] != $topt['search_type']))
    ) {

        $db_ary_slice = $db_ary;
    } else {
        $npage = $_GET['npage'];
        $npage_jump = ($max_items * $npage) - $max_items;
        $db_ary_slice = array_slice($db_ary, $npage_jump);
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
                $cache_img_response = cacheImg($item['poster']);
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

function msg_box($msg) {
    global $LNG;

    return getTpl('msgbox', array_merge($LNG, $msg));
}
