<?php

use App\Controllers\HomeController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->group('', function (Group $group) {
        $group->get('/', HomeController::class . ':home')->setName('home');
        $group->get('/about', HomeController::class . ':about')->setName('about');
    })->add(function (Request $request, RequestHandler $handler) use ($app) {
        $routeParser = $app->getRouteCollector()->getRouteParser();
        $menu = [
            'home' => $routeParser->urlFor('home'),
            'about' => $routeParser->urlFor('about')
        ];
        $request = $request->withAttribute('menu', $menu);
        return $handler->handle($request);
    });
};