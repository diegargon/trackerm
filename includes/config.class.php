<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Config {

    private $config;

    public function __construct() {
        global $db, $cfg;

        $results = $db->select('config');
        $this->config = $db->fetchAll($results);
        foreach ($this->config as $config) {
            if ($config['type'] <= 6) {
                $cfg[$config['cfg_key']] = $config['cfg_value'];
            } else {
                $split_values = $this->commaToArray($config['cfg_value']);
                if ($split_values) {
                    foreach ($split_values as $split_value) {
                        $cfg[$config['cfg_key']][] = $split_value;
                    }
                }
            }
        }
    }

    public function getConfig() {
        return $this->config;
    }

    public function saveKeys($config_keys) {
        global $db, $cfg;

        foreach ($config_keys as $key => $value) {
            $value = trim($value);
            foreach ($this->config as $key_id => $config) {
                if ($config['cfg_key'] == $key && $config['cfg_value'] !== $value) {
                    $db->update('config', ['cfg_value' => $value], ['id' => ['value' => $config['id']]], 'LIMIT 1');
                    $cfg[$config['cfg_key']] = $value;
                    $this->config[$key_id]['cfg_value'] = $value;
                }
            }
        }
    }

    public function removeCommaElement($key, $id) {

        $elements = $this->config;
        foreach ($elements as $element) {
            if ($element['cfg_key'] == $key) {
                $element_value = $element['cfg_value'];
                break;
            }
        }
        if (!empty($element_value)) {
            $elements_array = $this->commaToArray($element_value);
            unset($elements_array[$id]);
            $comma_elements = $this->arrayToComma($elements_array);
            $toSave[$key] = $comma_elements;
            $this->saveKeys($toSave);
        }
    }

    public function addCommaElement($key, $value, $id, $before = 0) {

        if (empty($value)) {
            return false;
        }
        $elements = $this->config;
        foreach ($elements as $element) {
            if ($element['cfg_key'] == $key) {
                $element_value = $element['cfg_value'];
                break;
            }
        }
        if ($id != null && !empty($element_value)) {
            $elements_array = $this->commaToArray($element_value);
            if ($before) {
                $where_id = $id - 1;
                $where_id < 0 ? $where_id = 0 : null;
            } else {
                $where_id = $id + 1;
            }
            array_splice($elements_array, $where_id, 0, $value);
            $comma_elements = $this->arrayToComma($elements_array);
        } else {
            $comma_elements = $value;
        }

        $toSave[$key] = $comma_elements;
        $this->saveKeys($toSave);
    }

    public function commaToArray($string) {
        if (empty($string)) {
            return null;
        }
        $array = array_map('trim', explode(',', $string));
        return (count($array) > 0) ? $array : null;
    }

    private function arrayToComma($array) {
        $string = '';
        foreach ($array as $element) {
            $element = trim($element);
            empty($string) ? $string = $element : $string .= ',' . $element;
        }
        return $string;
    }

}
