<?php
namespace App\Controllers;

use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

class HomeController
{
    protected $container;
    protected $db;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->db = $this->container->get(PDO::class);
    }

    public function home(Request $request, Response $response, $args) {
        $args = $this->setArgs($request, $args);
        return $this->container->get(PhpRenderer::class)->render($response, 'index.phtml', $args);
    }

    public function about(Request $request, Response $response, $args) {
        $args = $this->setArgs($request, $args);
        return $this->container->get(PhpRenderer::class)->render($response, 'about.phtml', $args);
    }

    private function listTables() {
        $tables = array();
        $sql = "SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name";
        $result = $this->db->query($sql);
        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $tables[] = $row['name'];
            }
        }
        return $tables;
    }

    private function fieldData($tablename) {
        $fields = array();
        $sql = "PRAGMA table_info(" . $tablename . ");";
        $result = $this->db->query($sql);
        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $fields[] = $row;
            }
        }
        return $fields;
    }

    /**
     * @param Request $request
     * @param $args
     * @return mixed
     */
    public function setArgs(Request $request, $args)
    {
        $session = $request->getAttribute('session');
        $args['username'] = $session['username'] == FALSE ? '' : $session['username'];
        $args['tablename'] = $session['tablename'] == FALSE ? 'psa_demo' : $session['tablename'];
        $args['menu'] = $request->getAttribute('menu');
        $args['menu']['list_data'] = 'data/list/' . $args['tablename'];
        $args['version'] = $this->db->getAttribute(PDO::ATTR_SERVER_VERSION);
        $args['tables'] = $this->listTables();
        $args['fields'] = $this->fieldData($args['tablename']);
        return $args;
    }
}