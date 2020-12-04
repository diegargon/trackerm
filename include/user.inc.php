<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function get_profiles() {
    global $db;

    $results = $db->select('users');

    return $db->fetchAll($results);
}

function get_profile($uid) {
    global $db;

    return $db->getItemById('users', $uid);
}
