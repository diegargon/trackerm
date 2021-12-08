<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function update_v13_rebuild_master($media_type) {
    global $db;

    if (empty($media_type)) {
        return false;
    }
    $library_table = 'library_' . $media_type;
    $library_master_table = 'library_master_' . $media_type;
    $result = $db->query("SELECT * FROM $library_table");
    $media = $db->fetchAll($result);

    foreach ($media as $media_item) {

        $media_master = $db->getItemByField($library_master_table, 'themoviedb_id', $media_item['themoviedb_id']);

        if (!empty($media_master)) {

            $total_items = $media_master['total_items'] + 1;
            $total_size = $media_master['total_size'] + $media_item['size'];
            $update_ary = [
                'total_items' => $total_items,
                'total_size' => $total_size,
            ];

            $db->update($library_master_table, $update_ary, ['id' => ['value' => $media_master['id']]]);
            $db->update('library_' . $media_type, ['master' => $media_master['id']], ['themoviedb_id' => ['value' => $media_item['themoviedb_id']]]);
        } else {

            $_media_item = [];
            $_media_item = $media_item;
            $_media_item['total_items'] = 1;
            $_media_item['total_size'] = $media_item['size'];
            unset($_media_item['id']);
            unset($_media_item['ilink']);
            unset($_media_item['elink']);
            unset($_media_item['in_library']);
            unset($_media_item['file_name']);
            unset($_media_item['predictible_title']);
            unset($_media_item['file_name']);
            unset($_media_item['size']);
            unset($_media_item['path']);
            unset($_media_item['tags']);
            unset($_media_item['ext']);
            unset($_media_item['season']);
            unset($_media_item['episode']);
            unset($_media_item['master']);
            unset($_media_item['added']);
            unset($_media_item['created']);
            unset($_media_item['file_hash']);
            unset($_media_item['mediainfo']);
            $db->insert('library_master_' . $media_type, $_media_item);
            $media_lastid = $db->getLastId();
            $db->update('library_' . $media_type, ['master' => $media_lastid], ['themoviedb_id' => ['value' => $_media_item['themoviedb_id']]]);
        }
    }
    return true;
}

function update_v16tov17() {
    global $db;

    foreach (['movies', 'shows'] as $media_type) {
        $result = $db->select('library_master_' . $media_type, 'id,updated');
        $media = $db->fetchAll($result);
        foreach ($media as $item) {
            if (empty($item['updated'])) {
                $updated['items_updated'] = date('Y-m-d H:i:s');
                $updated['updated'] = date('Y-m-d H:i:s');
            } else {
                $updated['items_updated'] = $item['updated'];
            }
            $db->update('library_master_' . $media_type, $updated, ['id' => ['value' => $item['id']]]);
        }
    }
}
