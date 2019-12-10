<?php
namespace App\Controllers;

use App\Datatables\DataBaseProcessing;
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

    public function add(Request $request, Response $response, $args) {
        if ($this->isAuthorised($request, $args['table'])) {
            $data = json_decode($request->getBody());
            if (property_exists($data, "password"))
                $data->password = md5($data->password);
            $columns = $this->getColumns($args['table'], 'id');
            if (DataBaseProcessing::add($data, $this->db, $args['table'], $columns)) {
                $result = $this->buildResult($columns, $data);
                $result['id'] = $this->db->lastInsertId();
                $payload = json_encode($result);
            } else {
                $response = $response->withStatus(500);
                $payload = $this->getErrorsPayload('Internal Server Error', "Unable to insert into database.");
            }
        } else {
            $response = $response->withStatus(401);
            $payload = $this->getErrorsPayload('Unauthorized', "User is not allowed to add data.");
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function edit(Request $request, Response $response, $args) {
        if ($this->isAuthorised($request, $args['table'])) {
            $data = json_decode($request->getBody());
            if (property_exists($data, "password"))
                $data->password = md5($data->password);
            $columns = $this->getColumns($args['table']);
            if (DataBaseProcessing::edit($data, $this->db, $args['table'], $columns)) {
                $result = $this->buildResult($columns, $data);
                $payload = json_encode($result);
            } else {
                $response = $response->withStatus(500);
                $payload = $this->getErrorsPayload('Internal Server Error', "Unable to update database.");
            }
        } else {
            $response = $response->withStatus(401);
            $payload = $this->getErrorsPayload('Unauthorized', "User is not allowed to edit data.");
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, $args) {
        if ($this->isAuthorised($request, $args['table'])) {
            $data = json_decode($request->getBody());
            $columns = $this->getColumns($args['table']);
            if (DataBaseProcessing::delete($data, $this->db, $args['table'])) {
                $result = $this->buildResult($columns, $data);
                $payload = json_encode($result);
            } else {
                $response = $response->withStatus(500);
                $payload = $this->getErrorsPayload('Internal Server Error', "Unable to delete from database");
            }
        } else {
            $response = $response->withStatus(401);
            $payload = $this->getErrorsPayload('Unauthorized', "User is not allowed to edit data.");
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function getErrorsPayload($error_type, $error_message) {
        $errors = array();
        $errors[$error_type][0] = $error_message;
        $responseJson = array();
        $responseJson['errors'] = $errors;
        return json_encode($responseJson);
    }

    private function isAuthorised(Request $request, $table) {
        $session = $request->getAttribute('session');
        $username = isset($session['username']) ? $session['username'] : '';
        $tablename = isset($session['tablename']) ? $session['tablename'] : '';
        return ($username == 'admin' || $tablename == $table);
    }

    private function getColumns($table, $primaryKey = null) {
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
            if ($primaryKey != null && $field['name'] == $primaryKey) continue;
            $columns[] = array('db' => $field['name'], 'dt' => $counter);
            $counter++;
        }
        return $columns;
    }

    /**
     * @param array $columns
     * @param $data
     * @return array
     */
    private function buildResult(array $columns, $data): array
    {
        $result = array();
        foreach ($columns as $column) {
            $key = $column['db'];
            $result[$key] = $data->$key;
        }
        return $result;
    }
}