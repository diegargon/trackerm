<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
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

    //TODO FILTER
    function varString($val, $size = null) {
        if (empty($val)) {
            return false;
        }

        if (is_array($val)) {

        } else {
            if ((!empty($size) && (strlen($val) > $size))) {
                return false;
            }
        }

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
//TODO
//$url = filter_var($var, FILTER_SANITIZE_URL);
//$url = filter_var($url, FILTER_VALIDATE_URL);
        return $val;
    }

    // AZaz
    function postAzChar($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return $this->varAzChar($_POST[$val], $size);
    }

    function getAzChar($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return $this->varAzChar($_GET[$val], $size);
    }

    function varAzChar($var, $max_size = null, $min_size = null) {

        if ((empty($var) ) || (!empty($max_size) && (strlen($var) > $max_size) ) || (!empty($min_size) && (strlen($var) < $min_size))
        ) {
            return false;
        }
        if (preg_match('/[^A-Za-z]/', $var)) {
            return false;
        }

        return $var;
    }

    //[0-9][A-Za-z]
    function postAlphanum($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return $this->varAlphanum($_POST[$val], $size);
    }

    function getAlphanum($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return $this->varAlphanum($_GET[$val], $size);
    }

    function varAlphanum($var, $max_size = null, $min_size = null) {
        if ((empty($var) ) || (!empty($max_size) && (strlen($var) > $max_size) ) || (!empty($min_size) && (strlen($var) < $min_size))
        ) {
            return false;
        }
        if (!preg_match('/^[A-Za-z0-9]+$/', $var)) {
            return false;
        }

        return $var;
    }

    //USERNAME
    function postUsername($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return $this->varUsername($_POST[$val], $size);
    }

    function getUsername($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return $this->varUsername($_GET[$val], $size);
    }

    function varUsername($var, $max_size = null, $min_size = null) {

        if ((empty($var) ) || (!empty($max_size) && (strlen($var) > $max_size) ) || (!empty($min_size) && (strlen($var) < $min_size))) {
            return false;
        }
        //TODO
        //if (!preg_match($user_name_regex, $var)) {
        //return false;
        //}

        return $var;
    }

    //EMAIL
    function postEmail($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return $this->varEmail($_POST[$val], $size);
    }

    function getEmail($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return $this->varEmail($_GET[$val], $size);
    }

    function varEmail($var, $max_size = null, $min_size = null) {

        if ((empty($var) ) || (!empty($max_size) && (strlen($var) > $max_size) ) || (!empty($min_size) && (strlen($var) < $min_size))) {
            return false;
        }
        //TODO

        return $var;
    }

    //Strict Chars: at least [A-z][0-9] _

    function postStrict($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return $this->varStrict($_POST[$val], $size);
    }

    function getStrict($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return $this->varStrict($_GET[$val], $size);
    }

    function varStrict($var, $max_size = null, $min_size = null) {

        if ((empty($var) ) || (!empty($max_size) && (strlen($var) > $max_size) ) || (!empty($min_size) && (strlen($var) < $min_size))
        ) {
            return false;
        }
        //TODO
        //if (preg_match('', $var)) {
        //    return false;
        //}

        return $var;
    }

    // PASSWORD
    function postPassword($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return $this->varPassword($_POST[$val], $size);
    }

    function getPassword($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return $this->varPassword($_GET[$val], $size);
    }

    function varPassword($var, $max_size = null, $min_size = null) {

        if ((!empty($max_size) && (strlen($var) > $max_size) ) || (!empty($min_size) && (strlen($var) < $min_size))
        ) {
            return false;
        }
        //TODO
        //if (!preg_match('/^(\S+)+$/', $var)) {
        //    return false;
        //        }
        return $var;
    }

}
