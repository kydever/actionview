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

class CreateConfigFieldTable extends Migration
{
    final public function up(): void
    {
        Schema::create('config_field', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('string', 'name', ['length' => 30])->default('')->comment('字段名');
            $table->addColumn('string', 'key', ['length' => 30])->default('')->comment('字段KEY');
            $table->addColumn('string', 'type', ['length' => 30])->default('')->comment('字段类型');
            $table->addColumn('string', 'description', ['length' => 256])->default('')->comment('字段描述');
            $table->addColumn('json', 'option_values', [])->comment('选项值');
            $table->addColumn('string', 'default_value', ['length' => 32])->default('')->comment('默认值');
            $table->addColumn('string', 'min_value', ['length' => 32])->default('')->comment('默认最小值');
            $table->addColumn('string', 'max_value', ['length' => 32])->default('')->comment('默认最大值');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');

            $table->index(['project_key'], 'INDEX_PROJECT_KEY');
        });
        Db::select("
            INSERT INTO `config_field` (`id`, `project_key`, `name`, `key`, `type`, `description`, `option_values`, `default_value`, `min_value`, `max_value`, `created_at`, `updated_at`)
            VALUES
                (1,'\$_sys_$','主题','title','Text','创建问题或编辑问题页面需配置此字段','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (2,'\$_sys_$','优先级','priority','Select','字段可选值参照优先级配置','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (3,'\$_sys_$','期望完成时间','expect_complete_time','DatePicker','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (4,'\$_sys_$','负责人','assignee','SingleUser','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (5,'\$_sys_$','模块','module','MultiSelect','可选值参照模块配置','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (6,'\$_sys_$','描述','descriptions','RichTextEditor','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (7,'\$_sys_$','解决版本','resolve_version','SingleVersion','字段可选值参照已创建版本数据','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (8,'\$_sys_$','影响版本','effect_versions','MultiVersion','字段可选值参照已创建版本数据','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (9,'\$_sys_$','原估时间','original_estimate','TimeTracking','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (10,'\$_sys_$','附件','attachments','File','只有配置了附件或其他文件类型字段的问题才可上传文档','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (11,'\$_sys_$','解决结果','resolution','Select','字段可选值参照解决结果配置','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (12,'\$_sys_$','备注','comments','TextArea','主要用于流程环节的备注页面','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (13,'\$_sys_$','Epic','epic','Select','字段可选值参照看板Epic配置','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (14,'\$_sys_$','故事点数','story_points','Number','','[]','','0','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (15,'\$_sys_$','关联用户','related_users','MultiUser','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (16,'\$_sys_$','标签','labels','MultiSelect','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (17,'\$_sys_$','期望开始时间','expect_start_time','DatePicker','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (18,'\$_sys_$','进度','progress','Number','','[]','','0','100','2021-01-01 00:00:00','2021-01-01 00:00:00');

        ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('config_field');
    }
}
