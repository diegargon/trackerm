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
//        $tdata['css_file'] = $this->getCssFile($cfg['theme'], $cfg['css']);

        return $this->getTpl('html_mstruct', $tdata);
    }

    function getTpl(string $tpl, array $tdata = []) {
        global $cfg, $L, $user; //NO delete work for templates

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
        global $L;

        (substr($msg['title'], 0, 2) == 'L_') ? $msg['title'] = $L[$msg['title']] : null;
        (substr($msg['body'], 0, 2) == 'L_') ? $msg['body'] = $L[$msg['body']] : null;
        return $this->getTpl('msgbox', $msg);
    }

    function msgPage(array $msg) {
        global $cfg;

        $footer = $this->getFooter();
        $menu = $this->getMenu();
        $body = $this->msgBox(['title' => $msg['title'], 'body' => $msg['body']]);
        $tdata = ['menu' => $menu, 'body' => $body, 'footer' => $footer];
        $tdata['css_file'] = $this->getCssFile($cfg['theme'], $cfg['css_file']);
        echo $this->getTpl('html_mstruct', $tdata);

        exit();
    }

    function getMenu() {
        global $cfg, $L, $user;

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

        $tdata = [];

        return $this->getTpl('footer', $tdata);
    }

    function getMenuOptions() {
        global $cfg, $L;

        $tdata['page'] = Filter::getString('page');


        return $this->getTpl('menu_options', $tdata);
    }

}
