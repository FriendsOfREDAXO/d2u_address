<?php

if (\rex::isBackend() && is_object(\rex::getUser())) {
    rex_perm::register('d2u_address[]', rex_i18n::msg('d2u_address_rights'));
    rex_perm::register('d2u_address[edit_data]', rex_i18n::msg('d2u_address_rights_edit_data'), rex_perm::OPTIONS);
    rex_perm::register('d2u_address[edit_lang]', rex_i18n::msg('d2u_address_rights_edit_lang'), rex_perm::OPTIONS);
    rex_perm::register('d2u_address[settings]', rex_i18n::msg('d2u_address_rights_settings'), rex_perm::OPTIONS);
}

if (\rex::isBackend()) {
    rex_extension::register('CLANG_DELETED', 'rex_d2u_address_clang_deleted');
    rex_extension::register('D2U_HELPER_TRANSLATION_LIST', 'rex_d2u_address_translation_list');
    rex_extension::register('MEDIA_IS_IN_USE', 'rex_d2u_address_media_is_in_use');
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
    $countries = D2U_Address\Country::getAll($clang_id);
    foreach ($countries as $country) {
        $country->delete(false);
    }

    // Delete language settings
    if (rex_config::has('d2u_address', 'lang_replacement_'. $clang_id)) {
        rex_config::remove('d2u_address', 'lang_replacement_'. $clang_id);
    }
    // Delete language replacements
    d2u_address_lang_helper::factory()->uninstall($clang_id);

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
    $filename = addslashes($params['filename']);

    // References
    $sql_address = rex_sql::factory();
    $sql_address->setQuery('SELECT address_id, contact_name, company FROM `' . \rex::getTablePrefix() . 'd2u_address_address`'
        .'WHERE picture = "'. $filename .'"');

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
 * @return array<int,array<string,string|array<string,string>>> Addon translation list
 */
function rex_d2u_address_translation_list(rex_extension_point $ep) {
    $params = $ep->getParams();
    $source_clang_id = $params['source_clang_id'];
    $target_clang_id = $params['target_clang_id'];
    $filter_type = $params['filter_type'];

    $list = $ep->getSubject();
    $list_entry = [
        'addon_name' => rex_i18n::msg('d2u_address'),
        'pages' => []
    ];

    $continents = D2U_Address\Continent::getTranslationHelperObjects($target_clang_id, $filter_type);
    if (count($continents) > 0) {
        $html_continents = '<ul>';
        foreach ($continents as $continent) {
            if ('' === $continent->name) {
                $continent = new \D2U_Address\Continent($continent->continent_id, $source_clang_id);
            }
            $html_continents .= '<li><a href="'. rex_url::backendPage('d2u_address/continent', ['entry_id' => $continent->continent_id, 'func' => 'edit']) .'">'. $continent->name .'</a></li>';
        }
        $html_continents .= '</ul>';
        
        $list_entry['pages'][] = [
            'title' => rex_i18n::msg('d2u_address_continents'),
            'icon' => 'fa-globe',
            'html' => $html_continents
        ];
    }

    $countries = D2U_Address\Country::getTranslationHelperObjects($target_clang_id, $filter_type);
    if (count($countries) > 0) {
        $html_countries = '<ul>';
        foreach ($countries as $country) {
            if ('' === $country->name) {
                $country = new \D2U_Address\Country($country->country_id, $source_clang_id);
            }
            $html_countries .= '<li><a href="'. rex_url::backendPage('d2u_address/continent', ['entry_id' => $country->country_id, 'func' => 'edit']) .'">'. $country->name .'</a></li>';
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