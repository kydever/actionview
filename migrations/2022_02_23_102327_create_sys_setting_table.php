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

class CreateSysSettingTable extends Migration
{
    final public function up(): void
    {
        Schema::create('sys_setting', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('json', 'properties', []);
            $table->addColumn('json', 'mailserver', []);
            $table->addColumn('json', 'sysroles', []);
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
        Db::select("
            INSERT INTO `sys_setting` (`id`, `properties`, `mailserver`, `sysroles`, `created_at`, `updated_at`)
            VALUES
	            (1,'{\"day2hour\": 8, \"week2day\": 5, \"login_mail_domain\": \"hyperf.io\", \"allow_create_project\": 0}','[]','[]','2021-01-01 00:00:00','2021-11-27 10:51:03');
        ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('sys_setting');
    }
}
