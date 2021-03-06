<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 *
 */
!defined('IN_WEB') ? exit : true;

class DB {

    private $tables = [];
    private $dbpath;

    function __construct($dbpath) {
        $this->dbpath = $dbpath;
    }

    public function addElements($table, $elements) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;
        !isset($this->tables[$table]['info']['last_id']) ? $lastid = 1 : $lastid = $this->tables[$table]['info']['last_id'];

        if (!is_array($elements)) {
            return false;
        }
        $id = $lastid;

        foreach ($elements as $element) {
            $this->tables[$table]['data'][$id] = $element;
            $id++;
        }
        $this->tables[$table]['info']['last_id'] = $id;
        $this->tables[$table]['info']['last_update'] = time();
        $this->saveTable($table);
    }

    /* Add element if not exist another uniq_key value : return false */

    public function addUniqElements($table, $elements, $uniq_key) {
        //array_search($file, array_column($movies, 'path')
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;
        !isset($this->tables[$table]['info']['last_id']) ? $lastid = 1 : $lastid = $this->tables[$table]['info']['last_id'];

        if (!is_array($elements)) {
            return false;
        }

        $id = $lastid;

        foreach ($elements as $element) {
            if (
                    !isset($this->tables[$table]['data']) ||
                    array_search($element[$uniq_key], array_column($this->tables[$table]['data'], $uniq_key)) === false
            ) {
                $this->tables[$table]['data'][$id] = $element;
                $this->tables[$table]['data'][$id]['id'] = $id;
                $id++;
            }
        }

        if ($id > $lastid) {
            $this->tables[$table]['info']['last_id'] = $id;
            $this->tables[$table]['info']['last_update'] = time();
            $this->saveTable($table);
        } else {
            return false;
        }
        return true;
    }

    public function addUniqKey($table, $elements) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;

        foreach ($elements as $key => $element) {
            $this->tables[$table]['data'][$key] = $element;
        }
        $this->tables[$table]['info']['last_update'] = time();
        $this->saveTable($table);
    }

    public function getUniqKey($table, $item) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;

        if (!isset($this->tables[$table]['data'][$item])) {
            return false;
        }

        return $this->tables[$table]['data'][$item];
    }

    public function addSimpleValue($table, $value) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;
        $last_id = $this->getLastId($table);

        $this->tables[$table]['data'][$last_id] = $value;
        $last_id++;
        $this->tables[$table]['info']['last_id'] = $last_id;
        $this->tables[$table]['info']['last_update'] = time();

        $this->saveTable($table);
    }

    public function getSimpleValues($table) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;
        if (isset($this->tables[$table]['data'])) {
            return $this->tables[$table]['data'];
        }

        return false;
    }

    public function getItemByID($table, $id) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;

        if (!isset($this->tables[$table]['data'][$id])) {
            return false;
        }
        return $this->tables[$table]['data'][$id];
    }

    public function getIdByField($table, $field, $field_value) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;

        if (!isset($this->tables[$table]['data'])) {
            return false;
        }

        foreach ($this->tables[$table]['data'] as $item) {
            if ($item[$field] == $field_value) {
                return $item['id'];
            }
        }
        return false;
    }

    public function getItemByField($table, $field, $field_value) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;

        if (!empty($this->tables[$table]['data'])) {
            foreach ($this->tables[$table]['data'] as $item) {
                if (isset($item[$field]) && ($item[$field] == $field_value)) {
                    return $item;
                }
            }
        }

        return false;
    }

    public function deleteById($table, $id) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;

        if (!isset($this->tables[$table]['data'][$id])) {
            return false;
        }

        unset($this->tables[$table]['data'][$id]);
        $this->saveTable($table);
    }

    public function deleteByFieldMatch($table, $field, $field_value) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;

        if (!empty($this->tables[$table]['data'])) {
            foreach ($this->tables[$table]['data'] as $item) {
                if ($item[$field] == $field_value) {
                    $this->deleteById($table, $item['id']);
                }
            }
        }

        return true;
    }

    public function getNumElements($table) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;

        if (!isset($this->tables[$table]['data'])) {
            return false;
        }

        return count($this->tables[$table]['data']);
    }

    public function getLastId($table) {
        $this->loadTable($table);

        if (!empty($this->tables[$table]['info']['last_id'])) {
            return $this->tables[$table]['info']['last_id'];
        } else {
            return 1;
        }
    }

    public function reloadTable($table) {

        return $this->loadTable($table);
    }

    public function getTableData($table, $force = false) {

        if (!isset($this->tables[$table]['data']) || $force === true) {
            if ($this->loadTable($table) && !empty($this->tables[$table]['data'])) {
                return $table_data = $this->tables[$table]['data'];
            } else {
                return false;
            }
        } else {
            $table_data = $this->tables[$table]['data'];
        }
        $this->tables[$table]['data'] = $table_data;

        return $table_data;
    }

    public function setTableData($table, $data) {
        $this->table['data'] = $data;

        return $this->saveTable($table);
    }

    public function updateRecordById($table, $id, $val_ary) {
        $this->getTableData($table);

        if (!isset($this->tables[$table]['data'][$id])) {
            return false;
        }
        foreach ($val_ary as $key => $val) {
            $this->tables[$table]['data'][$id][$key] = $val;
        }

        $this->saveTable($table);
        return true;
    }

    public function updateElementById($table, $id, $element) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;

        if (isset($this->tables[$table]['data'][$id])) {
            $element = current($element);
            $this->tables[$table]['data'][$id] = $element;
            $this->tables[$table]['info']['last_update'] = time();
            $this->saveTable($table);

            return true;
        }
        return false;
    }

    public function upsertElementById($table, $id, $element) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;

        $this->tables[$table]['data'][$id] = $element;
        $this->tables[$table]['info']['last_update'] = time();
        $this->saveTable($table);

        return true;
    }

    /* NOT TESTED */

    public function updateRecordsByField($table, $field, $value, $update_fields) {
        $this->getTableData($table);

        if (!isset($this->tables[$table]['data'][$field])) {
            return false;
        }

        foreach ($update_fields as $key => $val) {
            foreach ($this->tables[$table]['data'] as $id => $item) {
                if ($item[$field] == $value) {
                    $this->tables[$table]['data'][$id][$key] = $val;
                }
            }
        }

        $this->saveTable($table);

        return true;
    }

    /* A partir de un id y el campo key/valor de este actualiza otros registros que tengas ese key/valor */

    public function updateRecordsBySameField($table, $id, $field, $update_fields) {
        $this->getTableData($table);

        if (!isset($this->tables[$table]['data'][$id])) {
            return false;
        }

        foreach ($update_fields as $key => $val) {
            // Cogemos el valor del campo $field del id del registro proporcionado para buscar iguales
            $compare_value = $this->tables[$table]['data'][$id][$field];

            // Buscamos los registros para $field con el valor $compare_value, si coincidencia aplicamos val_array
            foreach ($this->tables[$table]['data'] as $db_id => $db_item) {
                if ($db_item[$field] == $compare_value) {
                    $this->tables[$table]['data'][$db_id][$key] = $val;
                }
            }
        }
        $this->saveTable($table);

        return true;
    }

    public function clearTable($table) {
        $this->tables[$table]['data'] = [];
        $this->saveTable($table);
    }

    private function loadTable($table) {
        $db_file = $this->dbpath . '/' . $table . '.db';

        if (($table_data = load_from_file_json($db_file))) {
            if ($table) {
                $this->tables[$table] = $table_data;
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    private function saveTable($table) {
        global $log;

        $db_file = $this->dbpath . '/' . $table . '.db';
        $this->tables[$table]['info']['last_saved'] = time();
        if (save_to_file_json($db_file, $this->dbpath, $this->tables[$table])) {
            return true;
        } else {
            $log->debug("db:save_to_file_json failed");
        }
        return false;
    }

    private function getTable($table) {
        if ($this->isLoaded($table)) {
            return $this->tables[$table];
        }

        return false;
    }

    private function isLoaded($table) {
        if (!isset($this->tables[$table])) {
            if ($this->loadTable($table)) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

}
