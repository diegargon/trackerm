<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
function mining_tick() {
    global $db, $cfg;

    $result = $db->select('planets', '*', ['uid' > ['value' => 0, 'op' => '>']]);
    $planets = $db->fetchAll($result);
    foreach ($planets as $planet) {
        //Titanium
        if ($planet['titanium'] > 0 && $planet['titanium_workers'] > 0) {
            $mining = $planet['titanium_workers'] * $cfg['mining_production'];
            $new_titanium = $planet['titanium'] - $mining;
            $titanium_stored = $planet['titanium_stored'] + $mining;
            $planet_set['titanium'] = $new_titanium;
            $planet_set['titanium_stored'] = $titanium_stored;
        }
        if (!empty($planet_set)) {
            $db->update('planets', $planet_set, ['id' => $planet['id']], 'LIMIT 1');
        }
    }
}

function workers_tick() {
    global $db, $cfg, $user;

    $workers_production = $cfg['workers_production'];
    $db->query("UPDATE planets SET workers = workers + round(workers * $workers_production)  WHERE uid > 0");

    //Random/Probabilist create characters
    $result = $db->select('planets', 'id,uid');
    $planets = $db->fetchAll($result);
    foreach ($planets as $planet) {
        ///if (chance(100)) {
        if (chance($cfg['character_creation_chance'])) {
            $char = character_creation();
            $char['uid'] = $planet['uid'];
            $char['planet_assigned'] = $planet['id'];
            $db->insert('characters', $char);
        }
    }
}

function character_creation() {
    global $perks;

    $char['name'] = random_names();
    $char['perk'] = random_int(1, count($perks));
    if (chance(1)) {
        $perk_value = random_int(80, 90);
    } else if (chance(5)) {
        $perk_value = random_int(70, 80);
    } else if (chance(10)) {
        $perk_value = random_int(60, 70);
    } else if (chance(20)) {
        $perk_value = random_int(50, 60);
    } else if (chance(40)) {
        $perk_value = random_int(40, 50);
    } else if (chance(80)) {
        $perk_value = random_int(30, 40);
    } else {
        $perk_value = random_int(10, 30);
    }
    $char['perk_value'] = $perk_value;

    return $char;
}
