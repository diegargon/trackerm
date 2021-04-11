<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function show_control_ships(array $ship, array $post_data) {
    global $L, $user, $frontend, $perks, $ship_parts;

    $tpl_data = '';
    $tdata = [];

    !empty($post_data['status_msg']) ? $status_msg = $post_data['status_msg'] : $status_msg = '';

    $tdata['sel_ship'] = frmt_select_user_ships($ship);

    $tdata['energy_max'] = $ship_parts['accumulator'][$ship['accumulator']]['cap'];

    $planet = $user->checkInUserPlanet($ship['x'], $ship['y'], $ship['z']);
    if (valid_array($planet)) {
        $tdata['planet_id'] = $planet['id'];
        $ship['own_planet_sector'] = 1;
    } else {
        $alien_planet = Planets::checkIfPlanet($ship['x'], $ship['y'], $ship['z']);
        if (valid_array($alien_planet)) {
            $ship['alien_planet_sector'] = $alien_planet['id'];
        }
    }
    if ($ship['in_port'] || $ship['ship_connection'] || $ship['in_shipyard']) {
        $ship['can_connect'] = 0;
    } else {
        $ship['can_connect'] = 1;
    }

    if (!$user->shipHavePilot($ship['id'])) {
        $ship['can_connect'] = 0;
    }
    if ($ship['can_connect']) {
        $own_ships_in_range = $user->getInRangeUserShips($ship['id']);
        if (valid_array($own_ships_in_range)) {
            $ir_values = [];
            foreach ($own_ships_in_range as $ship_ir) {
                if (!$ship_ir['in_shipyard'] && !$ship_ir['in_port'] && !$ship_ir['ship_connection']) {
                    $ir_values[] = ['name' => $ship_ir['name'], 'value' => $ship_ir['id']];
                }
            }
            if (valid_array($ir_values)) {
                $tdata['ship_conn_sel'] = html::input(['name' => 'ship_conn_submit', 'value' => $L['L_SHIP_CONNECT']]);
                $tdata['ship_conn_sel'] .= html::select(['name' => 'ship_conn'], $ir_values);
            }
        }

        if (valid_array($planet)) {
            if ($planet['have_port']) {
                //TODO check free slots
                $tdata['can_conn_port'] = 1;
            }
            if ($planet['have_shipyard']) {
                //TODO check free slots
                $tdata['can_conn_shipyard'] = 1;
            }
        }
    }
    if ($ship['ship_connection']) {
        $tdata['ship_conn_sel'] = html::input(['name' => 'ship_disconn_submit', 'value' => $L['L_SHIP_DISCONNECT']]);
    }

    if (valid_array($planet) && ($ship['in_shipyard'] || $ship['in_port'])) {
        $char_sel_values = [];
        foreach ($user->getPlanetCharacters($planet['id'], $free = 1) as $planet_character) {
            if (empty($planet_character['job'])) {
                $char_name = $planet_character['name'];
                $char_name .= ' ' . $L[$perks[$planet_character['perk']]];
                $char_name .= '(' . $planet_character['perk_value'] . ')';
                $char_sel_values[] = ['name' => $char_name, 'value' => $planet_character['id']];
                $tdata['add_vips_sel'] = html::select(['name' => 'char_add'], $char_sel_values);
            }
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

    //CARGO
    $can_cargo = 0;
    if ($ship['cargo_type'] && ($ship['in_shipyard'] || $ship['in_port'] || $ship['ship_connection'] )) {
        if ($ship['ship_connection']) {
            $conn_ship = $user->getShipById($ship['ship_connection']);
            if ($conn_ship['cargo_type']) {
                $can_cargo = 1;
            }
        } else {
            $can_cargo = 1;
        }
    }

    if ($can_cargo) {
        //$L['L_CARGO_LOAD'] . ':' . $tdata['actual_cargo'] . '(' . $tdata['actual_cargo_type'] . ')'
        if ($ship['cargo'] == 0) {
            $tdata['actual_cargo'] = $L['L_EMPTY'];
        } else {
            global $cargo_types;
            $tdata['actual_cargo'] = $ship['cargo'] . '(' . $L[$cargo_types[$ship['cargo_load_type']]] . ')';
        }

        $sel_values = [];
        if ($ship['in_shipyard'] || $ship['in_port']) {
            $tdata['cargo_link'] = 'planet';
            if ($ship['cargo_load_type']) {
                if ($ship['cargo_load_type'] == 1 && !empty($planet['titanium_stored'])) {
                    $sel_values[] = ['name' => $L['L_TITANIUM'], 'value' => 1];
                } else if ($ship['cargo_load_type'] == 2 && !empty($planet['lithium_stored'])) {
                    $sel_values[] = ['name' => $L['L_LITHIUM'], 'value' => 2];
                } else if ($ship['cargo_load_type'] == 3 && !empty($planet['armatita_stored'])) {
                    $sel_values[] = ['name' => $L['L_ARMATITA'], 'value' => 3];
                }
                $tdata['cargo_sel'] = html::select(['name' => 'selected_cargo', 'readonly' => 1], $sel_values);
            } else {
                !empty($planet['titanium_stored']) ? $sel_values[] = ['name' => $L['L_TITANIUM'], 'value' => 1] : null;
                !empty($planet['lithium_stored']) ? $sel_values[] = ['name' => $L['L_LITHIUM'], 'value' => 2] : null;
                !empty($planet['armatita_stored']) ? $sel_values[] = ['name' => $L['L_ARMATITA'], 'value' => 3] : null;

                if (valid_array($sel_values)) {
                    $tdata['cargo_sel'] = html::select(['name' => 'selected_cargo', 'sel_none' => 1], $sel_values);
                } else {
                    $can_cargo = 0;
                }
            }
        } else if ($ship['ship_connection']) {
            $ship_linked = $user->getShipById($ship['ship_connection']);

            if ($ship['cargo_load_type'] || $ship_linked['cargo_load_type']) {
                $can_ship_trade = 0;
                if (($ship['cargo_load_type'] == 1 && $ship_linked['cargo_load_type'] == 1) ||
                        ($ship['cargo_load_type'] == 1 && $ship_linked['cargo_load_type'] == 0) ||
                        ($ship['cargo_load_type'] == 0 && $ship_linked['cargo_load_type'] == 1)
                ) {
                    $sel_values[] = ['name' => $L['L_TITANIUM'], 'value' => 1];
                    $can_ship_trade = 1;
                } else if (($ship['cargo_load_type'] == 2 && $ship_linked['cargo_load_type'] == 2) ||
                        ($ship['cargo_load_type'] == 2 && $ship_linked['cargo_load_type'] == 0) ||
                        ($ship['cargo_load_type'] == 0 && $ship_linked['cargo_load_type'] == 2)
                ) {
                    $can_ship_trade = 1;
                    $sel_values[] = ['name' => $L['L_LITHIUM'], 'value' => 2];
                } else if (($ship['cargo_load_type'] == 3 && $ship_linked['cargo_load_type'] == 3) ||
                        ($ship['cargo_load_type'] == 3 && $ship_linked['cargo_load_type'] == 0) ||
                        ($ship['cargo_load_type'] == 0 && $ship_linked['cargo_load_type'] == 3)
                ) {
                    $can_ship_trade = 1;
                    $sel_values[] = ['name' => $L['L_ARMATITA'], 'value' => 3];
                }
                if ($can_ship_trade) {
                    $tdata['cargo_link'] = 'ship';
                    $tdata['cargo_sel'] = html::select(['name' => 'selected_cargo', 'readonly' => 1], $sel_values);
                }
            } else {
                $can_cargo = 0;
            }
        }
        $tdata['cargo_units'] = 0;
    }

    //END CARGO
    //CREW_CARGO
    $can_crew_cargo = 0;
    if ($ship['crew_type'] && ($ship['in_shipyard'] || $ship['in_port'] || $ship['ship_connection'] )) {
        if ($ship['ship_connection']) {
            $conn_ship = $user->getShipById($ship['ship_connection']);
            if ($conn_ship['crew_type']) {
                $can_crew_cargo = 1;
            }
        } else {
            $can_crew_cargo = 1;
        }
    }

    if ($can_crew_cargo) {
        if ($ship['crew'] == 0) {
            $tdata['actual_crew_cargo'] = $L['L_EMPTY'];
        } else {
            global $cargo_types;
            $tdata['actual_crew_cargo'] = $ship['crew'];
        }

        if ($ship['in_shipyard'] || $ship['in_port']) {
            $tdata['crew_cargo_link'] = 'planet';
        } else if ($ship['ship_connection']) {
            $ship_linked = $user->getShipById($ship['ship_connection']);
            if (!$ship_linked['crew_type']) {
                $can_crew_cargo = 0;
            } else {
                $tdata['crew_cargo_link'] = 'ship';
            }
        }
        $tdata['crew_cargo_units'] = 0;
    }
    //END_CREW_CARGO

    $ship_status = '';
    if ($ship['in_port']) {
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
    //ships_specs
    $tdata['ship_specs'] = get_ship_specs($ship);
    $tdata['ship_specs'] = $frontend->getTpl('ship-specs', $tdata);

    //
    $tdata['ship_status'] = $ship_status;
    $tdata['max_speed'] = 1;
    $tdata['status_msg'] = $status_msg;
    $tdata['can_cargo'] = $can_cargo;
    $tdata['can_crew_cargo'] = $can_crew_cargo;

    !empty($post_data['scan_planet_report']) ? $tdata['scan_planet_report'] = $post_data['scan_planet_report'] : null;

    $tpl_data .= $frontend->getTpl('ships', array_merge($ship, $tdata));

    return $tpl_data;
}

function get_ship_specs(array $ship) {
    global $ship_parts, $L;
    //TODO better
    $brief = '';

    $ship['bridge_type'] ? $brief .= $L['L_BRIDGE'] . ': ' . $ship_parts['bridge'][$ship['bridge_type']]['name'] . '</br>' : null;
    $ship['turrets'] ? $brief .= $L['L_TURRETS'] . ': ' . $ship_parts['turrets'][$ship['turrets']]['name'] . '</br>' : null;
    $ship['generator'] ? $brief .= $L['L_GENERATOR'] . ': ' . $ship_parts['generator'][$ship['generator']]['name'] . '</br>' : null;
    $ship['accumulator'] ? $brief .= $L['L_ACCUMULATOR'] . ': ' . $ship_parts['accumulator'][$ship['accumulator']]['name'] . '</br>' : null;
    $ship['propeller'] ? $brief .= $L['L_PROPELLER'] . ': ' . $ship_parts['propeller'][$ship['propeller']]['name'] . '</br>' : null;
    $ship['crew_type'] ? $brief .= $L['L_CREW'] . ': ' . $ship_parts['crew'][$ship['crew_type']]['name'] . '(' . $ship['crew'] . ')' . '</br>' : null;
    $ship['radar'] ? $brief .= $L['L_RADAR'] . ': ' . $ship_parts['radar'][$ship['radar']]['name'] . '</br>' : null;
    $ship['shields'] ? $brief .= $L['L_SHIELDS'] . ': ' . $ship_parts['shields'][$ship['shields']]['name'] . '</br>' : null;
    $ship['cargo_type'] ? $brief .= $L['L_CARGO'] . ': ' . $ship_parts['cargo'][$ship['cargo_type']]['name'] . '(' . $ship['cargo'] . ')' . '</br>' : null;
    return $brief;
}

function frmt_select_user_ships(array $ship) {
    global $user;

    $values = [];

    $ships = $user->getShips();

    foreach ($ships as $_ship) {
        $values[] = [
            'name' => $_ship['name'],
            'value' => $_ship['id'],
        ];
    }

    return html::select(['name' => 'ship_id', 'form' => 1, 'onChange' => 1, 'selected' => $ship['id']], $values);
}

function ship_control_exec() {
    global $db, $user, $cfg, $L, $frontend, $ship_parts;

    $post_data = [];
    $post_data['status_msg'] = '';

    $ship_id = Filter::postInt('ship_id');
    if (empty($ship_id)) {
        return false;
    }
    $ship = $user->getShipById($ship_id);
    if (!valid_array($ship)) {
        return false;
    }

    // SHIPYARD CONNECT
    if (!empty($_POST['ship_shipyard_connect']) && $user->shipHavePilot($ship_id)) {
        //TODO ENERGY -
        $db->update('ships', ['in_shipyard' => 1, 'speed' => 0], ['id' => $ship_id]);
        $user->setShipValue($ship_id, 'in_shipyard', 1);
        $user->setShipValue($ship_id, 'speed', 0);
    }

    // SHIPYARD DISCONNECT
    if (!empty($_POST['ship_shipyard_disconnect']) && $user->shipHavePilot($ship_id)) {
        //TODO ENERGY -
        $ship_chars = $user->getShipCharacters($ship_id);
        $have_pilot = 0;
        foreach ($ship_chars as $ship_char) {
            if ($ship_char['perk'] == 1 || $ship_char['perk'] == 2) {
                $have_pilot = 1;
                break;
            }
        }
        if ($have_pilot) {
            $db->update('ships', ['in_shipyard' => 0, 'speed' => 0.1], ['id' => $ship_id]);
            $user->setShipValue($ship_id, 'in_shipyard', 0);
            $user->setShipValue($ship_id, 'speed', 0.1);
        } else {
            $post_data['status_msg'] = $L['L_ERR_NEED_PILOT'];
            return $post_data;
        }
    }

    // PORT CONNECT
    if (!empty($_POST['ship_port_connect']) && $user->shipHavePilot($ship_id)) {
        //TODO ENERGY -
        $db->update('ships', ['in_port' => 1, 'speed' => 0], ['id' => $ship_id]);
        $user->setShipValue($ship_id, 'in_port', 1);
        $user->setShipValue($ship_id, 'speed', 0);
    }

    // PORT DISCONNECT
    if (!empty($_POST['ship_port_disconnect']) && $user->shipHavePilot($ship_id)) {
        //TODO ENERGY -
        $ship_chars = $user->getShipCharacters($ship_id);
        $have_pilot = 0;
        foreach ($ship_chars as $ship_char) {
            if ($ship_char['perk'] == 1 || $ship_char['perk'] == 2) {
                $have_pilot = 1;
                break;
            }
        }
        if ($have_pilot) {
            $db->update('ships', ['in_port' => 0, 'speed' => 0.1], ['id' => $ship_id]);
            $user->setShipValue($ship_id, 'in_port', 0);
            $user->setShipValue($ship_id, 'speed', 0.1);
        } else {
            $post_data['status_msg'] = $L['L_ERR_NEED_PILOT'];
            return $post_data;
        }
    }
    //SHIP CONNECT

    if (!empty($_POST['ship_conn_submit']) && !empty(Filter::postInt('ship_conn')) && $user->shipHavePilot($ship_id)) {
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
    if (!empty($_POST['ship_disconn_submit']) && $user->shipHavePilot($ship_id) && $user->shipHavePilot($ship_id)) {
        //TODO ENERGY
        $remote_ship_to_disconn = $ship['ship_connection'];
        $db->query("UPDATE ships SET ship_connection = 0, speed = 0.1 WHERE id = $remote_ship_to_disconn OR id = {$ship['id']} LIMIT 2");
        $user->setShipValue($ship['id'], 'ship_connection', 0);
        $user->setShipValue($remote_ship_to_disconn, 'ship_connection', 0);
        $user->setShipValue($ship['id'], 'speed', 0.1);
        $user->setShipValue($remote_ship_to_disconn, 'speed', 0.1);
    }
    // SET SPEED
    if (!empty($_POST['ship_set_speed']) && null !== (Filter::postInt('setspeed')) && $user->shipHavePilot($ship_id)) {
        if ($ship['in_shipyard'] || $ship['in_port'] || $ship['ship_connection']) {
            $post_data['status_msg'] = $L['L_DISCONNECT_FIRST'];
        } else {
            $setspeed = Filter::postInt('setspeed');
            if ($setspeed != $ship['speed']) {
                $db->update('ships', ['speed' => $setspeed, 'tick_div' => $setspeed], ['id' => $ship_id]);
                $user->setShipValue($ship_id, 'speed', $setspeed);
            }
        }
    }

    // SET DESTINATION
    if (!empty($_POST['ship_set_destination']) && $user->shipHavePilot($ship_id)) {
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
            $user->setCharacterValue($char_add, 'planet_assigned', 0);
            $user->setCharacterValue($char_add, 'ship_assigned', $ship['id']);
        }
    }

    // REMOVE VIP
    if (valid_array($ship) && !empty($_POST['del_vip']) && !empty($char_del = Filter::postInt('char_del'))) {
        if (($ship_destination_id = Filter::postInt('ship_connection_id'))) {
            $ship_dest = $user->getShipById($ship_destination_id);
            $bridge_cap = $ship_parts['bridge'][$ship_dest['bridge_type']]['cap'];
            $ship_dest_chars = $user->getShipCharacters($ship_dest['id']);
            if (count($ship_dest_chars) < $bridge_cap) {
                $db->update('characters', ['ship_assigned' => $ship_dest['id']], ['id' => $char_del]);
                $user->setCharacterValue($char_del, 'ship_assigned', $ship_dest['id']);
            } else {
                $post_data['status_msg'] .= $L['L_WITHOUT_SPACE'];
            }
        } else if (!empty($planet_id = Filter::postInt('planet_id')) && ($ship['in_port'] || $ship['in_shipyard'])) {
            $db->update('characters', ['planet_assigned' => $planet_id, 'ship_assigned' => 0], ['id' => $char_del]);
            $user->setCharacterValue($char_del, 'ship_assigned', 0);
            $user->setCharacterValue($char_del, 'planet_assigned', $planet_id);
        } else {
            $db->delete('characters', ['id' => $char_del], 'LIMIT 1');
            $post_data['status_msg'] = $L['L_VIP_DEL_CRYING'];
        }
    }

    //CARGO PLANET LOAD
    if (!empty($_POST['selected_cargo']) && isset($_POST['load_units']) && !empty($_POST['cargo_units'])) {
        if ($_POST['cargo_link'] == 'planet' && !empty(($planet_id = Filter::postInt('planet_id')))) {
            if (!empty(($cargo_units = Filter::postInt('cargo_units')))) {
                $planet = $user->getPlanetById($planet_id);
                $selected_cargo = Filter::postInt('selected_cargo');
                if (!empty($selected_cargo)) {
                    ship_load_cargo_planet($ship, $planet, $cargo_units, 1);
                }
            }
        }
    }
    //CARGO PLANET UNLOAD
    if (!empty($_POST['selected_cargo']) && isset($_POST['unload_units']) && !empty($_POST['cargo_units'])) {
        if ($_POST['cargo_link'] == 'planet' && !empty(($planet_id = Filter::postInt('planet_id')))) {
            if (!empty(($cargo_units = Filter::postInt('cargo_units')))) {
                $planet = $user->getPlanetById($planet_id);
                $selected_cargo = Filter::postInt('selected_cargo');
                if (!empty($selected_cargo)) {
                    ship_unload_cargo_planet($ship, $planet, $cargo_units, 1);
                }
            }
        }
    }
    //CARGO SHIP LOAD
    if (!empty($_POST['selected_cargo']) && isset($_POST['load_units']) && !empty($_POST['cargo_units'])) {
        if ($_POST['cargo_link'] == 'ship') {
            if (!empty(($cargo_units = Filter::postInt('cargo_units')))) {
                $selected_cargo = Filter::postInt('selected_cargo');
                if (!empty($selected_cargo)) {
                    ship_load_cargo_ship($ship, $cargo_units, 1);
                }
            }
        }
    }
    //CARGO SHIP UNLOAD
    if (!empty($_POST['selected_cargo']) && isset($_POST['unload_units']) && !empty($_POST['cargo_units'])) {
        if ($_POST['cargo_link'] == 'ship') {
            if (!empty(($cargo_units = Filter::postInt('cargo_units')))) {
                $selected_cargo = Filter::postInt('selected_cargo');
                if (!empty($selected_cargo)) {
                    ship_unload_cargo_ship($ship, $cargo_units, 1);
                }
            }
        }
    }

    //CARGO CREW LOAD
    if (isset($_POST['load_crew_units']) && !empty($crew_cargo_units = Filter::postInt('crew_cargo_units'))) {
        if ($_POST['crew_cargo_link'] == 'planet' && !empty(($planet_id = Filter::postInt('planet_id')))) {
            if (!empty($crew_cargo_units)) {
                $planet = $user->getPlanetById($planet_id);
                ship_load_crew_planet($ship, $planet, $crew_cargo_units);
            }
        }
    }

    //CARGO CREW UNLOAD
    if (isset($_POST['unload_crew_units']) && !empty($crew_cargo_units = Filter::postInt('crew_cargo_units'))) {
        if ($_POST['crew_cargo_link'] == 'planet' && !empty(($planet_id = Filter::postInt('planet_id')))) {
            if (!empty($crew_cargo_units)) {
                $planet = $user->getPlanetById($planet_id);
                ship_unload_crew_planet($ship, $planet, $crew_cargo_units);
            }
        }
    }

    //SHIP CARGO CREW LOAD
    if (isset($_POST['load_crew_units']) && !empty($_POST['crew_cargo_units']) && $_POST['crew_cargo_link'] == 'ship') {
        if (!empty(($crew_cargo_units = Filter::postInt('crew_cargo_units')))) {
            ship_load_crew_ship($ship, $crew_cargo_units);
        }
    }
    //SHIP CARGO SHIP UNLOAD
    if (isset($_POST['unload_crew_units']) && !empty($_POST['crew_cargo_units']) && $_POST['crew_cargo_link'] == 'ship') {
        if (!empty(($cargo_units = Filter::postInt('crew_cargo_units')))) {
            ship_unload_crew_ship($ship, $crew_cargo_units);
        }
    }

    //Scan planet
    if (!empty($_POST['scan_planet']) &&
            !empty(($alien_planet_id = Filter::postInt('alien_planet_id')))
    ) {
        $alien_planet = Planets::getPlanetById($alien_planet_id);
        if (valid_array($alien_planet)) {
            $post_data['scan_planet_report'] = $frontend->getTpl('scan_planet_report', $alien_planet);
        }
    }
    //Build Port
    if (!empty($_POST['build_port']) &&
            !empty(($alien_planet_id = Filter::postInt('alien_planet_id')))
    ) {
        $alien_planet = Planets::getPlanetById($alien_planet_id);
        if (valid_array($alien_planet) && !empty($ship['id'])) {
            $ship_chars = $user->getShipCharacters($ship['id']);
            $have_engineer = 0;
            foreach ($ship_chars as $ship_char) {
                if ($ship_char['perk'] == 7) {
                    $have_engineer = $ship_char['id'];
                    break;
                }
            }
            if (empty($have_engineer)) {
                $post_data['status_msg'] .= $L['L_NEED_ENGINEER'];
                return $post_data;
            }
            if ($ship['cargo_type'] != 1 || $ship['cargo'] < $cfg['build_port_cost']) {
                $post_data['status_msg'] .= $L['L_NEED_TITANIUM'] . '(' . $cfg['build_port_cost'] . ')';
                return $post_data;
            }
            if ($ship['crew'] < $cfg['build_port_workers']) {
                $post_data['status_msg'] .= $L['L_NEED_WORKERS'] . '(' . $cfg['build_port_workers'] . ')';
                return $post_data;
            }
        }
        $ship_set['cargo'] = $ship['cargo'] - $cfg['build_port_cost'];
        $ship_set['crew'] = $ship['crew'] - $cfg['build_port_workers'];
        $ship_set['speed'] = 0;
        //TODO ENERGY IF NOT STOPPED
        if ($ship_set['cargo'] == 0) {
            $ship_set['cargo_load_type'] = 0;
        }
        //Engineer move to planet
        $char_set['ship_assigned'] = 0;
        $char_set['planet_assigned'] = $alien_planet['id'];
        $char_set['job'] = 1;
        $alien_planet_set['port_engineer'] = $have_engineer;
        //Planet set
        $alien_planet_set['uid'] = $user->id();
        $alien_planet_set['port_workers'] = $cfg['build_port_workers'];
        $alien_planet_set['port_built'] = 1;

        $db->update('characters', $char_set, ['id' => $have_engineer], 'LIMIT 1');
        $db->update('ships', $ship_set, ['id' => $ship['id']], 'LIMIT 1');
        $db->update('planets', $alien_planet_set, ['id' => $alien_planet['id']], 'LIMIT 1');
        $db->insert('build', ['id_dest' => $alien_planet['id'], 'type' => 'port', 'ticks' => $cfg['build_port_ticks']]);
    }
    return $post_data;
}

function ship_load_cargo_planet(array $ship, array $planet, int $cargo_units, int $type) {
    global $db, $user, $ship_parts;

    $max_ship_cap = $ship_parts['cargo'][$ship['cargo_type']]['cap'];

    if (($cargo_units + $ship['cargo']) > $max_ship_cap) {
        $cargo_units = $max_ship_cap - $ship['cargo'];
    }


    if ($type == 1) {
        $item_stored = 'titanium_stored';
    } else if ($type == 2) {
        $item_stored = 'lithium_stored';
    } else if ($type == 3) {
        $item_stored = 'armatita_stored';
    }

    if ($cargo_units > $planet[$item_stored]) {
        $cargo_units = $planet[$item_stored];
    }
    if ($cargo_units == 0) {
        return false;
    }

    $planet_set[$item_stored] = $planet[$item_stored] - $cargo_units;
    $ship_set['cargo'] = $cargo_units + $ship['cargo'];
    $ship_set['cargo_load_type'] = $type;
    if ($type == 3) {
        if ($ship['cargo'] == 0) {
            $new_purity = $planet['armatita_stored_purity'];
        } else {
            $new_purity = calc_new_purity($ship['cargo_purity'], $cargo_units, $planet['armatita_stored_purity'], $planet['armatita_stored']);
        }
        $ship_set['cargo_purity'] = $new_purity;
        $user->setShipValue($ship['id'], 'cargo_purity', $new_purity);
        if ($planet_set[$item_stored] == 0) {
            $planet_set['armatita_stored_purity'] = 0;
        }
    }

    $db->update('planets', $planet_set, ['id' => $planet['id']], 'LIMIT 1');
    $user->setPlanetValue($planet['id'], $item_stored, $planet_set[$item_stored]);
    $db->update('ships', $ship_set, ['id' => $ship['id']], 'LIMIT 1');
    $user->setShipValue($ship['id'], 'cargo', $ship_set['cargo']);
    $user->setShipValue($ship['id'], 'cargo_load_type', $type);
}

function ship_unload_cargo_planet(array $ship, array $planet, int $cargo_units, int $type) {
    global $db, $user;

    if ($type == 1) {
        $item_stored = 'titanium_stored';
    } else if ($type == 2) {
        $item_stored = 'lithium_stored';
    } else if ($type == 3) {
        $item_stored = 'armatita_stored';
    }

    if ($cargo_units > $ship['cargo']) {
        $cargo_units = $ship['cargo'];
    }

    if ($cargo_units == 0) {
        return false;
    }
    $planet_set[$item_stored] = $planet[$item_stored] + $cargo_units;
    $ship_set['cargo'] = $ship['cargo'] - $cargo_units;

    if ($type == 3) {
        if ($planet['armatita_stored'] == 0) {
            $new_purity = $ship['cargo_purity'];
        } else {
            $new_purity = calc_new_purity($planet['armatita_stored_purity'], $planet['armatita_stored'], $ship['cargo_purity'], $cargo_units);
        }
        $planet_set['armatita_stored_purity'] = $ship['cargo_purity'];
        $user->setPlanetValue($planet['id'], 'armatita_stored_purity', $ship['cargo_purity']);
    }

    if ($ship_set['cargo'] == 0) {
        $ship_set['cargo_load_type'] = 0;
        $user->setShipValue($ship['id'], 'cargo_load_type', $ship_set['cargo_load_type']);
        if ($type == 3) {
            $ship_set['cargo_purity'] = 0;
            $user->setShipValue($ship['id'], 'cargo_purity', 0);
        }
    }

    $db->update('planets', $planet_set, ['id' => $planet['id']], 'LIMIT 1');
    $user->setPlanetValue($planet['id'], $item_stored, $planet_set[$item_stored]);
    $db->update('ships', $ship_set, ['id' => $ship['id']], 'LIMIT 1');
    $user->setShipValue($ship['id'], 'cargo', $ship_set['cargo']);
}

function ship_load_cargo_ship(array $ship, int $cargo_units, int $type) {
    global $db, $user, $ship_parts;

    $max_ship_cap = $ship_parts['cargo'][$ship['cargo_type']]['cap'];

    if (($cargo_units + $ship['cargo']) > $max_ship_cap) {
        $cargo_units = $max_ship_cap - $ship['cargo'];
    }


    if ($type == 1) {
        $item_stored = 'titanium_stored';
    } else if ($type == 2) {
        $item_stored = 'lithium_stored';
    } else if ($type == 3) {
        $item_stored = 'armatita_stored';
    }

    $ship_connected = $user->getShipById($ship['ship_connection']);

    if (!$ship_connected || $cargo_units == 0) {
        return false;
    }

    if ($cargo_units > $ship_connected['cargo']) {
        $cargo_units = $ship_connected['cargo'];
    }

    $ship_connected_set['cargo'] = $ship_connected['cargo'] - $cargo_units;
    $ship_set['cargo'] = $cargo_units + $ship['cargo'];
    $ship_set['cargo_load_type'] = $type;

    if ($ship_connected_set['cargo'] == 0) {
        $ship_connected_set['cargo_load_type'] = 0;
    }
    if ($type == 3) {
        if ($ship['cargo'] == 0) {
            $new_purity = $ship_connected['cargo_purity'];
        } else {
            $new_purity = calc_new_purity($ship['cargo_purity'], $cargo_units, $ship_connected['cargo_purity'], $ship_connected['cargo']);
        }
        $ship_set['cargo_purity'] = $new_purity;
        $user->setShipValue($ship['id'], 'cargo_purity', $new_purity);
        if ($ship_connected_set['cargo'] == 0) {
            $ship_connected_set['cargo_purity'] = 0;
        }
    }

    $db->update('ships', $ship_connected_set, ['id' => $ship_connected['id']], 'LIMIT 1');
    $user->setShipValue($ship_connected['id'], 'cargo', $ship_connected_set['cargo']);
    $db->update('ships', $ship_set, ['id' => $ship['id']], 'LIMIT 1');
    $user->setShipValue($ship['id'], 'cargo', $ship_set['cargo']);
    $user->setShipValue($ship['id'], 'cargo_load_type', $type);
}

function ship_unload_cargo_ship(array $ship, int $cargo_units, int $type) {
    global $db, $user, $ship_parts;

    if ($type == 1) {
        $item_stored = 'titanium_stored';
    } else if ($type == 2) {
        $item_stored = 'lithium_stored';
    } else if ($type == 3) {
        $item_stored = 'armatita_stored';
    }

    if ($cargo_units > $ship['cargo']) {
        $cargo_units = $ship['cargo'];
    }

    $ship_connected = $user->getShipById($ship['ship_connection']);

    $max_ship_connected_cap = $ship_parts['cargo'][$ship_connected['cargo_type']]['cap'];

    if (($cargo_units + $ship_connected['cargo']) > $max_ship_connected_cap) {
        $cargo_units = $max_ship_connected_cap - $ship_connected['cargo'];
    }


    if (!$ship_connected || $cargo_units == 0) {
        return false;
    }

    $ship_connected_set['cargo'] = $ship_connected['cargo'] + $cargo_units;
    $ship_set['cargo'] = $ship['cargo'] - $cargo_units;
    $ship_connected_set['cargo_load_type'] = $type;

    if ($ship_set['cargo'] == 0) {
        $ship_set['cargo_load_type'] = 0;
    }

    if ($type == 3) {
        if ($ship_connected['cargo'] == 0) {
            $new_purity = $ship['cargo_purity'];
        } else {
            $new_purity = calc_new_purity($ship_connected['cargo_purity'], $ship_connected['cargo'], $ship['cargo_purity'], $cargo_units);
        }
        $ship_connected_set['cargo_purity'] = $new_purity;

        $user->setShipValue($ship_connected['id'], 'cargo_purity', $new_purity);
    }

    if ($ship_set['cargo'] == 0) {
        $ship_set['cargo_load_type'] = 0;
        $user->setShipValue($ship['id'], 'cargo_load_type', $ship_set['cargo_load_type']);
        if ($type == 3) {
            $ship_set['cargo_purity'] = 0;
            $user->setShipValue($ship['id'], 'cargo_purity', 0);
        }
    }

    $db->update('ships', $ship_connected_set, ['id' => $ship_connected['id']], 'LIMIT 1');
    $user->setShipValue($ship_connected['id'], 'cargo', $ship_connected_set['cargo']);
    $db->update('ships', $ship_set, ['id' => $ship['id']], 'LIMIT 1');
    $user->setShipValue($ship['id'], 'cargo', $ship_set['cargo']);
}

function ship_load_crew_planet(array $ship, array $planet, int $crew_cargo_units) {
    global $db, $user, $ship_parts;

    $max_ship_crew = $ship_parts['crew'][$ship['crew_type']]['cap'];

    if (($crew_cargo_units + $ship['crew']) > $max_ship_crew) {
        $crew_cargo_units = $max_ship_crew - $ship['crew'];
    }

    if ($crew_cargo_units > $planet['workers']) {
        $crew_cargo_units = $planet['workers'];
    }
    if ($crew_cargo_units == 0) {
        return false;
    }

    $planet_set['workers'] = $planet['workers'] - $crew_cargo_units;
    $ship_set['crew'] = $crew_cargo_units + $ship['crew'];

    $db->update('planets', $planet_set, ['id' => $planet['id']], 'LIMIT 1');
    $user->setPlanetValue($planet['id'], 'workers', $planet_set['workers']);
    $db->update('ships', $ship_set, ['id' => $ship['id']], 'LIMIT 1');
    $user->setShipValue($ship['id'], 'crew', $ship_set['crew']);
}

function ship_unload_crew_planet(array $ship, array $planet, int $crew_cargo_units) {
    global $db, $user;

    if ($crew_cargo_units == 0) {
        return false;
    }
    $planet_set['workers'] = $planet['workers'] + $crew_cargo_units;
    $ship_set['crew'] = $ship['crew'] - $crew_cargo_units;

    $db->update('planets', $planet_set, ['id' => $planet['id']], 'LIMIT 1');
    $user->setPlanetValue($planet['id'], 'workers', $planet_set['workers']);
    $db->update('ships', $ship_set, ['id' => $ship['id']], 'LIMIT 1');
    $user->setShipValue($ship['id'], 'crew', $ship_set['crew']);
}

function ship_load_crew_ship(array $ship, int $crew_cargo_units) {
    global $db, $user, $ship_parts;

    $max_ship_crew = $ship_parts['crew'][$ship['crew_type']]['cap'];

    if (($crew_cargo_units + $ship['crew']) > $max_ship_crew) {
        $crew_cargo_units = $max_ship_crew - $ship['crew'];
    }

    $ship_connected = $user->getShipById($ship['ship_connection']);

    if (!$ship_connected || $crew_cargo_units == 0) {
        return false;
    }

    if ($crew_cargo_units > $ship_connected['crew']) {
        $crew_cargo_units = $ship_connected['crew'];
    }

    $ship_connected_set['crew'] = $ship_connected['crew'] - $crew_cargo_units;
    $ship_set['crew'] = $crew_cargo_units + $ship['crew'];

    $db->update('ships', $ship_connected_set, ['id' => $ship_connected['id']], 'LIMIT 1');
    $user->setShipValue($ship_connected['id'], 'crew', $ship_connected_set['crew']);
    $db->update('ships', $ship_set, ['id' => $ship['id']], 'LIMIT 1');
    $user->setShipValue($ship['id'], 'crew', $ship_set['crew']);
}

function ship_unload_crew_ship(array $ship, int $crew_cargo_units) {
    global $db, $user, $ship_parts;

    if ($crew_cargo_units > $ship['crew']) {
        $crew_cargo_units = $ship['crew'];
    }

    $ship_connected = $user->getShipById($ship['ship_connection']);

    $max_ship_connected_crew = $ship_parts['crew'][$ship_connected['crew_type']]['cap'];

    if (($crew_cargo_units + $ship_connected['crew']) > $max_ship_connected_crew) {
        $crew_cargo_units = $max_ship_connected_crew - $ship_connected['crew'];
    }

    if (!$ship_connected || $crew_cargo_units == 0) {
        return false;
    }

    $ship_connected_set['crew'] = $ship_connected['crew'] + $crew_cargo_units;
    $ship_set['crew'] = $ship['crew'] - $crew_cargo_units;

    $db->update('ships', $ship_connected_set, ['id' => $ship_connected['id']], 'LIMIT 1');
    $user->setShipValue($ship_connected['id'], 'crew', $ship_connected_set['crew']);
    $db->update('ships', $ship_set, ['id' => $ship['id']], 'LIMIT 1');
    $user->setShipValue($ship['id'], 'crew', $ship_set['crew']);
}
