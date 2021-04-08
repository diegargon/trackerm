<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
class Planets {

    public function __construct() {

    }

    function getPlanetById(int $id) {
        global $db;

        $result = $db->select('planets', '*', ['id' => $id], 'LIMIT 1');
        $planet = $db->fetch($result);
        if ($planet) {
            return $planet;
        }

        return false;
    }

    function checkIfPlanet(int $x, int $y, int $z) {
        global $db;

        $result = $db->select('planets', '*', ['x' => $x, 'y' => $y, 'z' => $z], 'LIMIT 1');
        $planet = $db->fetch($result);
        if ($planet) {
            return $planet;
        }

        return false;
    }

}
