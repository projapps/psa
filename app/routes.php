<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\DataController;
use App\Controllers\HomeController;
use App\Controllers\SchemaController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $mw = function (Request $request, RequestHandler $handler) use ($app) {
        $session = $request->getAttribute('session');
        $tablename = isset($session['tablename']) ? $session['tablename'] : 'psa_demo';
        $routeParser = $app->getRouteCollector()->getRouteParser();
        $menu = [
            'home' => $routeParser->urlFor('home'),
            'about' => $routeParser->urlFor('about'),
            'login' => $routeParser->urlFor('login'),
            'logout' => $routeParser->urlFor('logout'),
            'list_data' => $routeParser->urlFor('list_data', [ 'table' => $tablename ]),
            'add_data' => $routeParser->urlFor('add_data', [ 'table' => $tablename ]),
            'edit_data' => $routeParser->urlFor('edit_data', [ 'table' => $tablename ]),
            'delete_data' => $routeParser->urlFor('delete_data', [ 'table' => $tablename ]),
            'add_admin' => $routeParser->urlFor('add_admin'),
            'edit_admin' => $routeParser->urlFor('edit_admin'),
            'add_table' => $routeParser->urlFor('add_table'),
            'remove_table' => $routeParser->urlFor('remove_table'),
            'list_schema' => $routeParser->urlFor('list_schema', [ 'table' => '' ]),
            'add_schema' => $routeParser->urlFor('add_schema', [ 'table' => '' ]),
            'edit_schema' => $routeParser->urlFor('edit_schema', [ 'table' => '' ]),
            'remove_schema' => $routeParser->urlFor('remove_schema', [ 'table' => '' ])
        ];
        $request = $request->withAttribute('menu', $menu);
        return $handler->handle($request);
    };
    
    $app->group('', function (Group $group) {
        $group->get('/', HomeController::class . ':home')->setName('home');
        $group->get('/about', HomeController::class . ':about')->setName('about');
        $group->post('/login', HomeController::class . ':login')->setName('login');
        $group->any('/logout', HomeController::class . ':logout')->setName('logout');
    })->add($mw);

    $app->group('/data', function (Group $group) {
        $group->get('/list/{table}', DataController::class . ':get')->setName('list_data');
        $group->post('/create/{table}', DataController::class . ':add')->setName('add_data');
        $group->put('/replace/{table}', DataController::class . ':edit')->setName('edit_data');
        $group->delete('/delete/{table}', DataController::class . ':remove')->setName('delete_data');
    });

    $app->group('/admin', function (Group $group) {
        $group->get('/new', AdminController::class . ':new')->setName('add_admin');
        $group->get('/open/[{table}]', AdminController::class . ':open')->setName('edit_admin');
        $group->post('/add', AdminController::class . ':add')->setName('add_table');
        $group->delete('/remove/[{table}]', AdminController::class . ':remove')->setName('remove_table');
    })->add($mw);

    $app->group('/schema', function (Group $group) {
        $group->get('/list/{table}', SchemaController::class . ':get')->setName('list_schema');
        $group->post('/create/{table}', SchemaController::class . ':add')->setName('add_schema');
        $group->put('/replace/{table}', SchemaController::class . ':edit')->setName('edit_schema');
        $group->delete('/delete/{table}', SchemaController::class . ':remove')->setName('remove_schema');
    });
};