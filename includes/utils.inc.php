<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function in_range($number, $min, $max, $inclusive = FALSE) {
    if (is_int($number) && is_int($min) && is_int($max)) {
        return $inclusive ? ($number >= $min && $number <= $max) : ($number > $min && $number < $max);
    }

    return FALSE;
}

function get_user_ip() {
    return $_SERVER['REMOTE_ADDR'];
}

function valid_array($array) {
    if (!empty($array) && is_array($array) && count($array) > 0) {
        return true;
    }

    return false;
}

function notify_mail($msg) {
    global $db, $LNG;

    $tag = "[TRACKERM] ";
    $subject = $tag . $msg['subject'];

    $lib_stats = getLibraryStats();
    $footer = "\n\n -- \n {$LNG['L_STATS']} \n";
    if (isset($lib_stats['movies_paths']) && valid_array($lib_stats['movies_paths'])) {
        foreach ($lib_stats['movies_paths'] as $path_key => $path) {
            $footer .= $path_key . '(' . $path['basename'] . ') ' . $path['free'] . '/' . $path['total'] . "\n";
        }
    }
    if (isset($lib_stats['shows_paths']) && valid_array($lib_stats['shows_paths'])) {
        foreach ($lib_stats['shows_paths'] as $path_key => $path) {
            $footer .= $path_key . '(' . $path['basename'] . ') ' . $path['free'] . '/' . $path['total'] . "\n";
        }
    }
    $msg['msg'] .= $footer;
    $results = $db->query("SELECT id,email FROM users WHERE email <> ''");
    $users = $db->fetchAll($results);

    foreach ($users as $user) {
        if (getPrefValueByUid($user['id'], 'email_notify')) {
            mail($user['email'], $subject, $msg['msg'], "From: no@reply \r\n");
        }
    }
}

function format_seconds($s) {
    return gmdate("H:i:s", (int) $s);
}

function random_names() {
    global $character_names, $character_surnames;

    $names_elements = count($character_names);
    $surnames_elements = count($character_surnames);
    //var_dump($character_names);
    $name = $character_names[rand(0, $names_elements - 1)];
    $surname = $character_surnames[rand(0, $surnames_elements - 1)];

    return $name . ' ' . $surname;
}

function chance(int $chance) {
    $random = rand(1, 100);
    if ($random <= $chance) {
        return true;
    }
    return false;
}
