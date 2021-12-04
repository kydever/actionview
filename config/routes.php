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
use App\Constants\Permission;
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
    Router::put('project/{id:\d+}', App\Controller\ProjectController::class . '::update');
    Router::get('project/p_{key}', App\Controller\ProjectController::class . '::show');
    Router::get('project/{id:\d+}/createindex', App\Controller\ProjectController::class . '::createIndex');

    Router::get('mysetting', App\Controller\MySettingController::class . '::show');
    Router::post('mysetting/avatar', App\Controller\MySettingController::class . '::setAvatar');
    Router::post('mysetting/notify', App\Controller\MySettingController::class . '::setNotifications');
    Router::post('mysetting/account', App\Controller\MySettingController::class . '::updAccounts');
    Router::post('mysetting/resetpwd', App\Controller\MySettingController::class . '::resetPwd');
    // Route::post('mysetting/favorite', 'MysettingController@setFavorites');

    Router::get('user/search', App\Controller\UserController::class . '::search');

    Router::get('mygroup', App\Controller\GroupController::class . '::mygroup');
    Router::post('group', App\Controller\GroupController::class . '::store');
    Router::put('group/{id:\d+}', App\Controller\GroupController::class . '::update');
    Router::delete('group/{id:\d+}', App\Controller\GroupController::class . '::destroy');

    Router::get('getavatar', App\Controller\FileController::class . '::getAvatar');

    Router::addGroup('', function () {
        Router::get('project', App\Controller\ProjectController::class . '::index');
        Router::get('project/options', App\Controller\ProjectController::class . '::getOptions');
        Router::post('project/batch/status', App\Controller\ProjectController::class . '::updMultiStatus');
        Router::post('project/batch/createindex', App\Controller\ProjectController::class . '::createMultiIndex');
        Router::delete('project/{id}', App\Controller\ProjectController::class . '::destroy');

        Router::get('syssetting', App\Controller\SysSettingController::class . '::show');
        Router::post('syssetting', App\Controller\SysSettingController::class . '::update');
        Router::post('syssetting/restpwd', App\Controller\SysSettingController::class . '::resetPwd');
        Router::post('syssetting/sendtestmail', App\Controller\SysSettingController::class . '::sendTestMail');

        Router::get('user', App\Controller\UserController::class . '::index');
        Router::post('user', App\Controller\UserController::class . '::store');

        Router::get('group', App\Controller\GroupController::class . '::index');
    }, [
        'middleware' => [App\Middleware\PrivilegeMiddleware::class],
        'options' => [
            App\Middleware\PrivilegeMiddleware::class => [
                'sys_admin',
            ],
        ],
    ]);
}, [
    'middleware' => [App\Middleware\AuthorizeMiddleware::class],
]);

Router::addGroup('/project/{project_key}/', function () {
    // Router::get('role/{id}/reset', 'RoleController@reset');
    Router::post('role/{id:\d+}/permissions', App\Controller\RoleController::class . '::setPermissions');
    // Router::post('role/{id}/groupactor', 'RoleController@setGroupActor');
    Router::get('role', App\Controller\RoleController::class . '::index');
    Router::post('role/{id}/actor', App\Controller\RoleController::class . '::setActor');
// Router::resource('role', 'RoleController');
    // Router::get('role/{id}/used', 'RoleController@viewUsedInProject');
}, [
    'middleware' => [
        App\Middleware\AuthorizeMiddleware::class,
        App\Middleware\ProjectAuthMiddleware::class,
        App\Middleware\PrivilegeMiddleware::class,
    ],
    'options' => [
        App\Middleware\PrivilegeMiddleware::class => [
            Permission::MANAGE_PROJECT,
        ],
    ],
]);

Router::addGroup('/project/{project_key}/', function () {
    Router::get('summary', App\Controller\SummaryController::class . '::index');

    Router::get('issue', App\Controller\IssueController::class . '::index');
    Router::get('issue/options', App\Controller\IssueController::class . '::getOptions');
    Router::get('issue/{id:\d+}', App\Controller\IssueController::class . '::show');
    Router::put('issue/{id:\d+}', App\Controller\IssueController::class . '::update');
    Router::post('issue/{id:\d+}/assign', App\Controller\IssueController::class . '::setAssignee');
    Router::post('issue/{id:\d+}/reset', App\Controller\IssueController::class . '::resetState');

    Router::post('issue', App\Controller\IssueController::class . '::store', [
        'options' => [
            App\Middleware\PrivilegeMiddleware::class => [
                Permission::CREATE_ISSUE,
            ],
        ],
    ]);

    Router::get('version', App\Controller\VersionController::class . '::index');
    Router::post('version', App\Controller\VersionController::class . '::store');
    Router::put('version/{id:\d+}', App\Controller\VersionController::class . '::update');
    Router::post('version/{id:\d+}/release', App\Controller\VersionController::class . '::release');
    Router::post('version/{id:\d+}/delete', App\Controller\VersionController::class . '::delete');

    // Route::post('version/merge', 'VersionController@merge');
    // Route::post('version/{id}/delete', 'VersionController@delete');
    // Route::resource('version', 'VersionController');

    // Route::get('wiki/dirtree', 'WikiController@getDirTree');
    // Route::get('wiki/{id}/dirs', 'WikiController@getDirChildren');
    // Route::post('wiki/{id}/favorite', 'WikiController@favorite');
    // Route::post('wiki/{id}/upload', 'WikiController@upload');
    // Route::get('wiki/{id}/download', 'WikiController@download2');
    // Route::get('wiki/{id}/file/{fid}/download', 'WikiController@download');
    // Route::delete('wiki/{id}/file/{fid}', 'WikiController@remove');
    // Route::get('wiki/directory/{id}', 'WikiController@index');
    // Route::get('wiki/search/path', 'WikiController@searchPath');
    // Route::get('wiki/{id}', 'WikiController@show');
    // Route::post('wiki/move', 'WikiController@move');
    // Route::post('wiki/copy', 'WikiController@copy');
    Router::post('wiki', App\Controller\WikiController::class . '::create');
    // Route::put('wiki/{id}', 'WikiController@update');
    // Route::get('wiki/{id}/checkin', 'WikiController@checkin');
    // Route::get('wiki/{id}/checkout', 'WikiController@checkout');
    // Route::delete('wiki/{id}', 'WikiController@destroy');

    Router::get('team', App\Controller\RoleController::class . '::index');
}, [
    'middleware' => [
        App\Middleware\AuthorizeMiddleware::class,
        App\Middleware\ProjectAuthMiddleware::class,
        App\Middleware\PrivilegeMiddleware::class,
    ],
    'options' => [
        App\Middleware\PrivilegeMiddleware::class => [
            Permission::VIEW_PROJECT,
        ],
    ],
]);
