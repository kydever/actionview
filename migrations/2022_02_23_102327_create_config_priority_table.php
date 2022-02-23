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

class CreateConfigPriorityTable extends Migration
{
    final public function up(): void
    {
        Schema::create('config_priority', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('string', 'color', ['length' => 8])->default('')->comment('颜色');
            $table->addColumn('string', 'description', ['length' => 256])->default('')->comment('描述');
            $table->addColumn('string', 'key', ['length' => 16])->default('')->comment('KEY');
            $table->addColumn('string', 'name', ['length' => 32])->default('')->comment('名字');
            $table->addColumn('string', 'sn', ['length' => 16])->default('')->comment('版本');
            $table->addColumn('tinyInteger', 'default', ['unsigned' => true])->default('0');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
        Db::select("
            INSERT INTO `config_priority` (`id`, `project_key`, `color`, `description`, `key`, `name`, `sn`, `default`, `created_at`, `updated_at`)
            VALUES
                (1,'\$_sys_$','#cc0000','','Blocker','致命','1.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (2,'\$_sys_$','#ff0000','','Critical','严重','2.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (3,'\$_sys_$','#009900','','Major','重要','3.0',1,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (4,'\$_sys_$','#006600','','Minor','轻微','4.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (5,'\$_sys_$','#003300','','Trivial','微小','5.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00');
        ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('config_priority');
    }
}
