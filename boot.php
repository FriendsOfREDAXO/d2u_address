<?php
if(\rex::isBackend() && is_object(\rex::getUser())) {
	rex_perm::register('d2u_address[]', rex_i18n::msg('d2u_address_rights'));
	rex_perm::register('d2u_address[edit_data]', rex_i18n::msg('d2u_address_rights_edit_data'), rex_perm::OPTIONS);
	rex_perm::register('d2u_address[edit_lang]', rex_i18n::msg('d2u_address_rights_edit_lang'), rex_perm::OPTIONS);
	rex_perm::register('d2u_address[settings]', rex_i18n::msg('d2u_address_rights_settings'), rex_perm::OPTIONS);
}

if(\rex::isBackend()) {
	rex_extension::register('CLANG_DELETED', 'rex_d2u_address_clang_deleted');
	rex_extension::register('MEDIA_IS_IN_USE', 'rex_d2u_address_media_is_in_use');
}

/**
 * Deletes language specific configurations and objects
 * @param rex_extension_point $ep Redaxo extension point
 * @return string[] Warning message as array
 */
function rex_d2u_address_clang_deleted(rex_extension_point $ep) {
	/** @var string[] $warning */
	$warning = $ep->getSubject();
	$params = $ep->getParams();
	$clang_id = $params['id'];

	// Delete
	$countries = D2U_Address\Country::getAll($clang_id);
	foreach ($countries as $country) {
		$country->delete(FALSE);
	}
	
	// Delete language settings
	if(rex_config::has('d2u_address', 'lang_replacement_'. $clang_id)) {
		rex_config::remove('d2u_address', 'lang_replacement_'. $clang_id);
	}
	// Delete language replacements
	d2u_address_lang_helper::factory()->uninstall($clang_id);

	return $warning;
}

/**
 * Checks if media is used by this addon
 * @param rex_extension_point $ep Redaxo extension point
 * @return string[] Warning message as array
 */
function rex_d2u_address_media_is_in_use(rex_extension_point $ep) {
	/** @var string[] $warning */
	$warning = $ep->getSubject();
	$params = $ep->getParams();
	$filename = addslashes($params['filename']);

	// References
	$sql_address = rex_sql::factory();
	$sql_address->setQuery('SELECT address_id, contact_name, company FROM `' . \rex::getTablePrefix() . 'd2u_address_address`'
		.'WHERE picture = "'. $filename .'"');  

	// Prepare warnings
	// Address pics
	for($i = 0; $i < $sql_address->getRows(); $i++) {
		$message = '<a href="javascript:openPage(\'index.php?page=d2u_address/adress&func=edit&entry_id='.
			$sql_address->getValue('address_id') .'\')">'. rex_i18n::msg('d2u_address_rights') ." - ". rex_i18n::msg('d2u_address_address') .': '. $sql_address->getValue('contact_name') .' ('. $sql_address->getValue('company') .'</a>';
		if(!in_array($message, $warning)) {
			$warning[] = $message;
		}
		$sql_address->next();
    }

	return $warning;
}