<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function show_shipyard(array $planet) {
    global $frontend, $ship_parts, $L;

    $tpl_data = [];
    $desc_tpl = '';

    isset($_POST['sel_bridge']) ? $sel_bridge = Filter::postInt('sel_bridge') : $sel_bridge = '';
    foreach ($ship_parts['bridge'] as $key => $bridge) {
        if (!empty($sel_bridge) && $key == $sel_bridge) {
            $desc_tpl .= get_shipbuild_desc('L_BRIDGE', $bridge);
        }
        $bridge_opt[] = ['name' => $bridge['name'], 'value' => $key];
    }
    $tpl_data['bridge_sel'] = html::select(['name' => 'sel_bridge', 'sel_none' => 1, 'selected' => $sel_bridge], $bridge_opt);

    isset($_POST['sel_crew']) ? $sel_crew = Filter::postInt('sel_crew') : $sel_crew = '';
    foreach ($ship_parts['crew'] as $key => $crew) {
        if (!empty($sel_crew) && $key == $sel_crew) {
            $desc_tpl .= get_shipbuild_desc('L_CREW', $crew);
        }
        $crew_opt[] = ['name' => $crew['name'], 'value' => $key];
    }
    $tpl_data['crew_sel'] = html::select(['name' => 'sel_crew', 'sel_none' => 1, 'selected' => $sel_crew], $crew_opt);

    isset($_POST['sel_cargo']) ? $sel_cargo = Filter::postInt('sel_cargo') : $sel_cargo = '';
    foreach ($ship_parts['cargo'] as $key => $cargo) {
        if (!empty($sel_cargo) && $key == $sel_cargo) {
            $desc_tpl .= get_shipbuild_desc('L_CARGO', $cargo);
        }
        $cargo_opt[] = ['name' => $cargo['name'], 'value' => $key];
    }
    $tpl_data['cargo_sel'] = html::select(['name' => 'sel_cargo', 'sel_none' => 1, 'selected' => $sel_cargo], $cargo_opt);

    isset($_POST['sel_missile_storage']) ? $sel_missile_storage = Filter::postInt('sel_missile_storage') : $sel_missile_storage = '';
    foreach ($ship_parts['missile_storage'] as $key => $missile_storage) {
        if (!empty($sel_missile_storage) && $key == $sel_missile_storage) {
            $desc_tpl .= get_shipbuild_desc('L_MISSILE_STORAGE', $missile_storage);
        }
        $missile_storage_opt[] = ['name' => $missile_storage['name'], 'value' => $key];
    }
    $tpl_data['missile_storage_sel'] = html::select(['name' => 'sel_missile_storage', 'sel_none' => 1, 'selected' => $sel_missile_storage], $missile_storage_opt);

    isset($_POST['sel_front_guns']) ? $sel_front_guns = Filter::postInt('sel_front_guns') : $sel_front_guns = '';
    foreach ($ship_parts['front_guns'] as $key => $front_guns) {
        if (!empty($sel_front_guns) && $key == $sel_front_guns) {
            $desc_tpl .= get_shipbuild_desc('L_FRONT_GUNS', $front_guns);
        }
        $front_guns_opt[] = ['name' => $front_guns['name'], 'value' => $key];
    }
    $tpl_data['front_guns_sel'] = html::select(['name' => 'sel_front_guns', 'sel_none' => 1, 'selected' => $sel_front_guns], $front_guns_opt);

    isset($_POST['sel_turrets']) ? $sel_turrets = Filter::postInt('sel_turrets') : $sel_turrets = '';
    foreach ($ship_parts['turrets'] as $key => $turrets) {
        if (!empty($sel_turrets) && $key == $sel_turrets) {
            $desc_tpl .= get_shipbuild_desc('L_TURRETS', $turrets);
        }
        $turrets_opt[] = ['name' => $turrets['name'], 'value' => $key];
    }
    $tpl_data['turrets_sel'] = html::select(['name' => 'sel_turrets', 'sel_none' => 1, 'selected' => $sel_turrets], $turrets_opt);

    isset($_POST['sel_propeller']) ? $sel_propeller = Filter::postInt('sel_propeller') : $sel_propeller = '';
    foreach ($ship_parts['propeller'] as $key => $propeller) {
        if (!empty($sel_propeller) && $key == $sel_propeller) {
            $desc_tpl .= get_shipbuild_desc('L_PROPELLER', $propeller);
        }
        $propeller_opt[] = ['name' => $propeller['name'], 'value' => $key];
    }
    $tpl_data['propeller_sel'] = html::select(['name' => 'sel_propeller', 'sel_none' => 1, 'selected' => $sel_propeller], $propeller_opt);

    isset($_POST['sel_generator']) ? $sel_generator = Filter::postInt('sel_generator') : $sel_generator = '';
    foreach ($ship_parts['generator'] as $key => $generator) {
        if (!empty($sel_generator) && $key == $sel_generator) {
            $desc_tpl .= get_shipbuild_desc('L_GENERATOR', $generator);
        }
        $generator_opt[] = ['name' => $generator['name'], 'value' => $key];
    }
    $tpl_data['generator_sel'] = html::select(['name' => 'sel_generator', 'sel_none' => 1, 'selected' => $sel_generator], $generator_opt);

    isset($_POST['sel_accumulator']) ? $sel_accumulator = Filter::postInt('sel_accumulator') : $sel_accumulator = '';
    foreach ($ship_parts['accumulator'] as $key => $accumulator) {
        if (!empty($sel_accumulator) && $key == $sel_accumulator) {
            $desc_tpl .= get_shipbuild_desc('L_ACCUMULATOR', $accumulator);
        }
        $accumulator_opt[] = ['name' => $accumulator['name'], 'value' => $key];
    }
    $tpl_data['accumulator_sel'] = html::select(['name' => 'sel_accumulator', 'sel_none' => 1, 'selected' => $sel_accumulator], $accumulator_opt);

    isset($_POST['sel_shields']) ? $sel_shields = Filter::postInt('sel_shields') : $sel_shields = '';
    foreach ($ship_parts['shields'] as $key => $shields) {
        if (!empty($sel_shields) && $key == $sel_shields) {
            $desc_tpl .= get_shipbuild_desc('L_SHIELDS', $shields);
        }
        $shields_opt[] = ['name' => $shields['name'], 'value' => $key];
    }
    $tpl_data['shields_sel'] = html::select(['name' => 'sel_shields', 'sel_none' => 1, 'selected' => $sel_shields], $shields_opt);


    isset($_POST['sel_radar']) ? $sel_radar = Filter::postInt('sel_radar') : $sel_radar = '';
    foreach ($ship_parts['radar'] as $key => $radar) {
        if (!empty($sel_radar) && $key == $sel_radar) {
            $desc_tpl .= get_shipbuild_desc('L_RADAR', $radar);
        }
        $radar_opt[] = ['name' => $radar['name'], 'value' => $key];
    }
    $tpl_data['radar_sel'] = html::select(['name' => 'sel_radar', 'sel_none' => 1, 'selected' => $sel_radar], $radar_opt);

    $tpl_data['planet_id'] = $planet['id'];
    $tpl_data['descriptions'] = $desc_tpl;
    isset($_POST['ship_name']) ? $tpl_data['ship_name'] = Filter::postString('ship_name') : $tpl_data['ship_name'] = '';

    $tpl = $frontend->getTpl('shipyard', $tpl_data);

    return $tpl;
}

function get_shipbuild_desc($title, $item) {
    global $L;

    $desc = html::div(['class' => 'desc_item_title'], $L[$title]);
    $desc .= html::div(['class' => 'desc_item'], $item['desc']);
    if (isset($item['cap'])) {
        $desc .= html::div(['class' => 'desc_item_spec'], html::span(['class' => 'desc_item_spec_title'], $L['L_CAPACITY']) . ':' . html::span(['class' => 'desc_item_spec_value'], $item['cap']));
    }
    if (isset($item['mass'])) {
        $desc .= html::div(['class' => 'desc_item_spec'], html::span(['class' => 'desc_item_spec_title'], $L['L_MASS']) . ':' . html::span(['class' => 'desc_item_spec_value'], $item['mass']));
    }
    $desc .= html::br([]);

    return html::div([], $desc);
}

function get_post_shipbuild() {
    $ship_build['ship_name'] = Filter::postString('ship_name');
    $ship_build['planet_id'] = Filter::postInt('planet_id');
    $ship_build['bridge'] = Filter::postInt('sel_bridge');
    $ship_build['crew'] = Filter::postInt('sel_crew');
    $ship_build['cargo'] = Filter::postInt('sel_cargo');
    $ship_build['missile_storage'] = Filter::postInt('sel_missile_storage');
    $ship_build['front_guns'] = Filter::postInt('sel_front_guns');
    $ship_build['turrets'] = Filter::postInt('sel_turrets');
    $ship_build['propeller'] = Filter::postInt('sel_propeller');
    $ship_build['generator'] = Filter::postInt('sel_generator');
    $ship_build['accumulator'] = Filter::postInt('sel_accumulator');
    $ship_build['shields'] = Filter::postInt('sel_shields');
    $ship_build['radar'] = Filter::postInt('sel_radar');

    return $ship_build;
}

function check_shipbuild(array $ship_build) {
    global $L, $db, $user;

    $return = '';

    if (empty($ship_build['planet_id'])) {
        $return .= $L['L_ERROR_INTERNAL'] . ':2343GJWO3423RF2W3' . '<br/>';
    }
    if (empty($ship_build['ship_name'])) {
        $return .= $L['L_ERR_SHIPNAME_MANDATORY'] . '<br/>';
    }
    $results = $db->select('ships', 'id', ['uid' => $user->id(), 'name' => $ship_build['ship_name']], 'LIMIT 1');
    if ($db->fetch($results)) {
        $return .= $L['L_ERR_SHIPNAME_EXISTS'] . '<br/>';
    }
    if (empty($ship_build['bridge'])) {
        $return .= $L['L_ERR_BRIDGE_MANDATORY'] . '<br/>';
    }

    if (empty($ship_build['generator'])) {
        $return .= $L['L_ERR_GENERATOR_MANDATORY'] . '<br/>';
    }
    if (empty($ship_build['accumulator'])) {
        $return .= $L['L_ERR_ACCUMULATOR_MANDATORY'] . '<br/>';
    }
    if (empty($ship_build['propeller'])) {
        $return .= $L['L_ERR_PROPELLER_MANDATORY'] . '<br/>';
    }
    return empty($return) ? true : $return;
}

function calculate_ship_build() {
    $ship_build = get_post_shipbuild();

    if (($check_build = check_shipbuild($ship_build)) !== true) {
        return $check_build;
    }

    return 'The mass is ' . get_ship_mass($ship_build);
}

function ship_builder() {
    global $user, $cfg, $L, $db;

    $ship_build = get_post_shipbuild();

    if (($check_build = check_shipbuild($ship_build)) !== true) {
        return $check_build;
    }

    $planet = $user->getPlanetById($ship_build['planet_id']);

    $bship['x'] = $bship['dx'] = $planet['x'];
    $bship['y'] = $bship['dy'] = $planet['y'];
    $bship['z'] = $bship['dz'] = $planet['z'];
    $bship['uid'] = $user->id();

    $mass = get_ship_mass($ship_build);
    $bship['build_ticks'] = round($mass * $cfg['build_ticks_multiplicator']);

    if ($planet['titanium_stored'] < ($mass * $cfg['build_cost_multiplicator'])) {
        return $L['L_ERROR_NO_MATS'];
    } else {
        $cost = $mass * $cfg['build_cost_multiplicator'];
    }

    $bship['generator'] = $ship_build['generator'];
    $bship['accumulator'] = $ship_build['accumulator'];
    $bship['propeller'] = $ship_build['propeller'];
    $bship['bridge_type'] = $ship_build['bridge'];
    !empty($ship_build['crew']) ? $bship['crew_type'] = $ship_build['crew'] : null;
    !empty($ship_build['cargo']) ? $bship['cargo_type'] = $ship_build['cargo'] : null;
    !empty($ship_build['front_guns']) ? $bship['front_guns'] = $ship_build['front_guns'] : null;
    !empty($ship_build['missile_storage']) ? $bship['missile_storage'] = $ship_build['missile_storage'] : null;
    !empty($ship_build['turrets']) ? $bship['turrets'] = $ship_build['turrets'] : null;
    !empty($ship_build['shields']) ? $bship['shields'] = $ship_build['shields'] : null;
    !empty($ship_build['radar']) ? $bship['radar'] = $ship_build['radar'] : null;
    $bship['name'] = $ship_build['ship_name'];
    $bship['in_shipyard'] = 1;

    $new_resources = $planet['titanium_stored'] - $cost;
    //$db->update('planets', ['titanium_stored' => $new_resources], ['id' => $planet['id']]);

    $db->insert('ships', $bship);
    $status_msg = $L['L_BUILD_SUCCESS'];

    return $status_msg;
}
