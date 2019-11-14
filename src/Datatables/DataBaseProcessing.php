<?php
namespace App\Datatables;

use PDO;
use PDOException;

class DataBaseProcessing
{
    static function add ( $data, PDO $db, $table, $columns )
    {
        $pluck = ServerSideProcessing::pluck($columns, 'db');
        $sql = "INSERT INTO " . $table . "(" . implode(", ", $pluck) .
            ") VALUES(:" . implode(", :", $pluck) . ")";
        $stmt = self::bindValues($data, $db, $columns, $sql);
        return $stmt->execute();
    }

    static function edit ( $data, PDO $db, $table, $columns )
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

    static function delete() {}

    /**
     * @param $data
     * @param PDO $db
     * @param $columns
     * @param string $sql
     * @return bool|\PDOStatement
     */
    private static function bindValues($data, PDO $db, $columns, string $sql)
    {
        $stmt = $db->prepare($sql);
        foreach ($columns as $column) {
            $key = $column['db'];
            $stmt->bindValue(':' . $key, $data->$key);
        }
        return $stmt;
    }
}