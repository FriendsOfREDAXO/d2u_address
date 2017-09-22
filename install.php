<?php
$sql = rex_sql::factory();
// Install database
$sql->setQuery("CREATE TABLE IF NOT EXISTS `". rex::getTablePrefix() ."d2u_address_address` (
    `address_id` INT(11) NOT NULL AUTO_INCREMENT,
    `company` varchar(255) collate utf8_general_ci NOT NULL default '',
    `company_appendix` varchar(255) collate utf8_general_ci NOT NULL default '',
    `contact_name` varchar(255) collate utf8_general_ci NOT NULL default '',
    `street` varchar(255) collate utf8_general_ci NOT NULL default '',
    `additional_address` varchar(255) collate utf8_general_ci NOT NULL default '',
    `zip_code` varchar(30) collate utf8_general_ci NOT NULL default '',
    `city` varchar(255) collate utf8_general_ci NOT NULL default '',
    `country_id` INT(11) NULL,
    `latitude` decimal(10,10),
    `longitude` decimal(10,10),
    `email` varchar(255) collate utf8_general_ci NOT NULL default '',
    `url` varchar(255) collate utf8_general_ci NOT NULL default '',
    `phone` varchar(255) collate utf8_general_ci NOT NULL default '',
    `fax` varchar(255) collate utf8_general_ci NOT NULL default '',
    `picture` varchar(255) collate utf8_general_ci NOT NULL default '',
    `address_type_ids` varchar(255) collate utf8_general_ci NOT NULL default '',
    `article_id` INT(11) NULL,
    `priority` INT(10) NULL DEFAULT NULL,
    `online_status` varchar(10) collate utf8_general_ci NOT NULL default 'offline',
    PRIMARY KEY (`address_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;");

$sql->setQuery("CREATE TABLE IF NOT EXISTS `". rex::getTablePrefix() ."d2u_address_types` (
    `address_type_id` int(11) NOT NULL auto_increment,
    `name` varchar(255) collate utf8_general_ci NOT NULL default '',
    `show_address_details` varchar(5) collate utf8_general_ci NOT NULL default 'no',
    `show_country_select` varchar(5) collate utf8_general_ci NOT NULL default 'yes',
    `maps_zoom` tinyint(2) NULL DEFAULT '5',
    `default_address_id` int(11) NULL,
    `article_id` int(11) NULL,
    PRIMARY KEY (`address_type_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;");

$sql->setQuery("CREATE TABLE IF NOT EXISTS `". rex::getTablePrefix() ."d2u_address_countries` (
    `country_id` int(11) NOT NULL AUTO_INCREMENT,
    `iso_lang_codes` varchar(255) collate utf8_general_ci default NULL,
    `maps_zoom` tinyint(2) NULL DEFAULT '5',
    `address_ids` varchar(50) collate utf8_general_ci NOT NULL default '',
    PRIMARY KEY (`country_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;");
$sql->setQuery("CREATE TABLE IF NOT EXISTS `". rex::getTablePrefix() ."d2u_address_countries_lang` (
    `country_id` int(10) NOT NULL,
    `clang_id` int(10) NOT NULL,
    `name` varchar(255) collate utf8_general_ci default NULL,
	`translation_needs_update` varchar(7) collate utf8_general_ci default NULL,
    PRIMARY KEY (`country_id`, `clang_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;");

$sql->setQuery("CREATE TABLE IF NOT EXISTS `". rex::getTablePrefix() ."d2u_address_zipcodes` (
    `zipcode_id` int(11) NOT NULL auto_increment,
    `range_from` varchar(10) NULL default NULL,
    `range_to` varchar(10) NULL default NULL,
    `country_id` int(10) default NULL,
    `address_ids` varchar(255) collate utf8_general_ci NOT NULL default '',
    PRIMARY KEY (`zipcode_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;");

// Insert frontend translations
if(class_exists(d2u_address_lang_helper)) {
	d2u_address_lang_helper::factory()->install();
}