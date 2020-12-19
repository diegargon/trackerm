<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function mediadb_searchMovies($search) {
    return themoviedb_searchMovies($search);
}

function mediadb_searchShows($search) {
    return themoviedb_searchShows($search);
}

function mediadb_Prep($type, $items) {
    return themoviedb_MediaPrep($type, $items);
}

function mediadb_getSeasons($id) {
    return themoviedb_getSeasons($id);
}

function mediadb_showsDetailsPrep($id, $seasons_data, $episodes_data) {
    return themoviedb_showsDetailPrep($id, $seasons_data, $episodes_data);
}

function mediadb_getByLocalId($id) {
    return themoviedb_getByLocalId($id);
}

function mediadb_getByDbId($media_type, $id) {
    return themoviedb_getByDbId($media_type, $id);
}

function mediadb_getPopular() {
    return themoviedb_getPopular();
}

function mediadb_getTrending() {
    return themoviedb_getTrending();
}

function mediadb_getTrailer($media_type, $id) {
    return themoviedb_getTrailer($media_type, $id);
}

function mediadb_guessPoster($item) {
    global $db;
    if (!isset($item['media_type'])) {
        return false;
    }

    /*
      getFileTitle can return 'ángela', if title is 'Ángela' like/nocase/noaccent not work with accents
      fixed replacing accents by %
     */
    $title = trim(getFileTitle($item['title']));
    $replace = ['Á', 'á', 'É', 'é', 'Í', 'í', 'Ó', 'ó', 'Ú', 'ú'];
    $c_title = str_replace($replace, '%', $title);

    $media_type = $item['media_type'];
    $query = "SELECT poster FROM tmdb_search WHERE media_type='$media_type' AND title LIKE '$c_title' COLLATE NOCASE AND poster IS NOT NULL  ORDER BY release DESC";
    $results = $db->query($query);
    $tmdb_item = $db->fetch($results);
    if (is_array($tmdb_item) && count($tmdb_item) > 0) {
        $db->finalize($results);
        return $tmdb_item['poster'];
    }
    $db->finalize($results);

    $query = "SELECT poster FROM tmdb_search WHERE media_type='$media_type' AND title LIKE '%$c_title%' COLLATE NOCASE AND poster IS NOT NULL  ORDER BY release DESC";
    $results = $db->query($query);
    $tmdb_item = $db->fetch($results);
    if (is_array($tmdb_item) && count($tmdb_item) > 0) {
        $db->finalize($results);
        return $tmdb_item['poster'];
    }
    $db->finalize($results);

    if ($media_type == 'movies') {
        $tmdb_items = mediadb_searchMovies($title);
    } else if ($media_type == 'shows') {
        $tmdb_items = mediadb_searchShows($title);
    }
    if (!empty($tmdb_items) && count($tmdb_items) > 0) {
        foreach ($tmdb_items as $tmdb_item) {
            if (!empty($tmdb_item['poster'])) {
                return $tmdb_item['poster'];
            }
        }
    }
    return false;
}
