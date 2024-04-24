<?php

namespace FriendsOfRedaxo\D2UAddress;

use rex;
use rex_sql;

use function is_array;

/**
 * @api
 * Zip code object
 */
class ZipCode
{
    /** @var int Database ID */
    public int $zipcode_id = 0;

    /** @var int Redaxo language ID */
    public int $clang_id = 0;

    /** @var int Start range */
    public int $range_from = 0;

    /** @var int End range */
    public int $range_to = 0;

    /** @var Country Country */
    public Country $country;

    /** @var array<int> adress_ids */
    public array $address_ids = [];

    /**
     * Constructor.
     * @param int $zipcode_id ID
     * @param int $clang_id redaxo language ID
     */
    public function __construct($zipcode_id, $clang_id)
    {
        $this->clang_id = $clang_id;
        $query = 'SELECT * FROM '. rex::getTablePrefix() .'d2u_address_zipcodes '
                .'WHERE zipcode_id = '. $zipcode_id;
        $result = rex_sql::factory();
        $result->setQuery($query);

        if ($result->getRows() > 0) {
            $this->zipcode_id = (int) $result->getValue('zipcode_id');
            $address_ids = preg_grep('/^\s*$/s', explode('|', (string) $result->getValue('address_ids')), PREG_GREP_INVERT);
            $this->address_ids = is_array($address_ids) ? array_map('intval', $address_ids) : [];
            $this->range_from = (int) $result->getValue('range_from');
            $this->range_to = (int) $result->getValue('range_to');
            $this->country = new Country((int) $result->getValue('country_id'), $clang_id);
        }
    }

    /**
     * Deletes the object.
     */
    public function delete(): void
    {
        $query = 'DELETE FROM '. rex::getTablePrefix() .'d2u_address_zipcodes '
            .'WHERE zipcode_id = '. $this->zipcode_id;
        $result = rex_sql::factory();
        $result->setQuery($query);
    }

    /**
     * Get all zip codes.
     * @param Country $country Country
     * @param int $zip_code Zipcode
     * @return ZipCode|bool Zipcode object
     */
    public static function get($country, $zip_code)
    {
        $query = 'SELECT zipcode_id FROM '. rex::getTablePrefix() .'d2u_address_zipcodes '
            .'WHERE range_from <= '. $zip_code .' AND range_to >= '. $zip_code .' AND country_id = '. $country->country_id;
        $result = rex_sql::factory();
        $result->setQuery($query);

        if ($result->getRows() > 0) {
            $zipcode = new self((int) $result->getValue('zipcode_id'), $country->clang_id);
            return $zipcode;
        }
        return false;
    }

    /**
     * Get all zip codes.
     * @param int $country_id Country ID
     * @param int $clang_id Redaxo clang ID
     * @return ZipCode[] Array with all zip codes for a country
     */
    public static function getAll($country_id, $clang_id)
    {
        $query = 'SELECT zipcode_id FROM '. rex::getTablePrefix() .'d2u_address_zipcodes '
            .'WHERE country_id  = '. $country_id;
        $result = rex_sql::factory();
        $result->setQuery($query);

        $zip_codes = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $zip_codes[] = new self((int) $result->getValue('zipcode_id'), $clang_id);
            $result->next();
        }

        return $zip_codes;
    }

    /**
     * Returns addresses for zip code.
     * @param bool $online_only true to get only online addresses
     * @return array<Address> found addresses
     */
    public function getAdresses($online_only = true)
    {
        $addresses = [];
        foreach ($this->address_ids as $address_id) {
            $address = new Address($address_id, $this->clang_id);
            if (false === $online_only || ('online' === $address->online_status)) {
                $addresses[$address->priority] = new Address($address_id, $this->clang_id);
            }
        }
        ksort($addresses);

        return $addresses;
    }

    /**
     * Proves whether the object has addresses and in case it has, are these online?
     * @return bool true if there are online addresses for the object
     */
    public function isOnline()
    {
        foreach ($this->address_ids as $address_id) {
            $address = new Address($address_id, $this->clang_id);
            if ('online' === $address->online_status) {
                return true;
            }
        }

        return false;
    }

    /**
     * Updates or inserts the object into database.
     * @return bool true if error occured
     */
    public function save()
    {
        $query = rex::getTablePrefix() .'d2u_address_zipcodes SET '
                ."address_ids = '|". implode('|', $this->address_ids) ."|', "
                ."range_from = '". $this->range_from ."', "
                ."range_to = '". $this->range_to ."', "
                .'country_id = '. $this->country->country_id .' ';
        if (0 === $this->zipcode_id) {
            $query = 'INSERT INTO '. $query;
        } else {
            $query = 'UPDATE '. $query .' WHERE zipcode_id = '. $this->zipcode_id;
        }

        $result = rex_sql::factory();
        $result->setQuery($query);
        if (0 === $this->zipcode_id) {
            $this->zipcode_id = (int) $result->getLastId();
        }

        return $result->hasError();
    }
}

namespace D2U_Address;

/**
 * @deprecated since 1.5.0, to be removed in 2.0.0. Use \FriendsOfRedaxo\D2UAddress\ZipCode instead.
 */
class ZipCode extends \FriendsOfRedaxo\D2UAddress\ZipCode {}