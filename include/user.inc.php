<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function get_profiles() {
    global $newdb;

    $results = $newdb->select('users');

    return $newdb->fetchAll($results);
}

function get_profile($uid) {
    global $newdb;

    return $newdb->getItemById('users', $uid);
}
