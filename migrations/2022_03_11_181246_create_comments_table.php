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

class CreateCommentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('contents')->nullable()->comment('评论内容');
            $table->json('at_who')->nullable()->comment('at用户列表');
            $table->unsignedBigInteger('issue_id')->index('INDEX_ISSUE_ID')->default(0);
            $table->json('creator')->nullable()->comment('创建者');
            $table->json('reply')->nullable()->comment('二级评论');
            $table->tinyInteger('edited_flag')->default(0)->comment('是否修改');
            $table->timestamps();
            $table->comment('问题评论表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
}
