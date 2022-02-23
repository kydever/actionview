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

class CreateWikiTable extends Migration
{
    final public function up(): void
    {
        Schema::create('wiki', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('string', 'd', ['length' => 8])->default('');
            $table->addColumn('string', 'del_flag', ['length' => 8])->default('');
            $table->addColumn('string', 'name', ['length' => 32])->default('');
            $table->addColumn('json', 'pt', []);
            $table->addColumn('integer', 'parent', []);
            $table->addColumn('string', 'contents', ['length' => 1024])->default('');
            $table->addColumn('integer', 'version', [])->default('0');
            $table->addColumn('json', 'creator', []);
            $table->addColumn('json', 'editor', []);
            $table->addColumn('json', 'attachments', []);
            $table->addColumn('json', 'checkin', []);
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('wiki');
    }
}
