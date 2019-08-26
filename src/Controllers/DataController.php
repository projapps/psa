<?php
namespace App\Controllers;

use App\Datatables\ServerSideProcessing;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DataController
{
    protected $container;
    protected $db;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->db = $this->container->get(PDO::class);
    }

    public function list(Request $request, Response $response, $args) {
        $columns = $this->getColumns($args['table']);
        $payload = json_encode(
            ServerSideProcessing::simple(
                $request->getParsedBody(),
                $this->db,
                $args['table'],
                'id',
                $columns)
        );
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function getColumns($table) {
        $columns = array();
        $fields = array();
        $sql = "PRAGMA table_info(" . $table . ");";
        $result = $this->db->query($sql);
        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $fields[] = $row;
            }
        }
        $counter = 0;
        foreach ($fields as $field) {
            $columns[] = array('db' => $field['name'], 'dt' => $counter);
            $counter++;
        }
        return $columns;
    }
}