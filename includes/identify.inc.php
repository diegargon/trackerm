<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
function ident_by_already_have_show($media, &$ids) {
    global $log, $db;

    $ids_id = [];
    $debug_info = '';
    $log->debug("Called ident_by_already_have_show");
    foreach ($ids as $id_key => $id) {
        $item = $db->getItemById('library_shows', $id);
        foreach ($media as $id_item) {
            if (valid_array($item) && $id_item['predictible_title'] === ucwords($item['predictible_title']) &&
                    !empty($id_item['master'])
            ) {
                $master = $db->getItemById('library_master_shows', $id_item['master']);
                if (valid_array($master) && !empty($master['themoviedb_id'])) {
                    $tmdb_item = mediadb_getFromCache('shows', $master['themoviedb_id']);
                    if (valid_array($tmdb_item)) {
                        submit_ident('shows', $tmdb_item, $id);
                        unset($ids[$id_key]);
                        $ids_id[] = $id;
                        $debug_info .= "[$id:{$item['title']}:S{$item['season']}E{$item['episode']}]";
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

function show_identify_media($media_type) {
    global $LNG, $cfg, $db, $frontend;

    $titles = '';
    $i = 0;
    $uniq_shows = [];
    $iurl = '?page=' . Filter::getString('page');

    $result = $db->query('SELECT * FROM library_' . $media_type . ' WHERE master is NULL');
    $media = $db->fetchAll($result);

    if (!valid_array($media)) {
        return false;
    }

    if ($media_type == 'shows') {
        foreach ($media as $item) {
            if ((array_search($item['predictible_title'], $uniq_shows)) === false) {
                $uniq_shows[] = $item['predictible_title'];
                $media_tmp[] = $item;
            }
        }
        $media = $media_tmp;
    }

    if (!empty($cfg['auto_identify'])) {
        foreach ($media as $auto_id_item) {
            if (empty($auto_id_item['title']) || empty($auto_id_item['themoviedb_id'])) {
                $auto_id_ids[] = $auto_id_item['id'];
            }
        }
        (isset($auto_id_ids) && count($auto_id_ids) > 0 ) ? auto_ident_exact($media_type, $auto_id_ids) : null;
        //Need requery for failed automate ident, probably there is a better way TODO
        $result = $db->query("SELECT * FROM library_$media_type WHERE title = '' OR title is NULL");
        $media = $db->fetchAll($result);
        if (empty($media)) {
            return false;
        }
    }

    $uniq_shows = [];
    foreach ($media as $item) {
        $title_tdata['results_opt'] = '';

        if (empty($item['title'])) {
            if ($i >= $cfg['max_identify_items']) {
                break;
            }
            if ($media_type == 'movies') {
                $db_media = mediadb_searchMovies($item['predictible_title']);
            } else if ($media_type == 'shows') {
                if ((array_search($item['predictible_title'], $uniq_shows)) === false) {
                    $db_media = mediadb_searchShows($item['predictible_title']);
                    $uniq_shows[] = $item['predictible_title'];
                } else {
                    continue;
                }
            } else {
                return false;
            }

            if (valid_array($db_media)) {
                foreach ($db_media as $db_item) {
                    $year = trim(substr($db_item['release'], 0, 4));
                    $title_tdata['results_opt'] .= '<option value="' . $db_item['themoviedb_id'] . '">';
                    $title_tdata['results_opt'] .= $db_item['title'];
                    !empty($year) ? $title_tdata['results_opt'] .= ' (' . $year . ')' : null;
                    $title_tdata['results_opt'] .= '</option>';
                }
            }
            $title_tdata['del_iurl'] = $iurl . '&media_type=' . $media_type . '&ident_delete=' . $item['id'];
            $title_tdata['more_iurl'] = '?page=identify&media_type=' . $media_type . '&identify=' . $item['id'];
            $title_tdata['media_type'] = $media_type;
            $titles .= $table = $frontend->getTpl('identify_item', array_merge($item, $title_tdata));
            $i++;
        }
    }

    if (!empty($titles)) {
        $tdata['titles'] = $titles;
        $tdata['head'] = $LNG['L_IDENT_' . strtoupper($media_type) . ''];

        $table = $frontend->getTpl('identify', $tdata);

        return $table;
    }
    return false;
}

function auto_ident_exact($media_type, $ids) {
    global $log, $db, $cfg;

    if (!valid_array($ids) || empty($media_type)) {
        return false;
    }
    $uniq_shows = [];

    foreach ($ids as $id) {
        $log->debug("auto_ident by exact called for $id");
        $db_item = $db->getItemById('library_' . $media_type, $id);
        if ($media_type == 'movies') {
            $search_media = mediadb_searchMovies($db_item['predictible_title']);
        } else if ($media_type == 'shows') {
            if ((array_search($db_item['predictible_title'], $uniq_shows)) === false) {
                $search_media = mediadb_searchShows($db_item['predictible_title']);
                $uniq_shows[] = $db_item['predictible_title'];
            }
        } else {
            return false;
        }

        if (!empty($search_media[0]['themoviedb_id'])) {
            $found = 0;
            foreach ($search_media as $coincidence) {
                $coincidence_title = clean_title($coincidence['title']);
                $db_item_title = clean_title($db_item['predictible_title']);
                if ($coincidence_title == $db_item_title) {
                    submit_ident($media_type, $coincidence, $id);
                    $found = 1;
                    break;
                }
            }
            if (!$found && !$cfg['auto_ident_strict']) {
                $found = 1;
                submit_ident($media_type, $search_media[0], $id);
            }
        } else {
            continue;
        }
        !$found ? $log->debug("Auto ident is set but titles not match $db_item_title") : null;
    }

    return true;
}

function ident_by_idpairs($media_type, $id_pairs) {
    global $log;

    $log->debug("Ident by idpairs called");
    if (!valid_array($id_pairs)) {
        return false;
    }
    foreach ($id_pairs as $my_id => $tmdb_id) {
        (!empty($my_id) && !emptY($tmdb_id)) ? ident_by_id($media_type, $tmdb_id, $my_id) : null;
    }

    return true;
}

function ident_by_id($media_type, $tmdb_id, $id) {
    global $log;

    $log->debug("Ident by ident_by_id called tmdbid: $tmdb_id, id:$id");
    $db_data = mediadb_getFromCache($media_type, $tmdb_id);
    if (valid_array($db_data)) {
        submit_ident($media_type, $db_data, $id);
    } else {
        return false;
    }

    return true;
}

function submit_ident($media_type, $item, $id) {
    global $db, $log;

    $log->debug("Submit $media_type ident : (tmdb_id:" . $item['id'] . ")" . $item['title'] . ' id:' . $id);
    $upd_fields = [];

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

    $media_master = $db->getItemByField('library_master_' . $media_type, 'themoviedb_id', $item['themoviedb_id']);
    $_item = $item; //to remove, now not want modify item since we use later in actual behaviour.

    $media_in_library = $db->getItemById('library_' . $media_type, $id);

    if (valid_array($media_master)) {
        if (valid_array($media_in_library)) {
            $total_items = $media_master['total_items'] + 1;
            $total_size = $media_master['total_size'] + $media_in_library['size'];

            $db->update('library_master_' . $media_type, ['total_items' => $total_items, 'total_size' => $total_size, 'items_updated' => time()], ['id' => ['value' => $media_master['id']]]);
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
            $_item['total_items'] = 1;
            $_item['total_size'] = $media_in_library['size'];
            unset($_item['id']);
            unset($_item['ilink']);
            unset($_item['elink']);
            unset($_item['in_library']);
            unset($_item['added']);
            unset($_item['created']);
            unset($_item['file_hash']);
            unset($_item['media_info']);
            unset($_item['file_name']);
            unset($_item['predictible_title']);
            unset($_item['size']);
            $_item['items_updated'] = time();
            $db->insert('library_master_' . $media_type, $_item);
            $lastid_master = $db->getLastId();
            $db->update('library_' . $media_type, ['master' => $lastid_master], ['id' => ['value' => $id]]);
            //if is a change master rest 1 or delete from old master.
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

    /*
      if (!empty($item['title'])) {
      $upd_fields['title'] = $item['title'];
      $upd_fields['clean_title'] = clean_title($item['title']);
      }
      if (!empty($item['name'])) {
      $upd_fields['name'] = $item['name'];
      $upd_fields['clean_title'] = clean_title($item['name']);
      }
      $upd_fields['themoviedb_id'] = $item['themoviedb_id'];

      !empty($item['poster']) ? $upd_fields['poster'] = $item['poster'] : $upd_fields['poster'] = '';
      !empty($item['original_title']) ? $upd_fields['original_title'] = $item['original_title'] : $upd_fields['original_title'] = '';
      !empty($item['rating']) ? $upd_fields['rating'] = $item['rating'] : $upd_fields['rating'] = '';
      !empty($item['popularity']) ? $upd_fields['popularity'] = $item['popularity'] : $upd_fields['popularity'] = '';
      !empty($item['scene']) ? $upd_fields['scene'] = $item['scene'] : $upd_fields['scene'] = '';
      !empty($item['lang']) ? $upd_fields['lang'] = $item['lang'] : $upd_fields['lang'] = '';
      !empty($item['trailer']) ? $upd_fields['trailer'] = $item['trailer'] : $upd_fields['trailer'] = '';
      !empty($item['plot']) ? $upd_fields['plot'] = $item['plot'] : $upd_fields['plot'] = '';
      !empty($item['release']) ? $upd_fields['release'] = $item['release'] : $upd_fields['release'] = '';

      if ($media_type == 'movies') {
      $db->updateItemById('library_movies', $id, $upd_fields);
      } else if ($media_type == 'shows') {
      $mylib_shows = $db->getItemById('library_shows', $id);
      if (valid_array($mylib_shows)) {
      $where['predictible_title'] = ['value' => $mylib_shows['predictible_title']];
      $db->update('library_shows', $upd_fields, $where);
      } else {
      return false;
      }
      }
     */
    return true;
}

function ident_by_history($media_type, &$ids) {
    global $db, $log;

    $ids_id = [];

    $log->debug("Ident by history called");

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
