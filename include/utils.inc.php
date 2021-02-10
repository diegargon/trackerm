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
    $title = getFileTitle($title);
    $title = strtolower($title);
    $title = iconv("UTF-8", "ASCII//TRANSLIT", $title);

    $from_replace_signs = [':', ';', '.', ',', '?', '¿', '@', '#', '$', '-', '_', '{', '}', '[', ']', '!', '¡', '+', '^', '`', '*', '/', '|', 'º', 'ª', '%', '=', '<', '>', '\''];
    $title = str_replace($from_replace_signs, '', $title);
    $title = preg_replace('/\(.*\)/', '', $title);
    $title = preg_replace('/\s+/', ' ', $title);

    return trim($title);
}

function set_clean() {
    global $db;

    $tables = ['tmdb_search', 'jackett_movies', 'jackett_shows', 'library_history', 'library_shows', 'library_movies', 'shows_details'];

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

function update_stats() {
    global $db;

    $movies_size = 0;
    $shows_size = 0;

    $movies_db = $db->getTableData('library_movies');
    $num_movies = count($movies_db);

    if (!empty($movies_db)) {
        foreach ($movies_db as $db_movie) {
            if (isset($db_movie['size'])) {
                $movies_size = $movies_size + $db_movie['size'];
            }
        }
        $movies_size = human_filesize($movies_size);
    }

    $shows_db = $db->getTableData('library_shows');
    $num_episodes = count($shows_db);
    $count_shows = [];

    if (!empty($shows_db)) {
        foreach ($shows_db as $db_show) {
            if (isset($db_show['size'])) {
                $shows_size = $shows_size + $db_show['size'];
            }

            if (!empty($db_show['themoviedb_id'])) {
                $tmdb_id = $db_show['themoviedb_id'];
                if (!isset($count_shows[$tmdb_id])) {
                    $count_shows[$tmdb_id] = 1;
                }
            }
        }
        $shows_size = human_filesize($shows_size);
    }

    $num_shows = count($count_shows);


    $db->query("UPDATE config SET cfg_value='$num_movies' WHERE cfg_key='stats_movies' LIMIT 1");
    $db->query("UPDATE config SET cfg_value='$num_shows' WHERE cfg_key='stats_shows' LIMIT 1");
    $db->query("UPDATE config SET cfg_value='$num_episodes' WHERE cfg_key='stats_shows_episodes' LIMIT 1");
    $db->query("UPDATE config SET cfg_value='$movies_size' WHERE cfg_key='stats_total_movies_size' LIMIT 1");
    $db->query("UPDATE config SET cfg_value='$shows_size' WHERE cfg_key='stats_total_shows_size' LIMIT 1");
}
