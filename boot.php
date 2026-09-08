<?php

if (\rex::isBackend() && is_object(\rex::getUser())) {
    rex_perm::register('d2u_address[]', rex_i18n::msg('d2u_address_rights'));
    rex_perm::register('d2u_address[edit_data]', rex_i18n::msg('d2u_address_rights_edit_data'), rex_perm::OPTIONS);
    rex_perm::register('d2u_address[edit_lang]', rex_i18n::msg('d2u_address_rights_edit_lang'), rex_perm::OPTIONS);
    rex_perm::register('d2u_address[settings]', rex_i18n::msg('d2u_address_rights_settings'), rex_perm::OPTIONS);
}

if (\rex::isBackend()) {
    rex_extension::register('CLANG_DELETED', rex_d2u_address_clang_deleted(...));
    rex_extension::register('D2U_HELPER_TRANSLATION_LIST', rex_d2u_address_translation_list(...));
    rex_extension::register('D2U_HELPER_TRANSLATE_OBJECT', rex_d2u_address_translate_object(...));
    rex_extension::register('MEDIA_IS_IN_USE', rex_d2u_address_media_is_in_use(...));
}

/**
 * Deletes language specific configurations and objects.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<string> Warning message as array
 */
function rex_d2u_address_clang_deleted(rex_extension_point $ep)
{
    $warning = $ep->getSubject();
    $params = $ep->getParams();
    $clang_id = $params['id'];

    // Delete
    $countries = FriendsOfRedaxo\D2UAddress\Country::getAll($clang_id);
    foreach ($countries as $country) {
        $country->delete(false);
    }

    // Delete language settings
    if (rex_config::has('d2u_address', 'lang_replacement_'. $clang_id)) {
        rex_config::remove('d2u_address', 'lang_replacement_'. $clang_id);
    }
    // Delete language replacements
    FriendsOfRedaxo\D2UAddress\LangHelper::factory()->uninstall($clang_id);

    return $warning;
}

/**
 * Checks if media is used by this addon.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<string> Warning message as array
 */
function rex_d2u_address_media_is_in_use(rex_extension_point $ep)
{
    $warning = $ep->getSubject();
    $params = $ep->getParams();
    $filename = (string) $params['filename'];

    // References
    $sql_address = rex_sql::factory();
    $sql_address->setQuery('SELECT address_id, contact_name, company FROM `' . \rex::getTablePrefix() . 'd2u_address_address` '
        .'WHERE picture = :filename', [':filename' => $filename]);

    // Prepare warnings
    // Address pics
    for ($i = 0; $i < $sql_address->getRows(); ++$i) {
        $message = '<a href="javascript:openPage(\'index.php?page=d2u_address/adress&func=edit&entry_id='.
            $sql_address->getValue('address_id') .'\')">'. rex_i18n::msg('d2u_address_rights') .' - '. rex_i18n::msg('d2u_address_address') .': '. $sql_address->getValue('contact_name') .' ('. $sql_address->getValue('company') .'</a>';
        if (!in_array($message, $warning, true)) {
            $warning[] = $message;
        }
        $sql_address->next();
    }

    return $warning;
}

/**
 * Addon translation list.
 * @param rex_extension_point<array<string>> $ep Redaxo extension point
 * @return array<array<string,array<int,array<string,string>>|string>|string> Addon translation list
 */
function rex_d2u_address_translation_list(rex_extension_point $ep) {
    $params = $ep->getParams();
    $source_clang_id = (int) $params['source_clang_id'];
    $target_clang_id = (int) $params['target_clang_id'];
    $filter_type = (string) $params['filter_type'];

    $list = $ep->getSubject();
    $list_entry = [
        'addon_name' => rex_i18n::msg('d2u_address'),
        'pages' => []
    ];

    $continents = FriendsOfRedaxo\D2UAddress\Continent::getTranslationHelperObjects($target_clang_id, $filter_type);
    if (count($continents) > 0) {
        $html_continents = '<ul>';
        foreach ($continents as $continent) {
            if ('' === $continent->name) {
                $continent = new \FriendsOfRedaxo\D2UAddress\Continent($continent->continent_id, $source_clang_id);
            }
            $html_continents .= \TobiasKrais\D2UHelper\BackendHelper::getTranslationItem('d2u_address', 'continent', $continent->continent_id, $continent->name, rex_url::backendPage('d2u_address/continent', ['entry_id' => $continent->continent_id, 'func' => 'edit']));
        }
        $html_continents .= '</ul>';
        
        $list_entry['pages'][] = [
            'title' => rex_i18n::msg('d2u_address_continents'),
            'icon' => 'fa-globe',
            'html' => $html_continents
        ];
    }

    $countries = FriendsOfRedaxo\D2UAddress\Country::getTranslationHelperObjects($target_clang_id, $filter_type);
    if (count($countries) > 0) {
        $html_countries = '<ul>';
        foreach ($countries as $country) {
            if ('' === $country->name) {
                $country = new \FriendsOfRedaxo\D2UAddress\Country($country->country_id, $source_clang_id);
            }
            $html_countries .= \TobiasKrais\D2UHelper\BackendHelper::getTranslationItem('d2u_address', 'country', $country->country_id, $country->name, rex_url::backendPage('d2u_address/continent', ['entry_id' => $country->country_id, 'func' => 'edit']));
        }
        $html_countries .= '</ul>';
        
        $list_entry['pages'][] = [
            'title' => rex_i18n::msg('d2u_address_countries'),
            'icon' => 'fa-flag',
            'html' => $html_countries
        ];
    }

    $list[] = $list_entry;

    return $list;
}

/**
 * Translate a single d2u_address object with AI (D2U_HELPER_TRANSLATE_OBJECT).
 * @param rex_extension_point<array<string,mixed>> $ep Redaxo extension point
 * @return array<string,mixed> Result array with success, name and message
 */
function rex_d2u_address_translate_object(rex_extension_point $ep) {
    $params = $ep->getParams();
    if ('d2u_address' !== ($params['addon'] ?? '')) {
        return $ep->getSubject();
    }

    $type = (string) ($params['type'] ?? '');
    $id = (int) ($params['id'] ?? 0);
    $source_clang_id = (int) ($params['source_clang_id'] ?? 0);
    $target_clang_id = (int) ($params['target_clang_id'] ?? 0);

    // These constructors only return a row when a translation for that clang
    // exists. For missing translations load the source and retarget it.
    $object = null;
    switch ($type) {
        case 'continent':
            $continent = new \FriendsOfRedaxo\D2UAddress\Continent($id, $target_clang_id);
            if ($continent->continent_id <= 0) {
                $continent = new \FriendsOfRedaxo\D2UAddress\Continent($id, $source_clang_id);
                $continent->clang_id = $target_clang_id;
            }
            $object = $continent->continent_id > 0 ? $continent : null;
            break;
        case 'country':
            $country = new \FriendsOfRedaxo\D2UAddress\Country($id, $target_clang_id);
            if ($country->country_id <= 0) {
                $country = new \FriendsOfRedaxo\D2UAddress\Country($id, $source_clang_id);
                $country->clang_id = $target_clang_id;
            }
            $object = $country->country_id > 0 ? $country : null;
            break;
        default:
            return $ep->getSubject();
    }

    if (!$object instanceof \TobiasKrais\D2UHelper\ITranslateable) {
        return ['success' => false, 'name' => '', 'message' => rex_i18n::msg('d2u_helper_translations_ai_error')];
    }

    $success = $object->translateFrom($source_clang_id);

    return [
        'success' => $success,
        'name' => $object->name,
        'message' => $success ? '' : rex_i18n::msg('d2u_helper_translations_ai_error'),
    ];
}