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

class CreateIssueTable extends Migration
{
    final public function up(): void
    {
        Schema::create('issue', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('');
            $table->addColumn('integer', 'type', ['unsigned' => true]);
            $table->addColumn('integer', 'parent_id', ['unsigned' => true])->default('0');
            $table->addColumn('tinyInteger', 'del_flg', ['unsigned' => true])->default('0');
            $table->addColumn('string', 'resolution', ['length' => 32])->default('');
            $table->addColumn('json', 'assignee', []);
            $table->addColumn('json', 'reporter', []);
            $table->addColumn('json', 'modifier', []);
            $table->addColumn('integer', 'no', ['unsigned' => true])->default('0');
            $table->addColumn('json', 'data', []);
            $table->addColumn('dateTime', 'created_at', [])->default('2020-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2020-01-01 00:00:00');

            $table->index(['project_key'], 'INDEX_PROJECT_KEY');
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('issue');
    }
}
