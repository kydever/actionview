# ************************************************************
# Sequel Pro SQL dump
# Version 5446
#
# https://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: swoft-test.knowyourself.cc (MySQL 5.7.23)
# Database: actionview
# Generation Time: 2021-11-25 05:02:24 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table acl_role
# ------------------------------------------------------------

DROP TABLE IF EXISTS `acl_role`;

CREATE TABLE `acl_role` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '角色名',
  `project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$_sys_$' COMMENT '项目KEY',
  `created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ACL角色';

LOCK TABLES `acl_role` WRITE;
/*!40000 ALTER TABLE `acl_role` DISABLE KEYS */;

INSERT INTO `acl_role` (`id`, `name`, `project_key`, `created_at`, `updated_at`)
VALUES
	(1,'产品经理','$_sys_$','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(2,'开发经理','$_sys_$','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(3,'开发人员','$_sys_$','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(4,'测试经理','$_sys_$','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(5,'测试人员','$_sys_$','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(6,'质量经理','$_sys_$','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(7,'观察员','$_sys_$','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(8,'项目管理员','$_sys_$','2021-01-01 00:00:00','2021-01-01 00:00:00'),
	(9,'项目经理','$_sys_$','2021-01-01 00:00:00','2021-01-01 00:00:00');

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



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
