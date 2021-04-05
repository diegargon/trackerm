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
