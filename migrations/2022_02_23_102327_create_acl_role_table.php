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

class CreateAclRoleTable extends Migration
{
    final public function up(): void
    {
        Schema::create('acl_role', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('string', 'name', ['length' => 16])->default('')->comment('角色名');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');

            $table->index(['project_key'], 'INDEX_PROJECT_KEY');
        });
        Db::select("
            INSERT INTO `acl_role` (`id`, `project_key`, `name`, `created_at`, `updated_at`)
            VALUES
	            (1,'\$_sys_$','产品经理','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	            (2,'\$_sys_$','开发经理','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	            (3,'\$_sys_$','开发人员','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	            (4,'\$_sys_$','测试经理','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	            (5,'\$_sys_$','测试人员','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	            (6,'\$_sys_$','质量经理','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	            (7,'\$_sys_$','观察员','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	            (8,'\$_sys_$','项目管理员','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	            (9,'\$_sys_$','项目经理','2021-01-01 00:00:00','2021-01-01 00:00:00');
        ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('acl_role');
    }
}
