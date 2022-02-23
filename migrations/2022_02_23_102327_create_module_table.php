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

class CreateModuleTable extends Migration
{
    final public function up(): void
    {
        Schema::create('module', function (Blueprint $table) {
            $table->addColumn('integer', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'name', ['length' => 1024])->default('')->comment('名称');
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('json', 'principal', [])->comment('负责人');
            $table->addColumn('json', 'default_assignee', [])->comment('默认指定人');
            $table->addColumn('json', 'creator', [])->comment('创建者');
            $table->addColumn('string', 'description', ['length' => 1024])->default('')->comment('描述');
            $table->addColumn('string', 'sn', ['length' => 16])->default('')->comment('版本');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('module');
    }
}
