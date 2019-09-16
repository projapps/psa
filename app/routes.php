<?php

use App\Controllers\DataController;
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
        $session = $request->getAttribute('session');
        $tablename = $session['tablename'] == FALSE ? 'psa_demo' : $session['tablename'];
        $routeParser = $app->getRouteCollector()->getRouteParser();
        $menu = [
            'home' => $routeParser->urlFor('home'),
            'about' => $routeParser->urlFor('about'),
            'list_data' => $routeParser->urlFor('list_data', [ 'table' => $tablename ]),
            'add_data' => $routeParser->urlFor('add_data', [ 'table' => $tablename ]),
            'edit_data' => $routeParser->urlFor('edit_data', [ 'table' => $tablename ]),
            'delete_data' => $routeParser->urlFor('delete_data', [ 'table' => $tablename ])
        ];
        $request = $request->withAttribute('menu', $menu);
        return $handler->handle($request);
    });

    $app->group('/data', function (Group $group) {
        $group->get('/list/{table}', DataController::class . ':list')->setName('list_data');
        $group->post('/add/{table}', DataController::class . ':add')->setName('add_data');
        $group->put('/edit/{table}', DataController::class . ':edit')->setName('edit_data');
        $group->delete('/delete/{table}', DataController::class . ':delete')->setName('delete_data');
    });
};