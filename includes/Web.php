<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Web {

    private $frontend;
    private $backend;

    function __construct() {
        $this->backend = new BackEnd();
        $this->frontend = new FrontEnd();
    }

    function render() {
        $req_page = $this->getRequestPage();

        if ($req_page === false) {
            //User msgPage or template o modificar msgPage con page_data template?
            //$this->frontend->msgPage(['title' => 'L_ERROR', 'body' => 'L_PAGE_NOEXISTS']);
            $page_data['templates'][] = [
                'name' => 'msgbox', 'tpl_file' => 'msgbox', 'tpl_pri' => 4,
                'tpl_vars' => [
                    'title' => 'L_ERROR',
                    'body' => 'L_PAGE_NOEXISTS',
                ]
            ];
            $page_data['request_page'] = 'error';
        } else {
            $page_data = $this->backend->getPageData($req_page);
        }

        echo $this->frontend->buildPage($page_data);
    }

    function getRequestPage() {
        global $cfg, $user, $prefs;

        $req_page = Filter::getString('page');

        ($user->getId() < 1) ? $req_page = 'login' : null;

        if (empty($req_page) && $user->getId() > 0) {
            $index_page = trim($prefs->getPrefsItem('index_page'));
            (empty($index_page)) ? $index_page = 'index' : null;
            header("Location: {$cfg['REL_PATH']}/?page=$index_page");
            exit();
        }
        $valid_pages = ['index', 'library', 'news', 'tmdb', 'torrents', 'view', 'view_group', 'view_genres', 'view_director', 'view_cast', 'view_writer', 'wanted', 'identify',
            'download', 'localplayer', 'identify', 'download', 'transmission', 'config', 'login', 'logout'];

        (!isset($req_page) || $req_page == '') ? $req_page = 'index' : null;
        (in_array($req_page, ['library_movies', 'library_shows'])) ? $req_page = 'library' : null;
        (in_array($req_page, ['new_movies', 'new_shows'])) ? $req_page = 'news' : null;
        ($req_page == 'config' && !$user->isAdmin()) ? $req_page = 'index' : null;
        ($req_page == 'localplayer' && !$cfg['localplayer']) ? $req_page = 'index' : null;

        if (in_array($req_page, $valid_pages)) {
            return $req_page;
        }

        return false;
    }

}
