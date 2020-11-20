<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
session_start();

if (isset($_GET['profile']) && array_key_exists($_GET['profile'], $cfg['profiles'])) {
    $_SESSION['profile'] = $_GET['profile'];
}
if (isset($_SESSION['profile']) && array_key_exists($_SESSION['profile'], $cfg['profiles'])) {
    $cfg['profile'] = $_SESSION['profile'];
} else {
    $cfg['profile'] = 0;
}


