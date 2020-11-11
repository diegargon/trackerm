<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function db_search_movies($search) {
    global $cfg, $LNG, $db;

    $search = trim($search);
    $query = str_replace(' ', '+', $search);
    $url = 'https://api.themoviedb.org/3/search/movie?api_key=' . $cfg['db_api_token'] . '&query=' . $query . '&language=' . $cfg['LANG'];

    $data = curl_get_json($url);

    $img_path = 'https://image.tmdb.org/t/p/w500';

    if (isset($data['results'])) {
        foreach ($data['results'] as $item) {
            $link = 'https://www.themoviedb.org/movie/' . $item['id'];
            $id = $item['id'];
            $movies[$id]['id'] = $id;
            $movies[$id]['ilink'] = 'movies_db';
            $movies[$id]['themoviedb_id'] = $item['id'];
            $movies[$id]['title'] = $item['title'];
            $movies[$id]['original_title'] = $item['original_title'];
            $movies[$id]['rating'] = $item['vote_average'];
            $movies[$id]['popularity'] = $item['popularity'];
            $movies[$id]['elink'] = $link;
            if (!empty($item['poster_path'])) {
                $movies[$id]['poster'] = $img_path . $item['poster_path'];
            }
            if (!empty($item['backdrop_path'])) {
                $movies[$id]['scene'] = $img_path . $item['backdrop_path'];
            }
            $movies[$id]['lang'] = $item['original_language'];
            $movies[$id]['plot'] = $item['overview'];
            if (isset($item['release_date'])) {
                $movies[$id]['release'] = $item['release_date'];
            } else {
                $movies[$id]['release'] = '';
            }
            $db->addUniqElements('tmdb_search', $movies, 'themoviedb_id');
        }
    }
    if (!empty($movies)) {
        foreach ($movies as $key => $movie) {
            $id = $db->getIdbyField('tmdb_search', 'themoviedb_id', $movie['themoviedb_id']);
            $movies[$key]['id'] = $id;
        }
    }
    return isset($movies) ? $movies : null;
}

function db_search_shows($search) {
    global $cfg, $LNG, $db;

    $search = trim($search);
    $query = str_replace(' ', '+', $search);
    $url = 'https://api.themoviedb.org/3/search/tv?api_key=' . $cfg['db_api_token'] . '&query=' . $query . '&language=' . $cfg['LANG'];

    $data = curl_get_json($url);

    $img_path = 'https://image.tmdb.org/t/p/w500';

    if (isset($data['results'])) {
        foreach ($data['results'] as $item) {
            $link = 'https://www.themoviedb.org/movie/' . $item['id'];
            $id = $item['id'];
            $shows[$id]['id'] = $id;
            $shows[$id]['ilink'] = 'shows_db';
            $shows[$id]['themoviedb_id'] = $item['id'];
            $shows[$id]['title'] = $item['name'];
            $shows[$id]['original_title'] = $item['original_name'];
            $shows[$id]['rating'] = $item['vote_average'];
            $shows[$id]['popularity'] = $item['popularity'];
            $shows[$id]['elink'] = $link;
            if (!empty($item['poster_path'])) {
                $shows[$id]['poster'] = $img_path . $item['poster_path'];
            }
            if (!empty($item['backdrop_path'])) {
                $shows[$id]['scene'] = $img_path . $item['backdrop_path'];
            }
            $shows[$id]['lang'] = $item['original_language'];
            $shows[$id]['plot'] = $item['overview'];
            if (isset($item['first_air_date'])) {
                $shows[$id]['release'] = $item['first_air_date'];
            } else {
                $shows[$id]['release'] = '';
            }
            $db->addUniqElements('tmdb_search', $shows, 'themoviedb_id');
        }
    }
    if (!empty($shows)) {
        foreach ($shows as $key => $show) {
            $id = $db->getIdbyField('tmdb_search', 'themoviedb_id', $show['themoviedb_id']);
            $shows[$key]['id'] = $id;
        }
    }

    return isset($shows) ? $shows : null;
}

function db_get_byid($id, $table) {
    global $db;

    $search_db = $db->getTableData($table);

    foreach ($search_db as $item) {
        if ($item['id'] == $id) {
            return $item;
        }
    }
}
