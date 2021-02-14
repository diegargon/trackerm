<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function themoviedb_searchMovies($search) {
    global $cfg;

    $cache_data = [];

    $search = trim($search);

    $cache_data = themoviedb_searchCache($search, 'movies');
    if (!empty($cache_data) && count($cache_data) > 0) {
        return $cache_data;
    }
    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;

    $query = str_replace(' ', '+', trim($search));

    $url = 'https://api.themoviedb.org/3/search/movie?api_key=' . $cfg['db_api_token'] . '&query=' . $query . '&language=' . $cfg['TMDB_LANG'];

    $data = curl_get_tmdb($url);

    if (!$data) {
        return null;
    }

    if (!empty($data['results']) && is_array($data['results']) && count($data['results']) > 0) {
        themoviedb_updateCache($search, $data, 'movies');
        $movies = themoviedb_MediaPrep('movies', $data['results']);
    }

    return isset($movies) ? $movies : null;
}

function themoviedb_searchShows($search) {
    global $cfg;

    $cache_data = [];

    $search = trim($search);

    $cache_data = themoviedb_searchCache($search, 'shows');
    if (!empty($cache_data) && count($cache_data) > 0) {
        return $cache_data;
    }
    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;

    $query = str_replace(' ', '+', trim($search));

    $url = 'https://api.themoviedb.org/3/search/tv?api_key=' . $cfg['db_api_token'] . '&query=' . $query . '&language=' . $cfg['TMDB_LANG'];

    $data = curl_get_tmdb($url);

    if (!$data) {
        return null;
    }
    if (!empty($data['results']) && count($data['results']) > 0) {
        themoviedb_updateCache($search, $data, 'shows');
        $shows = themoviedb_MediaPrep('shows', $data['results']);
    }

    return isset($shows) ? $shows : null;
}

function themoviedb_updateCache($words, $results, $media_type) {
    global $db;

    $tmdb_cache_table = 'search_' . $media_type . '_cache';
    $ids = '';
    $results = $results['results'];

    foreach ($results as $result) {
        empty($ids) ? $ids = $result['id'] : $ids .= ',' . $result['id'];
    }
    $set['ids'] = $ids;
    $set['engine'] = 'tmdb';
    $set['updated'] = time();
    $set['words'] = $words;
    $db->upsertItemByField($tmdb_cache_table, $set, 'words');
}

function themoviedb_searchCache($search_words, $media_type) {
    global $db, $cfg;

    $tmdb_cache_table = 'search_' . $media_type . '_cache';

    $results = [];

    $where['words'] = ['value' => $search_words];
    $where['engine'] = ['value' => 'tmdb'];

    $query_results = $db->select($tmdb_cache_table, '*', $where, 'LIMIT 1');
    $cached_results = $db->fetchAll($query_results);
    if (empty($cached_results[0])) {
        return false;
    }

    $cached_results = $cached_results[0];
    if (empty($cached_results['updated']) || empty($cached_results['ids']) || (time() > ($cached_results['updated'] + $cfg['tmdb_search_cache_expire']))) {
        return false;
    } else {
        $ids = explode(',', $cached_results['ids']);
        foreach ($ids as $id) {
            !empty($id) ? $results[] = themoviedb_getFromCache($media_type, $id) : null;
        }
    }
    return (count($results) > 0) ? $results : false;
}

function themoviedb_MediaPrep($media_type, $items) {
    global $db, $db, $log;

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
            $library_item = $db->getItemByField('library_movies', 'themoviedb_id', $item['id']);
            if ($library_item !== false) {
                $in_library = $library_item['id'];
            }
        } else if ($media_type == 'shows') {
            $library_item = $db->getItemByField('library_shows', 'themoviedb_id', $item['id']);
            if ($library_item !== false) {
                $in_library = $library_item['id'];
            }
        }
        $trailer = mediadb_getTrailer($media_type, $item['id']);

        $fitems[] = [
            'ilink' => $media_type . '_db',
            'themoviedb_id' => $item['id'],
            'title' => $title,
            'original_title' => $original_title,
            'clean_title' => clean_title($title),
            'rating' => $item['vote_average'],
            'popularity' => isset($item['popularity']) ? $item['popularity'] : 0,
            'elink' => $link,
            'poster' => !empty($item['poster_path']) ? $img_path . $item['poster_path'] : null,
            'scene' => !empty($item['backdrop_path']) ? $img_path . $item['backdrop_path'] : null,
            'lang' => $item['original_language'],
            'plot' => $item['overview'],
            'trailer' => $trailer,
            'updated' => time(),
            'release' => isset($release) ? $release : null,
        ];
    }

    if (!empty($fitems)) {

        foreach ($fitems as $key => $fitem) {
            $where_select['themoviedb_id'] = ['value' => $fitem['themoviedb_id']];
            $results = $db->select('tmdb_search_' . $media_type, 'id', $where_select, 'LIMIT 1');
            $res_item = $db->fetch($results);
            if (empty($res_item) || count($res_item) < 1) {
                $db->insert('tmdb_search_' . $media_type, $fitem);
                $fitems[$key]['id'] = $db->getLastId();
            } else {
                if (!empty($res_item)) {
                    $fitems[$key]['id'] = $res_item['id'];
                    $db->update('tmdb_search_' . $media_type, $fitem, ['id' => ['value' => $res_item['id']]]);
                }
            }
            $db->finalize($results);
        }
    }

    return isset($fitems) ? $fitems : false;
}

function themoviedb_getSeasons($id) {
    global $cfg, $db;

    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;

    $seasons_url = 'https://api.themoviedb.org/3/tv/' . $id . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];

    $seasons_data = curl_get_tmdb($seasons_url);

    if (isset($seasons_data['number_of_seasons'])) {
        $nseasons = $seasons_data['number_of_seasons'];
        $episodes_data = [];

        for ($i = 1; $i <= $nseasons; $i++) {
            $seasons_url = 'https://api.themoviedb.org/3/tv/' . $id . '/season/' . $i . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
            $episodes_data[$i] = curl_get_tmdb($seasons_url);
        }

        $items = themoviedb_showsDetailsPrep($id, $seasons_data, $episodes_data);

        foreach ($items as $item) {
            $where = [];

            $where['themoviedb_id'] = ['value' => $id];
            $where['season'] = ['value' => $item['season']];
            $where['episode'] = ['value' => $item['episode']];

            $results = $db->select('shows_details', null, $where, 'LIMIT 1');
            $result_row = $db->fetch($results);
            $db->finalize($results);

            if ($result_row) {
                $where_update['id'] = ['value' => $result_row['id']];
                $db->update('shows_details', $item, $where_update, 'LIMIT 1');
            } else {
                $db->insert('shows_details', $item);
            }
        }

        return $items;
    }
    return false;
}

function themoviedb_showsDetailsPrep($id, $seasons_data, $episodes_data) {
    global $db;

    $item_seasons = [
        'themoviedb_id' => $id,
        'n_seasons' => $seasons_data['number_of_seasons'],
        'n_episodes' => $seasons_data['number_of_episodes'],
        'release' => !empty($seasons_data['first_air_date']) ? $seasons_data['first_air_date'] : null,
        'homepage' => !empty($seasons_data['homepage']) ? $seasons_data['homepage'] : null,
        'in_production' => !empty($seasons_data['in_production']) ? $seasons_data['in_production'] : null,
        'plot' => !empty($seasons_data['overview']) ? $seasons_data['overview'] : null,
        'status' => $seasons_data['status'] ? $seasons_data['status'] : null,
    ];

    $item_episodes = [];

    for ($i = 1; $i <= $seasons_data['number_of_seasons']; $i++) {
        $episodes = $episodes_data[$i]['episodes'];

        $episodes_number = count($episodes);

        foreach ($episodes as $episode) {
            if (isset($episode['episode_number'])) {
                isset($episode['name']) ? $episode_title = $episode['name'] : $episode_title = $episode['episode_number'];
                isset($episode['overview']) ? $episode_plot = $episode['overview'] : $episode_plot = '';
                isset($episode['air_date']) ? $episode_release = $episode['air_date'] : $episode_release = '';

                $item_episodes[] = [
                    'themoviedb_id' => $id,
                    'seasons' => $seasons_data['number_of_seasons'],
                    'episodes' => $seasons_data['number_of_episodes'],
                    'release' => !empty($seasons_data['first_air_date']) ? $seasons_data['first_air_date'] : null,
                    'season' => $i,
                    'episode' => $episode['episode_number'],
                    'title' => $episode_title,
                    'clean_title' => clean_title($episode_title),
                    'episode_release' => $episode_release,
                    'plot' => $episode_plot
                ];
            }
        }
    }

    return $item_episodes;
}

function themoviedb_getByLocalId($media_type, $id) {
    global $db;

    $item = $db->getItemById('tmdb_search_' . $media_type, $id);

    return $item ? $item : false;
}

function themoviedb_getFromCache($media_type, $id) {
    global $db, $cfg, $log;

    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;

    $item = $db->getItemByField('tmdb_search_' . $media_type, 'themoviedb_id', $id);
    if ($item) {
        return $item;
    }

    $log->debug('getByDbId: Not Found in local db id=' . $id);

    if (!isset($media_type)) {
        $log->err('getByDbId: media_type was not set ' . $id);
        return false;
    }

    if ($media_type == 'movies') {
        $url = 'https://api.themoviedb.org/3/movie/' . $id . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
    } else if ($media_type == 'shows') {
        $url = 'https://api.themoviedb.org/3/tv/' . $id . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
    } else {
        return false;
    }

    $response_item[] = curl_get_tmdb($url);

    if (empty($response_item[0]) || count($response_item) <= 0) {
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
    global $cfg;

    $cache_data['movies'] = themoviedb_searchCache('_POPULAR_', 'movies');
    $cache_data['shows'] = themoviedb_searchCache('_POPULAR_', 'shows');
    if (!empty($cache_data['movies']) && !empty($cache_data['shows'])) {
        return $cache_data;
    }
    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;

    $movies_url = 'https://api.themoviedb.org/3/movie/popular?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
    $shows_url = 'https://api.themoviedb.org/3/tv/popular?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
    if ($cfg['want_movies']) {
        $response_items = curl_get_tmdb($movies_url);
        themoviedb_updateCache('_POPULAR_', $response_items, 'movies');
        $results['movies'] = themoviedb_MediaPrep('movies', $response_items['results']);
    }
    if ($cfg['want_shows']) {
        $response_items = curl_get_tmdb($shows_url);
        themoviedb_updateCache('_POPULAR_', $response_items, 'shows');
        $results['shows'] = themoviedb_MediaPrep('shows', $response_items['results']);
    }
    return $results;
}

function themoviedb_getTrending() {
    global $cfg;

    $cache_data['movies'] = themoviedb_searchCache('_TRENDING_', 'movies');
    $cache_data['shows'] = themoviedb_searchCache('_TRENDING_', 'shows');
    if (!empty($cache_data['movies']) && !empty($cache_data['shows'])) {
        return $cache_data;
    }
    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;

    $movies_url = 'https://api.themoviedb.org/3/trending/movie/day?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
    $shows_url = 'https://api.themoviedb.org/3/trending/tv/day?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
    if ($cfg['want_movies']) {
        $response_items = curl_get_tmdb($movies_url);
        themoviedb_updateCache('_TRENDING_', $response_items, 'movies');
        $results['movies'] = themoviedb_MediaPrep('movies', $response_items['results']);
    }
    if ($cfg['want_shows']) {
        $response_items = curl_get_tmdb($shows_url);
        themoviedb_updateCache('_TRENDING_', $response_items, 'shows');
        $results['shows'] = themoviedb_MediaPrep('shows', $response_items['results']);
    }
    return $results;
}

function themoviedb_getTodayShows() {
    global $cfg;

    $cache_data['shows'] = themoviedb_searchCache('_TODAY_SHOWS_', 'shows');
    if (!empty($cache_data['shows'])) {
        return $cache_data;
    }
    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;

    $shows_url = 'https://api.themoviedb.org/3/tv/airing_today?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
    $response_items = curl_get_tmdb($shows_url);
    themoviedb_updateCache('_TODAY_SHOWS_', $response_items, 'shows');
    $results['shows'] = themoviedb_MediaPrep('shows', $response_items['results']);

    return $results;
}

function themoviedb_getTrailer($media_type, $id) {
    global $cfg, $log;

    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;

    if (empty($media_type) || empty($id)) {
        return false;
    }

    if ($media_type == 'movies') {
        $tmdb_type = 'movie';
    } else if ($media_type == 'shows') {
        $tmdb_type = 'tv';
    } else {
        return false;
    }

    $url = "http://api.themoviedb.org/3/{$tmdb_type}/{$id}/videos?api_key=" . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];

    $curl_data = curl_get_tmdb($url);
    if (!empty($curl_data['results']) && count($curl_data['results']) > 0) {
        $results = array_pop($curl_data['results']);
    } else {
        return false;
    }
    if (isset($results['site']) && $results['site'] == 'YouTube') {
        $video = 'http://www.youtube.com/embed/' . $results['key'];
    } else if (!empty($results['site'])) {
        $log->warning('Video trailer site not implemented ' . $results['site']);
        $video = false;
    } else {
        $log->debug("Get $media_type trailer seems got nothing on id: $id");
        return false;
    }

    return trim($video);
}
