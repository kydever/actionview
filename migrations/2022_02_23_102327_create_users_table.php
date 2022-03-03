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
use Hyperf\DbConnection\Db;

class CreateUsersTable extends Migration
{
    final public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'email', ['length' => 128])->default('')->comment('邮箱');
            $table->addColumn('string', 'first_name', ['length' => 32])->default('')->comment('姓名');
            $table->addColumn('string', 'password', ['length' => 256])->default('')->comment('密码');
            $table->addColumn('dateTime', 'last_login', [])->default('2021-01-01 00:00:00');
            $table->addColumn('json', 'permissions', []);
            $table->addColumn('tinyInteger', 'invalid_flag', ['unsigned' => true])->default('0');
            $table->addColumn('string', 'directory', ['length' => 128])->default('');
            $table->addColumn('string', 'phone', ['length' => 16])->default('');
            $table->addColumn('string', 'avatar', ['length' => 128])->default('');
            $table->addColumn('string', 'department', ['length' => 32])->default('');
            $table->addColumn('string', 'position', ['length' => 32])->default('');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');

            $table->unique(['email'], 'UNIQUE_EMAIL');
        });
        Db::select("
        INSERT INTO `users` (`id`, `email`, `first_name`, `password`, `last_login`, `permissions`, `invalid_flag`, `directory`, `phone`, `avatar`, `department`, `position`, `created_at`, `updated_at`)
        VALUES
	        (1,'l@hyperf.io','系统管理员','$2y$10\$NQMqcCmHzQ2Eae/9A3kSXObd90t.GWJ4erwoq.uZeWOLwqROWJGnK','2021-01-01 00:00:00','{\"sys_admin\": true}',0,'','','default.png','','','2021-01-01 00:00:00','2021-11-29 09:52:39');
        ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
