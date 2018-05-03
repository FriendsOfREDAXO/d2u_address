<?php
namespace D2U_Address;

/**
 * Address type
 */
class AddressType {
	/**
	 * @var int Database ID
	 */
	var $address_type_id = 0;
	
	/**
	 * @var int Redaxo language ID
	 */
	var $clang_id = 0;
	
	/**
	 * @var string Name
	 */
	var $name = "";
	
	/**
	 * @var string Show full address in frontend? "yes" or "no"
	 */
	var $show_address_details = "";
	
	/**
	 * @var string Show country selection in frontend? "yes" or "no"
	 */
	var $show_country_select = "";
	
	/**
	 * @var int Google maps zoom level
	 */
	var $maps_zoom = 5;
	
	/**
	 * @var int Redaxo article ID
	 */
	var $article_id = 0;
	
	/**
	 * @var int default country ID
	 */
	var $default_address_id = 0;
	
	/**
	 * Constructor
	 * @param int $address_type_id ID.
	 * @param int $clang_id Redaxo language ID.
	 */
	 public function __construct($address_type_id, $clang_id) {
		$this->clang_id = $clang_id;
		$query = "SELECT * FROM ". \rex::getTablePrefix() ."d2u_address_types "
				."WHERE address_type_id = ". $address_type_id;
		$result = \rex_sql::factory();
		$result->setQuery($query);

		if ($result->getRows() > 0) {
			$this->address_type_id = $result->getValue("address_type_id");
			$this->name = stripslashes($result->getValue("name"));
			$this->show_address_details = $result->getValue("show_address_details");
			$this->show_country_select = $result->getValue("show_country_select");
			if($result->getValue("maps_zoom") != "") {
				$this->maps_zoom = $result->getValue("maps_zoom");
			}
			$this->article_id = $result->getValue("article_id");
			$this->default_address_id = $result->getValue("default_address_id");
		}
	}
	
	/**
	 * Deletes the object.
	 */
	public function delete() {
		$query = "DELETE FROM ". \rex::getTablePrefix() ."d2u_address_types "
			."WHERE address_type_id = ". $this->address_type_id;
		$result = \rex_sql::factory();
		$result->setQuery($query);
	}
	
	/**
	 * Get all address types
	 * @param int $clang_id Redaxo language ID.
	 * @return AddressTypes[] Array with all address types
	 */
	public static function getAll($clang_id) {
		$query = 'SELECT address_type_id FROM '. \rex::getTablePrefix() .'d2u_address_types';
		$result = \rex_sql::factory();
		$result->setQuery($query);

		$address_types = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$address_types[] = new AddressType($result->getValue("address_type_id"), $clang_id);
			$result->next();
		}
		
		return $address_types;
    }

	/**
	 * Returns addresses for adress type
	 * @param boolean $online_only TRUE to get only online addresses
	 * @return Address[] Found addresses.
	 */
	public function getAddresses($online_only = TRUE) {
		$query = "SELECT address_id FROM ". \rex::getTablePrefix() ."d2u_address_address "
				."WHERE address_type_ids LIKE '%|". $this->address_type_id ."|%' ";
		if($online_only) {
			$query .= "AND online_status = 'online' ";
		}
		$query .= "ORDER BY priority";
		$result = \rex_sql::factory();
		$result->setQuery($query);
		$num_rows = $result->getRows();

		$addresses = [];
		for($i = 0; $i < $num_rows; $i++) {
			$addresses[] = new Address($result->getValue("address_id"), $this->clang_id);
			$result->next();
		}
		
		return $addresses;
	}

	/**
	 * Gets all countries used by this object.
	 * @return Country[] Array with country objects
	 */
	public function getCountries() {
		$query = 'SELECT address_id FROM '. \rex::getTablePrefix() .'d2u_address_address '
				."WHERE address_type_ids LIKE '%|". $this->address_type_id ."|%' ";
		$result = \rex_sql::factory();
		$result->setQuery($query);

		$countries = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$address = new Address($result->getValue("address_id"), $this->clang_id);
			foreach ($address->getReferringCountries() as $country) {
				if(!key_exists(Country::normalizeCountryName($country->name), $countries)) {
					$countries[Country::normalizeCountryName($country->name)] = $country;
				}
			}
			$result->next();
		}
		$default_address = new Address($this->default_address_id, $this->clang_id);
		$countries[Country::normalizeCountryName($default_address->country->name)] = $default_address->country;
		ksort($countries);
		
		return $countries;
    }

	/**
	 * Updates or inserts the object into database.
	 * @return boolean TRUE if error occured
	 */
	public function save() {
		$error = 0;

		$query = \rex::getTablePrefix() ."d2u_address_types SET "
				."name = '". addslashes($this->name) ."', "
				."show_address_details = '". ($this->show_address_details ? "yes" : "no") ."', "
				."show_country_select = '". ($this->show_country_select ? "yes" : "no") ."', "
				."maps_zoom = ". $this->maps_zoom .", "
				."default_address_id = ". ($this->default_address_id == "" ? 0 : $this->default_address_id) .", "
				."article_id = ". ($this->article_id == "" ? 0 : $this->article_id) ." ";
		if($this->address_type_id == 0) {
			$query = "INSERT INTO ". $query;
		}
		else {
			$query = "UPDATE ". $query ." WHERE address_type_id = ". $this->address_type_id;
		}

		$result = \rex_sql::factory();
		$result->setQuery($query);
		if($this->address_type_id == 0) {
			$this->address_type_id = $result->getLastId();
			$error = $result->hasError();
		}

		return $error;
	}
}