<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
function show_control_ships($ship) {
    global $L, $user, $frontend, $perks, $ship_parts;

    $tpl_data = '';
    $tdata = [];

    $tdata['sel_ship'] = frmt_select_user_ships($ship);

    $tdata['energy_max'] = $ship_parts['accumulator'][$ship['accumulator']]['cap'];

    $planet = $user->checkInUserPlanet($ship['x'], $ship['y'], $ship['z']);
    if ($planet) {
        $tdata['planet_id'] = $planet['id'];
        $ship['own_planet_sector'] = 1;
    } else {
        $alien_planet = $user->checkIfPlanet($ship['x'], $ship['y'], $ship['z']);
        $ship['alien_planet_sector'] = 1;
    }
    if ($ship['in_ship_cargo'] || $ship['ship_connection'] || $ship['in_shipyard']) {
        $ship['can_connect'] = 0;
    } else {
        $ship['can_connect'] = 1;
    }

    if ($ship['can_connect']) {
        $own_ships_in_range = $user->getInRangeUserShips($ship['id']);
        if (valid_array($own_ships_in_range)) {
            $ir_values = [];
            foreach ($own_ships_in_range as $ship_ir) {
                if (!$ship_ir['in_shipyard'] && !$ship_ir['in_ship_cargo'] && !$ship_ir['ship_connection']) {
                    $ir_values[] = ['name' => $ship_ir['name'], 'value' => $ship_ir['id']];
                }
            }
            if (valid_array($ir_values)) {
                $tdata['ship_conn_sel'] = html::input(['name' => 'ship_conn_submit', 'value' => $L['L_SHIP_CONNECT']]);
                $tdata['ship_conn_sel'] .= html::select(['name' => 'ship_conn'], $ir_values);
            }
        }
    }
    if ($ship['ship_connection']) {
        $tdata['ship_conn_sel'] = html::input(['name' => 'ship_disconn_submit', 'value' => $L['L_SHIP_DISCONNECT']]);
    }

    if ($ship['in_shipyard']) {
        $char_sel_values = [];
        foreach ($user->getPlanetCharacters($planet['id']) as $planet_character) {
            $char_name = $planet_character['name'];
            $char_name .= ' ' . $L[$perks[$planet_character['perk']]];
            $char_name .= '(' . $planet_character['perk_value'] . ')';
            $char_sel_values[] = ['name' => $char_name, 'value' => $planet_character['id']];
            $tdata['add_vips_sel'] = html::select(['name' => 'char_add'], $char_sel_values);
        }
    }

    $char_sel_values = [];
    foreach ($user->getShipCharacters($ship['id']) as $ship_character) {
        $char_name = $ship_character['name'];
        $char_name .= ' ' . $L[$perks[$ship_character['perk']]];
        $char_name .= '(' . $ship_character['perk_value'] . ')';
        $char_sel_values[] = ['name' => $char_name, 'value' => $ship_character['id']];
        $tdata['del_vips_sel'] = html::select(['name' => 'char_del'], $char_sel_values);
    }


    $ship_status = '';
    if ($ship['in_ship_cargo']) {
        $ship_status = $L['L_SHIPSTATUS_SHIP_CARGO'];
    } else if ($ship['ship_connection']) {
        $conn_ship = $user->getShipById($ship['ship_connection']);
        $ship_status = $L['L_SHIPSTATUS_SHIP_CONNECTION'] . ' ' . $conn_ship['name'];
    } else if ($ship['in_shipyard']) {
        $ship_status = $L['L_SHIPSTATUS_IN_SHIPYARD'];
    } else if ($ship['speed'] == 0 && (!empty($ship['own_planet_sector']) || !empty($ship['alien_planet_sector']) )) {
        $ship_status .= $L['L_SHIPSTATUS_ORBIT_PLANET'];
    } else if ($ship['speed'] > 0 && (!empty($ship['own_planet_sector']) || !empty($ship['alien_planet_sector']) )) {
        $ship_status .= $L['L_SHIPSTATUS_PLANET_SECTOR_PATROL'];
    } else if ($ship['speed'] == 0 && empty($ship['own_planet_sector']) && empty($ship['alien_planet_sector'])) {
        $ship_status .= $L['L_SHIPSTATUS_SPACE_STOPPED'];
    } else if ($ship['speed'] > 0 && empty($ship['own_planet_sector']) && empty($ship['alien_planet_sector'])) {
        $ship_status .= $L['L_SHIPSTATUS_SPACE_TRAVEL'];
    }

    $tdata['ship_status'] = $ship_status;
    $tdata['max_speed'] = 1;
    $tpl_data .= $frontend->getTpl('ships', array_merge($ship, $tdata));

    return $tpl_data;
}

function frmt_select_user_ships($ship) {
    global $user;

    $values = [];

    $ships = $user->getShips();

    foreach ($ships as $_ship) {
        $values[] = [
            'name' => $_ship['name'],
            'value' => $_ship['id'],
        ];
    }

    return html::select(['name' => 'ship_id', 'onChange' => 1, 'selected' => $ship['id']], $values);
}

function showShipOpts() {
    global $L, $user, $frontend, $perks, $ship_parts;

    $tpl_data = '';
    $tdata = [];

    $ship = $user->getShipById($ship_id);

    $values = [];
    foreach ($ships as $_ship) {
        $values[] = [
            'name' => $_ship['name'],
            'value' => $_ship['id'],
        ];
    }

    $tpl_data['sel_ship'] = html::select(['name' => 'ship_id', 'onChange' => 1, 'selected' => $ship_id], $values);

    $tdata['energy_max'] = $ship_parts['accumulator'][$ship['accumulator']]['cap'];

    $planet = $user->checkInUserPlanet($ship['x'], $ship['y'], $ship['z']);
    if ($planet) {
        $tdata['planet_id'] = $planet['id'];
        $ship['own_planet_sector'] = 1;
    } else {
        $alien_planet = $user->checkIfPlanet($ship['x'], $ship['y'], $ship['z']);
        $ship['alien_planet_sector'] = 1;
    }
    if ($ship['in_ship_cargo'] || $ship['ship_connection'] || $ship['in_shipyard']) {
        $ship['can_connect'] = 0;
    } else {
        $ship['can_connect'] = 1;
    }

    if ($ship['can_connect']) {
        $own_ships_in_range = $user->getInRangeUserShips($ship['id']);
        if (valid_array($own_ships_in_range)) {
            $ir_values = [];
            foreach ($own_ships_in_range as $ship_ir) {
                if (!$ship_ir['in_shipyard'] && !$ship_ir['in_ship_cargo'] && !$ship_ir['ship_connection']) {
                    $ir_values[] = ['name' => $ship_ir['name'], 'value' => $ship_ir['id']];
                }
            }
            if (valid_array($ir_values)) {
                $tdata['ship_conn_sel'] = html::input(['name' => 'ship_conn_submit', 'value' => $L['L_SHIP_CONNECT']]);
                $tdata['ship_conn_sel'] .= html::select(['name' => 'ship_conn'], $ir_values);
            }
        }
    }
    if ($ship['ship_connection']) {
        $tdata['ship_conn_sel'] = html::input(['name' => 'ship_disconn_submit', 'value' => $L['L_SHIP_DISCONNECT']]);
    }

    if ($ship['in_shipyard']) {
        $char_sel_values = [];
        foreach ($user->getPlanetCharacters($planet['id']) as $planet_character) {
            $char_name = $planet_character['name'];
            $char_name .= ' ' . $L[$perks[$planet_character['perk']]];
            $char_name .= '(' . $planet_character['perk_value'] . ')';
            $char_sel_values[] = ['name' => $char_name, 'value' => $planet_character['id']];
            $tdata['add_vips_sel'] = html::select(['name' => 'char_add'], $char_sel_values);
        }
    }

    $char_sel_values = [];
    foreach ($user->getShipCharacters($ship['id']) as $ship_character) {
        $char_name = $ship_character['name'];
        $char_name .= ' ' . $L[$perks[$ship_character['perk']]];
        $char_name .= '(' . $ship_character['perk_value'] . ')';
        $char_sel_values[] = ['name' => $char_name, 'value' => $ship_character['id']];
        $tdata['del_vips_sel'] = html::select(['name' => 'char_del'], $char_sel_values);
    }


    $ship_status = '';
    if ($ship['in_ship_cargo']) {
        $ship_status = $L['L_SHIPSTATUS_SHIP_CARGO'];
    } else if ($ship['ship_connection']) {
        $conn_ship = $user->getShipById($ship['ship_connection']);
        $ship_status = $L['L_SHIPSTATUS_SHIP_CONNECTION'] . ' ' . $conn_ship['name'];
    } else if ($ship['in_shipyard']) {
        $ship_status = $L['L_SHIPSTATUS_IN_SHIPYARD'];
    } else if ($ship['speed'] == 0 && (!empty($ship['own_planet_sector']) || !empty($ship['alien_planet_sector']) )) {
        $ship_status .= $L['L_SHIPSTATUS_ORBIT_PLANET'];
    } else if ($ship['speed'] > 0 && (!empty($ship['own_planet_sector']) || !empty($ship['alien_planet_sector']) )) {
        $ship_status .= $L['L_SHIPSTATUS_PLANET_SECTOR_PATROL'];
    } else if ($ship['speed'] == 0 && empty($ship['own_planet_sector']) && empty($ship['alien_planet_sector'])) {
        $ship_status .= $L['L_SHIPSTATUS_SPACE_STOPPED'];
    } else if ($ship['speed'] > 0 && empty($ship['own_planet_sector']) && empty($ship['alien_planet_sector'])) {
        $ship_status .= $L['L_SHIPSTATUS_SPACE_TRAVEL'];
    }

    $tdata['ship_status'] = $ship_status;
    $tdata['max_speed'] = 1;
    $tpl_data .= $frontend->getTpl('ships', array_merge($ship, $tdata));

    return $tpl_data;
}

function ship_control_exec() {
    global $db, $user, $ship_parts;

    //var_dump($_POST);
    $ship_id = Filter::postInt('ship_id');
    if (empty($ship_id)) {
        return false;
    }
    $ship = $user->getShipById($ship_id);

    // SHIPYARD CONNECT
    if (!empty($_POST['ship_shipyard_connect'])) {
        //TODO ENERGY -
        $db->update('ships', ['in_shipyard' => 1, 'speed' => 0], ['id' => $ship_id]);
        $user->setShipValue($ship_id, 'in_shipyard', 1);
        $user->setShipValue($ship_id, 'speed', 0);
    }

    // SHIPYARD DISCONNECT
    if (!empty($_POST['ship_shipyard_disconnect'])) {
        //TODO ENERGY -
        $db->update('ships', ['in_shipyard' => 0, 'speed' => 0.1], ['id' => $ship_id]);
        $user->setShipValue($ship_id, 'in_shipyard', 0);
        $user->setShipValue($ship_id, 'speed', 0.1);
    }
    //SHIP CONNECT

    if (!empty($_POST['ship_conn_submit']) && !empty(Filter::postInt('ship_conn'))) {
        $connect_to_ship = Filter::postInt('ship_conn');
        //TODO ENERGY;
        $db->query("UPDATE ships SET ship_connection = $connect_to_ship, speed = 0 WHERE id = {$ship['id']} LIMIT 1");
        $db->query("UPDATE ships SET ship_connection = {$ship['id']}, speed = 0 WHERE id = $connect_to_ship LIMIT 1");
        $user->setShipValue($ship['id'], 'ship_connection', $connect_to_ship);
        $user->setShipValue($connect_to_ship, 'ship_connection', $ship['id']);
        $user->setShipValue($ship['id'], 'speed', 0);
        $user->setShipValue($connect_to_ship, 'speed', 0);
    }

    //SHIP DISCONNECT
    if (!empty($_POST['ship_disconn_submit'])) {
        //TODO ENERGY
        $remote_ship_to_disconn = $ship['ship_connection'];
        $db->query("UPDATE ships SET ship_connection = 0, speed = 0.1 WHERE id = $remote_ship_to_disconn OR id = {$ship['id']} LIMIT 2");
        $user->setShipValue($ship['id'], 'ship_connection', 0);
        $user->setShipValue($remote_ship_to_disconn, 'ship_connection', 0);
        $user->setShipValue($ship['id'], 'speed', 0.1);
        $user->setShipValue($remote_ship_to_disconn, 'speed', 0.1);
    }
    // SET SPEED
    if (!empty($_POST['ship_set_speed']) && !empty(Filter::postInt('setspeed'))) {
        $setspeed = Filter::postInt('setspeed');
        if ($setspeed != $ship['speed']) {
            $db->update('ships', ['speed' => $setspeed, 'tick_div' => $setspeed], ['id' => $ship_id]);
            $user->setShipValue($ship_id, 'speed', $setspeed);
        }
    }

    // SET DESTINATION
    if (!empty($_POST['ship_set_destination'])) {
        $set_dest['dx'] = Filter::postInt('dest_x');
        $set_dest['dy'] = Filter::postInt('dest_y');
        $set_dest['dz'] = Filter::postInt('dest_z');

        if ($ship['dx'] != $set_dest['dx'] || $ship['dy'] != $set_dest['dy'] || $ship['dz'] != $set_dest['dz']) {
            $db->update('ships', $set_dest, ['id' => $ship['id']]);
            $user->setShipValue($ship_id, 'dx', $set_dest['dx']);
            $user->setShipValue($ship_id, 'dy', $set_dest['dy']);
            $user->setShipValue($ship_id, 'dz', $set_dest['dz']);
        }
    }

    // ADD VIP
    if (!empty($_POST['add_vip']) && !empty($char_add = Filter::postInt('char_add'))) {
        $cap = $ship_parts['bridge'][$ship['bridge_type']]['cap'];

        $vips = $user->getShipCharacters($ship['id']);
        $vips_in_ship = count($vips);
        if ($vips_in_ship < $cap) {
            $char_set['planet_assigned'] = 0;
            $char_set['ship_assigned'] = $ship['id'];
            $db->update('characters', $char_set, ['id' => $char_add]);
        }
    }

    // DEL VIP
    if (!empty($_POST['del_vip']) && !empty($char_add = Filter::postInt('char_del'))) {
        echo "DEL";
    }
    //var_dump($_POST);
}
