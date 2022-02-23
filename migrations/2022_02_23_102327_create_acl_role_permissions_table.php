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

class CreateAclRolePermissionsTable extends Migration
{
    final public function up(): void
    {
        Schema::create('acl_role_permissions', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('bigInteger', 'role_id', ['unsigned' => true])->comment('角色ID');
            $table->addColumn('json', 'permissions', [])->comment('权限表');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
        Db::select("
            INSERT INTO `acl_role_permissions` (`id`, `project_key`, `role_id`, `permissions`, `created_at`, `updated_at`)
            VALUES
                (2,'\$_sys_$',6,'[\"view_project\", \"link_issue\", \"upload_file\", \"exec_workflow\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (3,'\$_sys_$',4,'[\"view_project\", \"assigned_issue\", \"assign_issue\", \"create_issue\", \"edit_issue\", \"delete_issue\", \"link_issue\", \"move_issue\", \"resolve_issue\", \"close_issue\", \"exec_workflow\", \"upload_file\", \"add_worklog\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\", \"edit_self_worklog\", \"delete_self_worklog\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (4,'\$_sys_$',1,'[\"view_project\", \"exec_workflow\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\", \"add_worklog\", \"edit_self_worklog\", \"delete_self_worklog\", \"create_issue\", \"edit_issue\", \"assign_issue\", \"assigned_issue\", \"close_issue\", \"resolve_issue\", \"link_issue\", \"move_issue\", \"upload_file\", \"download_file\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (5,'\$_sys_$',2,'[\"view_project\", \"assigned_issue\", \"assign_issue\", \"create_issue\", \"edit_issue\", \"link_issue\", \"move_issue\", \"resolve_issue\", \"exec_workflow\", \"upload_file\", \"add_worklog\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\", \"edit_self_worklog\", \"delete_self_worklog\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (6,'\$_sys_$',7,'[\"view_project\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (7,'\$_sys_$',8,'[\"view_project\", \"manage_project\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (8,'\$_sys_$',3,'[\"view_project\", \"assigned_issue\", \"assign_issue\", \"create_issue\", \"edit_issue\", \"link_issue\", \"exec_workflow\", \"upload_file\", \"add_worklog\", \"resolve_issue\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\", \"edit_self_worklog\", \"delete_self_worklog\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (9,'\$_sys_$',5,'[\"view_project\", \"assigned_issue\", \"assign_issue\", \"create_issue\", \"edit_issue\", \"delete_issue\", \"link_issue\", \"resolve_issue\", \"close_issue\", \"exec_workflow\", \"upload_file\", \"add_worklog\", \"download_file\", \"edit_self_worklog\", \"delete_self_worklog\", \"delete_self_comments\", \"edit_self_comments\", \"add_comments\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (10,'\$_sys_$',9,'[\"view_project\", \"manage_project\", \"create_issue\", \"edit_issue\", \"delete_issue\", \"assign_issue\", \"assigned_issue\", \"resolve_issue\", \"close_issue\", \"reset_issue\", \"link_issue\", \"move_issue\", \"exec_workflow\", \"add_comments\", \"edit_comments\", \"delete_comments\", \"add_worklog\", \"edit_worklog\", \"delete_worklog\", \"upload_file\", \"download_file\", \"remove_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00');
	");
    }

    final public function down(): void
    {
        Schema::dropIfExists('acl_role_permissions');
    }
}
