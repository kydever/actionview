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

Router::addGroup('/', function () {
    Router::get('mysetting', App\Controller\MySettingController::class . '::show');
}, [
    'middleware' => [App\Middleware\AuthorizeMiddleware::class],
]);
