<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function db_search_movies($search) {
    global $cfg;

    $search = preg_replace('/\d{4}/', '', $search); //moviedb no encuentra con año si va en el titulo lo quitamos
    $query = str_replace(' ', '+', trim($search));

    $url = 'https://api.themoviedb.org/3/search/movie?api_key=' . $cfg['db_api_token'] . '&query=' . $query . '&language=' . $cfg['LANG'];

    $data = curl_get_json($url);

    (isset($data['results'])) ? $movies = db_prep('movies', $data['results']) : null;

    return isset($movies) ? $movies : null;
}

function db_search_shows($search) {
    global $cfg;

    $search = preg_replace('/\d{4}/', '', $search); //moviedb no encuentra con año si va en el titulo lo quitamos
    $query = str_replace(' ', '+', trim($search));

    $url = 'https://api.themoviedb.org/3/search/tv?api_key=' . $cfg['db_api_token'] . '&query=' . $query . '&language=' . $cfg['LANG'];

    $data = curl_get_json($url);

    (isset($data['results'])) ? $shows = db_prep('shows', $data['results']) : null;

    return isset($shows) ? $shows : null;
}

function db_prep($type, $items) {
    global $db;

    $img_path = 'https://image.tmdb.org/t/p/w500';

    if ($type == 'movies') {
        $tmdb_link = 'https://www.themoviedb.org/movie/';
    } else if ($type == 'shows') {
        $tmdb_link = 'https://www.themoviedb.org/tv/';
    } else {
        return false;
    }

    $fitems = [];

    foreach ($items as $item) {
        if ($type == 'movies') {
            $title = $item['title'];
            $original_title = $item['original_title'];
        } else if ($type == 'shows') {
            $title = $item['name'];
            $original_title = $item['original_name'];
        }

        $link = $tmdb_link . $item['id'];
        $id = $item['id'];
        $fitems[$id]['id'] = $id;
        $fitems[$id]['ilink'] = $type . '_db';
        $fitems[$id]['themoviedb_id'] = $item['id'];
        $fitems[$id]['title'] = $title;
        $fitems[$id]['original_title'] = $original_title;
        $fitems[$id]['rating'] = $item['vote_average'];
        $fitems[$id]['popularity'] = $item['popularity'];
        $fitems[$id]['elink'] = $link;
        if (!empty($item['poster_path'])) {
            $fitems[$id]['poster'] = $img_path . $item['poster_path'];
        }
        if (!empty($item['backdrop_path'])) {
            $fitems[$id]['scene'] = $img_path . $item['backdrop_path'];
        }
        $fitems[$id]['lang'] = $item['original_language'];
        $fitems[$id]['plot'] = $item['overview'];
        if (isset($item['release_date'])) {
            $fitems[$id]['release'] = $item['release_date'];
        } else {
            $fitems[$id]['release'] = '';
        }
        $db->addUniqElements('tmdb_search', $fitems, 'themoviedb_id');
    }

    if (!empty($fitems)) {
        foreach ($fitems as $key => $fitem) {
            $id = $db->getIdbyField('tmdb_search', 'themoviedb_id', $fitem['themoviedb_id']);
            $fitems[$key]['id'] = $id;
        }
    }

    return isset($fitems) ? $fitems : false;
}

function db_get_seasons($id) {
    global $cfg;

    $seasons_url = 'https://api.themoviedb.org/3/tv/' . $id . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['LANG'];

    $seasons_data = curl_get_json($seasons_url);

    if (isset($seasons_data['number_of_seasons'])) {
        $nseasons = $seasons_data['number_of_seasons'];
        $episodes_data = [];

        for ($i = 1; $i <= $nseasons; $i++) {
            $seasons_url = 'https://api.themoviedb.org/3/tv/' . $id . '/season/' . $i . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['LANG'];
            $episodes_data[$i] = curl_get_json($seasons_url);
        }
        return db_shows_details_prep($id, $seasons_data, $episodes_data);
    }

    return false;
}

function db_shows_details_prep($id, $seasons_data, $episodes_data) {
    global $db;

    $item = [];

    $lastid = $db->getLastID('shows_details');

    $item[$lastid]['id'] = $id;
    $item[$lastid]['themoviedb_id'] = $id;
    $item[$lastid]['n_seasons'] = $seasons_data['number_of_seasons'];
    $item[$lastid]['n_episodes'] = $seasons_data['number_of_episodes'];

    for ($i = 1; $i <= $seasons_data['number_of_seasons']; $i++) {
        $episodes = $episodes_data[$i]['episodes'];

        $item[$lastid]['seasons'][$i]['n_episodes'] = count($episodes);

        foreach ($episodes as $episode) {
            if (isset($episode['episode_number'])) {
                $n_episode = $episode['episode_number'];
                isset($item[$lastid]['seasons'][$i]['episodes'][$n_episode]['title']) ? $item[$lastid]['seasons'][$i]['episodes'][$n_episode]['title'] = $episode['name'] : $item[$lastid]['seasons'][$i]['episodes'][$n_episode]['title'] = $episode['episode_number'];
                isset($item[$lastid]['seasons'][$i]['episodes'][$n_episode]['plot']) ? $item[$lastid]['seasons'][$i]['episodes'][$n_episode]['plot'] = $episode['overview'] : $item[$lastid]['seasons'][$i]['episodes'][$n_episode]['plot'] = $episode['episode_number'];
            }
        }
    }

    $db->addUniqElements('shows_details', $item, 'themoviedb_id');
    $db->reloadTable('shows_details');

    return $db->getItemByField('shows_details', 'themoviedb_id', $id);
}

function db_get_byid($id, $table) {
    global $db;

    $search_db = $db->getTableData($table);

    foreach ($search_db as $item) {
        if ($item['id'] == $id) {
            return $item;
        }
    }
    return false;
}

function db_get_by_db_id($id, $table) {
    global $db;

    $search_db = $db->getTableData($table);

    foreach ($search_db as $item) {
        if ($item['themoviedb_id'] == $id) {
            return $item;
        }
    }
    return false;
}

function db_get_popular() {
    /*
      https://api.themoviedb.org/3/movie/popular?api_key=<<api_key>>&language=en-US&page=1
      https://api.themoviedb.org/3/tv/popular?api_key=<<api_key>>&language=en-US&page=1
     */
}
