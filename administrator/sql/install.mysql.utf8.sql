CREATE TABLE IF NOT EXISTS `#__myparcel_config` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL DEFAULT '1',
`checked_out` INT(11)  NOT NULL ,
`checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
`created_by` INT(11)  NOT NULL ,
`my_name` varchar(255)  NOT NULL DEFAULT '',
`my_api_key` varchar(255)  NOT NULL DEFAULT '',
`my_frontend_plugin` TINYINT(1)  NOT NULL DEFAULT '0',
`my_frontend_plugin_status` TINYINT(1)  NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

INSERT INTO `#__myparcel_config` (`id`, `ordering`, `state`, `checked_out`, `checked_out_time`, `created_by`) VALUES
(1, 1, 0, 0, '0000-00-00 00:00:00', 388) ON DUPLICATE KEY UPDATE id = 1;

CREATE TABLE IF NOT EXISTS `orders_myparcel` (
  `orders_myparcel_id` int(11) NOT NULL AUTO_INCREMENT,
  `orders_id` int(11) NOT NULL,
  `consignment_id` bigint(20) NOT NULL,
  `retour` tinyint(1) NOT NULL DEFAULT '0',
  `tracktrace` varchar(32) NOT NULL,
  `postcode` varchar(6) NOT NULL,
  `tnt_status` varchar(255) NOT NULL,
  `tnt_updated_on` datetime NOT NULL,
  `tnt_final` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`orders_myparcel_id`)
);

CREATE TABLE IF NOT EXISTS `orders_myparcel_pg_address` (
	`pg_address_id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`street` varchar(255) COLLATE utf8_unicode_ci NULL,
	`house_number` varchar(255) COLLATE utf8_unicode_ci NULL,
	`number_addition` varchar(255) COLLATE utf8_unicode_ci NULL,
	`postcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`town` varchar(255) COLLATE utf8_unicode_ci NULL,
	PRIMARY KEY (`pg_address_id`)
);