<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class FrontEnd {

    function buildPage(string $req_page) {
        global $cfg;

        $req_page != 'login' ? $menu = $this->getMenu() : $menu = '';
        $page_func = 'page_' . $req_page;
        $body = $page_func();
        $footer = $this->getFooter();
        $tdata = ['menu' => $menu, 'body' => $body, 'footer' => $footer];
        $tdata['css_file'] = $this->getCssFile($cfg['theme'], $cfg['css']);

        return $this->getTpl('html_mstruct', $tdata);
    }

    function getTpl(string $tpl, array $tdata = []) {
        global $cfg, $LNG, $user, $prefs; //NO delete need for templates

        ob_start();
        $tpl_file = 'tpl/' . $cfg['theme'] . '/' . $tpl . '.tpl.php';
        !file_exists($tpl_file) ? $tpl_file = 'tpl/default/' . $tpl . '.tpl.php' : null;
        include($tpl_file);

        return ob_get_clean();
    }

    function getCssFile(string $theme, string $css) {
        $css_file = 'tpl/' . $theme . '/css/' . $css . '.css';
        !file_exists($css_file) ? $css_file = 'tpl/default/css/default.css' : null;
        $css_file .= '?nocache=' . time(); //TODO: To Remove: avoid cache css while dev

        return $css_file;
    }

    function msgBox(array $msg) {
        global $LNG;

        (substr($msg['title'], 0, 2) == 'L_') ? $msg['title'] = $LNG[$msg['title']] : null;
        (substr($msg['body'], 0, 2) == 'L_') ? $msg['body'] = $LNG[$msg['body']] : null;
        return $this->getTpl('msgbox', $msg);
    }

    function msgPage(array $msg) {
        global $cfg;

        $footer = $this->getFooter();
        $menu = $this->getMenu();
        $body = $this->msgBox(['title' => $msg['title'], 'body' => $msg['body']]);
        $tdata = ['menu' => $menu, 'body' => $body, 'footer' => $footer];
        $tdata['css_file'] = $this->getCssFile($cfg['theme'], $cfg['css']);
        echo $this->getTpl('html_mstruct', $tdata);

        exit();
    }

    function getMenu() {
        global $prefs, $cfg;

        if (isset($_GET['sw_opt'])) {
            $value = $prefs->getPrefsItem('hide_opt');
            if ($value == 0) {
                $prefs->setPrefsItem('hide_opt', 1);
            } else {
                $prefs->setPrefsItem('hide_opt', 0);
            }
        }

        if (!empty(Filter::getString('page'))) {
            $page = Filter::getString('page');
            $tdata['menu_opt_link'] = str_replace('&sw_opt=1', '', basename($_SERVER['REQUEST_URI'])) . '&sw_opt=1';
        } else {
            $tdata['menu_opt_link'] = "?page=index&sw_opt=1";
            if (!empty($cfg['index_page'])) {
                $page = $cfg['index_page'];
            } else {
                $page = 'index';
            }
        }

        if (empty($prefs->getPrefsItem('hide_opt'))) {
            $tdata['menu_opt'] = $this->getMenuOptions();
            $tdata['arrow'] = '&uarr;';
        } else {
            $tdata['arrow'] = '&darr;';
        }

        $tdata['page'] = $page;
        return $this->getTpl('menu', $tdata);
    }

    function getFooter() {
        global $db, $cfg;

        $cfg['show_querys'] ?? 0;
        $querys = $db->getQuerys();
        valid_array($querys) ? $num_querys = count($querys) : $num_querys = 0;
        $tdata['num_querys'] = $num_querys;
        $cfg['show_querys'] ? $tdata['querys'] = $querys : null;

        return $this->getTpl('footer', $tdata);
    }

    function getMenuOptions() {
        global $cfg, $LNG, $prefs;

        $tdata = [];

        $tdata['page'] = Filter::getString('page');

        !empty($_POST['search_keyword']) ? $tdata['search_keyword'] = Filter::postString('search_keyword') : null;
        !empty($_GET['search_keyword']) ? $tdata['search_keyword'] = Filter::getString('search_keyword') : null;

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
        if (isset($_POST['new_ignore_size'])) {
            $prefs->setPrefsItem('new_ignore_size', Filter::postString('new_ignore_size'));
        }
        if (isset($_POST['new_ignore_words_enable'])) {
            $prefs->setPrefsItem('new_ignore_words_enable', Filter::postString('new_ignore_words_enable'));
        }
        if (isset($_POST['new_ignore_size_enable'])) {
            $prefs->setPrefsItem('new_ignore_size_enable', Filter::postString('new_ignore_size_enable'));
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
        ($prefs->getPrefsItem('max_identify_items') == 0) ? $tdata['max_id_sel_0'] = 'selected' : $tdata['max_id_sel_0'] = '';
        $tdata['max_id_sel_0'] = $tdata['max_id_sel_5'] = $tdata['max_id_sel_10'] = $tdata['max_id_sel_20'] = $tdata['max_id_sel_50'] = '';
        $tdata['max_id_sel_' . $prefs->getPrefsItem('max_identify_items') . ''] = 'selected';

        if (isset($_POST['show_collections'])) {
            $prefs->setPrefsItem('show_collections', Filter::postString('show_collections'));
            $prefs->setPrefsItem('show_genres', 0);
        }
        if (isset($_POST['show_genres'])) {
            $prefs->setPrefsItem('show_genres', Filter::postString('show_genres'));
            $prefs->setPrefsItem('show_collections', 0);
        }
        /* ROWS */
        $max_rows_sel_none = '';

        if (isset($_POST['num_rows_results'])) {
            if ($_POST['num_rows_results'] == $LNG['L_DEFAULT']) {
                $max_rows_sel_none = 'selected';
            } else {
                $num_rows_results = Filter::postInt('num_rows_results');
                $prefs->setPrefsItem('tresults_rows', $num_rows_results);
            }
        }

        $tdata['max_rows_sel_1'] = $tdata['max_rows_sel_2'] = $tdata['max_rows_sel_4'] = $tdata['max_rows_sel_6'] = $tdata['max_rows_sel_8'] = '';
        $tdata['max_rows_sel_10'] = $tdata['max_rows_sel_25'] = $tdata['max_rows_sel_50'] = '';
        $tdata['max_rows_sel_' . $prefs->getPrefsItem('tresults_rows') . ''] = 'selected';
        $tdata['max_rows_sel_none'] = $max_rows_sel_none;
        /* COLUMNS */

        $max_columns_sel_none = '';

        if (isset($_POST['num_columns_results'])) {
            if ($_POST['num_columns_results'] == $LNG['L_DEFAULT']) {
                $max_columns_sel_none = 'selected';
            } else {
                $num_columns_results = Filter::postInt('num_columns_results');
                $prefs->setPrefsItem('tresults_columns', $num_columns_results);
            }
        }
        $tdata['max_columns_sel_1'] = $tdata['max_columns_sel_2'] = $tdata['max_columns_sel_4'] = $tdata['max_columns_sel_6'] = '';
        $tdata['max_columns_sel_8'] = $tdata['max_columns_sel_10'] = '';
        $tdata['max_columns_sel_' . $prefs->getPrefsItem('tresults_columns') . ''] = 'selected';
        $tdata['max_columns_sel_none'] = $max_columns_sel_none;

        //new filters
        (!empty($prefs->getPrefsItem('sel_indexer')) && $prefs->getPrefsItem('sel_indexer') == 'sel_indexer_none') ? $selected_idx_none = 'selected' : $selected_idx_none = '';

        $tdata['sel_indexers'] = '<option ' . $selected_idx_none . ' value="sel_indexer_none">' . $LNG['L_ALL'] . '</option>';

        foreach ($cfg['jackett_indexers'] as $indexer) {
            (!empty($prefs->getPrefsItem('sel_indexer')) && $prefs->getPrefsItem('sel_indexer') == $indexer) ? $selected_indexer = 'selected="selected"' : $selected_indexer = '';
            $tdata['sel_indexers'] .= '<option ' . $selected_indexer . ' value="' . $indexer . '">' . $indexer . '</option>';
        }
        /* FIN */

        return $this->getTpl('menu_options', $tdata);
    }

    function buildTable($items, $opt) {
        global $prefs;

        $html_items = '';
        $items_rows = '';
        $page = '';
        $columns = $prefs->getPrefsItem('tresults_columns');
        $i = 1;

        foreach ($items as $item) {
            $html_items .= html::div(['class' => 'divTableCell'], $this->buildItem($item, $opt));
            if ($i == $columns) {
                $items_rows .= html::div(['class' => 'divTableRow'], $html_items);
                $html_items = '';
                $i = 1;
            } else {
                $i++;
            }
        }
        !empty($html_items) ? $items_rows .= html::div(['class' => 'divTableRow'], $html_items) : null;

        $head = !empty($opt['head']) ? ucfirst($opt['head']) : '';
        $page .= $this->getTpl('items_table', ['head' => $head, 'items' => $items_rows]);
        return $page;
    }

    function buildCollection(array $collections, array $opt) {
        global $prefs;

        $media_type = $opt['media_type'];
        $tdata['head'] = ucfirst($media_type);
        $columns = $prefs->getPrefsItem('tresults_columns');
        $tdata['collection_items'] = '';
        $items = '';
        $items_table = '';
        $tdata['get_params'] = '';

        if (!empty($opt['get_params']) && valid_array($opt['get_params'])) {
            foreach ($opt['get_params'] as $kparam => $vparam) {
                empty($tdata['get_params']) ? $tdata['get_params'] = '?' . $kparam . '=' . $vparam : $tdata['get_params'] .= '&' . $kparam . '=' . $vparam;
            }
        }

        $i = 1;
        foreach ($collections as $collection) {
            $fitems = $this->getTpl($opt['item_tpl'], array_merge($collection, $tdata));
            $items .= html::div(['class' => 'divTableCell'], $fitems);
            if ($i == $columns) {
                $items_table .= html::div(['class' => 'divTableRow'], $items);
                $items = '';
                $i = 1;
            } else {
                $i++;
            }
        }
        !empty($items) ? $items_table .= html::div(['class' => 'divTableRow'], $items) : null;

        $tdata['items'] = $items_table;

        $page_collection = $this->getTpl('items_table', $tdata);

        return $page_collection;
    }

    function buildItem($item, $opt) {

        $page = '';
        $item['poster'] = get_poster($item);

        !empty($item['release']) ? $item['title'] = $item['title'] . ' (' . strftime("%Y", strtotime($item['release'])) . ')' : null;
        empty($item['trailer']) && !empty($item['guessed_trailer']) && $item['guessed_trailer'] != -1 ? $item['trailer'] = $item['guessed_trailer'] : null;
        !empty($item['size']) ? $item['size'] = human_filesize($item['size']) : null;
        !empty($item['total_size']) ? $item['total_size'] = human_filesize($item['total_size']) : null;

        $page .= $this->getTpl('item_display', array_merge($item, $opt));

        return $page;
    }

    /*
     * opt:
     * get_params = url links params
     * npage = page number
     * nitems = number of items
     * media_type = movies/shows
     */

    function getPager(array $opt) {
        global $prefs;

        $pages_links = '';
        $columns = $prefs->getPrefsItem('tresults_columns');
        $rows = $prefs->getPrefsItem('tresults_rows');
        $page = Filter::getString('page');
        $items_per_page = $columns * $rows;
        $num_pages = ceil($opt['nitems'] / $items_per_page);
        $npage = $opt['npage'];
        $inpage = '';

        $get_params['page'] = $page;
        $get_params['search_type'] = $opt['media_type'];

        if (!empty($opt['get_params']) && valid_array($opt['get_params'])) {

            foreach ($opt['get_params'] as $kparam => $vparam) {
                $get_params[$kparam] = $vparam;
            }
        }

        if ($num_pages > 1) {

            $link_nav_opt = ['class' => 'num_pages_link', 'inpage' => $opt['media_type']];
            $link_nav_parms = $get_params;
            $link_nav_parms['npage'] = $npage > 1 ? $npage - 1 : 1;
            //Avoid show_loading if click same page since not reload on got stuck
            if ($npage != $link_nav_parms['npage']) {
                $link_nav_opt['onClick'] = 'show_loading()';
            } else {
                unset($link_nav_opt['onClick']);
            }
            $pages_links .= html::link($link_nav_opt, '', '&#x23F4;', $link_nav_parms);

            for ($i = 1; $i <= ceil($num_pages); $i++) {
                $same_page = 0;

                if (($i == 1 || $i == $num_pages || $i == $npage) ||
                        in_range($i, ($npage - 2), ($npage + 2), TRUE)
                ) {

                    $link_npage_class = "num_pages_link";

                    if (isset($npage) && ($npage == $i)) {
                        $same_page = 1;
                        $link_npage_class .= '_selected';
                    }
                    $link_options = [];

                    $get_params['npage'] = $i;
                    //click same page number page not reload then loading not dissapear, avoid
                    (empty($same_page)) ? $link_options['onClick'] = 'show_loading()' : null;

                    $link_options['inpage'] = $inpage;
                    $link_options['class'] = $link_npage_class;
                    $link_options['inpage'] = $opt['media_type'];
                    $pages_links .= html::link($link_options, '', $i, $get_params);
                }
            }

            $link_nav_parms['npage'] = $npage < $num_pages ? $npage + 1 : $num_pages;
            if ($npage != $link_nav_parms['npage']) {
                $link_nav_opt['onClick'] = 'show_loading()';
            } else {
                unset($link_nav_opt['onClick']);
            }
            $pages_links .= html::link($link_nav_opt, '', '&#x23F5;', $link_nav_parms);
        }

        return html::div(['class' => 'type_pages_numbers'], $pages_links);
    }

}
