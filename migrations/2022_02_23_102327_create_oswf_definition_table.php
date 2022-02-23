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

class CreateOswfDefinitionTable extends Migration
{
    final public function up(): void
    {
        Schema::create('oswf_definition', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('string', 'name', ['length' => 32])->default('');
            $table->addColumn('bigInteger', 'latest_modifier', ['unsigned' => true]);
            $table->addColumn('dateTime', 'latest_modified_time', [])->default('2021-01-01 00:00:00');
            $table->addColumn('json', 'state_ids', []);
            $table->addColumn('json', 'screen_ids', []);
            $table->addColumn('integer', 'steps', ['unsigned' => true])->default('0');
            $table->addColumn('json', 'contents', []);
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');

            $table->index(['project_key'], 'INDEX_PROJECT_KEY');
        });
        Db::select(file_get_contents(__DIR__ . '/init/oswf_definition.sql'));
    }

    final public function down(): void
    {
        Schema::dropIfExists('oswf_definition');
    }
}
