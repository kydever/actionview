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

class CreateUserSettingTable extends Migration
{
    final public function up(): void
    {
        Schema::create('user_setting', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'user_id', ['unsigned' => true])->comment('用户ID');
            $table->addColumn('json', 'notifications', []);
            $table->addColumn('json', 'favorites', []);
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');

            $table->primary(['user_id']);
        });
        Db::select("
            INSERT INTO `user_setting` (`user_id`, `notifications`, `favorites`, `created_at`, `updated_at`)
            VALUES
	            (1,'{\"mail_notify\": true, \"daily_notify\": false, \"mobile_notify\": false, \"weekly_notify\": false}','[]','2021-11-29 10:44:15','2021-11-29 10:45:08');
        ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('user_setting');
    }
}
