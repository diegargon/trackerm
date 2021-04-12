<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
function show_user_planets() {
    global $user, $html;

    $values = [];

    $planet_sel = Filter::postInt('planet_id');
    $planets = $user->getPlanets();
    $html = '';

    foreach ($planets as $_planet) {
        $values[] = [
            'name' => $_planet['name'],
            'value' => $_planet['id'],
        ];
    }
    !empty($planet_sel) ? $selected = $planet_sel : $selected = '';
    $tpl_data = html::select(['name' => 'planet_id', 'selected' => $selected, 'form' => 1, 'onChange' => 1], $values);

    if (!empty($planet_sel)) {
        foreach ($planets as $_planet) {
            if ($_planet['id'] == $planet_sel) {
                $planet = $_planet;
                break;
            }
        }
    } else {
        $planet = $planets[0];
    }

    $tpl_data .= showPlanetOpt($planet);

    return $tpl_data;
}

function showPlanetOpt(array $planet) {
    global $L, $frontend, $user;

    $tdata = [];
    $post_data = [];

    $tdata['planet_head_data'] = html::head(['h' => 1], $L['L_PLANETS']);

    if (!empty($_POST)) {
        if (isset($_POST['mining_submit'])) {
            $post_data = planet_post_mining($planet);
        } else if (isset($_POST['char_port_sel']) || isseT($_POST['char_shipyard_sel'])) {
            $post_data = planet_post_engineer($planet);
        } else if (isset($_POST['build_shipyard_submit']) && !empty($_POST['build_shipyard_sel'])) {
            $post_data = planet_build_shipyard($planet);
        }
        //RELOAD;
        $planet = $user->getPlanetById($planet['id']);
    }

    $tdata['planet_brief_data'] = planet_brief($planet);

    if ($planet['have_port']) {
        $port_engineer = '';
        $shipyard_engineer = '';
        //PORT ENGINEER
        $port_engineer .= html::div(['class' => 'inline_block'], $L['L_PORT_ENGINEER']);
        $perk_chars = $user->getCharactersByPerk(7);

        $values = [];
        foreach ($perk_chars as $perk_char) {
            if ($perk_char['planet_assigned'] == $planet['id']) {
                if ($perk_char['job'] == 0 || ($perk_char['job'] == 1 && $perk_char['id'] == $planet['port_engineer'])) {
                    $values[] = [
                        'name' => $perk_char['name'] . ' ( ' . $perk_char['perk_value'] . ' )',
                        'value' => $perk_char['id'],
                    ];
                }
            }
        }
        if (valid_array($values)) {
            $port_engineer .= html::input(['name' => 'planet_id', 'value' => $planet['id'], 'type' => 'hidden']);
            $port_engineer .= html::select(['name' => 'char_port_sel', 'selected' => $planet['port_engineer'], 'onChange' => 1], $values);
        }

        //SHIPYARD ENGINEER
        if ($planet['have_shipyard'] && valid_array($perk_chars)) {
            $shipyard_engineer .= html::div(['class' => 'inline_block'], $L['L_SHIPYARD_ENGINEER']);
            $values = [];
            foreach ($perk_chars as $perk_char) {
                if ($perk_char['planet_assigned'] == $planet['id']) {
                    if ($perk_char['job'] == 0 || ($perk_char['job'] == 1 && $perk_char['id'] == $planet['shipyard_engineer'])) {
                        $values[] = [
                            'name' => $perk_char['name'] . ' ( ' . $perk_char['perk_value'] . ' )',
                            'value' => $perk_char['id'],
                        ];
                    }
                }
            }
            if (valid_array($values)) {
                $shipyard_engineer .= html::input(['name' => 'planet_id', 'value' => $planet['id'], 'type' => 'hidden']);
                $shipyard_engineer .= html::select(['name' => 'char_shipyard_sel', 'selected' => $planet['shipyard_engineer'], 'onChange' => 1], $values);
            }
        }

        $tdata['planet_engineers_data'] = html::form(['name' => 'engineer_char_sel', 'class' => 'inline_block', 'method' => 'post'], $port_engineer . $shipyard_engineer);
        //Build Shipyard
        if (!$planet['have_shipyard'] && empty($planet['shipyard_built']) && count($perk_chars) > 1) {
            $build_shipyard = html::input(['name' => 'planet_id', 'value' => $planet['id'], 'type' => 'hidden']);
            $build_shipyard .= html::input(['name' => 'build_shipyard_submit', 'value' => $L['L_BUILD_SHIPYARD'], 'type' => 'submit']);
            $values = [];
            foreach ($perk_chars as $perk_char) {
                if ($perk_char['planet_assigned'] == $planet['id']) {
                    if ($perk_char['job'] == 0) {
                        $values[] = [
                            'name' => $perk_char['name'] . ' ( ' . $perk_char['perk_value'] . ' )',
                            'value' => $perk_char['id'],
                        ];
                    }
                }
            }
            $build_shipyard .= html::select(['name' => 'build_shipyard_sel'], $values);
            $tdata['build_shipyard'] = html::form(['name' => 'build_shipyard_form', 'class' => '', 'method' => 'post'], $build_shipyard);
        }

        //MINING
        $tdata['mining_data'] = planet_show_mining($planet);
        //CHARACTERS
        $tdata['vips_data'] = planet_show_characters($planet);
        //SHIPS
        $tdata['ships_data'] = planet_show_ships($planet);
    }

    if ($planet['have_shipyard'] && !$planet['shipyard_built']) {
        $tdata['planet_shipyard_data'] = show_port();
    }
    !empty($post_data['status_msg']) ? $tdata['status_msg'] = $post_data['status_msg'] : null;
    return $frontend->getTpl('planets', $tdata);
}

function planet_build_shipyard($planet) {
    global $db, $cfg, $L, $user;

    $post_data['status_msg'] = '';
    $build_engineer = Filter::postInt('build_shipyard_sel');

    if ($planet['workers'] < $cfg['build_shipyard_workers']) {
        $post_data['status_msg'] = $L['L_NEED_WORKERS'] . '(' . $cfg['build_shipyard_workers'] . ') <br/>';
    }
    if ($planet['titanium_stored'] < $cfg['build_shipyard_cost']) {
        $post_data['status_msg'] = $L['L_NEED_TITANIUM'] . '(' . $cfg['build_shipyard_cost'] . ')';
    }
    if (!empty($post_data['status_msg'])) {
        return $post_data;
    }

    if (!empty($build_engineer)) {
        $char_set['ship_assigned'] = 0;
        $char_set['planet_assigned'] = $planet['id'];
        $char_set['job'] = 1;
        $planet_set['shipyard_engineer'] = $build_engineer;
        $planet_set['shipyard_workers'] = $cfg['build_port_workers'];
        $planet_set['workers'] = $planet['workers'] - $cfg['build_port_workers'];
        $planet_set['shipyard_built'] = 1;

        $db->update('characters', $char_set, ['id' => $build_engineer], 'LIMIT 1');
        $db->update('planets', $planet_set, ['id' => $planet['id']], 'LIMIT 1');
        $db->insert('build', ['id_dest' => $planet['id'], 'type' => 'shipyard', 'ticks' => $cfg['build_shipyard_ticks']]);
        $user->setCharacterValue($build_engineer, 'job', 1);
        $user->setCharacterValue($build_engineer, 'planet_assigned', $planet['id']);
        $user->setCharacterValue($build_engineer, 'ship_assigned', 0);
        $user->setPlanetValue($planet['id'], 'shipyard_engineer', $build_engineer);
        $user->setPlanetValue($planet['id'], 'shipyard_workers', $planet_set['shipyard_workers']);
        $user->setPlanetValue($planet['id'], 'workers', $planet_set['workers']);
        $user->setPlanetValue($planet['id'], 'shipyard_built', 1);

        $post_data['status_msg'] = $L['L_ORDER_BUILD_SHIPYARD'];
    }

    return $post_data;
}

function planet_post_engineer($planet) {
    global $db, $user;

    $planet_set = [];
    $post_data = [];

    $port_engineer = Filter::postInt('char_port_sel');
    $shipyard_engineer = Filter::postInt('char_shipyard_sel');


    if ($port_engineer && $port_engineer != $planet['port_engineer']) {
        $planet_set['port_engineer'] = $port_engineer;
        $db->update('characters', ['job' => 1], ['id' => $port_engineer]);
        $db->update('characters', ['job' => 0], ['id' => $planet['port_engineer']]);
        $user->setCharacterValue($port_engineer, 'job', 1);
        $user->setCharacterValue($planet['port_engineer'], 'job', 0);
        $user->setPlanetValue($planet['id'], 'port_engineer', $port_engineer);
    }
    if ($shipyard_engineer && $shipyard_engineer != $planet['shipyard_engineer']) {
        $planet_set['shipyard_engineer'] = $shipyard_engineer;
        $db->update('characters', ['job' => 1], ['id' => $shipyard_engineer]);
        $db->update('characters', ['job' => 0], ['id' => $planet['shipyard_engineer']]);
        $user->setCharacterValue($shipyard_engineer, 'job', 1);
        $user->setCharacterValue($planet['shipyard_engineer'], 'job', 0);
        $user->setPlanetValue($planet['id'], 'shipyard_engineer', $port_engineer);
    }
    if (valid_array($planet_set)) {
        $db->update('planets', $planet_set, ['id' => $planet['id']]);
    }

    return $post_data;
}

function planet_show_ships($planet) {
    global $user;

    $in_planet_ships = $user->getPlanetShips($planet['id']);

    $ships_select = [];
    foreach ($in_planet_ships as $in_planet_ship) {
        $ships_select[] = ['name' => $in_planet_ship['name'], 'value' => $in_planet_ship['id']];
    }
    $ships_data = html::select(['name' => 'ships_select', 'size' => 10], $ships_select);

    return $ships_data;
}

function planet_show_characters($planet) {
    global $user, $perks, $L;

    $vips = $user->getPlanetCharacters($planet['id']);

    $vips_select = [];
    foreach ($vips as $vip) {
        $vips_data = $vip['name'];
        $vips_data .= ' ' . $L[$perks[$vip['perk']]];
        $vips_data .= '(' . $vip['perk_value'] . ')';
        $vips_select[] = ['name' => $vips_data, 'value' => $vip['id']];
    }

    return html::select(['name' => 'vip_select', 'size' => 10], $vips_select);
}

function planet_show_mining($planet) {
    global $frontend;

    return $frontend->getTpl('planet-mining', $planet);
}

function planet_post_mining($planet) {
    global $db, $user;

    $post_data = [];

    $titanium_assign = Filter::postInt('titanium_assign');
    $lithium_assign = Filter::postInt('lithium_assign');
    $armatita_assign = Filter::postInt('armatita_assign');

    if (isset($titanium_assign) && $titanium_assign != $planet['titanium_workers']) {
        $free_workers = $planet['workers'] + $planet['titanium_workers'];
        if ($titanium_assign <= $free_workers) {
            $planet_set['titanium_workers'] = $titanium_assign;
            $user->setPlanetValue($planet['id'], 'titanium_workers', $titanium_assign);
            $planet_set['workers'] = $free_workers - $titanium_assign;
            $user->setPlanetValue($planet['id'], 'workers', $planet_set['workers']);
        }
    }
    if (isset($lithium_assign) && $lithium_assign != $planet['lithium_workers']) {
        $free_workers = $planet['workers'] + $planet['lithium_workers'];
        if ($lithium_assign <= $free_workers) {
            $planet_set['lithium_workers'] = $lithium_assign;
            $user->setPlanetValue($planet['id'], 'lithium_workers', $lithium_assign);
            $planet_set['workers'] = $free_workers - $lithium_assign;
            $user->setPlanetValue($planet['id'], 'workers', $planet_set['workers']);
        }
    }

    if (isset($armatita_assign) && $armatita_assign != $planet['armatita_workers']) {
        $free_workers = $planet['workers'] + $planet['armatita_workers'];
        if ($armatita_assign <= $free_workers) {
            $planet_set['armatita_workers'] = $armatita_assign;
            $user->setPlanetValue($planet['id'], 'armatita_workers', $armatita_assign);
            $planet_set['workers'] = $free_workers - $armatita_assign;
            $user->setPlanetValue($planet['id'], 'workers', $planet_set['workers']);
        }
    }


    if (!empty($planet_set)) {
        $db->update('planets', $planet_set, ['id' => $planet['id']], 'LIMIT 1');
    }

    return $post_data;
}

function planet_brief(array $planet) {
    global $L, $db;

    $tpl_data = html::head(['h' => 2], $L['L_SUMMARY']);
    $tpl_data .= html::p([], $L['L_PLANET']);

    $info = $L['L_LOCATION'] . ": {$planet['x']}:{$planet['y']}:{$planet['z']}";
    $info .= html::br([]) . $L['L_WORKERS'] . ': ' . $planet['workers'];
    $info .= html::br([]) . $L['L_DEVELOP_LEVEL'] . ' ' . $planet['develop_level'];
    $slots = $L['L_PORT_SLOTS'] . ' ' . $planet['port_slots'];
    $slots .= ' ' . $L['L_SHIPYARD_SLOTS'] . ' ' . $planet['shipyard_slots'];
    $info .= html::br([]) . $slots;
    $info .= html::br([]);
    if (!empty($planet['titanium'])) {
        $info .= $L['L_TITANIUM'] . ': ' . $planet['titanium'] . ' / ' . $L['L_TITANIUM_STORED'] . ': ' . $planet['titanium_stored'] . ' / ' . $L['L_TITANIUM_WORKERS'] . ': ' . $planet['titanium_workers'];
    }
    if (!empty($planet['lithium'])) {
        $info .= '<br/>' . $L['L_LITHIUM'] . ': ' . $planet['lithium'] . ' / ' . $L['L_LITHIUM_STORED'] . ': ' . $planet['lithium_stored'] . ' / ' . $L['L_LITHIUM_WORKERS'] . ': ' . $planet['lithium_workers'];
    }
    if (!empty($planet['armatita'])) {
        $info .= '<br/>' . $L['L_ARMATITA'] . ': ' . $planet['armatita'] . ' (' . $planet['armatita_purity'] . '%) / ' . $L['L_ARMATITA_STORED'] . ': ' . $planet['armatita_stored'] . ' (' . $planet['armatita_stored_purity'] . '%) / ' . $L['L_ARMATITA_WORKERS'] . ': ' . $planet['armatita_workers'];
    }

    if (!empty($planet['port_built'])) {
        $result = $db->select('build', 'ticks', ['id_dest' => ['value' => $planet['id']]], 'LIMIT 1');
        $build_res = $db->fetch($result);
        if (valid_array($build_res)) {
            $info .= '<br/>' . $L['L_PORT_BUILT'] . ' ' . $L['L_ETA'] . ' ' . $build_res['ticks'] . ' ' . $L['L_TICKS'];
        }
    }

    $tpl_data .= html::p([], $info);

    return $tpl_data;
}
