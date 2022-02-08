<?php
namespace D2U_Address;

/**
 * Address class
 */
class Address {
	/**
	 * @var int Database address ID
	 */
	var $address_id = 0;
	
	/**
	 * @var int Redaxo language ID
	 */
	var $clang_id = 0;
	
	/**
	 * @var string Company Name
	 */
	var $company = "";
	
	/**
	 * @var string Appendix for company name
	 */
	var $company_appendix = "";
	
	/**
	 * @var string Name of contact person
	 */
	var $contact_name = "";
	
	/**
	 * @var string Street and house number
	 */
	var $street = "";
	
	/**
	 * @var string Additional address
	 */
	var $additional_address = "";
	
	/**
	 * @var string ZIP code
	 */
	var $zip_code = "";
	
	/**
	 * @var string city
	 */
	var $city = "";
	
	/**
	 * @var Country Country
	 */
	var $country;
	
	/**
	 * @var int IDs of assigned countries
	 */
	var $country_ids = [];
	
	/**
	 * @var string Latitude
	 */
	var $latitude = 0;
	
	/**
	 * @var string Longitude
	 */
	var $longitude = 0;
	
	/**
	 * @var string E-Mailadresse der Address.
	 */
	var $email = "";
	
	/**
	 * @var string Phone number
	 */
	var $phone = "";
	
	/**
	 * @var string Mobile phone number
	 */
	var $mobile = "";
	
	/**
	 * @var string Fax number
	 */
	var $fax = "";
	
	/**
	 * @var string Bild der Address.
	 */
	var $picture = "";
	
	/**
	 * @var AdressType[] Adress types
	 */
	var $address_type_ids = [];
	
	/**
	 * @var int Redaxo article ID with detailed information
	 */
	var $article_id = 0;
	
	/**
	 * @var string URL for detailed information
	 */
	var $url = "";
	
	/**
	 * @var int Sort priority
	 */
	var $priority = 0;
	
	/**
	 * @var string Online status, either "online" or "offline".
	 */
	var $online_status = "offline";
	
	/**
	 * Constructor.
	 * @param int $address_id Address ID
	 * @param int $clang_id Redaxo Language ID
	 */
	 public function __construct($address_id, $clang_id) {
		$this->clang_id = $clang_id;
		$query = "SELECT * FROM ". \rex::getTablePrefix() ."d2u_address_address "
				."WHERE address_id = ". $address_id;
		$result = \rex_sql::factory();
		$result->setQuery($query);
		$num_rows = $result->getRows();

		if ($num_rows > 0) {
			$this->address_id = $result->getValue("address_id");
			$this->company = stripslashes($result->getValue("company"));
			$this->company_appendix = stripslashes($result->getValue("company_appendix"));
			$this->contact_name = stripslashes($result->getValue("contact_name"));
			$this->street = stripslashes($result->getValue("street"));
			$this->additional_address = stripslashes($result->getValue("additional_address"));
			$this->zip_code = $result->getValue("zip_code");
			$this->city = stripslashes($result->getValue("city"));
			$this->country = new Country($result->getValue("country_id"), $clang_id);
			$this->latitude = $result->getValue("latitude");
			$this->longitude = $result->getValue("longitude");
			$this->email = $result->getValue("email");
			$this->url = $result->getValue("url");
			$this->phone = $result->getValue("phone");
			$this->mobile = $result->getValue("mobile");
			$this->fax = $result->getValue("fax");
			if($result->getValue("picture") != "") {
				$this->picture = $result->getValue("picture");
			}
			$this->address_type_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("address_type_ids")), PREG_GREP_INVERT);
			$this->article_id = $result->getValue("article_id");
			$this->priority = $result->getValue("priority");
			if($result->getValue("online_status") != "") {
				$this->online_status = $result->getValue("online_status");
			}

			// correct URL if needed
			if(strlen($this->url) > 3 && substr($this->url, 0, 4) != "http") {
				$this->url = "http://". $this->url;
			}
			
			$this->country_ids = $this->getAssignedCountryIDs();
		}
	}

	/**
	 * Changes the status of the object
	 */
	public function changeStatus() {
		if($this->online_status == "online") {
			if($this->address_id > 0) {
				$query = "UPDATE ". \rex::getTablePrefix() ."d2u_address_address "
					."SET online_status = 'offline' "
					."WHERE address_id = ". $this->address_id;
				$result = \rex_sql::factory();
				$result->setQuery($query);
			}
			$this->online_status = "offline";
		}
		else {
			if($this->address_id > 0) {
				$query = "UPDATE ". \rex::getTablePrefix() ."d2u_address_address "
					."SET online_status = 'online' "
					."WHERE address_id = ". $this->address_id;
				$result = \rex_sql::factory();
				$result->setQuery($query);
			}
			$this->online_status = "online";
		}
	}
	
	/**
	 * Deletes the object.
	 */
	public function delete() {
		$result = \rex_sql::factory();
		$result->setQuery($query = "DELETE FROM ". \rex::getTablePrefix() ."d2u_address_address "
			."WHERE address_id = ". $this->address_id);
		$result->setQuery("DELETE FROM ". \rex::getTablePrefix() ."d2u_address_2_countries "
			."WHERE address_id = ". $this->address_id);
	}
	
	/**
	 * Returns addresses
	 * @param int $clang_id Redaxo Language ID
	 * @param AddressType $address_type Address type, default: FALSE (all)
	 * @param boolean $online_only return only online adresses
	 * @return Address[] Addresses
	 */
	static public function getAll($clang_id, $address_type = FALSE, $online_only = TRUE) {
		$query = 'SELECT address_id, priority FROM '. \rex::getTablePrefix() .'d2u_address_address ';
		$where = [];
		if($address_type !== FALSE) {
			$where[] = 'address_type_ids LIKE "%|'. $address_type->address_type_id .'|%"';
		}
		if($online_only) {
			$where[] = 'online_status = "online"';
		}
		if(count($where) > 0) {
			$query .= 'WHERE '. implode(' AND ', $where);
		}
		$query .= ' ORDER BY company, contact_name, priority';
		$result = \rex_sql::factory();
		$result->setQuery($query);

		$addresses = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$addresses[$result->getValue("priority")] = new Address($result->getValue("address_id"), $clang_id);
			$result->next();
		}
		ksort($addresses);
		return $addresses;
	}

	/**
	 * Returns country ids of countries this address is assigned to.
	 * @return int[] country IDs
	 */
	private function getAssignedCountryIDs() {
		$query = 'SELECT a2c.country_id FROM '. \rex::getTablePrefix() .'d2u_address_2_countries AS a2c '
			.'LEFT JOIN '. \rex::getTablePrefix() .'d2u_address_countries_lang AS lang '
				.'ON a2c.country_id = lang.country_id AND lang.clang_id = '. $this->clang_id .' '
			.'WHERE a2c.address_id = '. $this->address_id .' '
			.'ORDER BY name';
		$result = \rex_sql::factory();
		$result->setQuery($query);

		$country_ids = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$country_ids[] = $result->getValue("country_id");
			$result->next();
		}
		
		return $country_ids;
	}
	
	/**
	 * Returns address types reffering address
	 * @return AddressType[] AddressType objects
	 */
	public function getReferringAddressTypes() {
		$query = 'SELECT address_type_id FROM '. \rex::getTablePrefix() .'d2u_address_types '
			.'WHERE default_address_id = '. $this->address_id .' '
			.'ORDER BY name';
		$result = \rex_sql::factory();
		$result->setQuery($query);

		$address_types = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$address_types[] = new AddressType($result->getValue("address_type_id"), $this->clang_id);
			$result->next();
		}
		return $address_types;
	}
	
	/**
	 * Returns countries reffering address
	 * @return Country[] Country objects, country ID is key
	 */
	public function getReferringCountries() {
		$countries = [];
		foreach($this->country_ids as $country_id) {
			$countries[$country_id] = new Country($country_id, $this->clang_id);
		}
		
		return $countries;
	}

	/**
	 * Returns zip codes reffering address
	 * @return ZipCode[] ZipCode objects
	 */
	public function getReferringZipCodes() {
		$query = 'SELECT zipcode_id FROM '. \rex::getTablePrefix() .'d2u_address_zipcodes '
			.'WHERE address_ids LIKE "%|'. $this->address_id .'|%" '
			.'ORDER BY range_from';
		$result = \rex_sql::factory();
		$result->setQuery($query);

		$zip_codes = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$zip_codes[] = new ZipCode($result->getValue("zipcode_id"), $this->clang_id);
			$result->next();
		}
		
		return $zip_codes;
	}

	/**
	 * Updates or inserts the object into database.
	 * @return boolean TRUE if error occured
	 */
	public function save() {
		$error = 0;

		$pre_save_object = new self($this->address_id, $this->clang_id);

		// save priority, but only if new or changed
		if($this->priority != $pre_save_object->priority || $this->address_id == 0) {
			$this->setPriority();
		}

		$query = \rex::getTablePrefix() ."d2u_address_address SET "
				."company = '". addslashes($this->company) ."', "
				."company_appendix = '". addslashes($this->company_appendix) ."', "
				."contact_name = '". addslashes($this->contact_name) ."', "
				."street = '". addslashes($this->street) ."', "
				."additional_address = '". addslashes($this->additional_address) ."', "
				."zip_code = '". $this->zip_code ."', "
				."city = '". addslashes($this->city) ."', "
				."country_id = ". $this->country->country_id .", "
				."latitude = ". $this->latitude .", "
				."longitude = ". $this->longitude .", "
				."email = '". $this->email ."', "
				."url = '". $this->url ."', "
				."phone = '". $this->phone ."', "
				."mobile = '". $this->mobile ."', "
				."fax = '". $this->fax ."', "
				."picture = '". $this->picture ."', "
				."address_type_ids = '|". implode("|", $this->address_type_ids) ."|', "
				."article_id = ". ($this->article_id ?: 0) .", "
				."priority = ". $this->priority .", "
				."online_status = '". $this->online_status ."' ";
		if($this->address_id == 0) {
			$query = "INSERT INTO ". $query;
		}
		else {
			$query = "UPDATE ". $query ." WHERE address_id = ". $this->address_id;
		}

		$result = \rex_sql::factory();
		$result->setQuery($query);
		if($this->address_id == 0) {
			$this->address_id = $result->getLastId();
			$error = $result->hasError();
		}
		
		// Update assigned countries
		$result->setQuery("DELETE FROM ". \rex::getTablePrefix() ."d2u_address_2_countries WHERE address_id = ". $this->address_id);
		foreach($this->country_ids as $country_id) {
			$result->setQuery("INSERT INTO ". \rex::getTablePrefix() ."d2u_address_2_countries SET "
					."country_id = ". $country_id .", "
					."address_id = ". $this->address_id);
			$error = $result->hasError();
		}


		return $error;
	}
		
	/**
	 * Reassigns priorities in database.
	 * @param boolean $delete Reorder priority after deletion
	 */
	private function setPriority($delete = FALSE) {
		// Pull prios from database
		$query = "SELECT address_id, priority FROM ". \rex::getTablePrefix() ."d2u_address_address "
			."WHERE address_id <> ". $this->address_id ." ORDER BY priority";
		$result = \rex_sql::factory();
		$result->setQuery($query);
		
		// When priority is too small, set at beginning
		if($this->priority <= 0) {
			$this->priority = 1;
		}
		
		// When prio is too high or was deleted, simply add at end 
		if($this->priority > $result->getRows() || $delete) {
			$this->priority = $result->getRows() + 1;
		}

		$objects = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$objects[$result->getValue("priority")] = $result->getValue("address_id");
			$result->next();
		}
		array_splice($objects, ($this->priority - 1), 0, [$this->address_id]);

		// Save all prios
		foreach($objects as $prio => $address_id) {
			$query = "UPDATE ". \rex::getTablePrefix() ."d2u_address_address "
					."SET priority = ". ($prio + 1) ." " // +1 because array_splice recounts at zero
					."WHERE address_id = ". $address_id;
			$result = \rex_sql::factory();
			$result->setQuery($query);
		}
	}
}