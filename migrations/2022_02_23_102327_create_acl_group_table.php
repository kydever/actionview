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

class CreateAclGroupTable extends Migration
{
    final public function up(): void
    {
        Schema::create('acl_group', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'name', ['length' => 32])->default('');
            $table->addColumn('json', 'users', [])->comment('用户ID列表');
            $table->addColumn('json', 'principal', [])->comment('负责人');
            $table->addColumn('tinyInteger', 'public_scope', ['unsigned' => true])->default('0');
            $table->addColumn('string', 'description', ['length' => 1024])->default('');
            $table->addColumn('string', 'directory', ['length' => 256])->default('')->comment('Unknown');
            $table->addColumn('string', 'ldap_dn', ['length' => 128])->default('')->comment('Unknown');
            $table->addColumn('string', 'sync_flag', ['length' => 64])->default('')->comment('Unknown');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
    }

    final public function down(): void
    {
        Schema::dropIfExists('acl_group');
    }
}
