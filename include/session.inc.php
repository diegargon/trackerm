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

if (isset($_GET['profile']) && array_key_exists($_GET['profile'], $cfg['profiles'])) {

    $_SESSION['profile'] = $_GET['profile'];
    setcookie("profile", $_SESSION['profile'], time() + 3600000);
    $cfg['profile'] = $_GET['profile'];
}

if (!isset($_GET['profile']) && !isset($_SESSION['profile']) && isset($_COOKIE['profile']) && array_key_exists($_COOKIE['profile'], $cfg['profiles'])) {
    $_SESSION['profile'] = $_COOKIE['profile'];
    $cfg['profile'] = $_COOKIE['profile'];
}

if (isset($_SESSION['profile']) && array_key_exists($_SESSION['profile'], $cfg['profiles'])) {
    $cfg['profile'] = $_SESSION['profile'];
} else {
    $cfg['profile'] = 0;
}

loadPrefs();
