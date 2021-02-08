<?php
$sql = rex_sql::factory();
// Install database
$sql->setQuery("CREATE TABLE IF NOT EXISTS `". \rex::getTablePrefix() ."d2u_address_address` (
    `address_id` INT(11) NOT NULL AUTO_INCREMENT,
    `company` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `company_appendix` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `contact_name` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `street` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `additional_address` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `zip_code` varchar(30) collate utf8mb4_unicode_ci NOT NULL default '',
    `city` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `country_id` INT(11) NULL,
    `latitude` decimal(14,10),
    `longitude` decimal(14,10),
    `email` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `url` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `phone` varchar(50) collate utf8mb4_unicode_ci NOT NULL default '',
    `mobile` varchar(50) collate utf8mb4_unicode_ci NOT NULL default '',
    `fax` varchar(50) collate utf8mb4_unicode_ci NOT NULL default '',
    `picture` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `address_type_ids` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `article_id` INT(11) NULL,
    `priority` INT(10) NULL DEFAULT NULL,
    `online_status` varchar(10) collate utf8mb4_unicode_ci NOT NULL default 'offline',
    PRIMARY KEY (`address_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;");

$sql->setQuery("CREATE TABLE IF NOT EXISTS `". \rex::getTablePrefix() ."d2u_address_types` (
    `address_type_id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    `show_address_details` varchar(5) collate utf8mb4_unicode_ci NOT NULL default 'no',
    `show_country_select` varchar(5) collate utf8mb4_unicode_ci NOT NULL default 'yes',
    `maps_zoom` tinyint(2) NULL DEFAULT '5',
    `default_address_id` int(11) NULL,
    `article_id` int(11) NULL,
    PRIMARY KEY (`address_type_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;");

$sql->setQuery("CREATE TABLE IF NOT EXISTS `". \rex::getTablePrefix() ."d2u_address_countries` (
    `country_id` int(11) NOT NULL AUTO_INCREMENT,
    `iso_lang_codes` varchar(255) collate utf8mb4_unicode_ci default NULL,
    `maps_zoom` tinyint(2) NULL DEFAULT '5',
    PRIMARY KEY (`country_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;");
$sql->setQuery("CREATE TABLE IF NOT EXISTS `". \rex::getTablePrefix() ."d2u_address_countries_lang` (
    `country_id` int(10) NOT NULL,
    `clang_id` int(10) NOT NULL,
    `name` varchar(255) collate utf8mb4_unicode_ci default NULL,
	`translation_needs_update` varchar(7) collate utf8mb4_unicode_ci default NULL,
    PRIMARY KEY (`country_id`, `clang_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;");

$sql->setQuery("CREATE TABLE IF NOT EXISTS `". \rex::getTablePrefix() ."d2u_address_2_countries` (
    `country_id` int(11) NOT NULL,
    `address_id` int(11) NOT NULL,
    PRIMARY KEY (`country_id`, `address_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;");

$sql->setQuery("CREATE TABLE IF NOT EXISTS `". \rex::getTablePrefix() ."d2u_address_zipcodes` (
    `zipcode_id` int(11) NOT NULL AUTO_INCREMENT,
    `range_from` varchar(10) NULL default NULL,
    `range_to` varchar(10) NULL default NULL,
    `country_id` int(10) default NULL,
    `address_ids` varchar(255) collate utf8mb4_unicode_ci NOT NULL default '',
    PRIMARY KEY (`zipcode_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;");

$sql->setQuery("SELECT * FROM ". \rex::getTablePrefix() ."media_manager_type WHERE name = 'd2u_address_120x150'");
if($sql->getRows() == 0) {
	$sql->setQuery("INSERT INTO ". \rex::getTablePrefix() ."media_manager_type (`status`, `name`, `description`) VALUES
		(0, 'd2u_address_120x150', 'D2U Adressen: Vorschaubild Vertriebskontakt');");
	$last_id = $sql->getLastId();
	$sql->setQuery("INSERT INTO ". \rex::getTablePrefix() ."media_manager_type_effect (`type_id`, `effect`, `parameters`, `priority`, `createdate`, `createuser`) VALUES
		(". $last_id .", 'resize', '{\"rex_effect_rounded_corners\":{\"rex_effect_rounded_corners_topleft\":\"\",\"rex_effect_rounded_corners_topright\":\"\",\"rex_effect_rounded_corners_bottomleft\":\"\",\"rex_effect_rounded_corners_bottomright\":\"\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"},\"rex_effect_crop\":{\"rex_effect_crop_width\":\"\",\"rex_effect_crop_height\":\"\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_rotate\":{\"rex_effect_rotate_rotate\":\"0\"},\"rex_effect_filter_colorize\":{\"rex_effect_filter_colorize_filter_r\":\"\",\"rex_effect_filter_colorize_filter_g\":\"\",\"rex_effect_filter_colorize_filter_b\":\"\"},\"rex_effect_image_properties\":{\"rex_effect_image_properties_jpg_quality\":\"\",\"rex_effect_image_properties_png_compression\":\"\",\"rex_effect_image_properties_webp_quality\":\"\",\"rex_effect_image_properties_interlace\":null},\"rex_effect_filter_brightness\":{\"rex_effect_filter_brightness_brightness\":\"\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_filter_contrast\":{\"rex_effect_filter_contrast_contrast\":\"\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"120\",\"rex_effect_resize_height\":\"150\",\"rex_effect_resize_style\":\"minimum\",\"rex_effect_resize_allow_enlarge\":\"enlarge\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_repeats\":\"10\",\"rex_effect_filter_blur_type\":\"gaussian\",\"rex_effect_filter_blur_smoothit\":\"\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_header\":{\"rex_effect_header_download\":\"open_media\",\"rex_effect_header_cache\":\"no_cache\"},\"rex_effect_convert2img\":{\"rex_effect_convert2img_convert_to\":\"jpg\",\"rex_effect_convert2img_density\":\"150\"},\"rex_effect_mediapath\":{\"rex_effect_mediapath_mediapath\":\"\"}}', 1, CURRENT_TIMESTAMP, 'd2u_address'), 
		(". $last_id .", 'crop', '{\"rex_effect_rounded_corners\":{\"rex_effect_rounded_corners_topleft\":\"\",\"rex_effect_rounded_corners_topright\":\"\",\"rex_effect_rounded_corners_bottomleft\":\"\",\"rex_effect_rounded_corners_bottomright\":\"\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"},\"rex_effect_crop\":{\"rex_effect_crop_width\":\"120\",\"rex_effect_crop_height\":\"150\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_rotate\":{\"rex_effect_rotate_rotate\":\"0\"},\"rex_effect_filter_colorize\":{\"rex_effect_filter_colorize_filter_r\":\"\",\"rex_effect_filter_colorize_filter_g\":\"\",\"rex_effect_filter_colorize_filter_b\":\"\"},\"rex_effect_image_properties\":{\"rex_effect_image_properties_jpg_quality\":\"\",\"rex_effect_image_properties_png_compression\":\"\",\"rex_effect_image_properties_webp_quality\":\"\",\"rex_effect_image_properties_interlace\":null},\"rex_effect_filter_brightness\":{\"rex_effect_filter_brightness_brightness\":\"\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_filter_contrast\":{\"rex_effect_filter_contrast_contrast\":\"\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"\",\"rex_effect_resize_height\":\"\",\"rex_effect_resize_style\":\"maximum\",\"rex_effect_resize_allow_enlarge\":\"enlarge\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_repeats\":\"10\",\"rex_effect_filter_blur_type\":\"gaussian\",\"rex_effect_filter_blur_smoothit\":\"\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_header\":{\"rex_effect_header_download\":\"open_media\",\"rex_effect_header_cache\":\"no_cache\"},\"rex_effect_convert2img\":{\"rex_effect_convert2img_convert_to\":\"jpg\",\"rex_effect_convert2img_density\":\"150\"},\"rex_effect_mediapath\":{\"rex_effect_mediapath_mediapath\":\"\"}}', 2, CURRENT_TIMESTAMP, 'd2u_address');");
}

// Insert frontend translations
if(class_exists('d2u_address_lang_helper')) {
	d2u_address_lang_helper::factory()->install();
}