<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function get_ship_mass($ship) {
    global $ship_parts;
    $mass = 0;
    unset($ship['planet_id']);
    unset($ship['ship_name']);

    foreach ($ship as $key => $opt) {
        if (!empty($opt)) {
            $mass = $mass + $ship_parts[$key][$opt]['mass'];
        }
    }
    return $mass;
}

function calc_new_purity(float $purity_dest, float $amount_dest, float $purity_orig, float $amount_orig) {

    $pure_stored = ($purity_dest / 100) * $amount_dest;
    $pure_mining = ($purity_orig / 100) * $amount_orig;

    return (($pure_stored + $pure_mining) / ($amount_dest + $amount_orig)) * 100;
}
