<?php

use App\Controllers\HomeController;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->group('', function (Group $group) {
        $group->get('/', HomeController::class . ':home')->setName('home');
        $group->get('/about', HomeController::class . ':about')->setName('about');
    });
};