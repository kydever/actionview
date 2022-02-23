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

class CreateSprintTable extends Migration
{
    final public function up(): void
    {
        Schema::create('sprint', function (Blueprint $table) {
            $table->addColumn('integer', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('')->comment('项目key');
            $table->addColumn('integer', 'no', ['unsigned' => true])->default('0');
            $table->addColumn('string', 'name', ['length' => 128])->default('')->comment('名称');
            $table->addColumn('string', 'status', ['length' => 16])->default('');
            $table->addColumn('integer', 'start_time', ['unsigned' => true])->default('0')->comment('开始时间');
            $table->addColumn('integer', 'complete_time', ['unsigned' => true])->default('0')->comment('完成时间');
            $table->addColumn('string', 'description', ['length' => 1024])->default('')->comment('描述');
            $table->addColumn('integer', 'real_complete_time', ['unsigned' => true])->default('0')->comment('真实完成时间');
            $table->addColumn('json', 'issues', []);
            $table->addColumn('json', 'origin_issues', []);
            $table->addColumn('json', 'completed_issues', []);
            $table->addColumn('json', 'incompleted_issues', []);
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('sprint');
    }
}
