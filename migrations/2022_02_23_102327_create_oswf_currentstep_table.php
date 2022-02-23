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

class CreateOswfCurrentstepTable extends Migration
{
    final public function up(): void
    {
        Schema::create('oswf_currentstep', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('bigInteger', 'entry_id', ['unsigned' => true])->default('0');
            $table->addColumn('bigInteger', 'step_id', ['unsigned' => true])->default('0');
            $table->addColumn('bigInteger', 'previous_id', ['unsigned' => true])->default('0');
            $table->addColumn('integer', 'start_time', ['unsigned' => true])->default('0');
            $table->addColumn('bigInteger', 'action_id', ['unsigned' => true])->default('0');
            $table->addColumn('json', 'owners', []);
            $table->addColumn('string', 'status', ['length' => 16])->default('');
            $table->addColumn('string', 'comments', ['length' => 1024])->default('');
            $table->addColumn('json', 'caller', []);
            $table->addColumn('dateTime', 'created_at', [])->default('2020-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2020-01-01 00:00:00');
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('oswf_currentstep');
    }
}
