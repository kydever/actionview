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

class CreateConfigResolutionPropertyTable extends Migration
{
    final public function up(): void
    {
        Schema::create('config_resolution_property', function (Blueprint $table) {
            $table->addColumn('integer', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('');
            $table->addColumn('json', 'sequence', []);
            $table->addColumn('string', 'default_value', ['length' => 32])->default('');
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('config_resolution_property');
    }
}
