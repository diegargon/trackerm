<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Ships {

    public function __construct() {

    }

    function getShipById(int $id) {
        global $db;

        $result = $db->select('ships', '*', ['id' => $id], 'LIMIT 1');
        $ships = $db->fetch($result);
        if ($ships) {
            return $ships;
        }

        return false;
    }

    function getCharacters(int $uid) {
        global $db;

        $chars = [];

        $result = $db->select('characters', '*', ['uid' => $uid]);
        $chars = $db->fetchAll($result);


        return valid_array($chars) ? $chars : false;
    }

    function getShipCharacters(int $ship_id) {
        $ship = self::getShipById($ship_id);

        if (!valid_array($ship)) {
            return false;
        }

        $ship_characters = [];

        $characters = self::getCharacters($ship['uid']);

        if (!valid_array($characters)) {
            return false;
        }

        foreach ($characters as $character) {
            if ($character['ship_assigned'] == $ship_id) {
                $ship_characters[] = $character;
            }
        }

        return valid_array($ship_characters) ? $ship_characters : false;
    }

    function havePilot($ship_id) {
        $ship_chars = self::getShipCharacters($ship_id);

        if (!valid_array($ship_chars)) {
            return false;
        } else {
            foreach ($ship_chars as $ship_char) {
                if ($ship_char['perk'] == 1 || $ship_char['perk'] == 2) {
                    return true;
                }
            }
        }
        return false;
    }

}
