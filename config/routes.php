<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController::index');

Router::post('/session', App\Controller\SessionController::class . '::create');
Router::get('/session', App\Controller\SessionController::class . '::getSession');
Router::delete('/session', App\Controller\SessionController::class . '::destroy');

Router::post('/user/login', App\Controller\UserController::class . '::login');
Router::post('/user/register', App\Controller\UserController::class . '::register');

Router::addGroup('/', function () {
    Router::get('myproject', App\Controller\ProjectController::class . '::mine');
    Router::get('project/recent', App\Controller\ProjectController::class . '::recent');
    Router::get('project/checkkey/{key}', App\Controller\ProjectController::class . '::checkKey');

    Router::get('mysetting', App\Controller\MySettingController::class . '::show');
}, [
    'middleware' => [App\Middleware\AuthorizeMiddleware::class],
]);
