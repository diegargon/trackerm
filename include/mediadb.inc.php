<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function mediadb_searchMovies($search) {
    return themoviedb_searchMovies($search);
}

function mediadb_searchShows($search) {
    return themoviedb_searchShows($search);
}

function mediadb_prep($type, $items) {
    return themoviedb_prep($type, $items);
}

function mediadb_getSeasons($id) {
    return themoviedb_getSeasons($id);
}

function mediadb_showsDetails_prep($id, $seasons_data, $episodes_data) {
    return themoviedb_showsDetails_prep($id, $seasons_data, $episodes_data);
}

function mediadb_getById($id, $table) {
    return themoviedb_getById($id, $table);
}

function mediadb_getByDbId($id, $table) {
    return themoviedb_getByDbId($id, $table);
}

function mediadb_getPopular() {
    return themoviedb_getPopular();
}
