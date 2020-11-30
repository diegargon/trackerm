<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
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

function mediadb_getSeasons($id, $update = false) {
    return themoviedb_getSeasons($id, $update);
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
