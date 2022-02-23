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

class CreateConfigScreenTable extends Migration
{
    final public function up(): void
    {
        Schema::create('config_screen', function (Blueprint $table) {
            $table->addColumn('bigInteger', 'id', ['autoIncrement' => true, 'unsigned' => true]);
            $table->addColumn('string', 'project_key', ['length' => 32])->default('$_sys_$')->comment('项目KEY');
            $table->addColumn('string', 'name', ['length' => 16])->default('')->comment('界面名');
            $table->addColumn('string', 'description', ['length' => 256])->default('')->comment('描述');
            $table->addColumn('json', 'schema', [])->comment('SCHEMA');
            $table->addColumn('json', 'field_ids', [])->comment('字段ID列表');
            $table->addColumn('dateTime', 'created_at', [])->default('2021-01-01 00:00:00');
            $table->addColumn('dateTime', 'updated_at', [])->default('2021-01-01 00:00:00');
        });
        Db::select("
            INSERT INTO `config_screen` (`id`, `project_key`, `name`, `description`, `schema`, `field_ids`, `created_at`, `updated_at`)
            VALUES
                (1,'\$_sys_$','系统默认界面','系统默认界面','[{\"id\": \"1\", \"key\": \"title\", \"name\": \"主题\", \"type\": \"Text\", \"required\": true}, {\"id\": \"2\", \"key\": \"priority\", \"name\": \"优先级\", \"type\": \"Select\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"4\", \"key\": \"assignee\", \"name\": \"负责人\", \"type\": \"SingleUser\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"5\", \"key\": \"module\", \"name\": \"模块\", \"type\": \"MultiSelect\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"7\", \"key\": \"resolve_version\", \"name\": \"解决版本\", \"type\": \"SingleVersion\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"6\", \"key\": \"descriptions\", \"name\": \"描述\", \"type\": \"RichTextEditor\"}, {\"id\": \"10\", \"key\": \"attachments\", \"name\": \"附件\", \"type\": \"File\"}, {\"id\": \"16\", \"key\": \"labels\", \"name\": \"标签\", \"type\": \"MultiSelect\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"17\", \"key\": \"expect_start_time\", \"name\": \"期望开始时间\", \"type\": \"DatePicker\"}, {\"id\": \"3\", \"key\": \"expect_complete_time\", \"name\": \"期望完成时间\", \"type\": \"DatePicker\"}, {\"id\": \"18\", \"key\": \"progress\", \"name\": \"进度\", \"type\": \"Number\", \"maxValue\": 100, \"minValue\": 0}, {\"id\": \"13\", \"key\": \"epic\", \"name\": \"Epic\", \"type\": \"Select\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"9\", \"key\": \"original_estimate\", \"name\": \"原估时间\", \"type\": \"TimeTracking\"}, {\"id\": \"14\", \"key\": \"story_points\", \"name\": \"故事点数\", \"type\": \"Number\", \"minValue\": 0}, {\"id\": \"15\", \"key\": \"related_users\", \"name\": \"关联用户\", \"type\": \"MultiUser\"}]','[\"1\", \"2\", \"4\", \"5\", \"7\", \"6\", \"10\", \"16\", \"17\", \"3\", \"18\", \"13\", \"9\", \"14\", \"15\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
                (2,'\$_sys_$','分配经办人','主要用户流程中间环节','[{\"id\": \"4\", \"key\": \"assignee\", \"name\": \"负责人\", \"type\": \"SingleUser\", \"required\": true, \"defaultValue\": \"\", \"optionValues\": []}]','[\"4\"]','2021-01-01 00:00:00','2021-01-01 00:00:00');
            ");
    }

    final public function down(): void
    {
        Schema::dropIfExists('config_screen');
    }
}
