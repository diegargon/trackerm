<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function mediadb_searchMovies($search) {
    return themoviedb_searchMovies($search);
}

function mediadb_searchShows($search) {
    return themoviedb_searchShows($search);
}

function mediadb_Prep($media_type, $items) {
    return themoviedb_MediaPrep($media_type, $items);
}

function mediadb_getSeasons($id) {
    return themoviedb_getSeasons($id);
}

function mediadb_showsDetailsPrep($id, $seasons_data, $episodes_data) {
    return themoviedb_showsDetailPrep($id, $seasons_data, $episodes_data);
}

function mediadb_getByLocalId($media_type, $id) {
    return themoviedb_getByLocalId($media_type, $id);
}

function mediadb_getFromCache($media_type, $id) {
    return themoviedb_getFromCache($media_type, $id);
}

function mediadb_getPopular() {
    return themoviedb_getPopular();
}

function mediadb_getTrending() {
    return themoviedb_getTrending();
}

function mediadb_getTodayShows() {
    return themoviedb_getTodayShows();
}

function mediadb_getTrailer($media_type, $id) {
    return themoviedb_getTrailer($media_type, $id);
}

function mediadb_guessPoster($title, $media_type) {

    $result = mediadb_guessFieldGet($title, $media_type, 'poster');

    return !empty($result) ? $result : false;
}

function mediadb_guessTrailer($title, $media_type) {

    $result = mediadb_guessFieldGet($title, $media_type, 'trailer');

    return !empty($result) ? $result : false;
}

function mediadb_getCollection($id) {
    $result = themoviedb_getCollection($id);

    return !empty($result) ? $result : false;
}

function mediadb_guessFieldGet($title, $media_type, $field) {
    global $db;

    //TODO: Too many querys do better
    /*
      if we search for angela and in database the field is Ángela we have a a problem.
      testing adding and using clean_title column
     */
    $title = $db->escape(trim(getFileTitle($title)));
    $c_title = clean_title($title);

    $table = 'tmdb_search_' . $media_type;
    $query = "SELECT $field FROM $table WHERE title LIKE '$title'  COLLATE NOCASE OR clean_title LIKE '$c_title' OR original_title LIKE '$title'  COLLATE NOCASE ORDER BY release DESC";
    $results = $db->query($query);
    $tmdb_item = $db->fetch($results);
    if (is_array($tmdb_item) && count($tmdb_item) > 0) {
        $db->finalize($results);
        return !empty($tmdb_item[$field]) ? $tmdb_item[$field] : false;
    }

    $db->finalize($results);

    $query = "SELECT $field FROM $table WHERE  title LIKE '%$title%' OR clean_title LIKE '%$c_title%' OR original_title LIKE '%$c_title%'  COLLATE NOCASE COLLATE NOCASE  ORDER BY release DESC";
    $results = $db->query($query);
    $tmdb_item = $db->fetch($results);
    if (is_array($tmdb_item) && count($tmdb_item) > 0) {
        $db->finalize($results);
        return !empty($tmdb_item[$field]) ? $tmdb_item[$field] : false;
    }
    $db->finalize($results);

    if ($media_type == 'movies') {
        $tmdb_items = mediadb_searchMovies($title);
    } else if ($media_type == 'shows') {
        $tmdb_items = mediadb_searchShows($title);
    }
    if (!empty($tmdb_items) && count($tmdb_items) > 0) {
        foreach ($tmdb_items as $tmdb_item) {
            if (!empty($tmdb_item[$field])) {
                return $tmdb_item[$field];
            }
        }
    }
    return false;
}
