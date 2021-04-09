<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
function ships_tick() {
    move_ships();
}

function move_ships() {
    global $db;

    $result = $db->select('ships', '*', ['speed' => ['value' => 0, 'op' => '>']]);
    $ships = $db->fetchAll($result);

    if (valid_array($ships)) {
        foreach ($ships as $ship) {
            //speed must be 0 if is connected but be check again here
            if ($ship['in_shipyard'] || $ship['in_port'] || $ship['ship_connection']) {
                continue;
            }
            $set = [];
            if ($ship['speed'] == 1) {
                $set = cal_new_ship_pos($ship);
                $ship['tick_div'] != 1 ? $set['tick_div'] = 1 : null;
            } else {
                $set['tick_div'] = $ship['tick_div'] + 0.1;
                if ($ship['tick_div'] + 0.1 == 1) {
                    $set = cal_new_ship_pos($ship);
                    $set['tick_div'] = $ship['speed'];
                }
            }

            update_cords('ships', $ship['id'], $set);
        }
    }
}

function update_cords($table, $id, $set) {
    global $db;

    $db->update($table, $set, ['id' => $id]);
}

function cal_new_ship_pos($ship) {
    $x = $ship['x'];
    $y = $ship['y'];
    $z = $ship['z'];
    $dx = $ship['dx'];
    $dy = $ship['dy'];
    $dz = $ship['dz'];
    $dir_x = $dx - $x;
    $dir_y = $dy - $y;
    $dir_z = $dz - $z;

    if ($x !== $dx) {
        $dir_x < 0 ? $x-- : $x++;
    }

    if ($y !== $dy) {
        $dir_y < 0 ? $y-- : $y++;
    }
    if ($z !== $dz) {
        $dir_z < 0 ? $z-- : $z++;
    }
    echo $ship['id'] . ": $x:$y:$z ($dir_x:$dir_y:$dir_z) ($dx:$dy:$dz)\n";
    return ['x' => $x, 'y' => $y, 'z' => $z];
}
