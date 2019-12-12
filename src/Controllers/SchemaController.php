<?php
namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class SchemaController
{
    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
}