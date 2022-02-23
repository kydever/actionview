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

class CreateUserIssueFiltersTable extends Migration
{
    final public function up(): void
    {
        Schema::create('user_issue_filters', function (Blueprint $table) {
            $table->addColumn('integer', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('')->comment('项目key');
            $table->addColumn('json', 'user', []);
            $table->addColumn('json', 'sequence', []);
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('user_issue_filters');
    }
}
