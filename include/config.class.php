<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
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
                $split_values = explode(',', trim($config['cfg_value']));
                foreach ($split_values as $split_value) {
                    $cfg[$config['cfg_key']][] = trim($split_value);
                }
            }
        }
    }

    public function display($display_cat = null) {
        global $LNG;
        $categories = [];

        empty($display_cat) ? $display_cat = 'L_MAIN' : null;

        $data_row = '';
        foreach ($this->config as $config) {
            if (!empty($config['category'])) {
                if (!in_array($config['category'], $categories)) {
                    $categories[] = $config['category'];
                }
            }
            if ($config['public'] == 1 && $config['category'] == $display_cat) {
                $data_row .= '<div class = "catRow">';
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
                } else {
                    $data_row .= '<input type="text" name="config_keys[' . $config['cfg_key'] . ']" value="' . $config['cfg_value'] . '" />';
                }
                $data_row .= '</div>';
                $data_row .= '<div class = "catCell">' . $LNG[$config['cfg_desc']] . '</div>';
                $data_row .= '</div>';
            }
        }

        $cat_head = '<div class="cats">';
        foreach ($categories as $category) {
            isset($LNG[$category]) ? $cat_desc = ucfirst($LNG[$category]) : $cat_desc = $category;
            $cat_head .= '<a href = "index.php?page=config&category=' . $category . '">' . $cat_desc . '</a> ';
        }
        $cat_head .= '</div>';


        $data_result = '<form method = "POST"><input class="submit_btn" type="submit" name="submit_config" value="' . $LNG['L_SUBMIT'] . '"/>';
        $data_result .= $cat_head . '<div class = "catTable">' . $data_row . '</div></form>';

        return $data_result;
    }

    public function save($config_keys) {
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

}
