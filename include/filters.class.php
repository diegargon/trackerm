<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
Class Filter {

    function __construct() {

    }

    //POST/GET
    function getInt($val, $size = PHP_INT_MAX) {
        if (empty($_GET[$val]) || $_GET[$val] > $size) {
            return false;
        }

        return $_GET[$val];
    }

    function postInt($val, $size = PHP_INT_MAX) {
        if (empty($_POST[$val]) || $_POST[$val] > $size) {
            return false;
        }

        return $_POST[$val];
    }

    function getString($val, $size = null) {
        if (empty($_GET[$val]) || (!empty($size) && (strlen($_GET[$val]) > $size))) {
            return false;
        }
        return $_GET[$val];
    }

    function postString($val, $size = null) {
        if (empty($_POST[$val]) || (!empty($size) && (strlen($_POST[$val]) > $size))) {
            return false;
        }
        return $_POST[$val];
    }

}
