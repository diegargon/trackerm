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

//POST/GET
    static function getInt($val, $size = PHP_INT_MAX) {
        if (!isset($_GET[$val])) {
            return false;
        }

        return self::varInt($_GET[$val], $size);
    }

    static function postInt($val, $size = PHP_INT_MAX) {
        if (!isset($_POST[$val])) {
            return false;
        }

        return self::varInt($_POST[$val], $size);
    }

    static function varInt($val, $size = PHP_INT_MAX) {
        if (!isset($val)) {
            return false;
        }

        if (!is_array($val)) {
            $val = (int) $val;
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
                $values[$key] = (int) trim($val);
                if (!is_numeric($val) || $val > $size) {
                    return false;
                }
                if (!is_numeric($key)) {
                    return false;
                }
            }
        }

        return $values;
    }

//Simple String words without accents or special characters
    static function getString($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varString($_GET[$val], $size);
    }

    static function postString($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varString($_POST[$val], $size);
    }

    //TODO FILTER
    static function varString($val, $size = null) {
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
    static function getUtf8($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varUtf8($_GET[$val], $size);
    }

    static function postUtf8($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varUtf8($_POST[$val], $size);
    }

    static function varUtf8($val, $size = null) {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }
//TODO FILTER
        return $val;
    }

//URL
    static function getUrl($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varUrl($_GET[$val], $size);
    }

    static function postUrl($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varUrl($_POST[$val], $size);
    }

    static function varUrl($val, $size = null) {
        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }
//TODO
//$url = filter_var($var, FILTER_SANITIZE_URL);
//$url = filter_var($url, FILTER_VALIDATE_URL);
        return $val;
    }

    static function getImgUrl($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varImgUrl($_GET[$val], $size);
    }

    static function postImgUrl($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varImgUrl($_POST[$val], $size);
    }

    static function varImgUrl($val, $size = null) {
        $exts = array('jpg', 'gif', 'png');

        if (empty($val) || (!empty($size) && (strlen($val) > $size))) {
            return false;
        }

        if (filter_var($val, FILTER_VALIDATE_URL) &&
                in_array(strtolower(pathinfo($val, PATHINFO_EXTENSION)), $exts)) {
            return true;
        } else {
            return false;
        }
    }

    // AZaz
    static function postAzChar($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varAzChar($_POST[$val], $size);
    }

    static function getAzChar($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varAzChar($_GET[$val], $size);
    }

    static function varAzChar($var, $max_size = null, $min_size = null) {

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
    static function postAlphanum($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varAlphanum($_POST[$val], $size);
    }

    static function getAlphanum($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varAlphanum($_GET[$val], $size);
    }

    static function varAlphanum($var, $max_size = null, $min_size = null) {
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
    static function postUsername($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varUsername($_POST[$val], $size);
    }

    static function getUsername($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varUsername($_GET[$val], $size);
    }

    static function varUsername($var, $max_size = null, $min_size = null) {

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
    static function postEmail($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varEmail($_POST[$val], $size);
    }

    static function getEmail($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varEmail($_GET[$val], $size);
    }

    static function varEmail($var, $max_size = null, $min_size = null) {

        if ((empty($var) ) || (!empty($max_size) && (strlen($var) > $max_size) ) || (!empty($min_size) && (strlen($var) < $min_size))) {
            return false;
        }
        //TODO

        return $var;
    }

    //Strict Chars: at least [A-z][0-9] _

    static function postStrict($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varStrict($_POST[$val], $size);
    }

    static function getStrict($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varStrict($_GET[$val], $size);
    }

    static function varStrict($var, $max_size = null, $min_size = null) {

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

    //File name

    static function postFilename($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varStrict($_POST[$val], $size);
    }

    static function getFilename($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varStrict($_GET[$val], $size);
    }

    static function varFilename($var, $max_size = null, $min_size = null) {

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
    static function postPassword($val, $size = null) {
        if (empty($_POST[$val])) {
            return false;
        }

        return self::varPassword($_POST[$val], $size);
    }

    static function getPassword($val, $size = null) {
        if (empty($_GET[$val])) {
            return false;
        }

        return self::varPassword($_GET[$val], $size);
    }

    static function varPassword($var, $max_size = null, $min_size = null) {

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
