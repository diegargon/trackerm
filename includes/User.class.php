<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
class User {

    private $ships;
    private $planets;
    private $characters;
    private $planet_store;

    function __construct() {
        $this->ships = [];
        $this->planets = [];
        $this->characters = [];
        $this->planet_store = [];
    }

    function username() {
        return 'diego';
    }

    function id() {
        return 1;
    }

    function isAdmin() {
        return true;
    }

    function updateSession() {
        global $db, $cfg;

        if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
            setcookie('sid', session_id(), [
                'expires' => time() + $cfg['sid_expire'],
                'secure' => true,
                'samesite' => 'lax',
            ]);
        } else {
            setcookie("sid", session_id(), time() + $cfg['sid_expire']);
        }
        $db->update('users', ['sid' => session_id()], ['id' => $this->id()]);
    }

    function getShips() {
        global $db;

        if (empty($this->ships)) {
            $result = $db->select('ships', '*', ['uid' => $this->id()]);
            $this->ships = $db->fetchAll($result);
        }

        return $this->ships;
    }

    function getInRangeUserShips($ship_id) {
        global $db;

        $in_range = [];
        $ship = $this->getShipById($ship_id);

        if (valid_array($ship)) {
            foreach ($this->ships as $check_ship) {
                if ($check_ship['id'] !== $ship_id && $check_ship['x'] == $ship['x'] && $check_ship['y'] == $ship['y'] && $check_ship['z'] == $ship['z']) {
                    $in_range[] = $check_ship;
                }
            }
        }

        return valid_array($in_range) ? $in_range : false;
    }

    function getPlanets() {
        global $db;

        if (empty($this->planets)) {
            $result = $db->select('planets', '*', ['uid' => $this->id()]);
            $this->planets = $db->fetchAll($result);
        }

        return $this->planets;
    }

    function checkInUserPlanet(int $x, int $y, int $z) {
        foreach ($this->getPlanets() as $planet) {
            if ($planet['x'] == $x && $planet['y'] == $y && $planet['z'] == $z) {
                return $planet;
            }
        }
        return false;
    }

    function getShipById(int $ship_id) {
        foreach ($this->getShips() as $ship) {
            if ($ship['id'] == $ship_id) {
                return $ship;
            }
        }
        return false;
    }

    function setShipValue(int $ship_id, $ckey, $cvalue) {
        foreach ($this->ships as $key => $ship) {
            if ($ship['id'] == $ship_id) {
                $this->ships[$key][$ckey] = $cvalue;
            }
        }
    }

    function getPlanetById(int $planet_id) {
        foreach ($this->getPlanets() as $planet) {
            if ($planet['id'] == $planet_id) {
                return $planet;
            }
        }
        return false;
    }

    function setPlanetValue(int $planet_id, $ckey, $cvalue) {
        foreach ($this->planets as $key => $planet) {
            if ($planet['id'] == $planet_id) {
                $this->planets[$key][$ckey] = $cvalue;
            }
        }
    }

    function getCharacters() {
        global $db;

        if (empty($this->characters)) {
            $result = $db->select('characters', '*', ['uid' => $this->id()], 'ORDER BY name');
            $this->characters = $db->fetchAll($result);
        }

        return $this->characters;
    }

    function getCharacterById(int $id) {
        $chars = $this->getCharacters();

        foreach ($chars as $char) {
            if ($char['id'] == $id) {
                return $char;
            }
        }

        return false;
    }

    function getCharactersByPerk(int $perk) {
        $chars = $this->getCharacters();
        $perk_chars = [];
        foreach ($chars as $char) {
            if ($char['perk'] == $perk) {
                $perk_chars[] = $char;
            }
        }

        return valid_array($perk_chars) ? $perk_chars : false;
    }

    function setCharacterValue(int $char_id, $ckey, $cvalue) {
        foreach ($this->characters as $key => $char) {
            if ($char['id'] == $char_id) {
                $this->characters[$key][$ckey] = $cvalue;
            }
        }
    }

    function getAvaibleTypeChar($planet_id, $char_perk) {
        $avaible_chars = [];

        foreach ($this->getCharacters() as $character) {
            if ($character['planet_assigned'] == $planet_id && $character['perk'] == $char_perk) {
                $avaible_chars[] = $character;
            }
        }

        return $avaible_chars;
    }

    function getShipCharacters($ship_id) {
        $ship_characters = [];

        foreach ($this->getCharacters() as $character) {
            if ($character['ship_assigned'] == $ship_id) {
                $ship_characters[] = $character;
            }
        }

        return $ship_characters;
    }

    function getPlanetCharacters(int $planet_id, int $only_free = 0) {
        $planet_characters = [];

        foreach ($this->getCharacters() as $character) {

            if ($character['planet_assigned'] == $planet_id) {
                if (!$only_free || ($only_free && !$character['job'])) {
                    $planet_characters[] = $character;
                }
            }
        }
        return $planet_characters;
    }

    function getPlanetShips(int $planet_id) {
        $planet_ships = [];
        $planet = $this->getPlanetById($planet_id);

        foreach ($this->getShips() as $ship) {
            if ($ship['x'] == $planet['x'] && $ship['y'] == $planet['y'] && $ship['z'] == $planet['z']) {
                $planet_ships[] = $ship;
            }
        }

        return $planet_ships;
    }

    function getPlanetStoreItems(int $planet_id) {
        global $db;

        if (empty($this->planet_store[$planet_id])) {
            $results = $db->select('store', '*', ['pid' => $planet_id]);
            $this->planet_store[$planet_id] = $db->fetchAll($results);
        }

        return $this->planet_store[$planet_id];
    }

    function getPlanetStoreItemType(int $planet_id, int $item_type_id) {
        $items_type = [];

        foreach ($this->getPlanetStoreItems($planet_id) as $item) {
            if ($item['type'] == $item_type_id) {
                $items_type[] = $item;
            }
        }

        return $items_type;
    }

}
