<?php
namespace App\Datatables;

use PDO;
use PDOException;

class DataBaseProcessing
{
    static function add ( $data, $db, $table, $columns )
    {
        $pluck = ServerSideProcessing::pluck($columns, 'db');
        $sql = "INSERT INTO " . $table . "(" . implode(", ", $pluck) .
            ") VALUES(:" . implode(", :", $pluck) . ")";
        $stmt = $db->prepare($sql);
        foreach ($columns as $column) {
            $key = $column['db'];
            $stmt->bindValue(':' . $key, $data->$key);
        }
        $stmt->execute();
        return $db->lastInsertId();
    }
    
}