<?php
/**
 * Address class
 */
class Address {
	/**
	 * @var int Database address ID
	 */
	var $address_id = 0;
	
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
	 * @var int Country ID
	 */
	var $country_id = 0;
	
	/**
	 * @var string Latitude
	 */
	var $latitude = "";
	
	/**
	 * @var string Longitude
	 */
	var $longitude = "";
	
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
	var $article_id = "";
	
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
	 */
	 public function __construct($address_id) {
		$query = "SELECT * FROM ". rex::getTablePrefix() ."d2u_address_address "
				."WHERE address_id = ". $address_id;
		$result = rex_sql::factory();
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
			$this->country_id = $result->getValue("country_id");
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
				$this->picture = rex_addon::get('d2u_address')->getAssetsUrl("noavatar.jpg");
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
	 * Returns address for zip code
	 * @param int $zip_code ZIP Code.
	 * @param AdressType $address_type Address type
	 * @param int $clang_id Redaxo language ID
	 * @return Address[] Fitting addresses
	 */
	static public function getAddressesForZipcode($zip_code, $address_type, $clang_id = 0) {
		$query = 'SELECT address_ids FROM '. rex::getTablePrefix() .'d2u_address_zipcodes '
				.'WHERE range_from <= '. $zip_code .' AND range_to >= '. $zip_code;
		$result = rex_sql::factory();
		$result->setQuery($query);

		$addresses = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$address_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("address_ids")), PREG_GREP_INVERT);
			foreach($address_ids as $address_id) {
				$address = new Address($address_id);
				if(in_array($address_type->address_type_id, $address->address_type_ids) && $address->online_status == "online") {
					$addresses[$address->priority ."-". $address->address_id] = $address;
				}
			}
			$result->next();
		}
		
		ksort($addresses);
		return $addresses;
	}

	/**
	 * Updates or inserts the object into database.
	 * @return boolean TRUE if error occured
	 */
	public function save() {
		$error = 0;

		$query = rex::getTablePrefix() ."d2u_address_address SET "
				."company = '". $this->company ."', "
				."company_appendix = '". $this->company_appendix ."', "
				."contact_name = '". $this->contact_name ."', "
				."street = '". $this->street ."', "
				."additional_address = '". $this->additional_address ."', "
				."zip_code = '". $this->zip_code ."', "
				."city = '". $this->city ."', "
				."country_id = ". $this->country_id .", "
				."latitude = ". $this->latitude .", "
				."longitude = ". $this->longitude .", "
				."email = '". $this->email ."', "
				."url = '". $this->url ."', "
				."phone = '". $this->phone ."', "
				."fax = '". $this->fax ."', "
				."picture = '". $this->picture ."', "
				."address_type_ids = '". implode("|", $this->address_type_ids) ."', "
				."article_id = ". $this->article_id .", "
				."priority = ". $this->priority .", "
				."online_status = '". $this->online_status ."' ";
		if($this->address_id == 0) {
			$query = "INSERT INTO ". $query;
		}
		else {
			$query = "UPDATE ". $query ." WHERE address_id = ". $this->address_id;
		}

		$result = rex_sql::factory();
		$result->setQuery($query);
		if($this->address_id == 0) {
			$this->address_id = $result->getLastId();
			$error = $result->hasError();
		}

		return $error;
	}
}