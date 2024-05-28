<?php

namespace FriendsOfRedaxo\D2UAddress;

use rex;
use rex_sql;

use function count;
use function is_array;
use function strlen;

/**
 * @api
 * Address class
 */
class Address
{
    /** @var int Database address ID */
    public int $address_id = 0;

    /** @var int Redaxo language ID */
    public int $clang_id = 0;

    /** @var string Company Name */
    public string $company = '';

    /** @var string Appendix for company name */
    public string $company_appendix = '';

    /** @var string Name of contact person */
    public string $contact_name = '';

    /** @var string Street and house number */
    public string $street = '';

    /** @var string Additional address */
    public string $additional_address = '';

    /** @var string ZIP code */
    public string $zip_code = '';

    /** @var string city */
    public string $city = '';

    /** @var Country|bool Country */
    public Country|bool $country = false;

    /** @var array<int> IDs of assigned countries */
    public array $country_ids = [];

    /** @var float Latitude */
    public float $latitude = 0;

    /** @var float Longitude */
    public float $longitude = 0;

    /** @var string E-Mailadresse der Address */
    public string $email = '';

    /** @var string Phone number */
    public string $phone = '';

    /** @var string Mobile phone number */
    public string $mobile = '';

    /** @var string Fax number */
    public string $fax = '';

    /** @var string bild der Address */
    public string $picture = '';

    /** @var array<int> Adress types */
    public array $address_type_ids = [];

    /** @var int Redaxo article ID with detailed information */
    public int $article_id = 0;

    /** @var string URL for detailed information */
    public string $url = '';

    /** @var int Sort priority */
    public int $priority = 0;

    /** @var string online status, either "online" or "offline" */
    public string $online_status = 'offline';

    /**
     * Constructor.
     * @param int $address_id Address ID
     * @param int $clang_id Redaxo Language ID
     */
    public function __construct($address_id, $clang_id)
    {
        $this->clang_id = $clang_id;
        $query = 'SELECT * FROM '. rex::getTablePrefix() .'d2u_address_address '
                .'WHERE address_id = '. $address_id;
        $result = rex_sql::factory();
        $result->setQuery($query);
        $num_rows = $result->getRows();

        if ($num_rows > 0) {
            $this->address_id = (int) $result->getValue('address_id');
            $this->company = stripslashes((string) $result->getValue('company'));
            $this->company_appendix = stripslashes((string) $result->getValue('company_appendix'));
            $this->contact_name = stripslashes((string) $result->getValue('contact_name'));
            $this->street = stripslashes((string) $result->getValue('street'));
            $this->additional_address = stripslashes((string) $result->getValue('additional_address'));
            $this->zip_code = (string) $result->getValue('zip_code');
            $this->city = stripslashes((string) $result->getValue('city'));
            if ((int) $result->getValue('country_id') > 0) {
                $this->country = new Country((int) $result->getValue('country_id'), $clang_id);
            }
            $this->latitude = (float) $result->getValue('latitude');
            $this->longitude = (float) $result->getValue('longitude');
            $this->email = (string) $result->getValue('email');
            $this->url = (string) $result->getValue('url');
            $this->phone = (string) $result->getValue('phone');
            $this->mobile = (string) $result->getValue('mobile');
            $this->fax = (string) $result->getValue('fax');
            if ('' !== $result->getValue('picture')) {
                $this->picture = (string) $result->getValue('picture');
            }
            $address_type_ids = preg_grep('/^\s*$/s', explode('|', (string) $result->getValue('address_type_ids')), PREG_GREP_INVERT);
            $this->address_type_ids = is_array($address_type_ids) ? array_map('intval', $address_type_ids) : [];
            $this->article_id = (int) $result->getValue('article_id');
            $this->priority = (int) $result->getValue('priority');
            if ('' !== $result->getValue('online_status')) {
                $this->online_status = (string) $result->getValue('online_status');
            }

            // correct URL if needed
            if (strlen($this->url) > 3 && 'http' !== substr($this->url, 0, 4)) {
                $this->url = 'https://'. $this->url;
            }

            $this->country_ids = $this->getAssignedCountryIDs();
        }
    }

    /**
     * Changes the status of the object.
     */
    public function changeStatus(): void
    {
        if ('online' === $this->online_status) {
            if ($this->address_id > 0) {
                $query = 'UPDATE '. rex::getTablePrefix() .'d2u_address_address '
                    ."SET online_status = 'offline' "
                    .'WHERE address_id = '. $this->address_id;
                $result = rex_sql::factory();
                $result->setQuery($query);
            }
            $this->online_status = 'offline';
        } else {
            if ($this->address_id > 0) {
                $query = 'UPDATE '. rex::getTablePrefix() .'d2u_address_address '
                    ."SET online_status = 'online' "
                    .'WHERE address_id = '. $this->address_id;
                $result = rex_sql::factory();
                $result->setQuery($query);
            }
            $this->online_status = 'online';
        }
    }

    /**
     * Deletes the object.
     */
    public function delete(): void
    {
        $result = rex_sql::factory();
        $result->setQuery($query = 'DELETE FROM '. rex::getTablePrefix() .'d2u_address_address '
            .'WHERE address_id = '. $this->address_id);
        $result->setQuery('DELETE FROM '. rex::getTablePrefix() .'d2u_address_2_countries '
            .'WHERE address_id = '. $this->address_id);
    }

    /**
     * Returns addresses.
     * @param int $clang_id Redaxo Language ID
     * @param AddressType|bool $address_type Address type, default: false (all)
     * @param bool $online_only return only online adresses
     * @return array<Address> Addresses
     */
    public static function getAll($clang_id, $address_type = false, $online_only = true)
    {
        $query = 'SELECT address_id, priority FROM '. rex::getTablePrefix() .'d2u_address_address ';
        $where = [];
        if ($address_type instanceof AddressType) {
            $where[] = 'address_type_ids LIKE "%|'. $address_type->address_type_id .'|%"';
        }
        if ($online_only) {
            $where[] = 'online_status = "online"';
        }
        if (count($where) > 0) {
            $query .= 'WHERE '. implode(' AND ', $where);
        }
        $query .= ' ORDER BY company, contact_name, priority';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $addresses = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $addresses[(int) $result->getValue('priority')] = new self((int) $result->getValue('address_id'), $clang_id);
            $result->next();
        }
        ksort($addresses);
        return $addresses;
    }

    /**
     * Returns country ids of countries this address is assigned to.
     * @return array<int> country IDs
     */
    private function getAssignedCountryIDs()
    {
        $query = 'SELECT a2c.country_id FROM '. rex::getTablePrefix() .'d2u_address_2_countries AS a2c '
            .'LEFT JOIN '. rex::getTablePrefix() .'d2u_address_countries_lang AS lang '
                .'ON a2c.country_id = lang.country_id AND lang.clang_id = '. $this->clang_id .' '
            .'WHERE a2c.address_id = '. $this->address_id .' '
            .'ORDER BY name';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $country_ids = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $country_ids[] = (int) $result->getValue('country_id');
            $result->next();
        }

        return $country_ids;
    }

    /**
     * Returns address types reffering address.
     * @return AddressType[] AddressType objects
     */
    public function getReferringAddressTypes()
    {
        $query = 'SELECT address_type_id FROM '. rex::getTablePrefix() .'d2u_address_types '
            .'WHERE default_address_id = '. $this->address_id .' '
            .'ORDER BY name';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $address_types = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $address_types[] = new AddressType((int) $result->getValue('address_type_id'), $this->clang_id);
            $result->next();
        }
        return $address_types;
    }

    /**
     * Returns countries reffering address.
     * @return Country[] Country objects, country ID is key
     */
    public function getReferringCountries()
    {
        $countries = [];
        foreach ($this->country_ids as $country_id) {
            $countries[$country_id] = new Country($country_id, $this->clang_id);
        }

        return $countries;
    }

    /**
     * Returns zip codes reffering address.
     * @return ZipCode[] ZipCode objects
     */
    public function getReferringZipCodes()
    {
        $query = 'SELECT zipcode_id FROM '. rex::getTablePrefix() .'d2u_address_zipcodes '
            .'WHERE address_ids LIKE "%|'. $this->address_id .'|%" '
            .'ORDER BY range_from';
        $result = rex_sql::factory();
        $result->setQuery($query);

        $zip_codes = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $zip_codes[] = new ZipCode((int) $result->getValue('zipcode_id'), $this->clang_id);
            $result->next();
        }

        return $zip_codes;
    }

    /**
     * Updates or inserts the object into database.
     * @return bool true if error occured
     */
    public function save()
    {
        $error = false;

        $pre_save_object = new self($this->address_id, $this->clang_id);

        // save priority, but only if new or changed
        if ($this->priority !== $pre_save_object->priority || 0 === $this->address_id) {
            $this->setPriority();
        }

        $query = rex::getTablePrefix() .'d2u_address_address SET '
                ."company = '". addslashes($this->company) ."', "
                ."company_appendix = '". addslashes($this->company_appendix) ."', "
                ."contact_name = '". addslashes($this->contact_name) ."', "
                ."street = '". addslashes($this->street) ."', "
                ."additional_address = '". addslashes($this->additional_address) ."', "
                ."zip_code = '". $this->zip_code ."', "
                ."city = '". addslashes($this->city) ."', "
                .'country_id = '. ($this->country instanceof Country ? $this->country->country_id : 0) .', '
                .'latitude = '. $this->latitude .', '
                .'longitude = '. $this->longitude .', '
                ."email = '". $this->email ."', "
                ."url = '". $this->url ."', "
                ."phone = '". $this->phone ."', "
                ."mobile = '". $this->mobile ."', "
                ."fax = '". $this->fax ."', "
                ."picture = '". $this->picture ."', "
                ."address_type_ids = '|". implode('|', $this->address_type_ids) ."|', "
                .'article_id = '. ($this->article_id > 0 ? $this->article_id : 0) .', '
                .'priority = '. $this->priority .', '
                ."online_status = '". $this->online_status ."' ";
        if (0 === $this->address_id) {
            $query = 'INSERT INTO '. $query;
        } else {
            $query = 'UPDATE '. $query .' WHERE address_id = '. $this->address_id;
        }

        $result = rex_sql::factory();
        $result->setQuery($query);
        if (0 === $this->address_id) {
            $this->address_id = (int) $result->getLastId();
            $error = $result->hasError();
        }

        // Update assigned countries
        $result->setQuery('DELETE FROM '. rex::getTablePrefix() .'d2u_address_2_countries WHERE address_id = '. $this->address_id);
        foreach ($this->country_ids as $country_id) {
            $result->setQuery('INSERT INTO '. rex::getTablePrefix() .'d2u_address_2_countries SET '
                    .'country_id = '. $country_id .', '
                    .'address_id = '. $this->address_id);
            $error = $result->hasError();
        }

        return $error;
    }

    /**
     * Reassigns priorities in database.
     * @param bool $delete Reorder priority after deletion
     */
    private function setPriority($delete = false): void
    {
        // Pull prios from database
        $query = 'SELECT address_id, priority FROM '. rex::getTablePrefix() .'d2u_address_address '
            .'WHERE address_id <> '. $this->address_id .' ORDER BY priority';
        $result = rex_sql::factory();
        $result->setQuery($query);

        // When priority is too small, set at beginning
        if ($this->priority <= 0) {
            $this->priority = 1;
        }

        // When prio is too high or was deleted, simply add at end
        if ($this->priority > $result->getRows() || $delete) {
            $this->priority = $result->getRows() + 1;
        }

        $objects = [];
        for ($i = 0; $i < $result->getRows(); ++$i) {
            $objects[$result->getValue('priority')] = $result->getValue('address_id');
            $result->next();
        }
        array_splice($objects, $this->priority - 1, 0, [$this->address_id]);

        // Save all prios
        foreach ($objects as $prio => $address_id) {
            $query = 'UPDATE '. rex::getTablePrefix() .'d2u_address_address '
                    .'SET priority = '. ((int) $prio + 1) .' ' // +1 because array_splice recounts at zero
                    .'WHERE address_id = '. $address_id;
            $result = rex_sql::factory();
            $result->setQuery($query);
        }
    }
}

namespace D2U_Address;

/**
 * @deprecated since 1.5.0, to be removed in 2.0.0. Use FriendsOfRedaxo\D2UAddress\Address instead.
 */
class Address extends \FriendsOfRedaxo\D2UAddress\Address {

}