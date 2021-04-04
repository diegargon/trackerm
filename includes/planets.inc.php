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
    if (!empty(Filter::postInt('planet'))) {
        $planet_sel = Filter::postInt('planet');
    }
    $planets = $user->getPlanets();
    $html = '';

    $conf['name'] = 'planet';
    $conf['onChange'] = 1;

    !empty($planet_sel) ? $conf['selected'] = $planet_sel : null;

    foreach ($planets as $_planet) {
        $values[] = [
            'name' => $_planet['name'],
            'value' => $_planet['id'],
        ];
    }

    $tpl_data = html::select($conf, $values);

    if (!empty(Filter::postInt('planet'))) {
        $planet_id = Filter::postInt('planet');
        foreach ($planets as $_planet) {
            if ($_planet['id'] == $planet_id) {
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
    global $L, $db, $user, $perks, $frontend;

    $tdata = [];

    if (!empty($_POST) && !empty(Filter::postInt('planet_id'))) {
        $planet_id = Filter::postInt('planet_id');
        if (isset($_POST['mining_submit'])) {
            $titanium_assign = Filter::postInt('titanium_assign');
            if (isset($titanium_assign)) {
                $free_workers = $planet['workers'] + $planet['titanium_workers'];
                if ($titanium_assign <= $free_workers) {
                    $planet_set['titanium_workers'] = $titanium_assign;
                    $planet['titanium_workers'] = $titanium_assign;
                    $planet_set['workers'] = $free_workers - $titanium_assign;
                    $planet['workers'] = $planet_set['workers'];
                }
            }

            if (!empty($planet_id) && !empty($planet_set)) {
                $db->update('planets', $planet_set, ['id' => $planet_id], 'LIMIT 1');
            }
        }
    }
    $tpl_data = html::head(['h' => 1], $L['L_PLANETS']);
    $tpl_data .= html::head(['h' => 2], $L['L_SUMMARY']);
    $tpl_data .= html::p([], $L['L_PLANET']);

    $info = $L['L_LOCATION'] . ": {$planet['x']}:{$planet['y']}:{$planet['z']}";
    $info .= html::br([]);
    if (!empty($planet['titanium'])) {
        $info .= $L['L_TITANIUM'] . ': ' . $planet['titanium'];
        $info .= html::br([]) . $L['L_TITANIUM_STORED'] . ': ' . $planet['titanium_stored'];
        $info .= html::br([]) . $L['L_TITANIUM_WORKERS'] . ': ' . $planet['titanium_workers'];
    }
    $info .= html::br([]) . $L['L_WORKERS'] . ': ' . $planet['workers'];
    $tpl_data .= html::p([], $info);

    //MINING
    $tpl_data .= html::head(['h' => 3], $L['L_MINING']);
    $form_wrap = html::input(['type' => 'hidden', 'name' => 'planet_id', 'value' => $planet['id']]);
    if (!empty($planet['titanium'])) {
        $form_wrap .= html::p([], $L['L_TITANIUM']);
        $form_wrap .= html::input(['type' => 'text', 'name' => 'titanium_assign', 'value' => $planet['titanium_workers'], 'size' => 5]);
    }
    $form_wrap .= html::input(['type' => 'submit', 'name' => 'mining_submit', 'value' => $L['L_ASSIGN']]);
    $tpl_data .= html::form(['id' => 'mining_form', 'method' => 'post'], $form_wrap);


    //CHARACTERS
    $vips = $user->getPlanetCharacters($planet['id']);

    $vips_select = [];
    foreach ($vips as $vip) {
        $vips_data = $vip['name'];
        $vips_data .= ' ' . $L[$perks[$vip['perk']]];
        $vips_data .= '(' . $vip['perk_value'] . ')';
        $vips_select[] = ['name' => $vips_data, 'value' => $vip['id']];
    }
    $tdata['vip_data'] = html::select(['name' => 'vip_select', 'size' => 10], $vips_select);

    //SHIPS
    $in_planet_ships = $user->getPlanetShips($planet['id']);

    $ships_select = [];
    foreach ($in_planet_ships as $in_planet_ship) {
        $ships_select[] = ['name' => $in_planet_ship['name'], 'value' => $in_planet_ship['id']];
    }
    $tdata['ships_data'] = html::select(['name' => 'ships_select', 'size' => 10], $ships_select);

    $tpl_data .= $frontend->getTpl('planets', $tdata);

    return $tpl_data;
}
