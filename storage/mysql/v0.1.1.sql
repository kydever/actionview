ALTER TABLE `issue_filters` ADD INDEX `INDEX_PROJECT_KEY` (`project_key`);

DROP TABLE IF EXISTS `issue_filters`;

CREATE TABLE `issue_filters` (
`id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
`project_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '项目key',
`name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
`query` json NOT NULL,
`scope` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
`creator` json NOT NULL,
`created_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
`updated_at` datetime NOT NULL DEFAULT '2021-01-01 00:00:00',
PRIMARY KEY (`id`),
KEY `INDEX_PROJECT_KEY` (`project_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
