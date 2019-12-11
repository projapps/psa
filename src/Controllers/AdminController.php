<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AdminController
{
    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function add(Request $request, Response $response, $args) {
        if ($this->isAuthorised($request)) {} else {}
    }

    private function isAuthorised(Request $request) {
        $session = $request->getAttribute('session');
        $username = isset($session['username']) ? $session['username'] : '';
        return ($username == 'admin');
    }
}