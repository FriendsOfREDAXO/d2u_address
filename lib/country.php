<?php
/**
 * Data of country.
 */
class Country {
	/**
	 * @var int ID 
	 */
	var $country_id = 0;
	
	/**
	 * @var int Redaxo language ID
	 */
	var $clang_id = 0;
	
	/**
	 * @var string Name
	 */
	var $name = "";
	
	/**
	 * @var string[] ISO language codes fitting for the country
	 */
	var $iso_lang_codes = [];
	
	/**
	 * @var int Google Maps zoom level
	 */
	var $maps_zoom = 5;
	
	/**
	 * @var int[] Adress IDs for this country
	 */
	var $address_ids = [];
	
	/**
	 * @var string "yes" if translation needs update
	 */
	var $translation_needs_update = "delete";
	
	/**
	 * Constructor.
	 * @param int $country_id Country ID.
	 * @param int $clang_id. Redaxo language ID
	 */
	 public function __construct($country_id, $clang_id = 0) {
		$this->clang_id = $clang_id;
		$query = "SELECT * FROM ". rex::getTablePrefix() ."d2u_address_countries_lang AS lang "
				."LEFT JOIN ". rex::getTablePrefix() ."d2u_address_countries AS countries "
					."ON lang.country_id = countries.country_id "
				."WHERE lang.country_id = ". $country_id ." "
					."AND clang_id = ". $clang_id;
		$result = rex_sql::factory();
		$result->setQuery($query);
		$num_rows = $result->getRows();

		if ($num_rows > 0) {
			$this->country_id = $result->getValue("country_id");
			$this->iso_lang_codes = preg_grep('/^\s*$/s', explode(",", strtolower($result->getValue("iso_lang_codes"))), PREG_GREP_INVERT);
			if($result->getValue("maps_zoom") != "") {
				$this->maps_zoom = $result->getValue("maps_zoom");
			}
			$this->address_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("address_ids")), PREG_GREP_INVERT);
			$this->name = $result->getValue("name");
			if($result->getValue("translation_needs_update") != "") {
				$this->translation_needs_update = $result->getValue("translation_needs_update");
			}
		}
	}
	
	/**
	 * Deletes the object.
	 * @param int $delete_all If TRUE, all translations and main object are deleted. If 
	 * FALSE, only this translation will be deleted.
	 */
	public function delete($delete_all = TRUE) {
		$query_lang = "DELETE FROM ". rex::getTablePrefix() ."d2u_address_countries_lang "
			."WHERE country_id = ". $this->country_id
			. ($delete_all ? '' : ' AND clang_id = '. $this->clang_id) ;
		$result_lang = rex_sql::factory();
		$result_lang->setQuery($query_lang);
		
		// If no more lang objects are available, delete
		$query_main = "SELECT * FROM ". rex::getTablePrefix() ."d2u_address_countries_lang "
			."WHERE country_id = ". $this->country_id;
		$result_main = rex_sql::factory();
		$result_main->setQuery($query_main);
		if($result_main->getRows() == 0) {
			$query = "DELETE FROM ". rex::getTablePrefix() ."d2u_address_countries "
				."WHERE country_id = ". $this->country_id;
			$result = rex_sql::factory();
			$result->setQuery($query);
		}
	}

	/**
	 * Returns addresses for country.
	 * @param AddressType $address_type Address type, FALSE if all address types should be used
	 * @param boolean $online_only True if only online addresses should be returned
	 * @return Address[] Found addresses
	 */
	public function getAddresses($address_type = FALSE, $online_only = TRUE) {
		$query = "SELECT address_ids FROM ". rex::getTablePrefix() ."d2u_address_countries "
			."WHERE country_id = ". $this->country_id ." ";
		$result = rex_sql::factory();
		$result->setQuery($query);

		$addresses = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$address_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("address_ids")), PREG_GREP_INVERT);
			foreach($address_ids as $address_id) {
				$address = new Address($address_id, $this->clang_id);
				if($address_type !== FALSE && in_array($address_type->address_type_id, $address->address_type_ids) && ($online_only && $address->online_status == "online")) {
					$addresses[$address->priority ."-". $address->address_id] = $address;
				}
			}
			$result->next();
		}

		ksort($addresses);
		return $addresses;
	}

	/**
	 * Returns zipcode objects for country.
	 * @return ZipCode[] Found zipcodes
	 */
	public function getZipCodes() {
		$query = "SELECT zipcode_id FROM ". rex::getTablePrefix() ."d2u_address_zipcodes "
			."WHERE country_id = ". $this->country_id ." "
			."ORDER BY range_from";
		$result = rex_sql::factory();
		$result->setQuery($query);

		$zipcodes = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$zipcodes[] = new ZipCode($result->getValue('zipcode_id'), $this->clang_id);
			$result->next();
		}
		return $zipcodes;
	}
	
	/**
	 * Gets all countries.
	 * @param int $clang_id Redaxo language ID
	 * @param int $address_type_id AddressType ID
	 * @return Country[] Array with country objects.
	 */
	public static function getAll($clang_id = 0) {
		$query = 'SELECT country_id FROM '. rex::getTablePrefix() .'d2u_address_countries_lang '
				."WHERE clang_id = ". ($clang_id == 0 ? rex_clang::getCurrentId() : $clang_id) ." "
				.'ORDER BY name';
		$result = rex_sql::factory();
		$result->setQuery($query);

		$countries = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$countries[] = new Country($result->getValue("country_id"), $clang_id);
			$result->next();
		}
		
		return $countries;
    }
	
	/**
	 * Get countries by ISO lang code
	 * @param string $iso_lang_code ISO language code
	 * @param int $clang_id Redaxo language ID
	 * @return Country[] Array with fitting countries.
	 */
	static public function getByLangCode($iso_lang_code, $clang_id = 0) {
		$query = 'SELECT country_id FROM '. rex::getTablePrefix() .'d2u_address_countries '
				.'WHERE iso_lang_codes LIKE "%'. substr($iso_lang_code, 0, 2) .'%" ';
		$result = rex_sql::factory();
		$result->setQuery($query);

		$countries = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$country = new Country($result->getValue("country_id"), $clang_id);
			if(in_array(strtolower($iso_lang_code), $country->iso_lang_codes)) {
				return [$country];
			}
			$countries[] = $country;
			$result->next();
		}

		return $countries;
	}
	
	/**
	 * Updates or inserts the object into database.
	 * @return boolean TRUE if error occured
	 */
	public function save() {
		$error = FALSE;

		// Save the not language specific part
		$pre_save_country = new Country($this->country_id, $this->clang_id);
	
		if($this->country_id == 0 || $pre_save_country != $this) {
			$query = rex::getTablePrefix() ."d2u_address_countries SET "
					."iso_lang_codes = '". $this->iso_lang_codes ."', "
					."maps_zoom = ". $this->maps_zoom .", "
					."address_ids = '|". implode('|', $this->address_ids) ."|' ";

			if($this->country_id == 0) {
				$query = "INSERT INTO ". $query;
			}
			else {
				$query = "UPDATE ". $query ." WHERE country_id = ". $this->country_id;
			}
			$result = rex_sql::factory();
			$result->setQuery($query);
			if($this->country_id == 0) {
				$this->country_id = $result->getLastId();
				$error = $result->hasError();
			}
		}
		
		if($error == 0) {
			// Save the language specific part
			$pre_save_country = new Country($this->country_id, $this->clang_id);
			if($pre_save_country != $this) {
				$query = "REPLACE INTO ". rex::getTablePrefix() ."d2u_address_countries_lang SET "
						."name = '". $this->name ."', "
						."translation_needs_update = '". $this->translation_needs_update ."' ";

				$result = rex_sql::factory();
				$result->setQuery($query);
				$error = $result->hasError();
			}
		}
		
		return $error;
	}
}