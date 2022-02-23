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

class CreateEpicTable extends Migration
{
    final public function up(): void
    {
        Schema::create('epic', function (Blueprint $table) {
            $table->addColumn('integer', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'name', ['length' => 1024])->default('')->comment('名称');
            $table->addColumn('string', 'bgColor', ['length' => 128])->default('')->comment('背景色');
            $table->addColumn('string', 'description', ['length' => 1024])->default('')->comment('描述');
            $table->addColumn('string', 'project_key', ['length' => 32])->default('')->comment('项目key');
            $table->addColumn('string', 'sn', ['length' => 16])->default('')->comment('版本号');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('epic');
    }
}
