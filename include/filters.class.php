<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

Class Filter {

    function __construct() {

    }

    //POST/GET
    function getInt($val, $size = PHP_INT_MAX) {
        if (!isset($_GET[$val])) {
            return false;
        }

        return $this->varInt($_GET[$val], $size);
    }

    function postInt($val, $size = PHP_INT_MAX) {
        if (!isset($_POST[$val])) {
            return false;
        }

        return $this->varInt($_POST[$val], $size);
    }

    function varInt($val, $size = PHP_INT_MAX) {
        if (!isset($val)) {
            return false;
        }

        if (!is_array($val)) {

            if (!isset($val) || trim($val) > $size || !is_numeric(trim($val))) {
                return false;
            }
            $values = trim($val);
        } else {
            $values = $val;
            if (count($values) <= 0) {
                return false;
            }
            foreach ($values as $key => $val) {
                $values[$key] = trim($val);
                if (!is_numeric($val) || $val > $size) {
                    return false;
                }
            }
        }

        return $values;
    }

    //Simple String words without accents or special characters
    function getString($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return $this->varString($_GET[$val], $size);
    }

    function postString($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return $this->varString($_POST[$val], $size);
    }

    function varString($val, $size = null) {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }

        //TODO FILTER
        return $val;
    }

    //UTF8
    function getUtf8($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return $this->varUtf8($_GET[$val], $size);
    }

    function postUtf8($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return $this->varUtf8($_POST[$val], $size);
    }

    function varUtf8($val, $size = null) {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }
        //TODO FILTER
        return $val;
    }

    //URL
    function getUrl($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return $this->varUrl($_GET[$val], $size);
    }

    function postUrl($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return $this->varUrl($_POST[$val], $size);
    }

    function varUrl($val, $size = null) {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }

        return $val;
    }

}
