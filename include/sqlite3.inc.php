<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
/*
  NOOB CHEATSEAT
  Muestra tablas
  sqlite> PRAGMA table_info(db_info)

  Contenido con las etiquetas
  .headers on
  .mode column
  y luego el select * o lo que sea;

 */
class newDB {

    private $version = 1;
    private $db;
    private $db_path;
    private $log = [];
    private $querys = [];

    public function __construct($db_path) {
        $this->db_path = $db_path;
    }

    public function connect() {
        $response = $this->checkInstall();
        $this->db = new SQLite3($this->db_path, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        if (!$response && !empty($this->db)) {
            $this->createTables();
            $response = $this->checkInstall();
        }
        if (empty($this->db)) {
            return $this->fail();
        } else {
            echo "\nConnection ok";
        }
        $version = $this->getDbVersion();
        if ($version < $this->version) {
            echo "\nNeed upgrade the database... upgrading";
            $this->upgradeDb($version);
        } else if ($version > $this->version) {
            echo "\nThis database version is newer than this api";
        }
        echo "\nSeems all go fine";
        echo "\nDB Version $version";
    }

    /* Helpers */

    public function getItemById($table, $id) {
        //select($table, $what = null, $where = null, $extra = null)
        $where['id'] = ['value' => $id];
        $query = $this->select($table, null, $where, 'LIMIT 1');
        return $this->fetch($query);
    }

    public function getItemByField($table, $field, $value) {
        $where[$field] = ['value' => $value];
        $query = $this->select($table, null, $where, 'LIMIT 1');
        return $this->fetch($query);
    }

    public function updateItemById($table, $id, $values) {
        $where['id'] = $id;

        $this->update($table, $values, $where, 'LIMIT 1');
    }

    public function getTableData($table) {
        return $this->select($table);
    }

    public function deleteById($table, $id) {
        $where['id'] = ['value' => $id];
        $this->delete($table, $where);
    }

    /* MAIN */

    public function insert($table, $values) {
        $query = 'INSERT INTO "' . $table . '" (';
        $query_binds = '';
        $query_keys = '';
        $bind_values = [];
        foreach ($values as $key => $value) {
            $query_keys .= '"' . $key . '"';
            $query_binds .= ':' . $key;
            $bind_values[':' . $key] = $value;

            if ($value != end($values)) {
                $query_keys .= ', ';
                $query_binds .= ', ';
            }
        }
        $query .= $query_keys . ') VALUES (' . $query_binds;
        $query .= ')';

        $this->querys[] = $query;
        $statement = $this->db->prepare($query);

        foreach ($bind_values as $bkey => $bvalue) {
            $statement->bindValue($bkey, $bvalue);
        }

        return (($statement->execute()) || $this->fail());
    }

    public function upsert() {
        /*
          TODO
          SYNTAX

          INSERT INTO players (user_name, age)
          VALUES('steven', 32)
          ON CONFLICT(user_name)
          DO UPDATE SET age=excluded.age;

         */
    }

    /*
      $where['userid'] = [ 'value' => diego, 'op' => '=', 'logic' => 'AND' ];
     */

    public function select($table, $what = null, $where = null, $extra = null) {

        $bind_values = [];

        $query = 'SELECT ';
        !empty($what) ? $query .= $what : $query .= '*';
        $query .= ' FROM "' . $table . '"';
        if ($where != null) {
            $query .= ' WHERE ';
            foreach ($where as $where_k => $where_v) {
                !empty($where_v['op']) ? $operator = $where_v['op'] : $operator = '=';
                !empty($where_v['logic']) ? $logic = $where_v['logic'] : $logic = 'AND';

                $query .= ' "' . $where_k . '" ' . $operator . ' :' . $where_k;
                if ($where_v != end($where)) {
                    $query .= ' ' . $operator . ' ';
                }
                $bind_values[':' . $where_k] = $where_v['value'];
            }
        }
        !empty($extra) ? $query .= $extra : null;
        $this->querys[] = $query;
        $statement = $this->db->prepare($query);

        if (!empty($bind_values)) {
            foreach ($bind_values as $bkey => $bvalue) {
                $statement->bindValue($bkey, $bvalue);
            }
        }

        $response = $statement->execute();

        return $response;
    }

    public function delete($table, $where) {

        $query = 'DELETE FROM ' . $table . ' ';

        if ($where != null) {
            $query .= ' WHERE ';
            foreach ($where as $where_k => $where_v) {
                !empty($where_v['op']) ? $operator = $where_v['op'] : $operator = '=';
                !empty($where_v['logic']) ? $logic = $where_v['logic'] : $logic = 'AND';

                $query .= ' "' . $where_k . '" ' . $operator . ' :' . $where_k;
                if ($where_v != end($where)) {
                    $query .= ' ' . $operator . ' ';
                }
                $bind_values[':' . $where_k] = $where_v['value'];
            }
        }

        $this->querys[] = $query;
        $statement = $this->db->prepare($query);

        if (!empty($bind_values)) {
            foreach ($bind_values as $bkey => $bvalue) {
                $statement->bindValue($bkey, $bvalue);
            }
        }

        $response = $statement->execute();

        return $response;
    }

    /*
      $set['username'] = 'diego';
      $where['userid'] = [ 'value' => diego, 'op' => '=', 'logic' => 'AND' ];
     */

    public function update($table, $set, $where = null, $extra = null) {
        $bind_values = [];

        $query = 'UPDATE ' . $table;

        $query .= ' SET ';
        foreach ($set as $set_k => $set_v) {
            $query .= ' "' . $set_k . '" = ' . ':' . $set_k;

            $bind_values[':' . $set_k] = $set_v;
        }

        if ($where != null) {
            foreach ($where as $where_k => $where_v) {
                !empty($where_v['op']) ? $operator = $where_v['op'] : $operator = '=';
                !empty($where_v['logic']) ? $logic = $where_v['logic'] : $logic = 'AND';

                $query .= ' "' . $where_k . '" ' . $operator . ' :' . $where_k;
                if ($where_v != end($where)) {
                    $query .= ' ' . $logic . ' ';
                }
                $bind_values[':' . $where_k] = $where_v['value'];
            }
        }
        !empty($extra) ? $query .= $extra : null;
        $this->querys[] = $query;

        $statement = $this->db->prepare($query);

        if (!empty($bind_values)) {
            foreach ($bind_values as $bkey => $bvalue) {
                $statement->bindValue($bkey, $bvalue);
            }
        }

        return (($statement->execute()) || $this->fail());
    }

    public function fetch($result) {
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function fetch_all($result) {
        $rows = [];
        while ($row = $result->fetchArray()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getLastId() {
        return $this->db->lastInsertRowID();
    }

    public function finalize($result) {
        $result->finalize();
    }

    public function getDbVersion() {
        $query = $this->select('db_info');
        $result = $this->fetch($query);
        return $result['version'];
    }

    function query($query) {
        return $this->db->query($query);
    }

    private function checkInstall() {
        if (file_exists($this->db_path)) {
            $this->log[] = 'db file exists';
            //check if exist tables
            return true;
        } else {
            $this->log[] = 'db file not exists';
            return false;
        }
    }

    private function fail() {
        echo "\n FAIL \n";
        echo print_r($this->querys);
        return false;
    }

    private function createTables() {
        echo "\nEntering create tables\n";
        require('../config/db.sql.php');

        return create_db();
    }

    private function upgradeDb($from) {
        echo "\nEntering updatedb from $from \n";
        require('../config/db.sql.php');

        return update_db($from);
    }

}
