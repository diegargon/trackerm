<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/*
 * media_db = all media entrys
 * ids = all new ids to identify
 */
function ident_by_already_have_show(array $media_db, array &$ids) {
    global $log, $db;

    $ids_id = [];
    $debug_info = '';
    $log->debug("Called ident_by_already_have_show");
    foreach ($ids as $id_key => $id) {
        $item = $db->getItemById('library_shows', $id);
        if (!valid_array($item)) {
            continue;
        }
        foreach ($media_db as $item_db) {
            $predictible_title_db = getFileTitle($item_db['file_name']);
            $predictible_title_ident = getFileTitle($item['file_name']);
            if (valid_array($item) && !empty($item_db['master']) && ($predictible_title_db === $predictible_title_ident)
            ) {
                $master = $db->getItemById('library_master_shows', $item_db['master']);
                if (valid_array($master) && !empty($master['themoviedb_id'])) {
                    $tmdb_item = mediadb_getMediaData('shows', $master['themoviedb_id'], true);
                    if (valid_array($tmdb_item)) {
                        submit_ident('shows', $tmdb_item, $id);
                        unset($ids[$id_key]);
                        $ids_id[] = $id;
                        $debug_info .= "[$id:$predictible_title_ident:S{$item['season']}E{$item['episode']}]";
                        break;
                    }
                }
            }
        }
    }
    if (valid_array($ids_id)) {
        !empty($debug_info) ? $log->debug('Ident by already have show items: ' . $debug_info) : null;
        return $ids_id;
    } else {
        return false;
    }
    return $ids;
}

/*
 * Show identify items on library
 */

function show_identify_media(string $media_type) {
    global $LNG, $cfg, $db, $prefs;

    $uniq_shows = [];
    $page = Filter::getString('page');
    $limit = $prefs->getPrefsItem('max_identify_items');
    $templates = [];
    $templates['templates'] = [];

    if (empty($limit)) {
        return false;
    }

    $result = $db->query("SELECT * FROM library_$media_type WHERE master = '' OR master IS NULL LIMIT $limit");
    $media_library = $db->fetchAll($result);

    if (!valid_array($media_library)) {
        return false;
    }

    /* discard all except one same title */
    if ($media_type == 'shows') {
        foreach ($media_library as $item) {
            $predictible_title = getFileTitle($item['file_name']);
            if ((array_search($predictible_title, $uniq_shows)) === false) {
                $uniq_shows[] = $predictible_title;
                $media_tmp[] = $item;
            }
        }
        $media_library = $media_tmp;
    }

    if (!empty($cfg['auto_identify'])) {
        /* Get IDS */
        foreach ($media_library as $auto_ident_item) {
            $auto_ident_ids[] = $auto_ident_item['id'];
        }
        if (isset($auto_ident_ids) && count($auto_ident_ids) > 0) {
            $return_ids = auto_ident($media_type, $auto_ident_ids);
        }
        if ($return_ids && (count($auto_ident_ids) != count($return_ids))) {
            //Need requery for failed automate ident,  TODO: better (loop media_library and unset ids that not exists in return ids?)
            //query again but up we discard dups titles on show, check the logic or rewrite TODO
            $limit = count($return_ids);
            $result = $db->query("SELECT * FROM library_$media_type WHERE master = '' OR master is NULL LIMIT $limit");
            $media_library = $db->fetchAll($result);
            if (empty($media_library)) {
                return false;
            }
        }
    }

    /* Show in library the files that need identify */

    $uniq_shows = [];
    foreach ($media_library as &$item) {
        $predictible_title = getFileTitle($item['file_name']);
        $item['odb_results'] = [];
        if ($media_type == 'movies') {
            $odb_media = mediadb_searchMovies($predictible_title);
        } else if ($media_type == 'shows') {
            if ((array_search($predictible_title, $uniq_shows)) === false) {
                $odb_media = mediadb_searchShows($predictible_title);
                $uniq_shows[] = $predictible_title;
            } else {
                continue;
            }
        } else {
            return false;
        }

        if (valid_array($odb_media)) {
            foreach ($odb_media as &$odb_item) {
                $odb_item['year'] = (int) trim(substr($odb_item['release'], 0, 4));
            }
            $item['odb_results'] = $odb_media;
        }
    }


    if (!empty($media_library) && is_array($media_library)) {

        $ident_items = [
            'name' => 'identify_item_' . $media_type,
            'tpl_file' => 'identify_item',
            'tpl_pri' => 21,
            'tpl_place' => 'ident_container_' . $media_type,
            'tpl_place_var' => 'identify_titles',
            'tpl_vars' => $media_library,
            'tpl_common_vars' => [
                'media_type' => $media_type,
                'page' => $page]
        ];

        $ident_container = [
            'name' => 'ident_container_' . $media_type,
            'tpl_file' => 'identify',
            'tpl_pri' => 20,
            'tpl_place' => null,
            'tpl_place_var' => null,
            'tpl_vars' => [
                'head' => $LNG['L_IDENT_' . strtoupper($media_type) . ''],
            ]
        ];
        $templates['templates'][] = $ident_items;
        $templates['templates'][] = $ident_container;
    }

    return $templates;
}

/*
 * Try auto identify items
 */

function auto_ident(string $media_type, array $ids) {
    global $log, $db, $cfg;

    if (!valid_array($ids) || empty($media_type)) {
        return false;
    }
    $uniq_shows = [];

    foreach ($ids as $key_id => $id) {
        $db_item = $db->getItemById('library_' . $media_type, $id);
        $predictible_title = getFileTitle($db_item['file_name']);
        $log->debug("auto_ident by exact called for $id, search $predictible_title");
        if ($media_type == 'movies') {
            $search_results = mediadb_searchMovies($predictible_title);
        } else if ($media_type == 'shows') {
            if ((array_search($predictible_title, $uniq_shows)) === false) {
                $search_results = mediadb_searchShows($predictible_title);
                $uniq_shows[] = $predictible_title;
            }
        } else {
            return false;
        }

        if (!empty($search_results[0]['themoviedb_id'])) {
            $found = 0;
            //Exact
            foreach ($search_results as $coincidence) {
                $coincidence_title = clean_title($coincidence['title']);
                $db_item_title = clean_title($predictible_title);
                if ($coincidence_title == $db_item_title) {
                    /*
                     * we do not obtain with mediadb_search the full media data, that why we ask again with getMediaData
                     * and force update
                     */
                    $coincidence = mediadb_getMediaData($media_type, $coincidence['themoviedb_id'], 1);
                    if (valid_array($coincidence)) {
                        submit_ident($media_type, $coincidence, $id);
                        $found = 1;
                        break;
                    }
                }
            }
            //If strict is not set and cant ident exact, identify with the first Result
            if (!$found && !$cfg['auto_ident_strict']) {
                $found = 1;
                /*
                 * we do not obtain with mediadb_search the full media data, that why we ask again with getMediaData
                 * and force update
                 */
                $coincidence = mediadb_getMediaData($media_type, $search_results[0]['themoviedb_id'], true);
                if (valid_array($coincidence)) {
                    submit_ident($media_type, $coincidence, $id);
                    unset($ids[$key_id]);
                }
            }
        } else {
            $log->debug("Not found any results on tmdb for $predictible_title");
            continue;
        }
        !$found ? $log->debug("Auto ident is set but titles not match $db_item_title") : null;
    }

    return $ids;
}

/*
 * Key pair array[localid] = tmdb_id
 */

function ident_by_idpairs(string $media_type, array $id_pairs) {
    global $log;

    $log->debug("Ident by idpairs called");
    if (!valid_array($id_pairs)) {
        return false;
    }
    foreach ($id_pairs as $my_id => $tmdb_id) {
        (!empty($my_id) && !empty($tmdb_id)) ? ident_by_id($media_type, $tmdb_id, $my_id) : null;
    }

    return true;
}

/*
 * Ident an item by id pairs
 */

function ident_by_id(string $media_type, $tmdb_id, $local_id) {
    global $log;

    $log->debug("Ident by ident_by_id called tmdbid: $tmdb_id, id:$local_id");
    $db_data = mediadb_getMediaData($media_type, $tmdb_id, true);
    if (valid_array($db_data)) {
        submit_ident($media_type, $db_data, $local_id);
    } else {
        return false;
    }

    return true;
}

/*
 * Compared file hash agains library_history
 * Items are added to history when remove the file
 * Items are not added to history when delete register
 */

function ident_by_history(string $media_type, array &$ids) {
    global $db, $log;

    $ids_id = [];

    $log->debug("Ident by history called for ids:" . print_r($ids, true));

    if (!valid_array($ids)) {
        return false;
    }
    ($media_type == 'movies') ? $library = 'library_movies' : $library = 'library_shows';

    foreach ($ids as $id_key => $id) {
        if (!is_numeric($id)) {
            continue;
        }
        $item = $db->getItemById($library, $id);
        if (empty($item['file_hash'])) {
            continue;
        }
        if (empty($item['themoviedb_id'])) {
            $where = [
                'media_type' => ['value' => $media_type],
                'file_hash' => ['value' => $item['file_hash']],
            ];

            $results = $db->select('library_history', 'themoviedb_id', $where, 'LIMIT 1');
            $item_history = $db->fetch($results);
            $db->finalize($results);
            if (valid_array($item_history) && !empty($item_history['themoviedb_id'])) {
                ident_by_id($media_type, $item_history['themoviedb_id'], $id);
                unset($ids[$id_key]);
                $ids_id[] = $id;
            }
        }
    }
    if (valid_array($ids_id)) {
        $log->debug('Identified items by history: tmdb ids ', implode(',', $ids_id));
        return $ids_id;
    } else {
        return false;
    }
}

/*
 * This is the final identification function and all identify options finish here
 * Here we submit the identification
 * Need the tmdb item (oitem) and the local id
 */

function submit_ident(string $media_type, array $oitem, $id) {
    global $db, $log;

    $log->debug("Submit $media_type ident : (tmdb_id:" . $oitem['id'] . ")" . $oitem['title'] . ' id:' . $id);

    if ($media_type == 'shows') {
        $show_check = $db->getItemById('library_shows', $id);
        if (!empty($show_check['season']) && !empty($show_check['episode'])) {
            $where_check['season'] = ['value' => $show_check['season']];
            $where_check['episode'] = ['value' => $show_check['episode']];
        } else {
            $log->err("submit_ident: its show but havent season/episode set " . $id);
            return false;
        }
    }

    $media_master = $db->getItemByField('library_master_' . $media_type, 'themoviedb_id', $oitem['themoviedb_id']);
    $media_in_library = $db->getItemById('library_' . $media_type, $id);

    if (valid_array($media_master)) {
        if (valid_array($media_in_library)) {
            if ($media_in_library['master'] == $media_master['id']) {
                return false;
            }
            $total_items = $media_master['total_items'] + 1;
            $total_size = $media_master['total_size'] + $media_in_library['size'];

            $db->update('library_master_' . $media_type, ['total_items' => $total_items, 'total_size' => $total_size, 'items_updated' => time(), 'trailer' => null], ['id' => ['value' => $media_master['id']]]);
            $db->update('library_' . $media_type, ['master' => $media_master['id']], ['id' => ['value' => $id]], 'LIMIT 1');
            //if is a change master rest 1 or delete from old master.
            if (!empty($media_in_library['master']) && $media_in_library['master'] !== $media_master['id']) {
                $old_master_id = $media_in_library['master'];
                $item_old_master = $db->getItemById('library_master_' . $media_type, $old_master_id);
                $total_items = $item_old_master['total_items'];
                if ($total_items == 1) {
                    $db->deleteItemById('library_master_' . $media_type, $old_master_id);
                } else {
                    $new_size = $item_old_master['total_size'] - $media_in_library['size'];
                    $db->updateItemById('library_master_' . $media_type, $old_master_id, ['total_items' => $total_items - 1, 'total_size' => $new_size]);
                }
            }
        }
    } else {
        if (valid_array($media_in_library)) {
            //TODO: Fix unused fields in tmdb_search_ table for avoid  unsets
            $new_item = $oitem;
            $new_item['total_items'] = 1;
            $new_item['total_size'] = $media_in_library['size'];
            $new_item['items_updated'] = $media_in_library['created'];
            $new_item['created'] = $media_in_library['created'];
            unset($new_item['id']);
            unset($new_item['ilink']);
            unset($new_item['elink']);
            unset($new_item['in_library']);
            unset($new_item['added']);
            unset($new_item['updated']);

            $db->insert('library_master_' . $media_type, $new_item);
            $lastid_master = $db->getLastId();
            $db->update('library_' . $media_type, ['master' => $lastid_master], ['id' => ['value' => $id]]);
            //if has master set, its a master change rest 1 or delete from old master if is 1.
            if (!empty($media_in_library['master'])) {
                $old_master_id = $media_in_library['master'];
                $item_old_master = $db->getItemById('library_master_' . $media_type, $old_master_id);
                $total_items = $item_old_master['total_items'];
                if ($total_items == 1) {
                    $db->deleteItemById('library_master_' . $media_type, $old_master_id);
                } else {
                    $new_size = $item_old_master['total_size'] - $media_in_library['size'];
                    $db->updateItemById('library_master_' . $media_type, $old_master_id, ['total_items' => $total_items - 1, 'total_size' => $new_size]);
                }
            }
        }
    }

    return true;
}
