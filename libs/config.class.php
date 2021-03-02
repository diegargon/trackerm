<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego@envigo.net)
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

    public function display($display_cat = null) {
        global $LNG;
        $categories = [];
        $selected_cat_title = '';

        empty($display_cat) ? $display_cat = 'L_MAIN' : null;

        $data_row = '';
        foreach ($this->config as $config) {
            if (!empty($config['category']) && $config['category'] != 'L_PRIV') {
                if (!in_array($config['category'], $categories)) {
                    $categories[] = $config['category'];
                }
            }
            if ($config['public'] == 1 && $config['category'] == $display_cat) {
                if (empty($selected_cat_title)) {
                    if (substr($config['category'], 0, 2) == 'L_') {
                        $selected_cat_title = '<h2>' . $LNG[$config['category']] . '</h2>';
                    } else {
                        $selected_cat_title = '<h2>' . $config['category'] . '</h2>';
                    }
                }
                $data_row .= '<div class = "catRow border_blue">';
                $data_row .= '<div class = "catCell">';
                if ($config['type'] == 3) {
                    if ($config['cfg_value'] == 0) {
                        $select_no = 'selected';
                        $select_yes = '';
                    } else {
                        $select_no = '';
                        $select_yes = 'selected';
                    }
                    $data_row .= '<select name="config_keys[' . $config['cfg_key'] . ']">';
                    $data_row .= '<option ' . $select_no . ' value="0">' . $LNG['L_NO'] . '</option>';
                    $data_row .= '<option ' . $select_yes . ' value="1">' . $LNG['L_YES'] . '</option>';
                    $data_row .= '</select>';
                    /* TODO: CONFIGSELECT */
                } else if ($config['type'] == 8) {
                    $values = $this->commaToArray($config['cfg_value']);
                    $data_row .= '<select name="config_id[' . $config['cfg_key'] . ']">';
                    if ($values) {
                        foreach ($values as $value_key => $value) {
                            $data_row .= '<option value="' . $value_key . '">' . $value . '</option>';
                        }
                    }
                    $data_row .= '</select>';
                    $data_row .= '<input class="action_btn" type="submit" name="config_remove[' . $config['cfg_key'] . ']" value="' . $LNG['L_DELETE'] . '" />';
                    $data_row .= '<br/><input size="10" type="text" name="add_item[' . $config['cfg_key'] . ']" value="" />';
                    $data_row .= '<input class="action_btn" type="submit" name="config_add[' . $config['cfg_key'] . ']" value="' . $LNG['L_ADD'] . '" />';
                    $data_row .= '<input type="hidden" name="add_before[' . $config['cfg_key'] . '] value="0" />';
                    $data_row .= '<div class="inline" data-tip="' . $LNG['L_ADD_BEFORE'] . '"><input type="checkbox" name="add_before[' . $config['cfg_key'] . '] value="1" /></div>';
                } else {
                    $data_row .= '<input type="text" name="config_keys[' . $config['cfg_key'] . ']" value="' . $config['cfg_value'] . '" />';
                }
                $data_row .= '</div>';
                $data_row .= '<div class = "catCell">' . $LNG[$config['cfg_desc']] . '</div>';
                $data_row .= '</div>';
            }
        }

        $cat_head = '<div class="config_cats">';
        foreach ($categories as $category) {
            isset($LNG[$category]) ? $cat_desc = ucfirst($LNG[$category]) : $cat_desc = $category;
            $cat_head .= '<a href = "index.php?page=config&category=' . $category . '">' . $cat_desc . '</a> ';
        }
        $cat_head .= '</div>';
        $data_result = $cat_head . $selected_cat_title;
        $data_result .= '<form method = "POST"><input class="submit_btn" type="submit" name="submit_config" value="' . $LNG['L_SUBMIT'] . '"/>';
        $data_result .= '<div class = "catTable">' . $data_row . '</div></form>';

        return $data_result;
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

    private function commaToArray($string) {
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
