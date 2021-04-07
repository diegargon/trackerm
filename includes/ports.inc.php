<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
function show_port() {
    require('includes/shipyard.inc.php');

    global $user, $frontend;

    $tpl_data = '';
    $status_msg = '';

    //POST
    if (!empty($_POST)) {
        (!empty($_POST['ship_builder_submit'])) ? $status_msg .= ship_builder() : null;
        (!empty($_POST['calculate_ship'])) ? $status_msg .= calculate_ship_build() : null;
    }

    $planets = $user->getPlanets();

    if (!empty(Filter::postInt('planet_id'))) {
        $planet = $user->getPlanetById(Filter::postInt('planet_id'));
    } else {
        if (valid_array($planets)) {
            $planet = $planets[0];
        }
    }

    $tdata = [];

    if ($planet['have_shipyard']) {
        $tdata['planet_shipyard'] = show_shipyard($planet);
    }
    $tdata['status_msg'] = $status_msg;
    $tpl_data .= $frontend->getTpl('port', $tdata);

    return $tpl_data;
}
