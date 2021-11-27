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
    Router::get('project/stats', App\Controller\ProjectController::class . '::stats');
    Router::get('project/checkkey/{key}', App\Controller\ProjectController::class . '::checkKey');
    Router::post('project', App\Controller\ProjectController::class . '::store');
    Router::get('project', App\Controller\ProjectController::class . '::index');

    Router::get('mysetting', App\Controller\MySettingController::class . '::show');

    Router::get('syssetting', App\Controller\SysSettingController::class . '::show');
    Router::post('syssetting', App\Controller\SysSettingController::class . '::update');
    Router::post('syssetting/restpwd', App\Controller\SysSettingController::class . '::resetPwd');
    Router::post('syssetting/sendtestmail', App\Controller\SysSettingController::class . '::sendTestMail');
}, [
    'middleware' => [App\Middleware\AuthorizeMiddleware::class],
]);
