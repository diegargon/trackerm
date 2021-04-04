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

    function __construct($frontend) {
        $this->frontend = $frontend;
    }

    function render() {

        $req_page = $this->getPage();

        if ($req_page === false) {
            $this->frontend->msgPage(['title' => 'L_ERROR', 'body' => 'L_PAGE_NOEXISTS']);
        }

        echo $this->frontend->buildPage($req_page);
    }

    function getPage() {
        global $cfg, $user;

        $req_page = Filter::getString('page');
        ($user->id() < 1) ? $req_page = 'login' : null;

        if (empty($req_page) && $user->id() > 0) {
//            $index_page = trim(getPrefsItem('index_page'));
            if (!empty($index_page) && $index_page != "index") {
                header("Location: ?page=$index_page");
                exit();
            }
        }
        $valid_pages = ['index', 'shipyard', 'ships', 'planets', 'research', 'production', 'config', 'login', 'logout'];

        (!isset($req_page) || $req_page == '') ? $req_page = 'index' : null;
        ($req_page == 'config' && $user->isAdmin() != 1) ? $req_page = 'index' : null;

        if (in_array($req_page, $valid_pages)) {
            return $req_page;
        }

        return false;
    }

}
