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
    include('tpl/' . $cfg['tpl_dir'] . '/' . $tpl . '.tpl.php');

    return ob_get_clean();
}

function buildTable($head, $db_ary, $topt = null) {
    global $cfg, $LNG;

    empty($topt['columns']) ? $columns = $cfg['tresults_columns'] : $columns = $topt['columns'];
    empty($topt['max_items']) ? $max_items = $cfg['tresults_rows'] * $columns : $max_items = $topt['max_items'];
    $page = '';

    !empty($head) ? $page .= '<h2>' . $LNG[$head] . '</h2>' : null;

    $page .= '<div class="divTable">';
    $num_col_items = 0;
    $num_items = 0;
    foreach ($db_ary as $item) {
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

    /* PAGES */
    $pages = '';
    $items_per_page = $cfg['tresults_columns'] * $cfg['tresults_rows'];
    $total_items = count($db_ary);
    $num_pages = $total_items / $items_per_page;
    for ($i = 1; $i <= ceil($num_pages); $i++) {
        $pages .= '<a class="num_pages_link" href="' . basename($_SERVER['REQUEST_URI']) . '&npage=' . $i . '">' . $i . '</a>';
    }
    $page .= "<p>" . $pages . '</p>';

    /* FIN PAGES */

    return $page;
}

function build_item($item, $detail = null) {
    global $cfg;

    !isset($detail) ? $details = $cfg['tresults_details'] : $details = $detail;
    $page = '';

    if ($details == 0) {
        $page .= '<a href="">' . $item['title'] . '</a>';
    } else if ($details == 1) {
        if (empty($item['poster'])) {
            $item['poster'] = $cfg['img_url'] . '/not_available.jpg';
        }
        if (!empty($item['size'])) {
            $item['hsize'] = human_filesize($item['size']);
        }
        $page .= getTpl('item_display_1', $item);
    }

    return $page;
}
