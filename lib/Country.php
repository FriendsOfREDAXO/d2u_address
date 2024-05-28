<?php

namespace FriendsOfRedaxo\D2UAddress;

use rex;
use rex_clang;
use rex_config;
use rex_sql;

use function in_array;
use function is_array;

/**
 * @api
 * Data of country.
 */
class Country implements \TobiasKrais\D2UHelper\ITranslationHelper
{
    /** @var int ID */
    public int $country_id = 0;

    /** @var int Redaxo language ID */
    public int $clang_id = 0;

    /** @var string Name */
    public string $name = '';

    /** @var array<string> ISO language codes fitting for the country */
    public array $iso_lang_codes = [];

    /** @var int Google Maps zoom level */
    public int $maps_zoom = 5;

    /** @var array<int> Adress IDs for this country */
    public array $address_ids = [];

    /** @var string "yes" if translation needs update */
    public string $translation_needs_update = 'delete';

    /**
     * Constructor.
     * @param int $country_id country ID
     * @param int $clang_id Redaxo language ID
     */
    public function __construct($country_id, $clang_id = 0)
    {
        $this->clang_id = $clang_id;
        $query = 'SELECT * FROM '. rex::getTablePrefix() .'d2u_address_countries_lang AS lang '
                .'LEFT JOIN '. rex::getTablePrefix() .'d2u_address_countries AS countries '
                    .'ON lang.country_id = countries.country_id '
                .'WHERE lang.country_id = '. $country_id .' '
                    .'AND clang_id = '. $clang_id;
        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();

        if ($num_rows > 0) {
            $this->country_id = (int) $result->getValue('country_id');
            $iso_lang_codes = preg_grep('/^\s*$/s', explode(',', strtolower((string) $result->getValue('iso_lang_codes'))), PREG_GREP_INVERT);
            $this->iso_lang_codes = is_array($iso_lang_codes) ? $iso_lang_codes : [];
            if ((int) $result->getValue('maps_zoom') > 0) {
                $this->maps_zoom = (int) $result->getValue('maps_zoom');
            }
            $this->name = stripslashes((string) $result->getValue('name'));
            if ('' !== $result->getValue('translation_needs_update') && null !== $result->getValue('translation_needs_update')) {
                $this->translation_needs_update = (string) $result->getValue('translation_needs_update');
            }

            // Get address IDs
            $this->address_ids = $this->getAddressIDs(false);
        }
    }

    /**
     * Deletes the object.
     * @param bool $delete_all If true, all translations and main object are deleted. If
     * false, only this translation will be deleted.
     */
    public function delete($delete_all = true): void
    {
        $query_lang = 'DELETE FROM '. rex::getTablePrefix() .'d2u_address_countries_lang '
            .'WHERE country_id = '. $this->country_id
            . ($delete_all ? '' : ' AND clang_id = '. $this->clang_id);
        $result_lang = rex_sql::factory();
        $result_lang->setQuery($query_lang);

        // If no more lang objects are available, delete
        $query_main = 'SELECT * FROM '. rex::getTablePrefix() .'d2u_address_countries_lang '
            .'WHERE country_id = '. $this->country_id;
        $result_main = rex_sql::factory();
        $result_main->setQuery($query_main);
        if (0 === $result_main->getRows()) {
            $result = rex_sql::factory();
            $result->setQuery('DELETE FROM '. rex::getTablePrefix() .'d2u_address_countries '
                .'WHERE country_id = '. $this->country_id);
            $result->setQuery('DELETE FROM '. rex::getTablePrefix() .'d2u_address_zipcodes '
                .'WHERE country_id = '. $this->country_id);
            $result->setQuery('DELETE FROM '. rex::getTablePrefix() .'d2u_address_2_countries '
                .'WHERE country_id = '. $this->country_id);
        }
    }

    /**
     * Returns addresses for country.
     * @param AddressType|bool $address_type Address type, false if all address types should be used
     * @param bool $online_only True if only online addresses should be returned
     * @return Address[] Found addresses
     */
    public function getAddresses($address_type = false, $online_only = true)
    {
        $addresses = [];
        $address_ids = $this->getAddressIDs($online_only);
        foreach ($address_ids as $address_id) {
            $address = new Address($address_id, $this->clang_id);
            if (false === $address_type || ($address_type instanceof AddressType && in_array($address_type->address_type_id, $address->address_type_ids, true))) {
                $addresses[$address->priority] = $address;
            }
        }

        ksort($addresses);
        return $addresses;
    }

    /**
     * Returns address IDs for country.
     * @param bool $online_only True if only online addresses should be returned
     * @return array<int> address IDs
     */
    private function getAddressIDs($online_only = true)
    {
        $query = 'SELECT a2c.country_id, a2c.address_id FROM '. rex::getTablePrefix() .'d2u_address_2_countries AS a2c ';
        if ($online_only) {
            $query .= 'LEFT JOIN '. rex::getTablePrefix() .'d2u_address_address AS address ON a2c.address_id = address.address_id ';
        }
        $query .= 'WHERE a2c.country_id = '. $this->country_id .' ';
        if ($online_only) {
            $query .= "AND address.online_status = 'online'";
        }

        $result = rex_sql::factory();
        $result->setQuery($query);

        $address_ids = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $address_ids[] = (int) $result->getValue('address_id');
            $result->next();
        }

        return $address_ids;
    }

    /**
     * Returns continent IDs for country.
     * @return array<int> address IDs
     */
    public function getContinentIDs()
    {
        $query = 'SELECT continent_id FROM '. rex::getTablePrefix() .'d2u_address_continents '
            ."WHERE country_ids LIKE '%|". $this->country_id ."|%' ";

        $result = rex_sql::factory();
        $result->setQuery($query);

        $continent_ids = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $continent_ids[] = (int) $result->getValue('continent_id');
            $result->next();
        }

        return $continent_ids;
    }

    /**
     * Returns zipcode objects for country.
     * @return ZipCode[] Found zipcodes
     */
    public function getZipCodes()
    {
        $query = 'SELECT zipcode_id FROM '. rex::getTablePrefix() .'d2u_address_zipcodes '
            .'WHERE country_id = '. $this->country_id .' '
            .'ORDER BY range_from';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $zipcodes = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $zipcodes[] = new ZipCode((int) $result->getValue('zipcode_id'), $this->clang_id);
            $result->next();
        }
        return $zipcodes;
    }

    /**
     * Gets all countries.
     * @param int $clang_id Redaxo language ID
     * @return Country[] array with country objects
     */
    public static function getAll($clang_id = 0)
    {
        $query = 'SELECT country_id FROM '. rex::getTablePrefix() .'d2u_address_countries_lang '
                .'WHERE clang_id = '. (0 === $clang_id ? rex_clang::getCurrentId() : $clang_id) .' '
                .'ORDER BY name';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $countries = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $countries[] = new self((int) $result->getValue('country_id'), $clang_id);
            $result->next();
        }

        return $countries;
    }

    /**
     * Get countries by ISO lang code.
     * @param string $iso_lang_code ISO language code
     * @param int $clang_id Redaxo language ID
     * @return Country[] array with fitting countries
     */
    public static function getByLangCode($iso_lang_code, $clang_id = 0)
    {
        $query = 'SELECT country_id FROM '. rex::getTablePrefix() .'d2u_address_countries '
                .'WHERE iso_lang_codes LIKE "%'. substr($iso_lang_code, 0, 2) .'%" ';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $countries = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $country = new self((int) $result->getValue('country_id'), $clang_id);
            if (in_array(strtolower($iso_lang_code), $country->iso_lang_codes, true)) {
                return [$country];
            }
            $countries[] = $country;
            $result->next();
        }

        return $countries;
    }

    /**
     * Get objects concerning translation updates.
     * @param int $clang_id Redaxo language ID
     * @param string $type 'update' or 'missing'
     * @return Country[] array with country objects
     */
    public static function getTranslationHelperObjects($clang_id, $type)
    {
        $query = 'SELECT country_id FROM '. rex::getTablePrefix() .'d2u_address_countries_lang '
                .'WHERE clang_id = '. $clang_id ." AND translation_needs_update = 'yes' "
                .'ORDER BY name';
        if ('missing' === $type) {
            $query = 'SELECT main.country_id FROM '. rex::getTablePrefix() .'d2u_address_countries AS main '
                    .'LEFT JOIN '. rex::getTablePrefix() .'d2u_address_countries_lang AS target_lang '
                        .'ON main.country_id = target_lang.country_id AND target_lang.clang_id = '. $clang_id .' '
                    .'LEFT JOIN '. rex::getTablePrefix() .'d2u_address_countries_lang AS default_lang '
                        .'ON main.country_id = default_lang.country_id AND default_lang.clang_id = '. rex_config::get('d2u_helper', 'default_lang') .' '
                    .'WHERE target_lang.country_id IS NULL '
                    .'ORDER BY default_lang.name';
            $clang_id = (int) rex_config::get('d2u_helper', 'default_lang');
        }
        $result = rex_sql::factory();
        $result->setQuery($query);

        $objects = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $objects[] = new self((int) $result->getValue('country_id'), $clang_id);
            $result->next();
        }

        return $objects;
    }

    /**
     * Replaces mutated vowels form name.
     * @param string $country_name Original country name
     * @return string Normalized country name
     */
    public static function normalizeCountryName($country_name)
    {
        return str_replace(
            ['À', 'Á', 'Â', 'Ã', 'Ä',  'Å', 'Æ',  'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö',  'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß',  'à', 'á', 'â', 'ã', 'ä',  'å', 'æ',  'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö',  'ø', 'ù', 'ú', 'û', 'ü',  'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ',  'ĳ',  'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ',  'œ',  'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ',  'ǽ',  'Ǿ', 'ǿ'],
            ['A', 'A', 'A', 'A', 'Ae', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'Oe', 'O', 'U', 'U', 'U', 'U', 'Y', 'ss', 'a', 'a', 'a', 'a', 'ae', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'oe', 'o', 'u', 'u', 'u', 'ue', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'],
            $country_name,
        );
    }

    /**
     * Updates or inserts the object into database.
     * @return bool true if error occured
     */
    public function save()
    {
        $error = false;

        // Save the not language specific part
        $pre_save_country = new self($this->country_id, $this->clang_id);

        $result = rex_sql::factory();
        if (0 === $this->country_id || $pre_save_country !== $this) {
            $query = rex::getTablePrefix() .'d2u_address_countries SET '
                    ."iso_lang_codes = '". implode(',', $this->iso_lang_codes) ."', "
                    .'maps_zoom = '. $this->maps_zoom .' ';

            if (0 === $this->country_id) {
                $query = 'INSERT INTO '. $query;
            } else {
                $query = 'UPDATE '. $query .' WHERE country_id = '. $this->country_id;
            }

            $result->setQuery($query);
            if (0 === $this->country_id) {
                $this->country_id = (int) $result->getLastId();
                $error = $result->hasError();
            }

            // Update assigned addresses
            $result->setQuery('DELETE FROM '. rex::getTablePrefix() .'d2u_address_2_countries WHERE country_id = '. $this->country_id);
            foreach ($this->address_ids as $address_id) {
                $query = 'INSERT INTO '. rex::getTablePrefix() .'d2u_address_2_countries SET '
                        .'country_id = '. $this->country_id .', '
                        .'address_id = '. $address_id;
                $result->setQuery($query);
                $error = $result->hasError();
            }
        }

        if (!$error) {
            // Save the language specific part
            $pre_save_country = new self($this->country_id, $this->clang_id);
            if ($pre_save_country !== $this) {
                $query = 'REPLACE INTO '. rex::getTablePrefix() .'d2u_address_countries_lang SET '
                        .'country_id = '. $this->country_id .', '
                        .'clang_id = '. $this->clang_id .', '
                        ."name = '". addslashes($this->name) ."', "
                        ."translation_needs_update = '". $this->translation_needs_update ."' ";
                $result->setQuery($query);
                $error = $result->hasError();
            }
        }

        return $error;
    }
}

namespace D2U_Address;

/**
 * @deprecated since 1.5.0, to be removed in 2.0.0. Use FriendsOfRedaxo\D2UAddress\Country instead.
 */
class Country extends \FriendsOfRedaxo\D2UAddress\Country {

}