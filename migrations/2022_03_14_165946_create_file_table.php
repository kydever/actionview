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

class CreateFileTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 64)->comment('文件名');
            $table->unsignedInteger('size')->comment('文件大小');
            $table->string('type', 32)->comment('文件类型');
            $table->json('uploader')->nullable()->comment('上传人信息');
            $table->string('index', 256)->comment('文件位置索引');
            $table->string('thumbnails_index', 256)->comment('缩略图文件索引');
            $table->unsignedTinyInteger('del_flg')->default(0)->comment('删除状态');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file');
    }
}
