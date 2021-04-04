<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
function show_user_ships() {
    global $user;

    if (!empty(Filter::postInt('ship_id'))) {
        $ship_id = Filter::postInt('ship_id');
    }
    $ships = $user->getShips();

    if (empty($ships)) {
        return false;
    }

    $values = [];
    foreach ($ships as $_ship) {
        $values[] = [
            'name' => $_ship['name'],
            'value' => $_ship['id'],
        ];
    }

    empty($ship_id) ? $ship_id = $ships[0]['id'] : null;
    $tpl_data = html::select(['name' => 'ship_id', 'onChange' => 1, 'selected' => $ship_id], $values);

    $ship = $user->getShipById($ship_id);

    $tpl_data .= showShipOpts($ship);

    return $tpl_data;
}

function showShipOpts($ship) {
    global $L, $user, $frontend, $perks, $ship_parts;

    $tpl_data = '';
    $tdata = [];

    $tdata['energy_max'] = $ship_parts['accumulator'][$ship['accumulator']]['cap'];

    $planet = $user->checkInUserPlanet($ship['x'], $ship['y'], $ship['z']);
    if ($planet) {
        $tdata['planet_id'] = $planet['id'];
        $ship['own_planet_sector'] = 1;
        if ($planet['have_shipyard']) {
            $ship['can_shipyard_connect'] = 1;
        }
    } else {
        $alien_planet = $user->checkIfPlanet($ship['x'], $ship['y'], $ship['z']);
        $ship['alien_planet_sector'] = 1;
    }



    if ($ship['in_shipyard']) {
        $char_sel_values = [];
        foreach ($user->getPlanetCharacters($planet['id']) as $planet_character) {
            $char_name = $planet_character['name'];
            $char_name .= ' ' . $L[$perks[$planet_character['perk']]];
            $char_name .= '(' . $planet_character['perk_value'] . ')';
            $char_sel_values[] = ['name' => $char_name, 'value' => $planet_character['id']];
            $tdata['add_vips_sel'] = html::select(['type' => 'select', 'name' => 'char_add'], $char_sel_values);
        }
    }

    $char_sel_values = [];
    foreach ($user->getShipCharacters($ship['id']) as $ship_character) {
        $char_name = $ship_character['name'];
        $char_name .= ' ' . $L[$perks[$ship_character['perk']]];
        $char_name .= '(' . $ship_character['perk_value'] . ')';
        $char_sel_values[] = ['name' => $char_name, 'value' => $ship_character['id']];
        $tdata['del_vips_sel'] = html::select(['type' => 'select', 'name' => 'char_del'], $char_sel_values);
    }


    $ship_status = '';
    if ($ship['in_ship_cargo']) {
        $ship_status = $L['L_SHIPSTATUS_SHIP_CARGO'];
    } else if ($ship['ship_connection']) {
        $ship_status = $L['L_SHIPSTATUS_SHIP_CONNECTION'] . ' ' . $ship['ship_connection'];
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

    if (!empty($_POST['ship_shipyard_connect'])) {
        //TODO ENERGY -
        $db->update('ships', ['in_shipyard' => 1], ['id' => $ship_id]);
        $user->setShipValue($ship_id, 'in_shipyard', 1);
    }

    if (!empty($_POST['ship_shipyard_disconnect'])) {
        //TODO ENERGY -
        $db->update('ships', ['in_shipyard' => 0], ['id' => $ship_id]);
        $user->setShipValue($ship_id, 'in_shipyard', 0);
    }
    if (!empty($_POST['ship_set_speed']) && isset($_POST['setspeed'])) {
        $setspeed = Filter::postInt('setspeed');
        if ($setspeed != $ship['speed']) {
            $db->update('ships', ['speed' => $setspeed, 'tick_div' => $setspeed], ['id' => $ship_id]);
            $user->setShipValue($ship_id, 'speed', $setspeed);
        }
    }

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
    if (!empty($_POST['del_vip']) && !empty($char_add = Filter::postInt('char_del'))) {
        echo "DEL";
    }
    //var_dump($_POST);
}
