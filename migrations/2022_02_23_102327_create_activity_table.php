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

class CreateActivityTable extends Migration
{
    final public function up(): void
    {
        Schema::create('activity', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('project_key', 32);
            $table->json('data');
            $table->string('event_key', 64);
            $table->json('issue');
            $table->unsignedBigInteger('issue_id');
            $table->json('user');
            $table->timestamps();
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('activity');
    }
}
