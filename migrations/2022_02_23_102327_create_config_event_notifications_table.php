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

class CreateConfigEventNotificationsTable extends Migration
{
    final public function up(): void
    {
        Schema::create('config_event_notifications', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('bigInteger', 'event_id', ['unsigned' => true])->comment('事件ID');
            $table->addColumn('json', 'notifications', [])->comment('通知');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');

            $table->index(['project_key'], 'INDEX_PROJECT_KEY');
        });
        Db::select("
            INSERT INTO `config_event_notifications` (`id`, `project_key`, `event_id`, `notifications`, `created_at`, `updated_at`)
            VALUES
                (1,'\$_sys_$',8,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (2,'\$_sys_$',1,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (3,'\$_sys_$',2,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (4,'\$_sys_$',3,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (5,'\$_sys_$',4,'[\"reporter\", \"assignee\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (6,'\$_sys_$',5,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (7,'\$_sys_$',6,'[\"reporter\", \"project_principal\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (8,'\$_sys_$',7,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (9,'\$_sys_$',9,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (10,'\$_sys_$',10,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (11,'\$_sys_$',11,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (12,'\$_sys_$',12,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (13,'\$_sys_$',13,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (14,'\$_sys_$',14,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (15,'\$_sys_$',15,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (16,'\$_sys_$',16,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (17,'\$_sys_$',17,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00');
        ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('config_event_notifications');
    }
}
