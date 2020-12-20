<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
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

    return $title;
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
