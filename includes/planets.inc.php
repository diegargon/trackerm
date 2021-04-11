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
    $conf = [];

    $planet_sel = Filter::postInt('planet_id');
    $planets = $user->getPlanets();
    $html = '';

    $conf['name'] = 'planet_id';
    $conf['onChange'] = 1;

    !empty($planet_sel) ? $conf['selected'] = $planet_sel : null;

    foreach ($planets as $_planet) {
        $values[] = [
            'name' => $_planet['name'],
            'value' => $_planet['id'],
        ];
    }

    $tpl_data = html::select($conf, $values);

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

    if (!empty($_POST)) {
        if (isset($_POST['mining_submit'])) {
            $planet = planet_post_mining($planet);
        }
    }

    $tpl_data = html::head(['h' => 1], $L['L_PLANETS']);
    $tpl_data .= planet_brief($planet);

    if ($planet['have_port']) {
        $port_engineer = html::div([], $L['L_ENGINEER']);
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

        $port_engineer .= html::select(['name' => 'char_perk_sel', 'selected' => $planet['port_engineer'], 'onChange' => 1], $values);

        $tpl_data .= html::form(['name' => 'port_char_sel', 'method' => 'post'], $port_engineer);

        //MINING
        $tpl_data .= planet_show_mining($planet);
        //CHARACTERS
        $tdata['vips_data'] = planet_show_characters($planet);
        //SHIPS
        $tdata['ships_data'] = planet_show_ships($planet);
    }

    $tpl_data .= $frontend->getTpl('planets', $tdata);

    return $tpl_data;
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
    global $db;

    $titanium_assign = Filter::postInt('titanium_assign');
    $lithium_assign = Filter::postInt('lithium_assign');
    $armatita_assign = Filter::postInt('armatita_assign');

    if (isset($titanium_assign) && $titanium_assign != $planet['titanium_workers']) {
        $free_workers = $planet['workers'] + $planet['titanium_workers'];
        if ($titanium_assign <= $free_workers) {
            $planet_set['titanium_workers'] = $titanium_assign;
            $planet['titanium_workers'] = $titanium_assign;
            $planet_set['workers'] = $free_workers - $titanium_assign;
            $planet['workers'] = $planet_set['workers'];
        }
    }
    if (isset($lithium_assign) && $lithium_assign != $planet['lithium_workers']) {
        $free_workers = $planet['workers'] + $planet['lithium_workers'];
        if ($lithium_assign <= $free_workers) {
            $planet_set['lithium_workers'] = $lithium_assign;
            $planet['lithium_workers'] = $lithium_assign;
            $planet_set['workers'] = $free_workers - $lithium_assign;
            $planet['workers'] = $planet_set['workers'];
        }
    }

    if (isset($armatita_assign) && $armatita_assign != $planet['armatita_workers']) {
        $free_workers = $planet['workers'] + $planet['armatita_workers'];
        if ($armatita_assign <= $free_workers) {
            $planet_set['armatita_workers'] = $armatita_assign;
            $planet['armatita_workers'] = $armatita_assign;
            $planet_set['workers'] = $free_workers - $armatita_assign;
            $planet['workers'] = $planet_set['workers'];
        }
    }


    if (!empty($planet_set)) {
        $db->update('planets', $planet_set, ['id' => $planet['id']], 'LIMIT 1');
    }

    return $planet;
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
