<?php

namespace FriendsOfREDAXO\D2UAddress;

use rex;
use rex_clang;
use rex_config;
use rex_sql;

use function is_array;

/**
 * @api
 * Data of country.
 */
class Continent implements \TobiasKrais\D2UHelper\ITranslationHelper
{
    /** @var int ID */
    public int $continent_id = 0;

    /** @var int Redaxo language ID */
    public int $clang_id = 0;

    /** @var string Name */
    public string $name = '';

    /** @var array<int> ISO language codes fitting for the country */
    public array $country_ids = [];

    /** @var string "yes" if translation needs update */
    public string $translation_needs_update = 'delete';

    /**
     * Constructor.
     * @param int $continent_id continent ID
     * @param int $clang_id Redaxo language ID
     */
    public function __construct($continent_id, $clang_id = 0)
    {
        $this->clang_id = $clang_id;
        $query = 'SELECT * FROM '. rex::getTablePrefix() .'d2u_address_continents_lang AS lang '
                .'LEFT JOIN '. rex::getTablePrefix() .'d2u_address_continents AS continents '
                    .'ON lang.continent_id = continents.continent_id '
                .'WHERE lang.continent_id = '. $continent_id .' '
                    .'AND clang_id = '. $clang_id;
        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();

        if ($num_rows > 0) {
            $this->continent_id = (int) $result->getValue('continent_id');
            $country_ids = preg_grep('/^\s*$/s', explode('|', (string) $result->getValue('country_ids')), PREG_GREP_INVERT);
            $this->country_ids = is_array($country_ids) ? array_map('intval', $country_ids) : [];
            $this->name = stripslashes((string) $result->getValue('name'));
            if ('' !== $result->getValue('translation_needs_update') && null !== $result->getValue('translation_needs_update')) {
                $this->translation_needs_update = (string) $result->getValue('translation_needs_update');
            }
        }
    }

    /**
     * Deletes the object.
     * @param bool $delete_all If true, all translations and main object are deleted. If
     * false, only this translation will be deleted.
     */
    public function delete($delete_all = true): void
    {
        $query_lang = 'DELETE FROM '. rex::getTablePrefix() .'d2u_address_continents_lang '
            .'WHERE continent_id = '. $this->continent_id
            . ($delete_all ? '' : ' AND clang_id = '. $this->clang_id);
        $result_lang = rex_sql::factory();
        $result_lang->setQuery($query_lang);

        // If no more lang objects are available, delete
        $query_main = 'SELECT * FROM '. rex::getTablePrefix() .'d2u_address_continents_lang '
            .'WHERE continent_id = '. $this->continent_id;
        $result_main = rex_sql::factory();
        $result_main->setQuery($query_main);
        if (0 === (int) $result_main->getRows()) {
            $result = rex_sql::factory();
            $result->setQuery('DELETE FROM '. rex::getTablePrefix() .'d2u_address_continents '
                .'WHERE continent_id = '. $this->continent_id);
        }
    }

    /**
     * Gets all continents.
     * @param int $clang_id Redaxo language ID
     * @return array<Continent> array with country objects
     */
    public static function getAll($clang_id = 0)
    {
        $query = 'SELECT continent_id FROM '. rex::getTablePrefix() .'d2u_address_continents_lang '
                .'WHERE clang_id = '. (0 === $clang_id ? rex_clang::getCurrentId() : $clang_id) .' '
                .'ORDER BY name';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $continents = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $continents[] = new self((int) $result->getValue('continent_id'), $clang_id);
            $result->next();
        }

        return $continents;
    }

    /**
     * Get objects concerning translation updates.
     * @param int $clang_id Redaxo language ID
     * @param string $type 'update' or 'missing'
     * @return Continent[] array with country objects
     */
    public static function getTranslationHelperObjects($clang_id, $type)
    {
        $query = 'SELECT continent_id FROM '. rex::getTablePrefix() .'d2u_address_continents_lang '
                .'WHERE clang_id = '. $clang_id ." AND translation_needs_update = 'yes' "
                .'ORDER BY name';
        if ('missing' === $type) {
            $query = 'SELECT main.continent_id FROM '. rex::getTablePrefix() .'d2u_address_continents AS main '
                    .'LEFT JOIN '. rex::getTablePrefix() .'d2u_address_continents_lang AS target_lang '
                        .'ON main.continent_id = target_lang.continent_id AND target_lang.clang_id = '. $clang_id .' '
                    .'LEFT JOIN '. rex::getTablePrefix() .'d2u_address_continents_lang AS default_lang '
                        .'ON main.continent_id = default_lang.continent_id AND default_lang.clang_id = '. rex_config::get('d2u_helper', 'default_lang') .' '
                    .'WHERE target_lang.continent_id IS NULL '
                    .'ORDER BY default_lang.name';
            $clang_id = (int) rex_config::get('d2u_helper', 'default_lang');
        }
        $result = rex_sql::factory();
        $result->setQuery($query);

        $objects = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $objects[] = new self((int) $result->getValue('continent_id'), $clang_id);
            $result->next();
        }

        return $objects;
    }

    /**
     * Updates or inserts the object into database.
     * @return bool true if error occured
     */
    public function save()
    {
        $error = false;

        // Save the not language specific part
        $pre_save_country = new self($this->continent_id, $this->clang_id);

        $result = rex_sql::factory();
        if (0 === $this->continent_id || $pre_save_country !== $this) {
            $query = rex::getTablePrefix() .'d2u_address_continents SET '
                    ."country_ids = '|". implode('|', $this->country_ids) ."|' ";

            if (0 === $this->continent_id) {
                $query = 'INSERT INTO '. $query;
            } else {
                $query = 'UPDATE '. $query .' WHERE continent_id = '. $this->continent_id;
            }

            $result->setQuery($query);
            if (0 === $this->continent_id) {
                $this->continent_id = (int) $result->getLastId();
                $error = $result->hasError();
            }
        }

        if (!$error) {
            // Save the language specific part
            $pre_save_country = new self($this->continent_id, $this->clang_id);
            if ($pre_save_country !== $this) {
                $query = 'REPLACE INTO '. rex::getTablePrefix() .'d2u_address_continents_lang SET '
                        .'continent_id = '. $this->continent_id .', '
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
 * @deprecated since 1.5.0, to be removed in 2.0.0. Use FriendsOfREDAXO\D2UAddress\Continent instead.
 */
class Continent extends \FriendsOfREDAXO\D2UAddress\Continent {

}