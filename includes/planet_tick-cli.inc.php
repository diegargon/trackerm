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
        //Lithium
        if ($planet['lithium'] > 0 && $planet['lithium_workers'] > 0) {
            $mining = round(($planet['lithium_workers'] * $cfg['mining_production']) / 2);
            $new_lithium = $planet['lithium'] - $mining;
            $lithium_stored = $planet['lithium_stored'] + $mining;
            $planet_set['lithium'] = $new_lithium;
            $planet_set['lithium_stored'] = $lithium_stored;
        }
        //Armatita
        if ($planet['armatita'] > 0 && $planet['armatita_workers'] > 0) {
            $mining = round(($planet['armatita_workers'] * $cfg['mining_production']) / 4);
            $new_armatita = $planet['armatita'] - $mining;
            $armatita_stored = $planet['armatita_stored'] + $mining;
            $planet_set['armatita'] = $new_armatita;
            $planet_set['armatita_stored'] = $armatita_stored;

            if ($planet['armatita_stored_purity'] == 0) {
                $planet_set['armatita_stored_purity'] = $planet['armatita_purity'];
            } else {
                /*
                  $pure_stored = ($planet['armatita_stored_purity'] / 100) * $planet['armatita_stored'];
                  $pure_mining = ($planet['armatita_purity'] / 100 ) * $mining;
                  $planet_set['armatita_stored_purity'] = (($pure_stored + $pure_mining) / $armatita_stored) * 100;
                 */
                $planet_set['armatita_stored_purity'] = calc_new_purity($planet['armatita_stored_purity'], $planet['armatita_stored'], $planet['armatita_purity'], $mining);
            }
        }

        if (!empty($planet_set)) {
            $db->update('planets', $planet_set, ['id' => $planet['id']], 'LIMIT 1');
        }
    }
}

function workers_tick() {
    global $db, $cfg;

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
