# ************************************************************
# Sequel Pro SQL dump
# Version 5446
#
# https://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: swoft-test.knowyourself.cc (MySQL 5.7.23)
# Database: actionview
# Generation Time: 2021-11-26 03:56:56 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table access_project_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `access_project_log`;

CREATE TABLE `access_project_log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_id` bigint(20) unsigned NOT NULL,
  `latest_access_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `INDEX_USER_ID` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table acl_role
# ------------------------------------------------------------

DROP TABLE IF EXISTS `acl_role`;

CREATE TABLE `acl_role` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `name` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '角色名',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ACL角色';

LOCK TABLES `acl_role` WRITE;
/*!40000 ALTER TABLE `acl_role` DISABLE KEYS */;

INSERT INTO `acl_role` (`id`, `project_key`, `name`, `created_at`, `updated_at`)
VALUES
	(1,'$_sys_$','产品经理','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(2,'$_sys_$','开发经理','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(3,'$_sys_$','开发人员','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(4,'$_sys_$','测试经理','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(5,'$_sys_$','测试人员','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(6,'$_sys_$','质量经理','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(7,'$_sys_$','观察员','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(8,'$_sys_$','项目管理员','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(9,'$_sys_$','项目经理','2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `acl_role` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table acl_role_permissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `acl_role_permissions`;

CREATE TABLE `acl_role_permissions` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `role_id` bigint(20) unsigned NOT NULL COMMENT '角色ID',
  `permissions` json NOT NULL COMMENT '权限表',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限表';

LOCK TABLES `acl_role_permissions` WRITE;
/*!40000 ALTER TABLE `acl_role_permissions` DISABLE KEYS */;

INSERT INTO `acl_role_permissions` (`id`, `project_key`, `role_id`, `permissions`, `created_at`, `updated_at`)
VALUES
	(2,'$_sys_$',6,'[\"view_project\", \"link_issue\", \"upload_file\", \"exec_workflow\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(3,'$_sys_$',4,'[\"view_project\", \"assigned_issue\", \"assign_issue\", \"create_issue\", \"edit_issue\", \"delete_issue\", \"link_issue\", \"move_issue\", \"resolve_issue\", \"close_issue\", \"exec_workflow\", \"upload_file\", \"add_worklog\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\", \"edit_self_worklog\", \"delete_self_worklog\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(4,'$_sys_$',1,'[\"view_project\", \"exec_workflow\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\", \"add_worklog\", \"edit_self_worklog\", \"delete_self_worklog\", \"create_issue\", \"edit_issue\", \"assign_issue\", \"assigned_issue\", \"close_issue\", \"resolve_issue\", \"link_issue\", \"move_issue\", \"upload_file\", \"download_file\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(5,'$_sys_$',2,'[\"view_project\", \"assigned_issue\", \"assign_issue\", \"create_issue\", \"edit_issue\", \"link_issue\", \"move_issue\", \"resolve_issue\", \"exec_workflow\", \"upload_file\", \"add_worklog\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\", \"edit_self_worklog\", \"delete_self_worklog\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(6,'$_sys_$',7,'[\"view_project\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(7,'$_sys_$',8,'[\"view_project\", \"manage_project\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(8,'$_sys_$',3,'[\"view_project\", \"assigned_issue\", \"assign_issue\", \"create_issue\", \"edit_issue\", \"link_issue\", \"exec_workflow\", \"upload_file\", \"add_worklog\", \"resolve_issue\", \"download_file\", \"add_comments\", \"edit_self_comments\", \"delete_self_comments\", \"edit_self_worklog\", \"delete_self_worklog\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(9,'$_sys_$',5,'[\"view_project\", \"assigned_issue\", \"assign_issue\", \"create_issue\", \"edit_issue\", \"delete_issue\", \"link_issue\", \"resolve_issue\", \"close_issue\", \"exec_workflow\", \"upload_file\", \"add_worklog\", \"download_file\", \"edit_self_worklog\", \"delete_self_worklog\", \"delete_self_comments\", \"edit_self_comments\", \"add_comments\", \"remove_self_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(10,'$_sys_$',9,'[\"view_project\", \"manage_project\", \"create_issue\", \"edit_issue\", \"delete_issue\", \"assign_issue\", \"assigned_issue\", \"resolve_issue\", \"close_issue\", \"reset_issue\", \"link_issue\", \"move_issue\", \"exec_workflow\", \"add_comments\", \"edit_comments\", \"delete_comments\", \"add_worklog\", \"edit_worklog\", \"delete_worklog\", \"upload_file\", \"download_file\", \"remove_file\"]','2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `acl_role_permissions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table activations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `activations`;

CREATE TABLE `activations` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '代码',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `completed` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否完成',
  `completed_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00' COMMENT '完成时间',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `activations` WRITE;
/*!40000 ALTER TABLE `activations` DISABLE KEYS */;

INSERT INTO `activations` (`id`, `code`, `user_id`, `completed`, `completed_at`, `created_at`, `updated_at`)
VALUES
	(1,'C5SKny95ix41rCbrh29Q14GbYwoEqj6I',1,1,'2021-01-01 00:00:00','2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `activations` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table config_event_notifications
# ------------------------------------------------------------

DROP TABLE IF EXISTS `config_event_notifications`;

CREATE TABLE `config_event_notifications` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `event_id` bigint(20) unsigned NOT NULL COMMENT '事件ID',
  `notifications` json NOT NULL COMMENT '通知',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `config_event_notifications` WRITE;
/*!40000 ALTER TABLE `config_event_notifications` DISABLE KEYS */;

INSERT INTO `config_event_notifications` (`id`, `project_key`, `event_id`, `notifications`, `created_at`, `updated_at`)
VALUES
	(1,'$_sys_$',8,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(2,'$_sys_$',1,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(3,'$_sys_$',2,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(4,'$_sys_$',3,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(5,'$_sys_$',4,'[\"reporter\", \"assignee\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(6,'$_sys_$',5,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(7,'$_sys_$',6,'[\"reporter\", \"project_principal\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(8,'$_sys_$',7,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(9,'$_sys_$',9,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(10,'$_sys_$',10,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(11,'$_sys_$',11,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(12,'$_sys_$',12,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(13,'$_sys_$',13,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(14,'$_sys_$',14,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(15,'$_sys_$',15,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(16,'$_sys_$',16,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(17,'$_sys_$',17,'[\"assignee\", \"reporter\", \"watchers\"]','2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `config_event_notifications` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table config_events
# ------------------------------------------------------------

DROP TABLE IF EXISTS `config_events`;

CREATE TABLE `config_events` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '事件KEY',
  `apply` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'APPLY',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '事件名',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `config_events` WRITE;
/*!40000 ALTER TABLE `config_events` DISABLE KEYS */;

INSERT INTO `config_events` (`id`, `project_key`, `key`, `apply`, `name`, `created_at`, `updated_at`)
VALUES
	(1,'$_sys_$','create_issue','','问题已创建','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(2,'$_sys_$','edit_issue','','问题被编辑','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(3,'$_sys_$','del_issue','','问题已删除','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(4,'$_sys_$','add_comments','','添加备注','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(5,'$_sys_$','edit_comments','','备注被编辑','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(6,'$_sys_$','del_comments','','备注被删除','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(7,'$_sys_$','add_worklog','','添加工作日志','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(8,'$_sys_$','edit_worklog','','编辑工作日志','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(9,'$_sys_$','del_worklog','','删除工作日志','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(10,'$_sys_$','resolve_issue','workflow','问题已解决','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(11,'$_sys_$','close_issue','workflow','问题已关闭','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(12,'$_sys_$','start_progress_issue','workflow','开始解决问题','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(13,'$_sys_$','stop_progress_issue','workflow','停止解决问题','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(14,'$_sys_$','assign_issue','workflow','问题已分配','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(15,'$_sys_$','normal','workflow','一般事件','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(16,'$_sys_$','move_issue','','问题被移动','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(17,'$_sys_$','reopen_issue','workflow','重新打开问题','2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `config_events` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table config_field
# ------------------------------------------------------------

DROP TABLE IF EXISTS `config_field`;

CREATE TABLE `config_field` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段名',
  `key` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段KEY',
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段类型',
  `description` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段描述',
  `option_values` json NOT NULL COMMENT '选项值',
  `default_value` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '默认值',
  `min_value` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '默认最小值',
  `max_value` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '默认最大值',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `config_field` WRITE;
/*!40000 ALTER TABLE `config_field` DISABLE KEYS */;

INSERT INTO `config_field` (`id`, `project_key`, `name`, `key`, `type`, `description`, `option_values`, `default_value`, `min_value`, `max_value`, `created_at`, `updated_at`)
VALUES
	(1,'$_sys_$','主题','title','Text','创建问题或编辑问题页面需配置此字段','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(2,'$_sys_$','优先级','priority','Select','字段可选值参照优先级配置','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(3,'$_sys_$','期望完成时间','expect_complete_time','DatePicker','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(4,'$_sys_$','负责人','assignee','SingleUser','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(5,'$_sys_$','模块','module','MultiSelect','可选值参照模块配置','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(6,'$_sys_$','描述','descriptions','RichTextEditor','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(7,'$_sys_$','解决版本','resolve_version','SingleVersion','字段可选值参照已创建版本数据','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(8,'$_sys_$','影响版本','effect_versions','MultiVersion','字段可选值参照已创建版本数据','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(9,'$_sys_$','原估时间','original_estimate','TimeTracking','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(10,'$_sys_$','附件','attachments','File','只有配置了附件或其他文件类型字段的问题才可上传文档','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(11,'$_sys_$','解决结果','resolution','Select','字段可选值参照解决结果配置','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(12,'$_sys_$','备注','comments','TextArea','主要用于流程环节的备注页面','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(13,'$_sys_$','Epic','epic','Select','字段可选值参照看板Epic配置','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(14,'$_sys_$','故事点数','story_points','Number','','[]','','0','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(15,'$_sys_$','关联用户','related_users','MultiUser','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(16,'$_sys_$','标签','labels','MultiSelect','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(17,'$_sys_$','期望开始时间','expect_start_time','DatePicker','','[]','','','','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(18,'$_sys_$','进度','progress','Number','','[]','','0','100','2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `config_field` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table config_priority
# ------------------------------------------------------------

DROP TABLE IF EXISTS `config_priority`;

CREATE TABLE `config_priority` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `color` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '颜色',
  `description` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `key` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'KEY',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名字',
  `sn` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '版本',
  `default` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `config_priority` WRITE;
/*!40000 ALTER TABLE `config_priority` DISABLE KEYS */;

INSERT INTO `config_priority` (`id`, `project_key`, `color`, `description`, `key`, `name`, `sn`, `default`, `created_at`, `updated_at`)
VALUES
	(1,'$_sys_$','#cc0000','','Blocker','致命','1.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(2,'$_sys_$','#ff0000','','Critical','严重','2.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(3,'$_sys_$','#009900','','Major','重要','3.0',1,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(4,'$_sys_$','#006600','','Minor','轻微','4.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(5,'$_sys_$','#003300','','Trivial','微小','5.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `config_priority` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table config_resolution
# ------------------------------------------------------------

DROP TABLE IF EXISTS `config_resolution`;

CREATE TABLE `config_resolution` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `key` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'KEY',
  `name` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名字',
  `sn` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '版本',
  `default` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `config_resolution` WRITE;
/*!40000 ALTER TABLE `config_resolution` DISABLE KEYS */;

INSERT INTO `config_resolution` (`id`, `project_key`, `key`, `name`, `sn`, `default`, `created_at`, `updated_at`)
VALUES
	(1,'$_sys_$','Unresolved','未解决','1.0',1,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(2,'$_sys_$','Fixed','已解决','2.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(3,'$_sys_$','Wont Fixed','无法修复','3.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(4,'$_sys_$','Incomplete','不明确','5.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(5,'$_sys_$','Cannot Reproduce','无法复现','7.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(6,'$_sys_$','Duplicate','重复问题','4.0',0,'2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `config_resolution` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table config_screen
# ------------------------------------------------------------

DROP TABLE IF EXISTS `config_screen`;

CREATE TABLE `config_screen` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `name` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '界面名',
  `description` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `schema` json NOT NULL COMMENT 'SCHEMA',
  `field_ids` json NOT NULL COMMENT '字段ID列表',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `config_screen` WRITE;
/*!40000 ALTER TABLE `config_screen` DISABLE KEYS */;

INSERT INTO `config_screen` (`id`, `project_key`, `name`, `description`, `schema`, `field_ids`, `created_at`, `updated_at`)
VALUES
	(1,'$_sys_$','系统默认界面','系统默认界面','[{\"id\": \"1\", \"key\": \"title\", \"name\": \"主题\", \"type\": \"Text\", \"required\": true}, {\"id\": \"2\", \"key\": \"priority\", \"name\": \"优先级\", \"type\": \"Select\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"4\", \"key\": \"assignee\", \"name\": \"负责人\", \"type\": \"SingleUser\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"5\", \"key\": \"module\", \"name\": \"模块\", \"type\": \"MultiSelect\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"7\", \"key\": \"resolve_version\", \"name\": \"解决版本\", \"type\": \"SingleVersion\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"6\", \"key\": \"descriptions\", \"name\": \"描述\", \"type\": \"RichTextEditor\"}, {\"id\": \"10\", \"key\": \"attachments\", \"name\": \"附件\", \"type\": \"File\"}, {\"id\": \"16\", \"key\": \"labels\", \"name\": \"标签\", \"type\": \"MultiSelect\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"17\", \"key\": \"expect_start_time\", \"name\": \"期望开始时间\", \"type\": \"DatePicker\"}, {\"id\": \"3\", \"key\": \"expect_complete_time\", \"name\": \"期望完成时间\", \"type\": \"DatePicker\"}, {\"id\": \"18\", \"key\": \"progress\", \"name\": \"进度\", \"type\": \"Number\", \"maxValue\": 100, \"minValue\": 0}, {\"id\": \"13\", \"key\": \"epic\", \"name\": \"Epic\", \"type\": \"Select\", \"defaultValue\": \"\", \"optionValues\": []}, {\"id\": \"9\", \"key\": \"original_estimate\", \"name\": \"原估时间\", \"type\": \"TimeTracking\"}, {\"id\": \"14\", \"key\": \"story_points\", \"name\": \"故事点数\", \"type\": \"Number\", \"minValue\": 0}, {\"id\": \"15\", \"key\": \"related_users\", \"name\": \"关联用户\", \"type\": \"MultiUser\"}]','[\"1\", \"2\", \"4\", \"5\", \"7\", \"6\", \"10\", \"16\", \"17\", \"3\", \"18\", \"13\", \"9\", \"14\", \"15\"]','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(2,'$_sys_$','分配经办人','主要用户流程中间环节','[{\"id\": \"4\", \"key\": \"assignee\", \"name\": \"负责人\", \"type\": \"SingleUser\", \"required\": true, \"defaultValue\": \"\", \"optionValues\": []}]','[\"4\"]','2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `config_screen` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table config_state
# ------------------------------------------------------------

DROP TABLE IF EXISTS `config_state`;

CREATE TABLE `config_state` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sn` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `category` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `config_state` WRITE;
/*!40000 ALTER TABLE `config_state` DISABLE KEYS */;

INSERT INTO `config_state` (`id`, `project_key`, `key`, `name`, `sn`, `category`, `created_at`, `updated_at`)
VALUES
	(1,'$_sys_$','Open','开始','1.0','new','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(2,'$_sys_$','In Progess','进行中','2.0','inprogress','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(3,'$_sys_$','Resolved','已完成','3.0','completed','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(4,'$_sys_$','Reopened','重新打开','4.0','new','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(5,'$_sys_$','Closed','关闭','5.0','completed','2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `config_state` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table config_type
# ------------------------------------------------------------

DROP TABLE IF EXISTS `config_type`;

CREATE TABLE `config_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `sn` int(10) unsigned NOT NULL,
  `name` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `abb` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `screen_id` bigint(20) unsigned NOT NULL,
  `workflow_id` bigint(20) unsigned NOT NULL,
  `type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `default` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `config_type` WRITE;
/*!40000 ALTER TABLE `config_type` DISABLE KEYS */;

INSERT INTO `config_type` (`id`, `project_key`, `sn`, `name`, `abb`, `screen_id`, `workflow_id`, `type`, `default`, `created_at`, `updated_at`)
VALUES
	(1,'$_sys_$',1499871082,'任务','T',1,1,'',1,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(2,'$_sys_$',1499926509,'新功能','F',1,1,'',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(3,'$_sys_$',1499926534,'缺陷','B',1,1,'',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(4,'$_sys_$',1499926556,'改进','I',1,1,'',0,'2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(5,'$_sys_$',1499926575,'子任务','S',1,1,'subtask',0,'2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `config_type` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table oswf_definition
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oswf_definition`;

CREATE TABLE `oswf_definition` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `latest_modifier` bigint(20) unsigned NOT NULL,
  `latest_modified_time` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `state_ids` json NOT NULL,
  `screen_ids` json NOT NULL,
  `steps` int(10) unsigned NOT NULL DEFAULT '0',
  `contents` json NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `oswf_definition` WRITE;
/*!40000 ALTER TABLE `oswf_definition` DISABLE KEYS */;

INSERT INTO `oswf_definition` (`id`, `project_key`, `name`, `latest_modifier`, `latest_modified_time`, `state_ids`, `screen_ids`, `steps`, `contents`, `created_at`, `updated_at`)
VALUES
	(1,'$_sys_$','系统工作流',1,'2021-01-01 00:00:00','[\"Open\", \"In Progess\", \"Resolved\", \"Reopened\", \"Closed\"]','[\"2\"]',5,'{\"steps\": [{\"id\": 1, \"name\": \"开始\", \"state\": \"Open\", \"actions\": [{\"id\": 1001, \"name\": \"开始处理\", \"screen\": \"\", \"results\": [{\"step\": 2, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"resolve_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"args\": {\"assigneeParam\": \"me\"}, \"name\": \"App\\\\Workflow\\\\Func@assignIssue\"}, {\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"start_progress_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}, {\"id\": 1002, \"name\": \"已解决\", \"screen\": \"\", \"results\": [{\"step\": 3, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"resolve_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"args\": {\"resolutionParam\": \"Fixed\"}, \"name\": \"App\\\\Workflow\\\\Func@setResolution\"}, {\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"resolve_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}, {\"id\": 1003, \"name\": \"关闭问题\", \"screen\": \"\", \"results\": [{\"step\": 5, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"close_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"args\": {\"resolutionParam\": \"Fixed\"}, \"name\": \"App\\\\Workflow\\\\Func@setResolution\"}, {\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"close_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}]}, {\"id\": 2, \"name\": \"进行中\", \"state\": \"In Progess\", \"actions\": [{\"id\": 2001, \"name\": \"已解决\", \"screen\": \"\", \"results\": [{\"step\": 3, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"resolve_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"args\": {\"resolutionParam\": \"Fixed\"}, \"name\": \"App\\\\Workflow\\\\Func@setResolution\"}, {\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"resolve_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}, {\"id\": 2002, \"name\": \"停止处理\", \"screen\": \"\", \"results\": [{\"step\": 1, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"resolve_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"args\": {\"resolutionParam\": \"Unresolved\"}, \"name\": \"App\\\\Workflow\\\\Func@setResolution\"}, {\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"stop_progress_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}, {\"id\": 2003, \"name\": \"关闭问题\", \"screen\": \"\", \"results\": [{\"step\": 5, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"close_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"args\": {\"resolutionParam\": \"Fixed\"}, \"name\": \"App\\\\Workflow\\\\Func@setResolution\"}, {\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"close_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}]}, {\"id\": 3, \"name\": \"已完成\", \"state\": \"Resolved\", \"actions\": [{\"id\": 3001, \"name\": \"关闭问题\", \"screen\": \"\", \"results\": [{\"step\": 5, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"close_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"close_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}, {\"id\": 3002, \"name\": \"重新开发\", \"screen\": \"2\", \"results\": [{\"step\": 1, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"close_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"args\": {\"resolutionParam\": \"Unresolved\"}, \"name\": \"App\\\\Workflow\\\\Func@setResolution\"}, {\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"normal\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}]}, {\"id\": 4, \"name\": \"重新打开\", \"state\": \"Reopened\", \"actions\": [{\"id\": 4001, \"name\": \"开始处理\", \"screen\": \"\", \"results\": [{\"step\": 2, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"resolve_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"args\": {\"assigneeParam\": \"me\"}, \"name\": \"App\\\\Workflow\\\\Func@assignIssue\"}, {\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"start_progress_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}, {\"id\": 4002, \"name\": \"已解决\", \"screen\": \"\", \"results\": [{\"step\": 3, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"resolve_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"args\": {\"resolutionParam\": \"Fixed\"}, \"name\": \"App\\\\Workflow\\\\Func@setResolution\"}, {\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"resolve_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}, {\"id\": 4003, \"name\": \"关闭问题\", \"screen\": \"\", \"results\": [{\"step\": 5, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"close_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"close_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}]}, {\"id\": 5, \"name\": \"关闭\", \"state\": \"Closed\", \"actions\": [{\"id\": 5001, \"name\": \"重新打开\", \"screen\": \"2\", \"results\": [{\"step\": 4, \"status\": \"Underway\", \"old_status\": \"Finished\"}], \"restrict_to\": {\"conditions\": {\"list\": [{\"args\": {\"permissionParam\": \"close_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@hasPermission\"}], \"type\": \"and\"}}, \"post_functions\": [{\"args\": {\"resolutionParam\": \"Unresolved\"}, \"name\": \"App\\\\Workflow\\\\Func@setResolution\"}, {\"name\": \"App\\\\Workflow\\\\Func@setState\"}, {\"name\": \"App\\\\Workflow\\\\Func@addComments\"}, {\"name\": \"App\\\\Workflow\\\\Func@updIssue\"}, {\"args\": {\"eventParam\": \"reopen_issue\"}, \"name\": \"App\\\\Workflow\\\\Func@triggerEvent\"}]}]}], \"initial_action\": {\"id\": 0, \"name\": \"initial_action\", \"results\": [{\"step\": 1, \"status\": \"Underway\"}]}}','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(2,'demo','复制 - 系统工作流',1,'2021-01-01 00:00:00','[\"Open\", \"In Progess\", \"Resolved\", \"Reopened\", \"Closed\"]','[\"2\"]',2,'[]','2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `oswf_definition` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table project
# ------------------------------------------------------------

DROP TABLE IF EXISTS `project`;

CREATE TABLE `project` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `principal` json NOT NULL,
  `category` bigint(10) unsigned NOT NULL DEFAULT '0',
  `description` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `creator` json NOT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table sys_setting
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sys_setting`;

CREATE TABLE `sys_setting` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `properties` json NOT NULL,
  `mailserver` json NOT NULL,
  `sysroles` json NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `sys_setting` WRITE;
/*!40000 ALTER TABLE `sys_setting` DISABLE KEYS */;

INSERT INTO `sys_setting` (`id`, `properties`, `mailserver`, `sysroles`, `created_at`, `updated_at`)
VALUES
	(1,'{\"day2hour\": 8, \"week2day\": 5, \"login_mail_domain\": \"actionview.cn\"}','[]','[]','2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `sys_setting` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_setting
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_setting`;

CREATE TABLE `user_setting` (
  `user_id` bigint(11) unsigned NOT NULL COMMENT '用户ID',
  `notifications` json NOT NULL,
  `favorites` json NOT NULL,
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `first_name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `password` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码',
  `last_login` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `permissions` json NOT NULL,
  `invalid_flag` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_EMAIL` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `email`, `first_name`, `password`, `last_login`, `permissions`, `invalid_flag`, `created_at`, `updated_at`)
VALUES
	(1,'l@hyperf.io','系统管理员','$2y$10$ivey5rQbs7dAy28lGzFBIOLhqLXlEV2X9esKkkHSZAp/9jNQvHNku','2021-01-01 00:00:00','{\"sys_admin\": true}',0,'2021-01-01 00:00:00','2021-01-01 00:00:00');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
