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
	 * Returns addresses for country.
	 * @param AddressType $address_type Address type
	 * @param boolean $online_only True if only online addresses should be returned
	 * @return Address[] Found addresses
	 */
	public function getAddresses($address_type, $online_only = TRUE) {
		$query = "SELECT address_ids FROM ". rex::getTablePrefix() ."d2u_address_countries "
			."WHERE country_id = ". $this->country_id ." ";
		$result = rex_sql::factory();
		$result->setQuery($query);

		$addresses = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$address_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("address_ids")), PREG_GREP_INVERT);
			foreach($address_ids as $address_id) {
				$address = new Address($address_id);
				if(in_array($address_type->address_type_id, $address->address_type_ids) && ($online_only && $address->online_status == "online")) {
					$addresses[$address->priority ."-". $address->address_id] = $address;
				}
			}
			$result->next();
		}

		ksort($addresses);
		return $addresses;
	}

	/**
	 * Gets all countries.
	 * @param int $clang_id Redaxo language ID
	 * @param int $address_type_id AddressType ID
	 * @return Country[] Array mit Land Objekten.
	 */
	public static function getAll($clang_id = 0, $address_type_id = 0) {
		$query = 'SELECT country_id, name FROM '. rex::getTablePrefix() .'d2u_address_countries_lang '
				."WHERE clang_id = ". ($clang_id == 0 ? rex_clang::getCurrentId() : $clang_id) ." "
				.'ORDER BY name';
		if($address_type_id > 0) {
			// get only countries wich differ from default country for address type - exception: country the address resides in
			$address_type = new AddressType($address_type_id);
	
			$query = 'SELECT address.country_id, country_lang.name FROM '. rex::getTablePrefix() .'d2u_address_address AS address '
				. 'LEFT JOIN '. rex::getTablePrefix() .'d2u_address_countries_lang AS country_lang '
					.'ON address.country_id = country_lang.country_id AND country_lang.clang_id = '. ($clang_id == 0 ? rex_clang::getCurrentId() : $clang_id) .' '
				.'GROUP BY country_id, name '
				.'ORDER BY country_lang.name';
			//FIXME Adressen müssen aus country Tabelle geholt werden !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		}
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
	static public function getCountriesByLangCode($iso_lang_code, $clang_id = 0) {
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
					."maps_zoom = ". maps_zoom .", "
					."adress_ids = '". implode('|', $this->adress_ids) ."' ";

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