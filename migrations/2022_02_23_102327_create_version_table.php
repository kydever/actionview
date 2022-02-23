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

class CreateVersionTable extends Migration
{
    final public function up(): void
    {
        Schema::create('version', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('');
            $table->addColumn('string', 'name', ['length' => 32])->default('');
            $table->addColumn('integer', 'start_time', ['unsigned' => true])->default('0');
            $table->addColumn('integer', 'end_time', ['unsigned' => true])->default('0');
            $table->addColumn('integer', 'released_time', ['unsigned' => true])->default('0');
            $table->addColumn('string', 'status', ['length' => 16])->default('');
            $table->addColumn('string', 'description', ['length' => 1024])->default('');
            $table->addColumn('json', 'creator', []);
            $table->addColumn('json', 'modifier', []);
            $table->addColumn('dateTime', 'created_at', [])->default('2020-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2020-01-01 00:00:00');

            $table->index(['project_key'], 'INDEX_PROJECT_KEY');
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('version');
    }
}
