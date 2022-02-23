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

class CreateConfigTypeTable extends Migration
{
    final public function up(): void
    {
        Schema::create('config_type', function (Blueprint $table) {
            $table->addColumn('integer', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('integer', 'sn', ['unsigned' => true]);
            $table->addColumn('string', 'name', ['length' => 16])->default('');
            $table->addColumn('string', 'abb', ['length' => 4])->default('');
            $table->addColumn('bigInteger', 'screen_id', ['unsigned' => true]);
            $table->addColumn('bigInteger', 'workflow_id', ['unsigned' => true]);
            $table->addColumn('string', 'type', ['length' => 16])->default('');
            $table->addColumn('tinyInteger', 'default', ['unsigned' => true])->default('0');
            $table->addColumn('string', 'description', ['length' => 1024])->default('');
            $table->addColumn('tinyInteger', 'disabled', ['unsigned' => true])->default('0');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
        Db::select("
            INSERT INTO `config_type` (`id`, `project_key`, `sn`, `name`, `abb`, `screen_id`, `workflow_id`, `type`, `default`, `description`, `disabled`, `created_at`, `updated_at`)
            VALUES
                (1,'\$_sys_$',1499871082,'任务','T',1,1,'',1,'',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (2,'\$_sys_$',1499926509,'新功能','F',1,1,'',0,'',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (3,'\$_sys_$',1499926534,'缺陷','B',1,1,'',0,'',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (4,'\$_sys_$',1499926556,'改进','I',1,1,'',0,'',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (5,'\$_sys_$',1499926575,'子任务','S',1,1,'subtask',0,'',0,'2021-01-01 00:00:00','2021-01-01 00:00:00');
        ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('config_type');
    }
}
