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
        $columns = DataBaseProcessing::getFields($this->db, $args['table']);
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
        if ($this->isAuthorised($request)) {
            $data = json_decode($request->getBody());
            if (DataBaseProcessing::alter($data, $this->db, $args['table'], "ADD")) {
                $result['fieldname'] = $data->fieldname;
                $result['fieldtype'] = $data->fieldtype;
                $payload = json_encode($result);
            } else {
                $response = $response->withStatus(500);
                $payload = $this->getErrorsPayload('Internal Server Error', "Unable to add column to schema.");
            }
        } else {
            $response = $response->withStatus(401);
            $payload = $this->getErrorsPayload('Unauthorized', "User is not allowed to add to schema.");
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function remove(Request $request, Response $response, $args) {
        if ($this->isAuthorised($request)) {
            $data = json_decode($request->getBody());
            if (DataBaseProcessing::alter($data, $this->db, $args['table'], "DROP")) {
                $result['fieldname'] = $data->fieldname;
                $result['fieldtype'] = $data->fieldtype;
                $payload = json_encode($result);
            } else {
                $response = $response->withStatus(500);
                $payload = $this->getErrorsPayload('Internal Server Error', "Unable to remove column from schema.");
            }
        } else {
            $response = $response->withStatus(401);
            $payload = $this->getErrorsPayload('Unauthorized', "User is not allowed to remove from schema.");
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

    private function isAuthorised(Request $request) {
        $session = $request->getAttribute('session');
        $username = isset($session['username']) ? $session['username'] : '';
        return ($username == 'admin');
    }
}