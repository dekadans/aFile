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
`encrypted_password` varchar(1024) DEFAULT NULL,
`expires` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `selector` (`selector`),
KEY `FK_auth_users` (`user_id`),
CONSTRAINT `FK_auth_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;",

"CREATE TABLE IF NOT EXISTS `files` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`user_id` int(10) unsigned DEFAULT NULL,
`name` varchar(255) NOT NULL,
`parent_id` int(10) unsigned DEFAULT NULL,
`size` bigint(20) unsigned DEFAULT NULL,
`mime` varchar(255) DEFAULT NULL,
`type` enum('FILE','DIRECTORY','SPECIAL') NOT NULL DEFAULT 'FILE',
`encryption` enum('PERSONAL','TOKEN','NONE') NOT NULL DEFAULT 'PERSONAL',
`last_edit` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
`string_id` varchar(10) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `hash_id` (`string_id`),
UNIQUE KEY `user_id_name_parent_id` (`user_id`,`name`,`parent_id`),
KEY `FK_files_files` (`parent_id`),
CONSTRAINT `FK_files_files` FOREIGN KEY (`parent_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
CONSTRAINT `FK_files_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
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
