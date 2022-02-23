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
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

class CreateConfigEventsTable extends Migration
{
    final public function up(): void
    {
        Schema::create('config_events', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('string', 'key', ['length' => 32])->default('')->comment('事件KEY');
            $table->addColumn('string', 'apply', ['length' => 16])->default('')->comment('APPLY');
            $table->addColumn('string', 'name', ['length' => 32])->default('')->comment('事件名');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');

            $table->index(['project_key'], 'INDEX_PROJECT_KEY');
        });
        Db::select("
            INSERT INTO `config_events` (`id`, `project_key`, `key`, `apply`, `name`, `created_at`, `updated_at`)
            VALUES
                (1,'\$_sys_$','create_issue','','问题已创建','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (2,'\$_sys_$','edit_issue','','问题被编辑','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (3,'\$_sys_$','del_issue','','问题已删除','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (4,'\$_sys_$','add_comments','','添加备注','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (5,'\$_sys_$','edit_comments','','备注被编辑','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (6,'\$_sys_$','del_comments','','备注被删除','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (7,'\$_sys_$','add_worklog','','添加工作日志','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (8,'\$_sys_$','edit_worklog','','编辑工作日志','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (9,'\$_sys_$','del_worklog','','删除工作日志','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (10,'\$_sys_$','resolve_issue','workflow','问题已解决','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (11,'\$_sys_$','close_issue','workflow','问题已关闭','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (12,'\$_sys_$','start_progress_issue','workflow','开始解决问题','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (13,'\$_sys_$','stop_progress_issue','workflow','停止解决问题','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (14,'\$_sys_$','assign_issue','workflow','问题已分配','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (15,'\$_sys_$','normal','workflow','一般事件','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (16,'\$_sys_$','move_issue','','问题被移动','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (17,'\$_sys_$','reopen_issue','workflow','重新打开问题','2021-01-01 00:00:00','2021-01-01 00:00:00');
        ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('config_events');
    }
}
