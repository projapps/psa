<?php

namespace App\Datatables;

use PDO;
use PDOException;
use PDOStatement;
use stdClass;

class DataBaseProcessing
{
    static function insert($data, PDO $db, $table, $columns)
    {
        $pluck = ServerSideProcessing::pluck($columns, 'db');
        $sql = "INSERT INTO " . $table . "(" . implode(", ", $pluck) . ") VALUES(:" . implode(", :", $pluck) . ")";
        $stmt = self::bindValues($data, $db, $columns, $sql);
        return $stmt->execute();
    }

    static function update($data, PDO $db, $table, $columns)
    {
        $sql = "UPDATE " . $table;
        $comma = false;
        foreach ($columns as $column) {
            $key = $column['db'];
            if ($key != 'id') {
                if ($comma) {
                    $sql .= ", " . $key . " = :" . $key;
                } else {
                    $sql .= " SET " . $key . " = :" . $key;
                    $comma = true;
                }
            }
        }
        $sql .= " WHERE id = :id";
        $stmt = self::bindValues($data, $db, $columns, $sql);
        return $stmt->execute();
    }

    static function delete($data, PDO $db, $table)
    {
        $key = "id";
        $sql = "DELETE FROM " . $table . " WHERE " . $key . " = :" . $key;
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':' . $key, $data->$key);
        return $stmt->execute();
    }

    static function select($data, PDO $db, $table, $columns)
    {
        $pluck = ServerSideProcessing::pluck($columns, 'db');
        $sql = "SELECT " . implode(", ", $pluck) . " FROM " . $table;
        $and = false;
        foreach ($data as $key => $value) {
            if ($and) {
                $sql .= " AND " . $key . " = :" . $key;
            } else {
                $sql .= " WHERE " . $key . " = :" . $key;
                $and = true;
            }
        }
        $stmt = self::bindValues($data, $db, $columns, $sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    static function create(PDO $db, $table, $columns)
    {
        $sql = "CREATE TABLE " . $table . " ( ";
        $comma = false;
        foreach ($columns as $column) {
            if ($comma) {
                $sql .= ", " . $column->fieldname . " " . $column->fieldtype;
            } else {
                $sql .= $column->fieldname . " " . $column->fieldtype;
                $comma = true;
            }
        }
        $sql .= " )";
        $stmt = $db->prepare($sql);
        return $stmt->execute();
    }

    static function alter($data, PDO $db, $table, $action)
    {
        if ($action == "ADD") {
            $sql = "ALTER TABLE " . $table . " ADD " . $data->fieldname . " " . $data->fieldtype;
            $stmt = $db->prepare($sql);
            return $stmt->execute();
        } elseif ($action == "MODIFY") {
            $db->beginTransaction();
            $db->rollBack();
        } elseif ($action == "DROP") {
            try {
                $db->beginTransaction();
                $sql = "ALTER TABLE " . $table . " RENAME TO _" . $table . "_old";
                $stmt = $db->prepare($sql);
                $stmt->execute();

                $fields = self::getFields($db, $table);
                $columns = array();
                $col_names = array();
                foreach ($fields as $field) {
                    if ($field->fieldname == $data->fieldname) continue;
                    $columns[] = $field;
                    $col_names[] = $field->fieldname;
                }
                
                self::create($db, $table, $columns);
                self::insertFromTable($db, $col_names, $table, "_" . $table . "_old");
                return $db->commit();
            } catch (PDOException $e) {
                $db->rollBack();
            }
        }
        return false;
    }

    static function drop(PDO $db, $table)
    {
        $sql = "DROP TABLE " . $table;
        $stmt = $db->prepare($sql);
        return $stmt->execute();
    }

    static function list(PDO $db)
    {
        $tables = array();
        $sql = "SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name";
        $result = $db->query($sql);
        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $tables[] = $row['name'];
            }
        }
        return $tables;
    }

    static function getFields(PDO $db, $table)
    {
        $fields = array();
        $sql = "PRAGMA table_info(" . $table . ");";
        $result = $db->query($sql);
        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $class = new stdClass();
                $class->fieldname = $row["name"];
                $class->fieldtype = $row["type"];
                $fields[] = $class;
            }
        }
        return $fields;
    }

    /**
     * @param $data
     * @param PDO $db
     * @param $columns
     * @param string $sql
     * @return bool|PDOStatement
     */
    private static function bindValues($data, PDO $db, $columns, string $sql)
    {
        $stmt = $db->prepare($sql);
        foreach ($columns as $column) {
            $key = $column['db'];
            if (property_exists($data, $key))
                $stmt->bindValue(':' . $key, $data->$key);
        }
        return $stmt;
    }

    private static function insertFromTable(PDO $db, $columns, $new_table, $old_table)
    {
        $sql = "INSERT INTO " . $new_table . "(" . implode(", ", $columns) . ") " .
            "SELECT " . implode(", ", $columns) . " FROM " . $old_table;
        $stmt = $db->prepare($sql);
        return $stmt->execute();
    }
}