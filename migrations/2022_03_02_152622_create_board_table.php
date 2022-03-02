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

class CreateBoardTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('board', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('project_key', 32)->comment('项目 key');
            $table->string('name', 30)->comment('看板名称');
            $table->string('type', 30)->comment('看板类型');
            $table->string('description', 1024)->default('')->comment('看板描述');
            $table->json('columns');
            $table->json('filters');
            $table->json('query');
            $table->json('creator');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board');
    }
}
