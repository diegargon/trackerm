<?php

function index_page() {
    
}

function getMenu() {
    global $cfg, $LNG;
    ob_start();
    ?>
    <div class="main_menu">
        <a href="<?= $cfg['REL_PATH'] ?>"><div class="menu_element">Home</div></a>
        <a href="?page=biblio"><div class="menu_element">Biblioteca</div></a>
        <a href="?page=news"><div class="menu_element">Novedades</div></a>
        <a href="?page=torrents"><div class="menu_element">Torrentes</div></a>
        <a href="?page=tmdb"><div class="menu_element">TMDB</div></a>
    </div>
    <?php
    return ob_get_clean();
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

    $page = '';

    !empty($head) ? $page .= '<h2>' . $LNG[$head] . '</h2>' : null;

    $page .= '<div class="divTable">';
    $num_items = 0;



    foreach ($db_ary as $item) {

        $num_items == 0 ? $page .= '<div class="divTableRow">' : null;
        $page .= '<div class="divTableCell">';
        $page .= build_item($item);
        $page .= '</div>';

        $num_items++;
        if ($num_items == $columns) {
            $page .= '</div>';
            $num_items = 0;
        }
    }
    $num_items != 0 ? $page .= '</div>' : false;
    $page .= '</div>';

    return $page;
}

function build_item($item) {
    global $cfg;

    $details = $cfg['tresults_details'];
    $page = '';

    if ($details == 0) {
        $page .= $item['title'];
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
