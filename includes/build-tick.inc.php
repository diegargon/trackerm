<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function build_tick() {
    global $db;

    $db->query("UPDATE build SET ticks = ticks -1");

    $result = $db->select('build', '*', ['ticks' => ['value' => 0]]);
    $builds = $db->fetchAll($result);
    $delete_ids = '';

    if (valid_array($builds)) {
        $ports_creation = '';
        $shipyard_creation = '';

        foreach ($builds as $build) {
            //PORT
            if ($build['type'] == 'port') {
                empty($ports_creation) ? $ports_creation .= $build['id_dest'] : $ports_creation .= ',' . $build['id_dest'];
                empty($delete_ids) ? $delete_ids .= $build['id'] : $delete_ids .= ',' . $build['id'];
            }
            //SHIPYARD
            if ($build['type'] == 'shipyard') {
                empty($shipyard_creation) ? $shipyard_creation .= $build['id_dest'] : $shipyard_creation .= ',' . $build['id_dest'];
                empty($delete_ids) ? $delete_ids .= $build['id'] : $delete_ids .= ',' . $build['id'];
            }
        }

        if (!empty($ports_creation)) {
            $db->query("UPDATE planets SET port_built = 0, have_port = 1, port_slots = 1, develop_level = develop_level + 1 WHERE id IN ($ports_creation)");
        }
        if (!empty($shipyard_creation)) {
            $db->query("UPDATE planets SET shipyard_built = 0, have_shipyard = 1, shipyard_slots = 1, develop_level = develop_level + 1 WHERE id IN ($shipyard_creation)");
        }
    }

    if (!empty($delete_ids)) {
        $db->query("DELETE FROM build WHERE id IN ($delete_ids)");
    }
}
