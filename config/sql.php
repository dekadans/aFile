<?php
return [
"CREATE TABLE IF NOT EXISTS `users` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`username` varchar(50) NOT NULL,
`password` varchar(255) NOT NULL,
`encryption_key` varchar(1024) NOT NULL,
`account_type` enum('USER','ADMIN') NOT NULL DEFAULT 'USER',
PRIMARY KEY (`id`),
UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

"CREATE TABLE IF NOT EXISTS `auth` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`user_id` int(11) unsigned NOT NULL,
`selector` char(12) DEFAULT NULL,
`hashed_token` char(64) DEFAULT NULL,
`expires` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `selector` (`selector`),
KEY `FK_auth_users` (`user_id`),
CONSTRAINT `FK_auth_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

"CREATE TABLE IF NOT EXISTS `files` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` INT(10) UNSIGNED NULL DEFAULT NULL,
	`name` VARCHAR(255) NOT NULL,
	`parent_id` INT(10) UNSIGNED NULL DEFAULT NULL,
	`size` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	`mime` VARCHAR(255) NULL DEFAULT NULL,
	`type` ENUM('FILE','DIRECTORY','LINK') NOT NULL DEFAULT 'FILE',
	`encryption` ENUM('PERSONAL','TOKEN','NONE') NOT NULL DEFAULT 'PERSONAL',
	`last_edit` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`string_id` VARCHAR(10) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `hash_id` (`string_id`),
	UNIQUE INDEX `user_id_name_parent_id` (`user_id`, `name`, `parent_id`),
	INDEX `FK_files_files` (`parent_id`),
	CONSTRAINT `FK_files_files` FOREIGN KEY (`parent_id`) REFERENCES `files` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
	CONSTRAINT `FK_files_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

"CREATE TABLE IF NOT EXISTS `share` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`file_id` int(10) unsigned NOT NULL DEFAULT '0',
`open_token` varchar(50) DEFAULT NULL,
`active` enum('OPEN','RESTRICTED','NONE') NOT NULL DEFAULT 'NONE',
`password` varchar(255) DEFAULT NULL,
`encryption_key` varchar(512) DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `file_id` (`file_id`),
CONSTRAINT `FK_links_files` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
];
