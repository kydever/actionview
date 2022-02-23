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

class CreateWikiFavoritesTable extends Migration
{
    final public function up(): void
    {
        Schema::create('wiki_favorites', function (Blueprint $table) {
            $table->addColumn('integer', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('bigInteger', 'wid', []);
            $table->addColumn('bigInteger', 'user_id', []);
            $table->addColumn('json', 'user', []);
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('wiki_favorites');
    }
}
