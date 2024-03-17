<?php

namespace FriendsOfREDAXO\D2UAddress;

use rex;
use rex_sql;

use function array_key_exists;

/**
 * @api
 * Address type
 */
class AddressType
{
    /** @var int Database ID */
    public int $address_type_id = 0;

    /** @var int Redaxo language ID */
    public int $clang_id = 0;

    /** @var string Name */
    public string $name = '';

    /** @var bool Show full address in frontend? "yes" or "no" */
    public bool $show_address_details = false;

    /** @var bool Show country selection in frontend? "yes" or "no" */
    public bool $show_country_select = false;

    /** @var int Google maps zoom level */
    public int $maps_zoom = 5;

    /** @var int Redaxo article ID */
    public int $article_id = 0;

    /** @var int default country ID */
    public int $default_address_id = 0;

    /**
     * Constructor.
     * @param int $address_type_id ID
     * @param int $clang_id redaxo language ID
     */
    public function __construct($address_type_id, $clang_id)
    {
        $this->clang_id = $clang_id;
        $query = 'SELECT * FROM '. rex::getTablePrefix() .'d2u_address_types '
                .'WHERE address_type_id = '. $address_type_id;
        $result = rex_sql::factory();
        $result->setQuery($query);

        if ($result->getRows() > 0) {
            $this->address_type_id = (int) $result->getValue('address_type_id');
            $this->name = stripslashes((string) $result->getValue('name'));
            $this->show_address_details = 'yes' === (string) $result->getValue('show_address_details') ? true : false;
            $this->show_country_select = 'yes' === (string) $result->getValue('show_country_select') ? true : false;
            if ((int) $result->getValue('maps_zoom') > 0) {
                $this->maps_zoom = (int) $result->getValue('maps_zoom');
            }
            $this->article_id = (int) $result->getValue('article_id');
            $this->default_address_id = (int) $result->getValue('default_address_id');
        }
    }

    /**
     * Deletes the object.
     */
    public function delete(): void
    {
        $query = 'DELETE FROM '. rex::getTablePrefix() .'d2u_address_types '
            .'WHERE address_type_id = '. $this->address_type_id;
        $result = rex_sql::factory();
        $result->setQuery($query);
    }

    /**
     * Get all address types.
     * @param int $clang_id redaxo language ID
     * @return array<int, AddressType> Array with all address types
     */
    public static function getAll($clang_id)
    {
        $query = 'SELECT address_type_id FROM '. rex::getTablePrefix() .'d2u_address_types';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $address_types = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $address_types[] = new self((int) $result->getValue('address_type_id'), $clang_id);
            $result->next();
        }

        return $address_types;
    }

    /**
     * Returns addresses for adress type.
     * @param bool $online_only true to get only online addresses
     * @return array<int, Address> found addresses
     */
    public function getAddresses($online_only = true)
    {
        $query = 'SELECT address_id, priority FROM '. rex::getTablePrefix() .'d2u_address_address '
                ."WHERE address_type_ids LIKE '%|". $this->address_type_id ."|%' ";
        if ($online_only) {
            $query .= "AND online_status = 'online' ";
        }
        $query .= 'ORDER BY priority';
        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();

        $addresses = [];
        for ($i = 0; $i < $num_rows; ++$i) {
            $addresses[(int) $result->getValue('priority')] = new Address((int) $result->getValue('address_id'), $this->clang_id);
            $result->next();
        }

        ksort($addresses);
        return $addresses;
    }

    /**
     * Gets all countries used by this object.
     * @param bool $online_only If true only online objects are returned
     * @return array<string, Country> Array with country objects
     */
    public function getCountries($online_only = true)
    {
        $query = 'SELECT address_id FROM '. rex::getTablePrefix() .'d2u_address_address '
                ."WHERE address_type_ids LIKE '%|". $this->address_type_id ."|%' ";
        if ($online_only) {
            $query .= "AND online_status = 'online'";
        }
        $result = rex_sql::factory();
        $result->setQuery($query);

        $countries = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $address = new Address((int) $result->getValue('address_id'), $this->clang_id);
            foreach ($address->getReferringCountries() as $country) {
                if (!array_key_exists(Country::normalizeCountryName($country->name), $countries)) {
                    $countries[Country::normalizeCountryName($country->name)] = $country;
                }
            }
            $result->next();
        }
        $default_address = new Address($this->default_address_id, $this->clang_id);
        if ($default_address->country instanceof Country) {
            $countries[Country::normalizeCountryName($default_address->country->name)] = $default_address->country;
        }
        ksort($countries);

        return $countries;
    }

    /**
     * Updates or inserts the object into database.
     * @return bool true if error occured
     */
    public function save()
    {
        $error = false;

        $query = rex::getTablePrefix() .'d2u_address_types SET '
                ."name = '". addslashes($this->name) ."', "
                ."show_address_details = '". ($this->show_address_details ? 'yes' : 'no') ."', "
                ."show_country_select = '". ($this->show_country_select ? 'yes' : 'no') ."', "
                .'maps_zoom = '. $this->maps_zoom .', '
                .'default_address_id = '. $this->default_address_id .', '
                .'article_id = '. $this->article_id .' ';
        if (0 === $this->address_type_id) {
            $query = 'INSERT INTO '. $query;
        } else {
            $query = 'UPDATE '. $query .' WHERE address_type_id = '. $this->address_type_id;
        }

        $result = rex_sql::factory();
        $result->setQuery($query);
        if (0 === $this->address_type_id) {
            $this->address_type_id = (int) $result->getLastId();
            $error = $result->hasError();
        }

        return $error;
    }
}

namespace D2U_Address;

/**
 * @deprecated since 1.5.0, to be removed in 2.0.0. Use FriendsOfREDAXO\D2UAddress\AddressType instead.
 */
class AddressType extends \FriendsOfREDAXO\D2UAddress\AddressType {

}