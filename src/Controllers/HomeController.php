<?php
namespace App\Controllers;

use App\Validators\RespectValidator;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as Validator;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

class HomeController
{
    protected $container;
    protected $db;
    protected $flash;
    protected $renderer;
    protected $validator;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->db = $this->container->get(PDO::class);
        $this->flash = $this->container->get(Messages::class);
        $this->renderer = $this->container->get(PhpRenderer::class);
        $this->validator = $this->container->get(RespectValidator::class);
    }

    public function home(Request $request, Response $response, $args) {
        $args = $this->setArgs($request, $args);
        $args['nav']['home'] = 'active';
        $args['nav']['about'] = '';
        return $this->renderer->render($response, 'index.phtml', $args);
    }

    public function about(Request $request, Response $response, $args) {
        $args = $this->setArgs($request, $args);
        $args['nav']['home'] = '';
        $args['nav']['about'] = 'active';
        return $this->renderer->render($response, 'about.phtml', $args);
    }

    public function login(Request $request, Response $response, $args) {
        $validation = $this->validator->validate($request, [
            'username' => Validator::noWhitespace()->notEmpty(),
            'password' => Validator::noWhitespace()->notEmpty()
        ]);
        if ($validation->failed()) {
            $errors = $validation->getErrors();
            foreach ($errors as $key => $value) {
                $this->flash->addMessage($key, $value);
            }
        }
        return $response->withHeader('Location', '/')->withStatus(302);
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
        $args['username'] = isset($session['username']) ? $session['username'] : '';
        $args['tablename'] = isset($session['tablename']) ? $session['tablename'] : 'psa_demo';
        $args['menu'] = $request->getAttribute('menu');
        $args['version'] = $this->db->getAttribute(PDO::ATTR_SERVER_VERSION);
        $args['tables'] = $this->listTables();
        $args['fields'] = $this->fieldData($args['tablename']);
        $args['errors'] = $this->flash->getMessages();
        return $args;
    }
}