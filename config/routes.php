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
Router::post('/webhook/handle', [App\Controller\WebHookController::class, 'handle']);

Router::addGroup('/', function () {
    Router::get('myproject', [App\Controller\ProjectController::class, 'mine']);
    Router::get('project/recent', [App\Controller\ProjectController::class, 'recent']);
    Router::get('project/stats', [App\Controller\ProjectController::class, 'stats']);
    Router::get('project/checkkey/{key}', App\Controller\ProjectController::class . '::checkKey');
    Router::post('project', App\Controller\ProjectController::class . '::store');
    Router::put('project/{id:\d+}', App\Controller\ProjectController::class . '::update');
    Router::get('project/p_{key}', [App\Controller\ProjectController::class, 'show']);
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
        Router::get('group/search', [App\Controller\GroupController::class, 'search']);
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
    Router::post('role', [App\Controller\RoleController::class, 'store']);
    Router::put('role/{id:\d+}', [App\Controller\RoleController::class, 'update']);
    Router::delete('role/{id:\d+}', [App\Controller\RoleController::class, 'destroy']);
    Router::get('role/{id:\d+}/reset', [App\Controller\RoleController::class, 'reset']);
    Router::post('role/{id}/actor', [App\Controller\RoleController::class, 'setActor']);
    // Router::resource('role', 'RoleController');
    // Router::get('role/{id}/used', 'RoleController@viewUsedInProject');

    Router::get('state', App\Controller\StateController::class . '::index');
    Router::post('state', [App\Controller\StateController::class, 'store']);
    Router::put('state/{id:\d+}', [App\Controller\StateController::class, 'update']);
    Router::delete('state/{id:\d+}', [App\Controller\StateController::class, 'destroy']);
    // Route::resource('state', 'StateController');
    // Route::post('state/batch', 'StateController@handle');
    // Route::get('state/{id}/used', 'StateController@viewUsedInProject');

    Router::get('labels', [App\Controller\LabelController::class, 'index']);
    Router::post('labels', [App\Controller\LabelController::class, 'store']);
    Router::put('labels/{id:\d+}', [App\Controller\LabelController::class, 'update']);
    Router::post('labels/{id:\d+}/delete', [App\Controller\LabelController::class, 'delete']);

    Router::get('type', [App\Controller\TypeController::class, 'index']);
    Router::post('type', [App\Controller\TypeController::class, 'store']);
    Router::put('type/{id:\d+}', [App\Controller\TypeController::class, 'update']);
    Router::delete('type/{id:\d+}', [App\Controller\TypeController::class, 'destroy']);
    Router::post('type/batch', [App\Controller\TypeController::class, 'handle']);

    Router::get('workflow', [App\Controller\WorkflowController::class, 'index']);
    Router::post('workflow', [App\Controller\WorkflowController::class, 'store']);
    Router::put('workflow/{id:\d+}', [App\Controller\WorkflowController::class, 'update']);
    Router::get('workflow/{id:\d+}', [App\Controller\WorkflowController::class, 'info']);
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
    Router::get('summary', [App\Controller\SummaryController::class, 'index']);

    Router::get('issue', [App\Controller\IssueController::class, 'index']);
    Router::get('issue/options', [App\Controller\IssueController::class, 'getOptions']);
    Router::get('issue/{id:\d+}', [App\Controller\IssueController::class, 'show']);
    Router::put('issue/{id:\d+}', App\Controller\IssueController::class . '::update');
    Router::delete('issue/{id:\d+}', [App\Controller\IssueController::class, 'destroy']);
    Router::post('issue/{id:\d+}/assign', App\Controller\IssueController::class . '::setAssignee');
    Router::post('issue/{id:\d+}/reset', [App\Controller\IssueController::class, 'resetState']);
    Router::post('issue/batch', [App\Controller\IssueController::class, 'batchHandle']);
    Router::get('issue/{id:\d+}/comments', [App\Controller\CommentController::class, 'index']);
    Router::post('issue/{id:\d+}/comments', [App\Controller\CommentController::class, 'store']);
    Router::put('issue/{id:\d+}/comments/{commentId:\d+}', [App\Controller\CommentController::class, 'update']);
    Router::delete('issue/{id:\d+}/comments/{commentId:\d+}', [App\Controller\CommentController::class, 'destroy']);
    Router::get('issue/{id:\d+}/worklog', [App\Controller\WorklogController::class, 'index']);
    Router::post('issue/{id:\d+}/worklog', [App\Controller\WorklogController::class, 'store']);
    Router::put('issue/{id:\d+}/worklog/{worklogId:\d+}', [App\Controller\WorklogController::class, 'update']);
    Router::delete('issue/{id:\d+}/worklog/{worklogId:\d+}', [App\Controller\WorklogController::class, 'destroy']);
    Router::get('issue/{id:\d+}/history', [App\Controller\IssueController::class, 'getHistory']);
    Router::post('issue/{id:\d+}/watching', [App\Controller\IssueController::class, 'watch']);

    Router::post('issue', App\Controller\IssueController::class . '::store', [
        'options' => [
            App\Middleware\PrivilegeMiddleware::class => [
                Permission::CREATE_ISSUE,
            ],
        ],
    ]);

    Router::post('issue/filter', App\Controller\IssueController::class . '::saveIssueFilter');
    Router::get('issue/filters', App\Controller\IssueController::class . '::getIssueFilters');
    Router::get('issue/filters/reset', App\Controller\IssueController::class . '::resetIssueFilters');
    Router::post('issue/filters', App\Controller\IssueController::class . '::batchHandleFilters');
    Router::post('issue/{id:\d+}/workflow/{workflowId:\d+}', App\Controller\IssueController::class . '::doAction', [
        'options' => [
            App\Middleware\PrivilegeMiddleware::class => [
                Permission::EXEC_WORKFLOW,
            ],
        ],
    ]);

    Router::get('activity', [App\Controller\ActivityController::class, 'index']);

    Router::get('version', App\Controller\VersionController::class . '::index');
    Router::post('version', App\Controller\VersionController::class . '::store');
    Router::put('version/{id:\d+}', App\Controller\VersionController::class . '::update');
    Router::post('version/{id:\d+}/release', App\Controller\VersionController::class . '::release');
    Router::post('version/{id:\d+}/delete', App\Controller\VersionController::class . '::delete');
    Router::post('version/merge', App\Controller\VersionController::class . '::merge');

    Router::get('report/index', [App\Controller\ReportController::class, 'index']);
    Router::get('report/issues', [App\Controller\ReportController::class, 'getIssues']);
    Router::get('report/timetracks', [App\Controller\ReportController::class, 'getTimetracks']);
    Router::get('report/timetracks/issue/{id:\d+}', [App\Controller\ReportController::class, 'getTimetracksDetail']);

    Router::post('wiki', App\Controller\WikiController::class . '::create');
    Router::get('wiki/directory/{directory:\d+}', [App\Controller\WikiController::class, 'index']);
    Router::get('wiki/dirtree', App\Controller\WikiController::class . '::getDirTree');
    Router::get('wiki/search/path', App\Controller\WikiController::class . '::searchPath');
    Router::get('wiki/{id:\d+}', App\Controller\WikiController::class . '::show');
    Router::post('wiki/copy', App\Controller\WikiController::class . '::copy');
    Router::put('wiki/{id:\d+}', App\Controller\WikiController::class . '::update');
    Router::delete('wiki/{id:\d+}', App\Controller\WikiController::class . '::destroy');
    Router::get('wiki/{id:\d+}/checkin', App\Controller\WikiController::class . '::checkin');
    Router::get('wiki/{id:\d+}/checkout', App\Controller\WikiController::class . '::checkout');
    Router::post('wiki/{id:\d+}/favorite', App\Controller\WikiController::class . '::favorite');
    Router::get('wiki/{id:\d+}/dirs', App\Controller\WikiController::class . '::getDirChildren');
    Router::post('wiki/move', App\Controller\WikiController::class . '::move');

    Router::get('kanban', [App\Controller\BoardController::class, 'index']);
    Router::post('kanban', [App\Controller\BoardController::class, 'store']);
    Router::put('kanban/{id:\d+}', [App\Controller\BoardController::class, 'update']);
    Router::get('kanban/{id:\d+}/access', [App\Controller\BoardController::class, 'recordAccess']);

    //    后续优化为上传OSS
    //    Router::post('wiki/{id:\d+}/upload', App\Controller\WikiController::class . '::upload');
    //    Router::get('wiki/{id}/file/{fid}/download', App\Controller\WikiController::class . '::download');
    //    Router::get('wiki/{id}/download', App\Controller\WikiController::class . '::download2');
    //    Router::delete('wiki/{id}/file/{fid}',  App\Controller\WikiController::class . '::remove');

    Router::get('issue/{id:\d+}/wfactions', [App\Controller\IssueController::class, 'wfactions']);

    Router::get('team', App\Controller\RoleController::class . '::index');

    Router::get('workflow/{id:\d+}/preview', App\Controller\WorkflowController::class . '::preview');

    Router::post('file', [App\Controller\FileController::class, 'upload']);
    Router::get('file/{id:\d+}/thumbnail', [App\Controller\FileController::class, 'thumbnail']);
    Router::get('file/{id:\d+}[/{name}]', [App\Controller\FileController::class, 'download']);
    Router::delete('file/{id:\d+}', [App\Controller\FileController::class, 'delete']);
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
