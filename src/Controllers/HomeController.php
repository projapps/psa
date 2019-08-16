<?php
namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

class HomeController
{
    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function home(Request $request, Response $response, $args) {
        return $this->container->get(PhpRenderer::class)->render($response, 'index.phtml', $args);
    }

    public function about(Request $request, Response $response, $args) {
        return $this->container->get(PhpRenderer::class)->render($response, 'about.phtml', $args);
    }
}