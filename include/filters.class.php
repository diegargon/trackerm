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
        if (!isset($_GET[$val])) {
            return false;
        }
        if (!is_array($_GET[$val])) {
            if (!isset($_GET[$val]) || $_GET[$val] > $size || !is_numeric($_GET[$val])) {
                return false;
            }
            $values = $_GET[$val];
        } else {
            $values = $_GET[$val];
            if (count($values) <= 0) {
                return false;
            }
            foreach ($values as $val) {
                if (!is_numeric($val) || $val > $size) {
                    return false;
                }
            }
        }

        return $values;
    }

    function postInt($val, $size = PHP_INT_MAX) {
        if (!isset($_POST[$val])) {
            return false;
        }
        if (!is_array($_POST[$val])) {
            if (!isset($_POST[$val]) || $_POST[$val] > $size || !is_numeric($_POST[$val])) {
                return false;
            }
            $values = $_POST[$val];
        } else {
            $values = $_POST[$val];
            if (count($values) <= 0) {
                return false;
            }
            foreach ($values as $val) {
                if (!is_numeric($val) || $val > $size) {
                    return false;
                }
            }
        }

        return $values;
    }

    function getString($val, $size = null) {
        if (empty($_GET[$val]) || (!empty($size) && (strlen($_GET[$val]) > $size))) {
            return false;
        }
        //TODO FILTER
        return $_GET[$val];
    }

    function postString($val, $size = null) {
        if (empty($_POST[$val]) || (!empty($size) && (strlen($_POST[$val]) > $size))) {
            return false;
        }
        //TODO FILTER
        return $_POST[$val];
    }

}
