ALTER TABLE `issue` ADD INDEX `INDEX_PROJECT_KEY` (`project_key`);

CREATE TABLE `oswf_entry` (
`id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
`definition_id` bigint(10) unsigned NOT NULL DEFAULT '0',
`creator` json NOT NULL,
`state` int(16) DEFAULT '0',
`propertysets` json NOT NULL,
`created_at` datetime NOT NULL DEFAULT '2020-01-01 00:00:00',
`updated_at` datetime NOT NULL DEFAULT '2020-01-01 00:00:00',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `oswf_currentstep` (
`id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
`entry_id` bigint(20) unsigned NOT NULL DEFAULT '0',
`step_id` bigint(20) unsigned NOT NULL DEFAULT '0',
`previous_id` bigint(20) unsigned NOT NULL DEFAULT '0',
`start_time` int(10) unsigned NOT NULL DEFAULT '0',
`action_id` bigint(20) unsigned NOT NULL DEFAULT '0',
`owners` json NOT NULL,
`status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
`comments` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
`caller` json NOT NULL,
`created_at` datetime NOT NULL DEFAULT '2020-01-01 00:00:00',
`updated_at` datetime NOT NULL DEFAULT '2020-01-01 00:00:00',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `oswf_historystep` (
`id` bigint(11) unsigned NOT NULL,
`entry_id` bigint(20) unsigned NOT NULL DEFAULT '0',
`step_id` bigint(20) unsigned NOT NULL DEFAULT '0',
`previous_id` bigint(20) unsigned NOT NULL DEFAULT '0',
`start_time` int(10) unsigned NOT NULL DEFAULT '0',
`action_id` bigint(20) unsigned NOT NULL DEFAULT '0',
`owners` json NOT NULL,
`status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
`comments` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
`caller` json NOT NULL,
`finish_time` int(11) NOT NULL DEFAULT '0',
`created_at` datetime NOT NULL DEFAULT '2020-01-01 00:00:00',
`updated_at` datetime NOT NULL DEFAULT '2020-01-01 00:00:00',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
