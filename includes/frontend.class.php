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
        global $cfg, $LNG, $user; //NO delete need for templates

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
        global $cfg, $LNG, $user;

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

        if (!empty(Filter::getString('page'))) {
            $tdata['menu_opt_link'] = str_replace('&sw_opt=1', '', basename($_SERVER['REQUEST_URI'])) . '&sw_opt=1';
        } else {
            $tdata['menu_opt_link'] = "?page=index&sw_opt=1";
        }

        if (empty($cfg['hide_opt'])) {
            $tdata['menu_opt'] = $this->getMenuOptions();
            $tdata['arrow'] = '&uarr;';
        } else {
            $tdata['arrow'] = '&darr;';
        }

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
        global $cfg, $LNG;

        $tdata = [];

        (isset($_POST['rebuild_movies'])) ? rebuild('movies', $cfg['MOVIES_PATH']) : null;
        (isset($_POST['rebuild_shows'])) ? rebuild('shows', $cfg['SHOWS_PATH']) : null;

        $tdata['page'] = Filter::getString('page');

        !empty($_POST['search_term']) ? $tdata['search_term'] = Filter::postString('search_term') : null;
        !empty($_GET['search_term']) ? $tdata['search_term'] = Filter::getString('search_term') : null;

        if (isset($_POST['num_ident_toshow']) &&
                ($cfg['max_identify_items'] != $_POST['num_ident_toshow'])
        ) {
            $num_ident_toshow = Filter::postInt('num_ident_toshow');
            $cfg['max_identify_items'] = $num_ident_toshow;
            setPrefsItem('max_identify_items', $num_ident_toshow);
        }

        if (isset($_POST['new_ignore_keywords'])) {
            $cfg['new_ignore_keywords'] = Filter::postString('new_ignore_keywords');
            setPrefsItem('new_ignore_keywords', $cfg['new_ignore_keywords']);
        }

        if (isset($_POST['new_ignore_size'])) {
            $cfg['new_ignore_size'] = Filter::postString('new_ignore_size');
            setPrefsItem('new_ignore_size', $cfg['new_ignore_size']);
        }

        if (isset($_POST['new_ignore_words_enable'])) {
            $cfg['new_ignore_words_enable'] = Filter::postString('new_ignore_words_enable');
            setPrefsItem('new_ignore_words_enable', $cfg['new_ignore_words_enable']);
        }

        if (isset($_POST['new_ignore_size_enable'])) {
            $cfg['new_ignore_size_enable'] = Filter::postString('new_ignore_size_enable');
            setPrefsItem('new_ignore_size_enable', $cfg['new_ignore_size_enable']);
        }

        if (isset($_POST['sel_indexer'])) {
            $cfg['sel_indexer'] = Filter::postString('sel_indexer');
            setPrefsItem('sel_indexer', $cfg['sel_indexer']);
        }

        ($cfg['max_identify_items'] == 0) ? $tdata['max_id_sel_0'] = 'selected' : $tdata['max_id_sel_0'] = '';

        $tdata['max_id_sel_0'] = $tdata['max_id_sel_5'] = $tdata['max_id_sel_10'] = $tdata['max_id_sel_20'] = $tdata['max_id_sel_50'] = '';
        $tdata['max_id_sel_' . $cfg['max_identify_items'] . ''] = 'selected';

        /* ROWS */
        $max_rows_sel_none = '';

        if (isset($_POST['num_rows_results'])) {
            if ($_POST['num_rows_results'] == $LNG['L_DEFAULT']) {
                $max_rows_sel_none = 'selected';
            } else {
                $num_rows_results = Filter::postInt('num_rows_results');
                $cfg['tresults_rows'] = $num_rows_results;
                setPrefsItem('tresults_rows', $num_rows_results);
            }
        }

        $tdata['max_rows_sel_1'] = $tdata['max_rows_sel_2'] = $tdata['max_rows_sel_4'] = $tdata['max_rows_sel_6'] = $tdata['max_rows_sel_8'] = '';
        $tdata['max_rows_sel_10'] = $tdata['max_rows_sel_25'] = $tdata['max_rows_sel_50'] = '';
        $tdata['max_rows_sel_' . $cfg['tresults_rows'] . ''] = 'selected';
        $tdata['max_rows_sel_none'] = $max_rows_sel_none;
        /* COLUMNS */

        $max_columns_sel_none = '';

        if (isset($_POST['num_columns_results'])) {
            if ($_POST['num_columns_results'] == $LNG['L_DEFAULT']) {
                $max_columns_sel_none = 'selected';
            } else {
                $num_columns_results = Filter::postInt('num_columns_results');
                $cfg['tresults_columns'] = $num_columns_results;
                setPrefsItem('tresults_columns', $num_columns_results);
            }
        }

        $tdata['max_columns_sel_1'] = $tdata['max_columns_sel_2'] = $tdata['max_columns_sel_4'] = $tdata['max_columns_sel_6'] = '';
        $tdata['max_columns_sel_8'] = $tdata['max_columns_sel_10'] = '';
        $tdata['max_columns_sel_' . $cfg['tresults_columns'] . ''] = 'selected';
        $tdata['max_columns_sel_none'] = $max_columns_sel_none;

        //new filters
        (!empty($cfg['sel_indexer']) && $cfg['sel_indexer'] == 'sel_indexer_none') ? $selected_idx_none = 'selected' : $selected_idx_none = '';

        $tdata['sel_indexers'] = '<option ' . $selected_idx_none . ' value="sel_indexer_none">' . $LNG['L_ALL'] . '</option>';

        foreach ($cfg['jackett_indexers'] as $indexer) {
            (isset($cfg['sel_indexer']) && $cfg['sel_indexer'] == $indexer) ? $selected_indexer = 'selected="selected"' : $selected_indexer = '';
            $tdata['sel_indexers'] .= '<option ' . $selected_indexer . ' value="' . $indexer . '">' . $indexer . '</option>';
        }
        /* FIN */

        return $this->getTpl('menu_options', $tdata);
    }

}
