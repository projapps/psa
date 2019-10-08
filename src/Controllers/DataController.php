<?php
namespace App\Controllers;

use App\Datatables\ServerSideProcessing;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

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

    public function add(Request $request, Response $response, $args) {
        if ($this->isAuthorised($request, $args['table'])) {
            $data = json_decode($request->getBody());
            $columns = $this->getColumns($args['table']);
            $result = array();
            foreach ($columns as $column) {
                $key = $column['db'];
                $result[$column['db']] = $data->$key;
            }
            $payload = json_encode($result);
        } else {
            $response->withStatus(401);
            $errors = array();
            $errors['Unauthorized'] = "User is not allowed to add data.";
            $payload = json_encode($errors);
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function edit(Request $request, Response $response, $args) {}

    public function delete(Request $request, Response $response, $args) {}

    private function isAuthorised(Request $request, $table) {
        $session = $request->getAttribute('session');
        return ($session['username'] == 'admin' || $session['tablename'] == $table);
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