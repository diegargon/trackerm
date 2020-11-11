<?php

/**
 * 
 *  @author diego@envigo.net
 *  @package 
 *  @subpackage 
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 * 
 */
class DB {

    private $tables = [];
    private $dbpath;

    function __construct($dbpath) {
        $this->dbpath = $dbpath;
    }

    public function addElements($table, $elements) {
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;
        !isset($this->tables[$table]['info']['last_id']) ? $lastid = 0 : $lastid = $this->tables[$table]['info']['last_id'];

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

    public function addUniqElements($table, $elements, $uniq_key) {
        //array_search($file, array_column($movies, 'path')
        !isset($this->tables[$table]) ? $this->loadTable($table) : null;
        !isset($this->tables[$table]['info']['last_id']) ? $lastid = 0 : $lastid = $this->tables[$table]['info']['last_id'];

        if (!is_array($elements)) {
            return false;
        }

        $id = $lastid;

        foreach ($elements as $element) {
            if (!isset($this->tables[$table]['data']) ||
                    array_search($element['id'], array_column($this->tables[$table]['data'], $uniq_key)) === false
            ) {
                //echo "<br>" . $element['id'] . ':' . $this->tables[$table]['data'] .':'. $uniq_key;
                $this->tables[$table]['data'][$id] = $element;
                $id++;
            }
        }
        $this->tables[$table]['info']['last_id'] = $id;
        if ($id > $lastid) {
            $this->tables[$table]['info']['last_update'] = time();
            $this->saveTable($table);
        }
    }

    public function getLastID($table) {
        if (!isLoaded($table)) {
            return false;
        }
        if (!empty($this->tables[$table]['info']['lastid'])) {
            return $this->tables[$table]['info']['lastid'];
        } else {
            return false;
        }
    }

    public function getTableData($table, $force = false) {
        if (!isset($this->tables[$table]['data']) || $force === true) {

            if ($this->loadTable($table) && $this->tables[$table]['data']) {
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

    public function updateRecordByID($table, $id, $val_ary) {
        $this->getTableData($table);

        foreach ($val_ary as $key => $val) {
            $this->tables[$table]['data'][$id][$key] = $val;
        }

        $this->saveTable($table);
    }

    /* NOT TESTED */

    public function updateRecordsByField($table, $field, $value, $update_fields) {
        $this->getTableData($table);

        foreach ($update_fields as $key => $val) {
            foreach ($this->tables[$table]['data'] as $id => $item) {
                if ($item[$field] == $value) {
                    $this->tables[$table]['data'][$id][$key] = $val;
                }
            }
        }

        $this->saveTable($table);
    }

    /* A partir de un id y el campo key/valor de este actualiza otros registros que tengas ese key/valor */

    public function updateRecordsBySameField($table, $id, $field, $update_fields) {
        $this->getTableData($table);

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
        $db_file = $this->dbpath . '/' . $table . '.db';
        $this->tables[$table]['info']['last_saved'] = time();
        if (save_to_file_json($db_file, $this->dbpath, $this->tables[$table])) {
            return true;
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
