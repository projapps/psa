<?php
namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

class AdminController
{
    protected $container;
    protected $flash;
    protected $renderer;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->flash = $this->container->get(Messages::class);
        $this->renderer = $this->container->get(PhpRenderer::class);
    }

    public function add(Request $request, Response $response, $args) {
        if ($this->isAuthorised($request)) {
            $args = $this->setArgs($request, $args);
            $args['nav']['home'] = '';
            $args['nav']['about'] = '';
            return $this->renderer->render($response, 'admin.phtml', $args);
        } else {
            $errors['login'] = "User is not allowed to add table.";
            $this->flash->addMessage('errors', $errors);
            return $response->withHeader('Location', '/')->withStatus(302);
        }
    }

    private function isAuthorised(Request $request) {
        $session = $request->getAttribute('session');
        $username = isset($session['username']) ? $session['username'] : '';
        return ($username == 'admin');
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
        $args['errors'] = $this->flash->getMessage('errors');
        $args['inputs'] = $this->flash->getMessage('inputs');
        return $args;
    }
}