<?php
namespace App\Datatables;

use PDO;
use PDOException;

class DataBaseProcessing
{
    static function add ( $data, $db, $table, $columns )
    {
        $pluck = ServerSideProcessing::pluck($columns, 'db');
        $sql = "INSERT INTO " . $table . "(" . implode(", ", $pluck) . ") VALUES()";
        return $sql;
    }
}