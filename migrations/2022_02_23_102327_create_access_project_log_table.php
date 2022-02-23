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

class CreateAccessProjectLogTable extends Migration
{
    final public function up(): void
    {
        Schema::create('access_project_log', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('');
            $table->addColumn('bigInteger', 'user_id', ['unsigned' => true]);
            $table->addColumn('integer', 'latest_access_time', ['unsigned' => true])->default('0');

            $table->index(['user_id'], 'INDEX_USER_ID');
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('access_project_log');
    }
}
