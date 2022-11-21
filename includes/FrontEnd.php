<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
class FrontEnd {

    public function __construct() {

    }

    function buildPage(array $page_data) {
        global $cfg;

        $body = '';

        $page_data['request_page'] !== 'login' ? $menu = $this->getMenu() : $menu = '';

        if (isset($page_data['msg_type'])) {
            if ($page_data['msg_type'] === 1) {
                $body .= $this->msgBox($page_data);
            } else {
                $body .= $this->msgBox(['title' => 'L_ERROR', 'body' => 'L_ERR_UNKNOWN_CODE']);
            }
        } else if (isset($page_data['templates']) && is_array($page_data['templates'])) {
            $body .= $this->procTplData($page_data);
        } else {
            exit('Render error: buildpage');
        }

        $footer = $this->getFooter();
        $tdata = ['menu' => $menu, 'body' => $body, 'footer' => $footer];
        $tdata['css_file'] = $this->getCssFile($cfg['theme'], $cfg['css']);

        return $this->getTpl('html_mstruct', $tdata);
    }

    function procTplData(array $page_data) {
        $html = '';
        $templates = $page_data['templates'];
        //var_dump($templates);
        //var_dump($page_data);
        usort($templates, function ($a, $b) {
            return $b['tpl_pri'] <=> $a['tpl_pri'];
        });

        //var_dump($page_data);
        //var_dump($templates);
        foreach ($templates as $key_template => &$template) {
            $tpl = '';
            $t_vars = [];
            $items_break = null;

            if (!empty($template['tpl_vars'])) {
                $t_vars = $template['tpl_vars'];
            }

            //var_dump($t_vars);
            //exit();
            if (count($t_vars) < 1 || !is_array($t_vars[array_key_first($t_vars)])) {
                $tpl = $this->getTpl($template['tpl_file'], $t_vars);
            } else { // is array of array
                //var_dump($template);
                $common_opts = [];
                if (!empty($template['tpl_common_vars']) && is_array($template['tpl_common_vars'])) {
                    $common_opts = $template['tpl_common_vars'];
                }
                $i = 1;
                $nelements = count($t_vars);
                if (isset($common_opts) && !empty($common_opts['tpl_items_break'])) {
                    $items_break = 1; //$common_opts['tpl_items_break'];
                }

                //var_dump($t_vars);

                foreach ($t_vars as $t_vars_element) {
                    ($i === 1) ? $t_vars_element['tpl_var_ary_first'] = 1 : null;

                    ($i === $nelements) ? $t_vars_element['tpl_var_ary_last'] = 1 : null;
                    !empty($items_break) ? $t_vars_element['tpl_var_ary_item_break'] = $items_break : null;

                    $tpl .= $this->getTpl($template['tpl_file'], array_merge($t_vars_element, $common_opts));
                    if (!empty($items_break)) {
                        $items_break++;
                        if ($items_break > $common_opts['tpl_items_break']) {
                            $items_break = 1;
                        }
                    }
                    $i++;
                }
            }

            if (!empty($template['tpl_place']) && !empty($template['tpl_place_var'])) {

                //we place content withit tpl_vars on tpl_place name template
                foreach ($templates as $_key_template => $_template) {

                    if ($_template['name'] == $template['tpl_place']) {

                        if (empty($templates[$_key_template]['tpl_vars'][$template['tpl_place_var']]) || isset($template['tpl_place_replace'])) {
                            $templates[$_key_template]['tpl_vars'][$template['tpl_place_var']] = $tpl;
                        } else {
                            $templates[$_key_template]['tpl_vars'][$template['tpl_place_var']] .= $tpl;
                        }
                    }
                    //TODO Warn if template name not found
                }
            } else {
                $html .= $tpl;
            }

            unset($templates[$key_template]);
        }


        return $html;
    }

    function getTpl(string $tpl, array $tdata = []) {
        global $cfg, $LNG, $user, $prefs; //NO delete need for templates

        ob_start();
        $tpl_file = 'tpl/' . $cfg['theme'] . '/' . $tpl . '.tpl.php';
        !file_exists($tpl_file) ? $tpl_file = 'tpl/default/' . $tpl . '.tpl.php' : null;
        //TODO deal if still not exist
        include($tpl_file);

        return ob_get_clean();
    }

    function getCssFile(string $theme, string $css) {
        $css_file = 'tpl/' . $theme . '/css/' . $css . '.css';
        !file_exists($css_file) ? $css_file = 'tpl/default/css/default.css' : null;
        $css_file .= '?nocache = ' . time(); //TODO: To Remove: avoid cache css while dev

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
            $tdata['arrow'] = '&uarr;
            ';
        } else {
            $tdata['arrow'] = '&darr;
            ';
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

        ($prefs->getPrefsItem('max_identify_items') == 0) ? $tdata['max_id_sel_0'] = 'selected' : $tdata['max_id_sel_0'] = '';
        $tdata['max_id_sel_0'] = $tdata['max_id_sel_5'] = $tdata['max_id_sel_10'] = $tdata['max_id_sel_20'] = $tdata['max_id_sel_50'] = '';
        $tdata['max_id_sel_' . $prefs->getPrefsItem('max_identify_items') . ''] = 'selected';

        /* Collections */
        $collections_sel = 'collection_sel_none';
        if (!empty(Filter::postInt('show_collections'))) {
            $collections_sel = 'collection_sel_' . Filter::postInt('show_collections');
        }
        if (!empty($prefs->getPrefsItem('show_collections'))) {
            $collections_sel = 'collection_sel_' . $prefs->getPrefsItem('show_collections');
        }
        $tdata[$collections_sel] = 'selected';

        /* ROWS */
        $max_rows_sel_none = '';

        if ($prefs->getPrefsItem('tresults_rows')) {
            if ($prefs->getPrefsItem('tresults_rows') == $LNG['L_DEFAULT']) {
                $max_rows_sel_none = 'selected';
            }
        }

        $tdata['max_rows_sel_1'] = $tdata['max_rows_sel_2'] = $tdata['max_rows_sel_4'] = $tdata['max_rows_sel_6'] = $tdata['max_rows_sel_8'] = '';
        $tdata['max_rows_sel_10'] = $tdata['max_rows_sel_25'] = $tdata['max_rows_sel_50'] = '';
        $tdata['max_rows_sel_' . $prefs->getPrefsItem('tresults_rows') . ''] = 'selected';
        $tdata['max_rows_sel_none'] = $max_rows_sel_none;

        /* COLUMNS */
        $max_columns_sel_none = '';

        if ($prefs->getPrefsItem('tresults_columns')) {
            if ($prefs->getPrefsItem('tresults_columns') == $LNG['L_DEFAULT']) {
                $max_columns_sel_none = 'selected';
            }
        }
        $tdata['max_columns_sel_1'] = $tdata['max_columns_sel_2'] = $tdata['max_columns_sel_4'] = $tdata['max_columns_sel_6'] = '';
        $tdata['max_columns_sel_8'] = $tdata['max_columns_sel_10'] = '';
        $tdata['max_columns_sel_' . $prefs->getPrefsItem('tresults_columns') . ''] = 'selected';
        $tdata['max_columns_sel_none'] = $max_columns_sel_none;

        //new filters
        (!empty($prefs->getPrefsItem('sel_indexer')) && $prefs->getPrefsItem('sel_indexer') == 'sel_indexer_none') ? $selected_idx_none = 'selected' : $selected_idx_none = '';

        $tdata['sel_indexers'] = '<option ' . $selected_idx_none . ' value = "sel_indexer_none">' . $LNG['L_ALL'] . '</option>';

        foreach ($cfg['jackett_indexers'] as $indexer) {
            (!empty($prefs->getPrefsItem('sel_indexer')) && $prefs->getPrefsItem('sel_indexer') == $indexer) ? $selected_indexer = 'selected = "selected"' : $selected_indexer = '';
            $tdata['sel_indexers'] .= '<option ' . $selected_indexer . ' value = "' . $indexer . '">' . $indexer . '</option>';
        }
        /* FIN */

        return $this->getTpl('menu_options', $tdata);
    }

}
