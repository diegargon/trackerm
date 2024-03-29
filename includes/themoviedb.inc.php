<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function themoviedb_searchMovies($search) {
    global $cfg;

    $cache_data = [];
    $search = trim($search);

    $cache_data = themoviedb_searchCache($search, 'movies');
    if (valid_array($cache_data)) {
        return $cache_data;
    }
    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;
    $search_query = str_replace(' ', '+', trim($search));
    $url = 'https://api.themoviedb.org/3/search/movie?api_key=' . $cfg['db_api_token'] . '&query=' . $search_query . '&language=' . $cfg['TMDB_LANG'];
    $data = curl_get_tmdb($url);

    if (!valid_array($data) || empty($data['results'])) {
        return false;
    }
    themoviedb_updateCache($search, $data, 'movies');
    $movies = themoviedb_MediaPrep('movies', $data['results']);

    return valid_array($movies) ? $movies : null;
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
    $search_query = str_replace(' ', '+', trim($search));
    $url = 'https://api.themoviedb.org/3/search/tv?api_key=' . $cfg['db_api_token'] . '&query=' . $search_query . '&language=' . $cfg['TMDB_LANG'];
    $data = curl_get_tmdb($url);

    if (!valid_array($data) || empty($data['results'])) {
        return false;
    }
    themoviedb_updateCache($search, $data, 'shows');
    $shows = themoviedb_MediaPrep('shows', $data['results']);

    return valid_array($shows) ? $shows : null;
}

function themoviedb_updateCache($words, $results, $media_type) {
    global $db;

    $ids = '';
    $tmdb_cache_table = 'search_' . $media_type . '_cache';
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
    if (!valid_array($cached_results)) {
        return false;
    }

    if ($search_words == '_TODAY_SHOWS_' || $search_words == '_POPULAR_' || $search_words == '_TRENDING_') {
        $cache_expire = $cfg['tmdb_opt_cache_expire'];
    } else {
        $cache_expire = $cfg['tmdb_search_cache_expire'];
    }
    $cached_results = $cached_results[0];

    if (empty($cached_results['updated']) || empty($cached_results['ids']) || (time() > ($cached_results['updated'] + $cache_expire))) {
        return false;
    } else {
        $ids = explode(',', $cached_results['ids']);
        if (valid_array($ids)) {
            //TODO ONE QUERY
            foreach ($ids as $id) {
                !empty($id) ? $results[] = themoviedb_getMediaData($media_type, $id) : null;
            }
        }
    }
    return (valid_array($results)) ? $results : false;
}

function themoviedb_MediaPrep($media_type, $items) {
    global $db, $cfg, $log;

    if ($media_type == 'movies') {
        $tmdb_link = $cfg['odb_movies_link'];
    } else if ($media_type == 'shows') {
        $tmdb_link = $cfg['odb_shows_link'];
    } else {
        $log->err('mediaprep: media_type was not set');
        return false;
    }

    $fitems = [];

    $i = 0;
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
            ($library_item !== false) ? $in_library = $library_item['id'] : null;
        } else if ($media_type == 'shows') {
            $library_item = $db->getItemByField('library_shows', 'themoviedb_id', $item['id']);
            ($library_item !== false) ? $in_library = $library_item['id'] : null;
        }

        //avoid get trailer if items > 15 for alleviate big querys tracker-cli would fix that
        if (count($items) <= 15) {
            $trailer = mediadb_getTrailer($media_type, $item['id']);
            empty($trailer) ? $trailer = 0 : null; //we use 0 for mark as not have trailer atm
        }

        //TODO DELETE genres coma separated and modify functions for work with [genre]
        $genres = '';

        // Something wrong with o_api sometimes "genre_ids" and others "genres" check both
        if (!empty($item['genre_ids'])) {
            $o_genres = $item['genre_ids'];
            if (valid_array($o_genres)) {
                foreach ($o_genres as $o_genre) {
                    $genres .= '[' . $o_genre . ']';
                }
            }
        } else if (!empty($item['genres'])) {
            $o_genres = $item['genres'];
            if (valid_array($o_genres)) {
                foreach ($o_genres as $o_genre) {
                    if (!empty($o_genre['id'])) {
                        $genres .= '[' . $o_genre['id'] . ']';
                    }
                }
            }
        }
       
        if(!empty($item['vote_average'])) {
            $rating = round($item['vote_average'], 1);
        } else {
            $rating = '';
        }
        
        $fitems[$i] = [
            'themoviedb_id' => $item['id'],
            'title' => $title,
            'original_title' => $original_title,
            'clean_title' => clean_title($title),
            'rating' => $rating,
            'popularity' => isset($item['popularity']) ? round($item['popularity'], 1) : 0,
            'elink' => $link,
            'poster' => !empty($item['poster_path']) ? $cfg['odb_images_link'] . $item['poster_path'] : null,
            'scene' => !empty($item['backdrop_path']) ? $cfg['odb_images_link'] . $item['backdrop_path'] : null,
            'lang' => $item['original_language'],
            'plot' => $item['overview'],
            'genres' => !empty($genres) ? $genres : null,
            'release' => isset($release) ? $release : null,
            'updated' => time(),
        ];

        !empty($item['belongs_to_collection']['id']) ? $fitems[$i]['collection'] = $item['belongs_to_collection']['id'] : null;
        !empty($trailer) ? $fitems[$i]['trailer'] = $trailer : null;
        if ($media_type == 'shows' && !empty($item['in_production'])) {
            $fitems[$i]['ended'] = 0;
        } else if (isset($item['in_production'])) {
            $fitems[$i]['ended'] = 1;
        }
        $i++;
    }

    if (valid_array($fitems)) {
        foreach ($fitems as $key => $fitem) {
            $where_select['themoviedb_id'] = ['value' => $fitem['themoviedb_id']];
            $results = $db->select('tmdb_search_' . $media_type, 'id', $where_select, 'LIMIT 1');
            $res_item = $db->fetch($results);
            if (empty($res_item) || count($res_item) < 1) {
                $db->insert('tmdb_search_' . $media_type, $fitem);
                $fitems[$key]['id'] = $db->getLastId();
            } else if (!empty($res_item)) {
                $fitems[$key]['id'] = $res_item['id'];
                $db->update('tmdb_search_' . $media_type, $fitem, ['id' => ['value' => $res_item['id']]]);
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

    if (!isset($seasons_data['number_of_seasons'])) {
        return false;
    }
    $nseasons = $seasons_data['number_of_seasons'];
    $episodes_data = [];

    for ($i = 1; $i <= $nseasons; $i++) {
        $seasons_url = 'https://api.themoviedb.org/3/tv/' . $id . '/season/' . $i . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
        $episodes_data[$i] = curl_get_tmdb($seasons_url);
    }

    $items = themoviedb_showsDetailsPrep($id, $seasons_data, $episodes_data);
    if (!valid_array($items)) {
        return false;
    }

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

function themoviedb_showsDetailsPrep($id, $seasons_data, $episodes_data) {
    $item_episodes = [];

    for ($i = 1; $i <= $seasons_data['number_of_seasons']; $i++) {
        $episodes = $episodes_data[$i]['episodes'];
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
                    'updated' => time(),
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

    return valid_array($item) ? $item : false;
}

function themoviedb_getMediaData($media_type, $id, $force_update = false) {
    global $db, $cfg, $log;

    if (!isset($media_type)) {
        $log->err('getMediaData: media_type was not set ' . $id);
        return false;
    }
    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;

    if (!$force_update) {
        if ($media_type == 'movies') {
            $time_update_req = time() - 2592000; // 1 month
        } else {
            $time_update_req = time() - 604800; // 15 days
        }

        $item = $db->getItemByField('tmdb_search_' . $media_type, 'themoviedb_id', $id);
        if (valid_array($item)) {
            if ($item['updated'] > $time_update_req) {
                return $item;
            }
        }

        $log->debug('getByDbId: Not Found in local db or need update id=' . $id);
    }
    if ($media_type == 'movies') {
        $url = 'https://api.themoviedb.org/3/movie/' . $id . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
    } else if ($media_type == 'shows') {
        $url = 'https://api.themoviedb.org/3/tv/' . $id . '?api_key=' . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
    }
    $response_item[] = curl_get_tmdb($url);

    if (!valid_array($response_item)) {
        $log->err('getMediaData: remote database id ' . $id . ' for ' . $media_type . ' not exists');
        return false;
    }

    $result = themoviedb_MediaPrep($media_type, $response_item);
    if (!valid_array($result)) {
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
        if (valid_array($response_items['results']) && $response_items['results'] !== false) {
            themoviedb_updateCache('_POPULAR_', $response_items, 'movies');
            $results['movies'] = themoviedb_MediaPrep('movies', $response_items['results']);
        }
    }
    if ($cfg['want_shows']) {
        $response_items = curl_get_tmdb($shows_url);
        if (isset($response_items['results']) && valid_array($response_items['results'])) {
            themoviedb_updateCache('_POPULAR_', $response_items, 'shows');
            $results['shows'] = themoviedb_MediaPrep('shows', $response_items['results']);
        }
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
        if (valid_array($response_items['results']) && $response_items['results'] !== false) {
            themoviedb_updateCache('_TRENDING_', $response_items, 'movies');
            $results['movies'] = themoviedb_MediaPrep('movies', $response_items['results']);
        }
    }
    if ($cfg['want_shows']) {
        $response_items = curl_get_tmdb($shows_url);
        if (isset($response_items['results']) && valid_array($response_items['results'])) {
            themoviedb_updateCache('_TRENDING_', $response_items, 'shows');
            $results['shows'] = themoviedb_MediaPrep('shows', $response_items['results']);
        }
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
    if (isset($response_items['results']) && valid_array($response_items['results'])) {
        themoviedb_updateCache('_TODAY_SHOWS_', $response_items, 'shows');
        $results['shows'] = themoviedb_MediaPrep('shows', $response_items['results']);
    }
    return !empty($results) ? $results : false;
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

    if (empty($curl_data['results'])) {
        return false;
    }
    $results = array_pop($curl_data['results']);
    if (!empty($results['site']) && $results['site'] == 'YouTube') {
        $video = 'http://www.youtube.com/embed/' . $results['key'];
    } else if (!empty($results['site']) && $results['site'] == 'Vimeo') {
        $video = 'https://player.vimeo.com/video/' . $results['key'];
    } else if (!empty($results['site'])) {
        $log->warning('Video trailer site not implemented \"' . $results['site'] . '\"');
        return false;
    } else {
        $log->debug("Get $media_type trailer seems got nothing on id: $id");
        return false;
    }

    return trim($video);
}

function themoviedb_getCollection($col_id) {
    global $cfg;

    $item = [];

    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;
    $url = "https://api.themoviedb.org/3/collection/{$col_id}?api_key=" . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];

    $results = curl_get_tmdb($url);

    if (!valid_array($results) || !valid_array($results['parts'])) {
        return false;
    }

    $item['title'] = $results['name'];
    $item['plot'] = $results['overview'];
    if (!empty($results['poster_path'])) {
        $item['poster'] = $cfg['odb_images_link'] . $results['poster_path'];
    }

    foreach ($results['parts'] as $part) {
        empty($item['mids']) ? $item['mids'] = $part['id'] : $item['mids'] .= ',' . $part['id'];
        if (empty($item['poster']) && !empty($part['poster_path'])) {
            $item['poster'] = $cfg['odb_images_link'] . $part['poster_path'];
        }
    }

    return $item;
}

function themoviedb_getPeople(string $media_type, int $oid) {
    global $cfg;

    !isset($cfg['TMDB_LANG']) ? $cfg['TMDB_LANG'] = $cfg['LANG'] : null;
    if ($media_type == 'shows') {
        $url = "https://api.themoviedb.org/3/tv/{$oid}/aggregate_credits?api_key=" . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
    } else {
        $url = "https://api.themoviedb.org/3/movie/{$oid}/credits?api_key=" . $cfg['db_api_token'] . '&language=' . $cfg['TMDB_LANG'];
    }


    $results = curl_get_tmdb($url);

    if (!valid_array($results) || !valid_array($results['cast'])) {
        return false;
    }

    return $results;
}
