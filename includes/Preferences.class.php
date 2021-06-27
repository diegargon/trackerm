<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
/*
 * User mode populate prefs and sys_prefs, for get/modify sys_prefs system=true
 * Cli mode (when uid=0) class only use sys_prefs not need to use system=true
 */

class Preferences {

    private $id;
    private $prefs = [];
    private $sys_prefs = [];

    public function __construct(int $id) {
        if (!isset($id) || $id < 0) {
            return false;
        } else {
            $this->id = $id;
            $this->loadPrefs();
        }
    }

    public function getPrefsItem(string $r_key, bool $system = false) {
        if ($system || $this->id == 0) {
            return !empty($this->sys_prefs[$r_key]) ? $this->sys_prefs[$r_key] : false;
        } else {
            return !empty($this->prefs[$r_key]) ? $this->prefs[$r_key] : false;
        }
    }

    public function setPrefsItem(string $key, string $value, bool $system = false) {
        global $db;

        if ($system || $this->id == 0) {
            $prefs = &$this->sys_prefs;
            $id = 0;
        } else {
            $prefs = &$this->prefs;
            $id = $this->id;
        }

        if (isset($prefs[$key])) {
            if ($prefs[$key] !== $value) {
                $where['uid'] = ['value' => $id];
                $where['pref_name'] = ['value' => $key];
                $set['pref_value'] = $value;
                $db->update('preferences', $set, $where, 'LIMIT 1');
            }
        } else {
            $new_item = [
                'uid' => $id,
                'pref_name' => $key,
                'pref_value' => $value,
            ];
            $db->addItem('preferences', $new_item);
        }
        $prefs[$key] = $value;
    }

    public function getPrefValueByUid(int $id, string $r_key) {
        global $db;

        if ($id == $this->id && $this->id > 0) {
            return !empty($this->prefs[$r_key]) ? $this->prefs[$r_key] : false;
        } else if ($id == 0) {
            return !empty($this->sys_prefs[$r_key]) ? $this->sys_prefs[$r_key] : false;
        } else {
            $where = ['pref_name' => ['value' => $r_key], 'uid' => ['value' => $id]];

            $results = $db->select('preferences', null, $where, 'LIMIT 1');
            $user_prefs = $db->fetchAll($results);
            if (valid_array($user_prefs)) {
                return $user_prefs[0]['pref_value'];
            }
        }
        return false;
    }

    private function loadPrefs() {
        global $db, $cfg;

        if ($this->id == 0) {
            $query = 'SELECT * FROM preferences WHERE uid = ' . $this->id;
        } else {
            $query = 'SELECT * FROM preferences WHERE uid = ' . $this->id . ' OR uid = ' . 0;
        }
        $results = $db->query($query);

        if (valid_array($prefs = $db->fetchAll($results))) {
            foreach ($prefs as $pref) {
                if (!empty($pref['pref_name']) && $pref['uid'] == 0) {
                    $this->sys_prefs[$pref['pref_name']] = $pref['pref_value'];
                } else if (!empty($pref['pref_name'])) {
                    $this->prefs[$pref['pref_name']] = $pref['pref_value'];
                }
            }
        }
        // SET DEFAULTS
        if ($this->id > 0) {
            empty($this->prefs['tresults_rows']) ? $this->prefs['tresults_rows'] = $cfg['tresults_rows'] : null;
            empty($this->prefs['tresults_columns']) ? $this->prefs['tresults_columns'] = $cfg['tresults_columns'] : null;
            empty($this->prefs['max_identify_items']) ? $this->prefs['max_identify_items'] = $cfg['max_identify_items'] : null;
            //empty($this->prefs['']) ? $this->prefs[''] = $cfg[''] : null;
        }
    }

}
