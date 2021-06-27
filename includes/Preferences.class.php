<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
class Preferences {

    private $id;
    private $prefs = [];

    public function __construct($id) {
        if (!isset($id) || $id < 0) {
            return false;
        } else {
            $this->id = $id;
            $this->loadPrefs();
        }
    }

    public function getPrefsItem(string $r_key) {
        return !empty($this->prefs[$r_key]) ? $this->prefs[$r_key] : false;
    }

    public function setPrefsItem(string $key, string $value) {
        global $db;

        if (isset($this->prefs[$key])) {
            if ($this->prefs[$key] !== $value) {
                $where['uid'] = ['value' => $this->id];
                $where['pref_name'] = ['value' => $key];
                $set['pref_value'] = $value;
                $db->update('preferences', $set, $where, 'LIMIT 1');
            }
        } else {
            $new_item = [
                'uid' => $this->id,
                'pref_name' => $key,
                'pref_value' => $value,
            ];
            $db->addItem('preferences', $new_item);
        }
        $this->prefs[$key] = $value;
    }

    public function getPrefValueByUid(int $id, string $r_key) {
        global $db;

        if ($id == $this->id) {
            return !empty($this->prefs[$r_key]) ? $this->prefs[$r_key] : false;
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

        $where['uid'] = ['value' => $this->id];
        $results = $db->select('preferences', null, $where);

        if (valid_array($user_prefs = $db->fetchAll($results))) {
            foreach ($user_prefs as $pref) {
                if (!empty($pref['pref_name']) && isset($pref['pref_value'])) {
                    $this->prefs[$pref['pref_name']] = $pref['pref_value'];
                }
            }
        }
        // SET DEFAULTS
        empty($this->prefs['tresults_rows']) ? $this->prefs['tresults_rows'] = $cfg['tresults_rows'] : null;
        empty($this->prefs['tresults_columns']) ? $this->prefs['tresults_columns'] = $cfg['tresults_columns'] : null;
        empty($this->prefs['max_identify_items']) ? $this->prefs['max_identify_items'] = $cfg['max_identify_items'] : null;
        //empty($this->prefs['']) ? $this->prefs[''] = $cfg[''] : null;
    }

}
