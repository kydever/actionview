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

class CreateConfigStateTable extends Migration
{
    final public function up(): void
    {
        Schema::create('config_state', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('string', 'key', ['length' => 32])->default('');
            $table->addColumn('string', 'name', ['length' => 8])->default('');
            $table->addColumn('string', 'sn', ['length' => 16])->default('');
            $table->addColumn('string', 'category', ['length' => 16])->default('');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
        Db::select("
            INSERT INTO `config_state` (`id`, `project_key`, `key`, `name`, `sn`, `category`, `created_at`, `updated_at`)
            VALUES
                (1,'\$_sys_$','Open','开始','1.0','new','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (2,'\$_sys_$','In Progess','进行中','2.0','inprogress','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (3,'\$_sys_$','Resolved','已完成','3.0','completed','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (4,'\$_sys_$','Reopened','重新打开','4.0','new','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (5,'\$_sys_$','Closed','关闭','5.0','completed','2021-01-01 00:00:00','2021-01-01 00:00:00');
        ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('config_state');
    }
}
