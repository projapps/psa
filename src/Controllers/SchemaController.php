<?php
namespace App\Controllers;

use App\Datatables\DataBaseProcessing;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class SchemaController
{
    protected $container;
    protected $db;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->db = $this->container->get(PDO::class);
    }

    public function get(Request $request, Response $response, $args) {
        $columns = $this->getFields($args['table']);
        $body = $request->getParsedBody();
        $payload = json_encode(array(
            "draw"            => isset ( $body['draw'] ) ? intval( $body['draw'] ) : 0,
            "recordsTotal"    => count( $columns ),
            "recordsFiltered" => count( $columns ),
            "data"            => $columns
        ));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function add(Request $request, Response $response, $args) {
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function isAuthorised(Request $request) {
        $session = $request->getAttribute('session');
        $username = isset($session['username']) ? $session['username'] : '';
        return ($username == 'admin');
    }

    private function getFields($table, $primaryKey = null) {
        $fields = array();
        $sql = "PRAGMA table_info(" . $table . ");";
        $result = $this->db->query($sql);
        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $class = new \stdClass();
                $class->fieldname = $row["name"]; 
                $class->fieldtype = $row["type"];
                $fields[] = $class;
            }
        }
        return $fields;
    }
}