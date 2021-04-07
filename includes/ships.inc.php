<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function show_control_ships($ship, $status_msg) {
    global $L, $user, $frontend, $perks, $ship_parts;

    $tpl_data = '';
    $tdata = [];

    $tdata['sel_ship'] = frmt_select_user_ships($ship);

    $tdata['energy_max'] = $ship_parts['accumulator'][$ship['accumulator']]['cap'];

    $planet = $user->checkInUserPlanet($ship['x'], $ship['y'], $ship['z']);
    if ($planet) {
        $tdata['planet_id'] = $planet['id'];
        $ship['own_planet_sector'] = 1;
        $tdata['have_shipyard'] = 1;
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
    //ships_specs
    $tdata['ship_specs'] = get_ship_specs($ship);
    $tdata['ship_specs'] = $frontend->getTpl('ship-specs', $tdata);

    //
    $tdata['ship_status'] = $ship_status;
    $tdata['max_speed'] = 1;
    $tdata['status_msg'] = $status_msg;
    $tdata['can_cargo'] = $can_cargo;

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
    $ship['crew_type'] ? $brief .= $L['L_CREW'] . ': ' . $ship_parts['crew'][$ship['crew_type']]['name'] . '</br>' : null;
    $ship['radar'] ? $brief .= $L['L_RADAR'] . ': ' . $ship_parts['radar'][$ship['radar']]['name'] . '</br>' : null;
    $ship['shields'] ? $brief .= $L['L_SHIELDS'] . ': ' . $ship_parts['shields'][$ship['shields']]['name'] . '</br>' : null;
    $ship['cargo_type'] ? $brief .= $L['L_CARGO'] . ': ' . $ship_parts['cargo'][$ship['cargo_type']]['name'] . '</br>' : null;
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

    return html::select(['name' => 'ship_id', 'onChange' => 1, 'selected' => $ship['id']], $values);
}

function ship_control_exec() {
    global $db, $user, $L, $ship_parts;

    $status_msg = '';

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
            return $L['L_ERR_NEED_PILOT'];
        }
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
        //TODO
        echo "DEL";
    }

    //var_dump($_POST);
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
}

function ship_load_cargo_planet($ship, $planet, $cargo_units, $type) {
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

function ship_unload_cargo_planet($ship, $planet, $cargo_units, $type) {
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

function ship_load_cargo_ship($ship, $cargo_units, $type) {
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

function ship_unload_cargo_ship($ship, $cargo_units, $type) {
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
