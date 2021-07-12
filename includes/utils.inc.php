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

function clean_title($title) {
    global $cfg;

    $title = getFileTitle($title);
    $title = strtolower($title);
    $title = iconv($cfg['charset'], "ASCII//TRANSLIT", $title);

    $from_replace_signs = [':', ';', '.', ',', '?', '¿', '@', '#', '$', '-', '_', '{', '}', '[', ']', '!', '¡', '+', '^', '`', '*', '/', '|', 'º', 'ª', '%', '=', '<', '>', '\''];
    $title = str_replace($from_replace_signs, '', $title);
    $title = preg_replace('/\(.*\)/', '', $title);
    $title = preg_replace('/\s+/', ' ', $title);

    return trim($title);
}

/* UNUSED: keep for if want change how clean_title clean */

function set_clean_titles() {
    global $db;

    $tables = ['tmdb_search_movies', 'tmdb_search_shows', 'jackett_movies', 'jackett_shows', 'library_history', 'library_shows', 'library_movies', 'shows_details'];

    foreach ($tables as $table) {
        $query = "SELECT * FROM $table WHERE clean_title IS NULL";
        $results = $db->query($query);
        $items = $db->fetchAll($results);

        foreach ($items as $item) {
            $title = clean_title($item['title']);
            $query = "UPDATE $table SET clean_title='$title' WHERE id={$item['id']}";
            $db->query($query);
        }
    }
}

function get_user_ip() {
    return $_SERVER['REMOTE_ADDR'];
}

function is_local_ip() {
    $ip = get_user_ip();

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return false;
    } else {
        return true;
    }
}

function getPerfTime() {
    return hrtime(true);
}

function formatPerfTime($time) {
    return round($time / 1e+9, 2);
}

function bytesToGB($bytes) {
    return $bytes / round(pow(1024, 3), 2);
}

function human_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

function isLinux() {
    if (get_operating_system() == 'Linux') {
        return true;
    }
    return false;
}

function get_operating_system() {
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $operating_system = 'Unknown Operating System';

    if (preg_match('/linux/i', $u_agent)) {
        $operating_system = 'Linux';
    } elseif (preg_match('/macintosh|mac os x|mac_powerpc/i', $u_agent)) {
        $operating_system = 'Mac';
    } elseif (preg_match('/windows|win32|win98|win95|win16/i', $u_agent)) {
        $operating_system = 'Windows';
    } elseif (preg_match('/ubuntu/i', $u_agent)) {
        $operating_system = 'Linux';
    } elseif (preg_match('/iphone/i', $u_agent)) {
        $operating_system = 'Mac';
    } elseif (preg_match('/ipod/i', $u_agent)) {
        $operating_system = 'Mac';
    } elseif (preg_match('/ipad/i', $u_agent)) {
        $operating_system = 'Mac';
    } elseif (preg_match('/android/i', $u_agent)) {
        $operating_system = 'Linux';
    }

    return $operating_system;
}

/* Valid array is a array with at least one element */

function valid_array($array) {
    if (!empty($array) && is_array($array) && count($array) > 0) {
        return true;
    }

    return false;
}

/* Valid object is a object with at least one element */

function valid_object($object) {
    if (!empty($object) && is_object($object) && count(array($object)) > 0) {
        return true;
    }

    return false;
}

function notify_mail($msg) {
    global $db, $LNG, $prefs;

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
    $users_db = $db->fetchAll($results);

    foreach ($users_db as $db_user) {
        if ($prefs->getPrefValueByUid($db_user['id'], 'email_notify')) {
            mail($db_user['email'], $subject, $msg['msg'], "From: no@reply \r\n");
        }
    }
}

function format_seconds($s) {
    return gmdate("H:i:s", (int) $s);
}
