<?php

// Install database
\rex_sql_table::get(\rex::getTable('d2u_address_address'))
    ->ensureColumn(new rex_sql_column('address_id', 'INT(11) unsigned', false, null, 'auto_increment'))
    ->setPrimaryKey('address_id')
    ->ensureColumn(new \rex_sql_column('company', 'VARCHAR(191)'))
    ->ensureColumn(new \rex_sql_column('company_appendix', 'VARCHAR(191)'))
    ->ensureColumn(new \rex_sql_column('contact_name', 'VARCHAR(191)'))
    ->ensureColumn(new \rex_sql_column('street', 'VARCHAR(191)'))
    ->ensureColumn(new \rex_sql_column('additional_address', 'VARCHAR(191)'))
    ->ensureColumn(new \rex_sql_column('zip_code', 'VARCHAR(30)'))
    ->ensureColumn(new \rex_sql_column('city', 'VARCHAR(191)'))
    ->ensureColumn(new \rex_sql_column('country_id', 'INT(11)'))
    ->ensureColumn(new \rex_sql_column('latitude', 'DECIMAL(14,10)'))
    ->ensureColumn(new \rex_sql_column('longitude', 'DECIMAL(14,10)'))
    ->ensureColumn(new \rex_sql_column('email', 'VARCHAR(191)'))
    ->ensureColumn(new \rex_sql_column('url', 'TEXT'))
    ->ensureColumn(new \rex_sql_column('phone', 'VARCHAR(50)'))
    ->ensureColumn(new \rex_sql_column('mobile', 'VARCHAR(50)'))
    ->ensureColumn(new \rex_sql_column('fax', 'VARCHAR(50)'))
    ->ensureColumn(new \rex_sql_column('picture', 'VARCHAR(191)'))
    ->ensureColumn(new \rex_sql_column('address_type_ids', 'TEXT'))
    ->ensureColumn(new \rex_sql_column('article_id', 'INT(11)'))
    ->ensureColumn(new \rex_sql_column('priority', 'INT(10)'))
    ->ensureColumn(new \rex_sql_column('online_status', 'VARCHAR(10)'))
    ->ensure();

\rex_sql_table::get(\rex::getTable('d2u_address_types'))
    ->ensureColumn(new rex_sql_column('address_type_id', 'INT(11) unsigned', false, null, 'auto_increment'))
    ->setPrimaryKey('address_type_id')
    ->ensureColumn(new \rex_sql_column('name', 'VARCHAR(191)'))
    ->ensureColumn(new \rex_sql_column('show_address_details', 'VARCHAR(5)'))
    ->ensureColumn(new \rex_sql_column('show_country_select', 'VARCHAR(5)'))
    ->ensureColumn(new \rex_sql_column('maps_zoom', 'INT(2)', false, '5'))
    ->ensureColumn(new \rex_sql_column('default_address_id', 'INT(11)'))
    ->ensureColumn(new \rex_sql_column('article_id', 'INT(11)'))
    ->ensure();

\rex_sql_table::get(\rex::getTable('d2u_address_countries'))
    ->ensureColumn(new rex_sql_column('country_id', 'INT(11) unsigned', false, null, 'auto_increment'))
    ->setPrimaryKey('country_id')
    ->ensureColumn(new \rex_sql_column('iso_lang_codes', 'VARCHAR(255)'))
    ->ensureColumn(new \rex_sql_column('maps_zoom', 'INT(2)', false, '5'))
    ->ensure();
\rex_sql_table::get(\rex::getTable('d2u_address_countries_lang'))
    ->ensureColumn(new rex_sql_column('country_id', 'INT(11)', false, null))
    ->ensureColumn(new \rex_sql_column('clang_id', 'INT(11)', false, (string) rex_clang::getStartId()))
    ->setPrimaryKey(['country_id', 'clang_id'])
    ->ensureColumn(new \rex_sql_column('name', 'VARCHAR(255)'))
    ->ensureColumn(new \rex_sql_column('translation_needs_update', 'VARCHAR(7)'))
    ->ensure();

\rex_sql_table::get(\rex::getTable('d2u_address_2_countries'))
    ->ensureColumn(new \rex_sql_column('country_id', 'INT(11)'))
    ->ensureColumn(new \rex_sql_column('address_id', 'INT(11)'))
    ->setPrimaryKey(['country_id', 'address_id'])
    ->ensure();

\rex_sql_table::get(\rex::getTable('d2u_address_zipcodes'))
    ->ensureColumn(new rex_sql_column('zipcode_id', 'INT(11) unsigned', false, null, 'auto_increment'))
    ->setPrimaryKey('zipcode_id')
    ->ensureColumn(new \rex_sql_column('range_from', 'INT(11)'))
    ->ensureColumn(new \rex_sql_column('range_to', 'INT(11)'))
    ->ensureColumn(new \rex_sql_column('country_id', 'INT(11)', false))
    ->ensureColumn(new \rex_sql_column('address_ids', 'TEXT', true))
    ->ensure();

\rex_sql_table::get(\rex::getTable('d2u_address_continents'))
    ->ensureColumn(new rex_sql_column('continent_id', 'INT(10) unsigned', false, null, 'auto_increment'))
    ->setPrimaryKey('continent_id')
    ->ensureColumn(new \rex_sql_column('country_ids', 'VARCHAR(1000)', true))
    ->ensure();
\rex_sql_table::get(\rex::getTable('d2u_address_continents_lang'))
    ->ensureColumn(new rex_sql_column('continent_id', 'INT(10)', false, null))
    ->ensureColumn(new \rex_sql_column('clang_id', 'INT(11)', false, (string) rex_clang::getStartId()))
    ->setPrimaryKey(['continent_id', 'clang_id'])
    ->ensureColumn(new \rex_sql_column('name', 'VARCHAR(255)'))
    ->ensureColumn(new \rex_sql_column('translation_needs_update', 'VARCHAR(7)'))
    ->ensure();

$sql = rex_sql::factory();
$sql->setQuery('SELECT * FROM '. \rex::getTablePrefix() ."media_manager_type WHERE name = 'd2u_address_120x150'");
if (0 === $sql->getRows()) {
    $sql->setQuery('INSERT INTO '. \rex::getTablePrefix() ."media_manager_type (`status`, `name`, `description`) VALUES
		(0, 'd2u_address_120x150', 'D2U Adressen: Vorschaubild Vertriebskontakt');");
    $last_id = $sql->getLastId();
    $sql->setQuery('INSERT INTO '. \rex::getTablePrefix() .'media_manager_type_effect (`type_id`, `effect`, `parameters`, `priority`, `createdate`, `createuser`) VALUES
		('. $last_id .", 'resize', '{\"rex_effect_rounded_corners\":{\"rex_effect_rounded_corners_topleft\":\"\",\"rex_effect_rounded_corners_topright\":\"\",\"rex_effect_rounded_corners_bottomleft\":\"\",\"rex_effect_rounded_corners_bottomright\":\"\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"},\"rex_effect_crop\":{\"rex_effect_crop_width\":\"\",\"rex_effect_crop_height\":\"\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_rotate\":{\"rex_effect_rotate_rotate\":\"0\"},\"rex_effect_filter_colorize\":{\"rex_effect_filter_colorize_filter_r\":\"\",\"rex_effect_filter_colorize_filter_g\":\"\",\"rex_effect_filter_colorize_filter_b\":\"\"},\"rex_effect_image_properties\":{\"rex_effect_image_properties_jpg_quality\":\"\",\"rex_effect_image_properties_png_compression\":\"\",\"rex_effect_image_properties_webp_quality\":\"\",\"rex_effect_image_properties_interlace\":null},\"rex_effect_filter_brightness\":{\"rex_effect_filter_brightness_brightness\":\"\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_filter_contrast\":{\"rex_effect_filter_contrast_contrast\":\"\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"120\",\"rex_effect_resize_height\":\"150\",\"rex_effect_resize_style\":\"minimum\",\"rex_effect_resize_allow_enlarge\":\"enlarge\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_repeats\":\"10\",\"rex_effect_filter_blur_type\":\"gaussian\",\"rex_effect_filter_blur_smoothit\":\"\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_header\":{\"rex_effect_header_download\":\"open_media\",\"rex_effect_header_cache\":\"no_cache\"},\"rex_effect_convert2img\":{\"rex_effect_convert2img_convert_to\":\"jpg\",\"rex_effect_convert2img_density\":\"150\"},\"rex_effect_mediapath\":{\"rex_effect_mediapath_mediapath\":\"\"}}', 1, CURRENT_TIMESTAMP, 'd2u_address'),
		(". $last_id .", 'crop', '{\"rex_effect_rounded_corners\":{\"rex_effect_rounded_corners_topleft\":\"\",\"rex_effect_rounded_corners_topright\":\"\",\"rex_effect_rounded_corners_bottomleft\":\"\",\"rex_effect_rounded_corners_bottomright\":\"\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"},\"rex_effect_crop\":{\"rex_effect_crop_width\":\"120\",\"rex_effect_crop_height\":\"150\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_rotate\":{\"rex_effect_rotate_rotate\":\"0\"},\"rex_effect_filter_colorize\":{\"rex_effect_filter_colorize_filter_r\":\"\",\"rex_effect_filter_colorize_filter_g\":\"\",\"rex_effect_filter_colorize_filter_b\":\"\"},\"rex_effect_image_properties\":{\"rex_effect_image_properties_jpg_quality\":\"\",\"rex_effect_image_properties_png_compression\":\"\",\"rex_effect_image_properties_webp_quality\":\"\",\"rex_effect_image_properties_interlace\":null},\"rex_effect_filter_brightness\":{\"rex_effect_filter_brightness_brightness\":\"\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_filter_contrast\":{\"rex_effect_filter_contrast_contrast\":\"\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"\",\"rex_effect_resize_height\":\"\",\"rex_effect_resize_style\":\"maximum\",\"rex_effect_resize_allow_enlarge\":\"enlarge\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_repeats\":\"10\",\"rex_effect_filter_blur_type\":\"gaussian\",\"rex_effect_filter_blur_smoothit\":\"\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_header\":{\"rex_effect_header_download\":\"open_media\",\"rex_effect_header_cache\":\"no_cache\"},\"rex_effect_convert2img\":{\"rex_effect_convert2img_convert_to\":\"jpg\",\"rex_effect_convert2img_density\":\"150\"},\"rex_effect_mediapath\":{\"rex_effect_mediapath_mediapath\":\"\"}}', 2, CURRENT_TIMESTAMP, 'd2u_address');");
}

// Insert / update frontend translations
if (!class_exists(FriendsOfRedaxo\D2UAddress\LangHelper::class)) {
    // Load class in case addon is deactivated
    require_once 'lib/LangHelper.php';
}
FriendsOfRedaxo\D2UAddress\LangHelper::factory()->install();

// Update modules
include __DIR__ . DIRECTORY_SEPARATOR .'lib'. DIRECTORY_SEPARATOR .'Module.php';
$d2u_module_manager = new \TobiasKrais\D2UHelper\ModuleManager(\FriendsOfRedaxo\D2UAddress\Module::getModules(), '', 'd2u_address');
$d2u_module_manager->autoupdate();