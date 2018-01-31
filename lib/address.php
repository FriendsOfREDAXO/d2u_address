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
	 * @var string Fax number
	 */
	var $fax = "";
	
	/**
	 * @var string Bild der Address.
	 */
	var $picture = "noavatar.jpg";
	
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
			$this->company = $result->getValue("company");
			$this->company_appendix = $result->getValue("company_appendix");
			$this->contact_name = $result->getValue("contact_name");
			$this->street = $result->getValue("street");
			$this->additional_address = $result->getValue("additional_address");
			$this->zip_code = $result->getValue("zip_code");
			$this->city = $result->getValue("city");
			$this->country = new Country($result->getValue("country_id"), $clang_id);
			$this->latitude = $result->getValue("latitude");
			$this->longitude = $result->getValue("longitude");
			$this->email = $result->getValue("email");
			$this->url = $result->getValue("url");
			$this->phone = $result->getValue("phone");
			$this->fax = $result->getValue("fax");
			if($result->getValue("picture") != "") {
				$this->picture = $result->getValue("picture");
			}
			else {
				$this->picture = \rex_addon::get('d2u_address')->getAssetsUrl("noavatar.jpg");
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
		}
	}

	/**
	 * Changes the status of a property
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
		$query = "DELETE FROM ". \rex::getTablePrefix() ."d2u_address_address "
			."WHERE address_id = ". $this->address_id;
		$result = \rex_sql::factory();
		$result->setQuery($query);
	}
	
	/**
	 * Returns addresses
	 * @param int $clang_id Redaxo Language ID
	 * @param AddressType $address_type Address type, default: FALSE (all)
	 * @param boolean $online_only return only online adresses
	 * @return Address[] Addresses
	 */
	static public function getAll($clang_id, $address_type = FALSE, $online_only = TRUE) {
		$query = 'SELECT address_id FROM '. \rex::getTablePrefix() .'d2u_address_address ';
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
		$query .= ' ORDER BY company, priority';
		$result = \rex_sql::factory();
		$result->setQuery($query);

		$addresses = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$addresses[] = new Address($result->getValue("address_id"), $clang_id);
			$result->next();
		}
		return $addresses;
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
	 * @return Country[] Country objects
	 */
	public function getReferringCountries() {
		$query = 'SELECT countries.country_id, name FROM '. \rex::getTablePrefix() .'d2u_address_countries AS countries '
			.'LEFT JOIN '. \rex::getTablePrefix() .'d2u_address_countries_lang AS lang '
				.'ON countries.country_id = lang.country_id '
			.'WHERE clang_id = '. $this->clang_id .' AND address_ids LIKE "%|'. $this->address_id .'|%" '
			.'ORDER BY name';
		$result = \rex_sql::factory();
		$result->setQuery($query);

		$countries = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$countries[] = new Country($result->getValue("country_id"), $this->clang_id);
			$result->next();
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

		$query = \rex::getTablePrefix() ."d2u_address_address SET "
				."company = '". $this->company ."', "
				."company_appendix = '". $this->company_appendix ."', "
				."contact_name = '". $this->contact_name ."', "
				."street = '". $this->street ."', "
				."additional_address = '". $this->additional_address ."', "
				."zip_code = '". $this->zip_code ."', "
				."city = '". $this->city ."', "
				."country_id = ". $this->country->country_id .", "
				."latitude = ". ($this->latitude > 0 ? $this->latitude : 0) .", "
				."longitude = ". ($this->longitude > 0 ? $this->longitude : 0) .", "
				."email = '". $this->email ."', "
				."url = '". $this->url ."', "
				."phone = '". $this->phone ."', "
				."fax = '". $this->fax ."', "
				."picture = '". $this->picture ."', "
				."address_type_ids = '". implode("|", $this->address_type_ids) ."', "
				."article_id = ". ($this->article_id > 0 ? $this->article_id : 0) .", "
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

		return $error;
	}
}