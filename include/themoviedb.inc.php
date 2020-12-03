<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function themoviedb_searchMovies($search) {
    global $cfg;

    $search = preg_replace('/\d{4}/', '', $search); //moviedb no encuentra con año si va en el titulo lo quitamos
    $query = str_replace(' ', '+', trim($search));

    $url = 'https://api.themoviedb.org/3/search/movie?api_key=' . $cfg['db_api_token'] . '&query=' . $query . '&language=' . $cfg['LANG'];

    $data = curl_get_tmdb($url);

    (isset($data['results'])) ? $movies = themoviedb_MediaPrep('movies', $data['results']) : null;

    return isset($movies) ? $movies : null;
}

function themoviedb_searchShows($search) {
    global $cfg;

    $search = preg_replace('/\d{4}/', '', $search); //moviedb no encuentra con año si va en el titulo lo quitamos
    $query = str_replace(' ', '+', trim($search));

    $url = 'https://api.themoviedb.org/3/search/tv?api_key=' . $cfg['db_api_token'] . '&query=' . $query . '&language=' . $cfg['LANG'];

    $data = curl_get_tmdb($url);

    (isset($data['results'])) ? $shows = themoviedb_MediaPrep('shows', $data['results']) : null;

    return isset($shows) ? $shows : null;
}

function themoviedb_MediaPrep($media_type, $items) {
    global $db, $newdb, $log;

    $img_path = 'https://image.tmdb.org/t/p/w500';

    if ($media_type == 'movies') {
        $tmdb_link = 'https://www.themoviedb.org/movie/';
    } else if ($media_type == 'shows') {
        $tmdb_link = 'https://www.themoviedb.org/tv/';
    } else {
        $log->err('mediaprep: media_type was not set');
        return false;
    }

    $fitems = [];

    foreach ($items as $item) {

        if ($media_type == 'movies') {
            $title = $item['title'];
            $original_title = $item['original_title'];
        } else if ($media_type == 'shows') {
            $title = $item['name'];
            $original_title = $item['original_name'];
        }

        $link = $tmdb_link . $item['id'];

        if (isset($item['release_date'])) {
            $release = $item['release_date'];
        } else if (isset($item['first_air_date'])) {
            $release = $item['first_air_date'];
        }

        if ($media_type == 'movies') {
            $library_item = $db->getItemByField('biblio-movies', 'themoviedb_id', $item['id']);
            if ($library_item !== false) {
                $in_library = $library_item['id'];
            }
        } else if ($media_type == 'shows') {
            $library_item = $db->getItemByField('biblio-shows', 'themoviedb_id', $item['id']);
            if ($library_item !== false) {
                $in_library = $library_item['id'];
            }
        }
        $fitems[] = [
            'ilink' => $media_type . '_db',
            'media_type' => $media_type,
            'themoviedb_id' => $item['id'],
            'title' => $title,
            'original_title' => $original_title,
            'rating' => $item['vote_average'],
            'popularity' => $item['popularity'],
            'elink' => $link,
            'poster' => !empty($item['poster_path']) ? $img_path . $item['poster_path'] : null,
            'scene' => !empty($item['backdrop_path']) ? $img_path . $item['backdrop_path'] : null,
            'lang' => $item['original_language'],
            'plot' => $item['overview'],
            'release' => $release,
        ];
    }

    if (!empty($fitems)) {
        $newdb->addItemsUniqField('tmdb_search', $fitems, 'themoviedb_id');

        foreach ($fitems as $key => $fitem) {
            $fitems[$key]['id'] = $newdb->getIdByField('tmdb_search', 'themoviedb_id', $fitem['themoviedb_id']);
        }
    }

    return isset($fitems) ? $fitems : false;
}

function themoviedb_getSeasons($id, $update = false) {
    global $cfg, $db;

    $seasons_url = 'https://api.themoviedb.org/3/tv/' . $id . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['LANG'];

    $seasons_data = curl_get_tmdb($seasons_url);

    if (isset($seasons_data['number_of_seasons'])) {
        $nseasons = $seasons_data['number_of_seasons'];
        $episodes_data = [];

        for ($i = 1; $i <= $nseasons; $i++) {
            $seasons_url = 'https://api.themoviedb.org/3/tv/' . $id . '/season/' . $i . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['LANG'];
            $episodes_data[$i] = curl_get_tmdb($seasons_url);
        }

        $item = themoviedb_showsDetailsPrep($id, $seasons_data, $episodes_data);
        if ($update !== false) {
            $shows_details_id = $db->getIdByField('shows_details', 'themoviedb_id', $id);
            $db->updateElementById('shows_details', $shows_details_id, $item);
        } else {
            $db->addUniqElements('shows_details', $item, 'themoviedb_id');
        }
        $db->reloadTable('shows_details');

        return $db->getItemByField('shows_details', 'themoviedb_id', $id);
    }

    return false;
}

function themoviedb_showsDetailsPrep($id, $seasons_data, $episodes_data) {
    global $db;

    $item = [];

    $lastid = $db->getLastId('shows_details');

    $item[$lastid]['id'] = $lastid;
    $item[$lastid]['themoviedb_id'] = $id;
    $item[$lastid]['n_seasons'] = $seasons_data['number_of_seasons'];
    $item[$lastid]['n_episodes'] = $seasons_data['number_of_episodes'];
    $item[$lastid]['last_update'] = time();
    isset($item[$lastid]['first_air_date']) ? $item[$lastid]['release'] = $seasons_data['first_air_date'] : null;
    isset($item[$lastid]['homepage']) ? $item[$lastid]['homepage'] = $seasons_data['homepage'] : null;
    isset($item[$lastid]['in_production']) ? $item[$lastid]['in_production'] = $seasons_data['in_production'] : null;
    isset($item[$lastid]['overview']) ? $item[$lastid]['plot'] = $seasons_data['overview'] : null;
    isset($item[$lastid]['status']) ? $item[$lastid]['status'] = $seasons_data['status'] : null;

    for ($i = 1; $i <= $seasons_data['number_of_seasons']; $i++) {
        $episodes = $episodes_data[$i]['episodes'];

        $item[$lastid]['seasons'][$i]['n_episodes'] = count($episodes);

        foreach ($episodes as $episode) {
            if (isset($episode['episode_number'])) {
                $n_episode = $episode['episode_number'];
                isset($episode['name']) ? $item[$lastid]['seasons'][$i]['episodes'][$n_episode]['title'] = $episode['name'] : $item[$lastid]['seasons'][$i]['episodes'][$n_episode]['title'] = $episode['episode_number'];
                isset($episode['overview']) ? $item[$lastid]['seasons'][$i]['episodes'][$n_episode]['plot'] = $episode['overview'] : $item[$lastid]['seasons'][$i]['episodes'][$n_episode]['plot'] = '';
                isset($episode['air_date']) ? $item[$lastid]['seasons'][$i]['episodes'][$n_episode]['release'] = $episode['air_date'] : $item[$lastid]['seasons'][$i]['episodes'][$n_episode]['release'] = '';
            }
        }
    }

    return $item;
}

function themoviedb_getByLocalId($id) {
    global $newdb;

    $item = $newdb->getItemById('tmdb_search', $id);

    return $item ? $item : false;
}

function themoviedb_getByDbId($media_type, $id) {
    global $newdb, $cfg, $log;

    $item = $newdb->getItemByField('tmdb_search', 'themoviedb_id', $id);
    if ($item) {
        return $item;
    }

    $log->debug('getByDbId: Not Found in local db id=' . $id);

    if (!isset($media_type)) {
        $log->err('getByDbId: media_type was not set ' . $id);
        return false;
    }

    if ($media_type == 'movies') {
        $url = 'https://api.themoviedb.org/3/movie/' . $id . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['LANG'];
    } else if ($media_type == 'shows') {
        $url = 'https://api.themoviedb.org/3/tv/' . $id . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['LANG'];
    } else {
        return false;
    }

    $response_item[] = curl_get_tmdb($url);


    if (count($response_item) <= 0) {
        $log->err('getByDbId: Request id=' . $id . ' to remote databse fail');
        return false;
    }

    $result = themoviedb_MediaPrep($media_type, $response_item);
    if (empty($result)) {
        $log->err('getByDbId: mediaPrep return empty/false');
        return false;
    }

    return current($result);
}

function themoviedb_getPopular() {
    /*
      https://api.themoviedb.org/3/movie/popular?api_key=<<api_key>>&language=en-US&page=1
      https://api.themoviedb.org/3/tv/popular?api_key=<<api_key>>&language=en-US&page=1
     */
}
