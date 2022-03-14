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

class CreateWorklogTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('worklog', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('issue_id');
            $table->string('project_key', 32)->default('')->comment('项目键值');
            $table->json('recorder');
            $table->integer('recorded_at');
            $table->integer('started_at')->comment('开始日期');
            $table->string('spend', 30)->comment('	总耗费时间');
            $table->integer('spend_m');
            $table->tinyInteger('adjust_type')->default(1);
            $table->string('comments', 1024)->default('')->comment('备注');
            $table->string('leave_estimate', 30)->default('');
            $table->string('cut', 30)->default('');
            $table->boolean('edited_flag')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worklog');
    }
}
