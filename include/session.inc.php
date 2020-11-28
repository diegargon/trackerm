<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
require('include/prefs.inc.php');
session_start();

$profile = $filter->getInt($profile);

if (isset($profile) && array_key_exists($profile, $cfg['profiles'])) {

    $_SESSION['profile'] = $profile;
    setcookie("profile", $_SESSION['profile'], time() + 3600000);
    $cfg['profile'] = $profile;
}

if (!isset($profile) && !isset($_SESSION['profile']) && isset($_COOKIE['profile']) && array_key_exists($_COOKIE['profile'], $cfg['profiles'])) {
    $_SESSION['profile'] = $_COOKIE['profile'];
    $cfg['profile'] = $_COOKIE['profile'];
}

if (isset($_SESSION['profile']) && array_key_exists($_SESSION['profile'], $cfg['profiles'])) {
    $cfg['profile'] = $_SESSION['profile'];
} else {
    $cfg['profile'] = 0;
}

loadPrefs();
