<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
/*
  NOOB CHEATSEAT
  Muestra tablas
  sqlite> PRAGMA table_info(db_info)

  Contenido con las etiquetas
  .headers on
  .mode column
  y luego el select * o lo que sea;

  v0.8
 */
!defined('IN_WEB') ? exit : true;

class DB {

    private $version = 21;
    private $db;
    private $db_path;
    private $querys = [];

    public function __construct($db_path) {
        $this->db_path = $db_path;
    }

    public function connect() {

        $response = $this->checkInstall();
        $this->db = new SQLite3($this->db_path, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        $this->db->busyTimeout(5000);
        //$this->db->enableExceptions(true);
        $this->db->exec('PRAGMA journal_mode = wal;');

        if (!$response && !empty($this->db)) {
            $this->createTables();
            $response = $this->checkInstall();
        }
        if (empty($this->db)) {
            return $this->fail();
        }
    }

    public function checkNeedUpgrade() {
        $version = $this->getDbVersion();
        if ($version == false) {
            print('Impossible determine installed db version');
        }
        if ($version < $this->version) {
            $this->upgradeDb($version);
        }
    }

    /* Common / Helpers */

    public function addItem(string $table, array $item) {
        $this->insert($table, $item);
        return $this->getLastId();
    }

    public function addItems(string $table, array $items) {
        $ids = [];
        foreach ($items as $item) {
            $this->insert($table, $item);
            $ids[] = $this->getLastId();
        }
        return $ids;
    }

    public function addItemUniqField(string $table, array $item, string $field) {
        $value = $item[$field];
        $id = $this->getIdByField($table, $field, $value);
        if (empty($id)) {
            $this->addItem($table, $item);
            return $this->getLastId();
        }
        //item already exists
        return false;
    }

    public function addItemsUniqField(string $table, array $items, string $field) {
        foreach ($items as $item) {
            !isset($fields) ? $fields = $item[$field] : $fields .= ',' . $item[$field];
        }

        $items_fields = $this->selectMultiple($table, $field, $fields, $field);
        foreach ($items as $item) {
            $found = 0;
            foreach ($items_fields as $item_field) {
                if ($item_field[$field] == $item[$field]) {
                    break;
                }
            }
            if ($found) {
                $this->addItemUniqField($table, $item, $field);
            }
        }
    }

    //TODO create a upsert insert and replace SQLite have it... i think
    public function upsertItemByField(string $table, array $item, string $field) {
        $value = $item[$field];
        $_item = $this->getItemByField($table, $field, $value);
        if (!empty($_item)) {
            $this->updateItemByField($table, $item, $field);
            return true;
        } else {
            $this->addItem($table, $item);
            return true;
        }

        return false;
    }

    public function getItemById(string $table, $id) {
        $where['id'] = ['value' => $id];
        $result = $this->select($table, null, $where, 'LIMIT 1');
        $row = $this->fetch($result);
        $this->finalize($result);

        return (!empty($row) && (count($row) > 0)) ? $row : false;
    }

    public function getItemsByIds(string $table, string $ids) {
        $rows = $this->selectMultiple($table, 'id', $ids);

        return is_array($rows) && (count($rows)) ? $rows : false;
    }

    public function getItemByField(string $table, string $field, string $value) {
        $where[$field] = ['value' => $value];
        $result = $this->select($table, null, $where, 'LIMIT 1');
        $row = $this->fetch($result);
        $this->finalize($result);
        return (!empty($row) && (count($row) > 0)) ? $row : false;
    }

    public function getItemsByField(string $table, string $field, $value, $extra = null) {
        $where[$field] = ['value' => $value];
        $result = $this->select($table, null, $where, $extra);
        $rows = $this->fetchAll($result);
        $this->finalize($result);
        return (!empty($rows) && (count($rows) > 0)) ? $rows : false;
    }

    public function getItemsByFieldM(string $table, string $field, string $values) {
        $rows = $this->selectMultiple($table, $field, $values);

        return is_array($rows) && (count($rows)) ? $rows : false;
    }

    public function getIdByField(string $table, string $field, $value) {
        $where[$field] = ['value' => $value];
        $result = $this->select($table, 'id', $where, 'LIMIT 1');
        $row = $this->fetch($result);
        $this->finalize($result);

        return (!empty($row) && (count($row) > 0)) ? $row['id'] : false;
    }

    public function updateItemById($table, $id, $values) {
        $where['id'] = ['value' => $id];

        $this->update($table, $values, $where, 'LIMIT 1');
    }

    public function updateItemByField(string $table, array $item, $field) {
        $where[$field] = ['value' => $item[$field]];

        $this->update($table, $item, $where, 'LIMIT 1');
    }

    public function updateItemsByField(string $table, array $items, $field) {
        $where[$field] = ['value' => $items[$field]];

        $this->update($table, $items, $where);
    }

    public function getTableData(string $table) {
        $result = $this->select($table);
        $rows = $this->fetchAll($result);
        $this->finalize($result);

        return $rows;
    }

    public function deleteItemById(string $table, $id) {
        $where['id'] = ['value' => $id];
        $this->delete($table, $where, 'LIMIT 1');
    }

    public function deleteItemByField(string $table, $field, $value) {
        $where[$field] = ['value' => $value];
        $this->delete($table, $where, 'LIMIT 1');
    }

    public function deleteItemsByField(string $table, $field, $value) {
        $where[$field] = ['value' => $value];
        $this->delete($table, $where);
    }

    //exact mean word separated with spaces don't known yet if works like this possible TODO/FIX
    public function search(string $table, $field, $value, bool $exact = null, string $extra = null) {
        $value = $this->escapeString($value);
        $query = "SELECT * FROM " . $table . " WHERE " . $field . " LIKE ";
        if (!empty($exact)) {
            $query .= "'%" . $value . "%'";
        } else {
            $query .= "'% " . $value . " %'";
        }
        !empty($extra) ? $query .= ' ' . $extra : null;
        $this->query($query);
    }

    /* MAIN */

    public function count(string $table) {
        $result = $this->query('SELECT COUNT (*) FROM ' . $table);
        $row = $result->fetchArray();

        return $row[0];
    }

    public function insert(string $table, array $values) {
        $query = 'INSERT INTO "' . $table . '" (';
        $query_binds = '';
        $query_keys = '';
        $bind_values = [];

        foreach ($values as $key => $value) {
            $query_keys .= '"' . $key . '"';
            $query_binds .= ':' . $key;
            $bind_values[':' . $key] = $this->escape($value);

            if ($key != array_key_last($values)) {
                $query_keys .= ', ';
                $query_binds .= ', ';
            }
        }
        $query .= $query_keys . ') VALUES (' . $query_binds;
        $query .= ')';
        $this->querys[]['query'] = $query;

        $st = $this->db->prepare($query);

        $last_ary_id = array_key_last($this->querys);
        $this->querys[$last_ary_id]['bind'] = print_r($bind_values, true);
        foreach ($bind_values as $bkey => $bvalue) {
            $st->bindValue($bkey, $bvalue);
        }

        $response = $st->execute();

        return $response;
    }

    public function upsert(string $table, array $set, array $where) {
        //FIX upsert with fixed field name id not good
        $result = $this->select($table, 'id', $where, 'LIMIT 1');
        $item = $this->fetch($result);
        $this->finalize($result);
        if ($item) {
            $update_where['id'] = ['value' => $item['id']];
            $this->update($table, $set, $update_where);
        } else {
            $this->insert($table, $set);
        }
    }

    /*
      $where['userid'] = [ 'value' => diego, 'op' => '=', 'logic' => 'AND' ];
     */

    public function select(string $table, string $what = null, array $where = null, string $extra = null) {

        $bind_values = [];

        $query = 'SELECT ';

        if (!empty($what) && $what != "*") {
            $what_string = '';
            foreach (explode(',', $what) as $what_ary) {
                empty($what_string) ? $what_string .= '"' . $what_ary . '"' : $what_string .= ',"' . $what_ary . '"';
            }
            $query .= $what_string;
        } else {
            $query .= '*';
        }

        $query .= ' FROM "' . $table . '"';
        if ($where != null) {
            $query .= ' WHERE ';
            foreach ($where as $where_k => $where_v) {
                !empty($where_v['op']) ? $operator = $where_v['op'] : $operator = '=';
                !empty($where_v['logic']) ? $logic = $where_v['logic'] : $logic = 'AND';

                $query .= ' "' . $where_k . '" ' . $operator . ' :' . $where_k;
                if ($where_k != array_key_last($where)) {
                    $query .= ' ' . $logic . ' ';
                }
                $bind_values[':' . $where_k] = $this->escape($where_v['value']);
            }
        }
        !empty($extra) ? $query .= ' ' . $extra : null;
        $this->querys[]['query'] = $query;

        $st = $this->db->prepare($query);

        if (!empty($bind_values)) {
            $last_ary_id = array_key_last($this->querys);
            $this->querys[$last_ary_id]['bind'] = print_r($bind_values, true);
            foreach ($bind_values as $bkey => $bvalue) {
                $st->bindValue($bkey, $bvalue);
            }
        }

        $response = $st->execute();

        return $response;
    }

    /* values can be array or comma separate */

    public function selectMultiple(string $table, $field, $values, $what = null) {
        !isset($what) ? $what = '*' : null;
        if (!is_array($values)) {
            $final_values = array_map('trim', explode(',', $values));
        } else {
            $final_values = $values;
        }

        $prep_values = '';
        foreach ($final_values as $final_value) {
            empty($prep_values) ? $prep_values = '\'' . trim($final_value) . '\'' : $prep_values .= ',\'' . $this->escape(trim($final_value)) . '\'';
        }
        $query = 'SELECT ' . $what . ' FROM ' . $table . ' WHERE ' . $field . ' IN(' . $prep_values . ')';

        $result = $this->query($query);
        $rows = $this->fetchAll($result);

        return $rows;
    }

    public function delete(string $table, array $where = null, string $extra = null) {

        $query = 'DELETE FROM ' . $table . ' ';

        if ($where != null) {
            $query .= ' WHERE ';
            foreach ($where as $where_k => $where_v) {
                !empty($where_v['op']) ? $operator = $where_v['op'] : $operator = '=';
                !empty($where_v['logic']) ? $logic = $where_v['logic'] : $logic = 'AND';

                $query .= ' "' . $where_k . '" ' . $operator . ' :' . $where_k;
                if ($where_k != array_key_last($where)) {
                    $query .= ' ' . $logic . ' ';
                }
                $bind_values[':' . $where_k] = $where_v['value'];
            }
        }
        !empty($extra) ? $query .= ' ' . $extra : null;
        $this->querys[]['query'] = $query;
        $st = $this->db->prepare($query);

        if (!empty($bind_values)) {
            $last_ary_id = array_key_last($this->querys);
            $this->querys[$last_ary_id]['bind'] = print_r($bind_values, true);
            foreach ($bind_values as $bkey => $bvalue) {
                $st->bindValue($bkey, $bvalue);
            }
        }

        $response = $st->execute();

        return $response;
    }

    /*
      $set['username'] = 'diego';
      $where['userid'] = [ 'value' => diego, 'op' => '=', 'logic' => 'AND' ];
     */

    public function update(string $table, array $set, array $where = null, string $extra = null) {
        $bind_values = [];

        $query = 'UPDATE ' . $table;

        $query .= ' SET ';
        foreach ($set as $set_k => $set_v) {
            $query .= ' "' . $set_k . '" = ' . ':' . $set_k;
            ($set_k != array_key_last($set)) ? $query .= ', ' : null;
            $bind_values[':' . $set_k] = $set_v;
        }

        if ($where != null) {
            $query .= ' WHERE ';
            foreach ($where as $where_k => $where_v) {
                !empty($where_v['op']) ? $operator = $where_v['op'] : $operator = '=';
                !empty($where_v['logic']) ? $logic = $where_v['logic'] : $logic = 'AND';

                $query .= ' "' . $where_k . '" ' . $operator . ' :' . $where_k;
                if ($where_k != array_key_last($where)) {
                    $query .= ' ' . $logic . ' ';
                }
                $bind_values[':' . $where_k] = $this->escape($where_v['value']);
            }
        }
        !empty($extra) ? $query .= ' ' . $extra : null;
        $this->querys[]['query'] = $query;

        $st = $this->db->prepare($query);

        if (!empty($bind_values)) {
            $last_ary_id = array_key_last($this->querys);
            $this->querys[$last_ary_id]['bind'] = print_r($bind_values, true);
            foreach ($bind_values as $bkey => $bvalue) {
                $st->bindValue($bkey, $bvalue);
            }
        }

        $response = $st->execute();

        return $response;
    }

    public function escape($string) {
        if ($string === null) {
            return null;
        } else if ($string === '') {
            return '';
        }
        return $this->db->escapeString($string);
    }

    public function fetch($result) {
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function fetchAll($result) {
        $rows = [];
        while ($row = $this->fetch($result)) {
            $rows[] = $row;
        }

        $this->finalize($result);
        return $rows;
    }

    public function getLastId() {
        return $this->db->lastInsertRowID();
    }

    public function finalize($results) {
        $results->finalize();
    }

    public function free($results) {
        $results->finalize();
    }

    public function getDbVersion() {
        global $cfg;

        return !empty($cfg['db_version']) ? $cfg['db_version'] : false;
    }

    public function getQuerys() {
        return $this->querys;
    }

    public function qSingle($query) {
        $this->querys[]['query'] = $query;
        return $this->db->querySingle($query);
    }

    public function query($query) {
        $this->querys[]['query'] = $query;
        return $this->db->query($query);
    }

    public function close() {
        $this->db->close();
        unset($this->db);
    }

    private function checkInstall() {
        if (file_exists($this->db_path)) {
            return true;
        } else {
            return false;
        }
    }

    private function fail() {
        print ('Fatal error: impossible connect to the database');
        exit();
    }

    private function createTables() {
        require_once('config/db.sql.php');

        return create_db();
    }

    private function upgradeDb($from) {
        require_once('config/db.sql.php');

        return update_db($from);
    }

}
