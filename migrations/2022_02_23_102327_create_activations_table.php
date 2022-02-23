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

class CreateActivationsTable extends Migration
{
    final public function up(): void
    {
        Schema::create('activations', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'code', ['length' => 32])->default('')->comment('代码');
            $table->addColumn('bigInteger', 'user_id', ['unsigned' => true])->comment('用户ID');
            $table->addColumn('tinyInteger', 'completed', ['unsigned' => true])->default('0')->comment('是否完成');
            $table->addColumn('dateTime', 'completed_at', [])->default('2021-01-01 00:00:00')->comment('完成时间');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
        Db::select("
            INSERT INTO `activations` (`id`, `code`, `user_id`, `completed`, `completed_at`, `created_at`, `updated_at`)
            VALUES
	            (1,'C5SKny95ix41rCbrh29Q14GbYwoEqj6I',1,1,'2021-01-01 00:00:00','2021-01-01 00:00:00','2021-01-01 00:00:00');
        ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('activations');
    }
}
