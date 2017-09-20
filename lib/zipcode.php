<?php
/**
 * Zip code object
 */
class ZipCode {
	/**
	 * @var int Database ID
	 */
	var $zipcode_id = 0;
	
	/**
	 * @var int Start range
	 */
	var $range_from = 0;
	
	/**
	 * @var int End range
	 */
	var $range_to = 0;
	
	/**
	 * @var int country id
	 */
	var $country_id = 0;
	
	/**
	 * @var int[] adress_ids
	 */
	var $adress_ids = [];
	
	/**
	 * Constructor
	 * @param int $zipcode_id ID.
	 */
	 public function __construct($zipcode_id) {
		$query = "SELECT * FROM ". rex::getTablePrefix() ."d2u_address_zipcodes "
				."WHERE zipcode_id = ". $zipcode_id;
		$result = rex_sql::factory();
		$result->setQuery($query);

		if ($result->getRows() > 0) {
			$this->zipcode_id = $result->getValue("zipcode_id");
			$this->adress_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("address_type_ids")), PREG_GREP_INVERT);
			$this->range_from = $result->getValue("range_from");
			$this->range_to = $result->getValue("range_to");
			$this->country_id = $result->getValue("country_id");
		}
	}
	
	/**
	 * Get all zip codes
	 * @param int $country_id Country ID
	 * @return ZipCode[] Array with all zip codes for a country
	 */
	public static function getAll($country_id) {
		$query = 'SELECT zipcode_id FROM '. rex::getTablePrefix() .'d2u_address_zipcodes '
			.'WHERE country_id  = '. $country_id;
		$result = rex_sql::factory();
		$result->setQuery($query);

		$zip_codes = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$zip_codes[] = new ZipCode($result->getValue("zipcode_id"));
			$result->next();
		}
		
		return $zip_codes;
    }

	/**
	 * Returns addresses for zip code
	 * @param boolean $online_only TRUE to get only online addresses
	 * @return Address[] Found addresses.
	 */
	public function getAdresses($online_only = TRUE) {
		$addresses = [];
		foreach ($this->adress_ids as $address_id) {
			$address = new Address($address_id);
			if($online_only === FALSE || ($online_only && $address->online_status == "online")) {
				$addresses[$address->priority] = new Address($address_id);
			}
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

		$query = rex::getTablePrefix() ."d2u_address_zipcodes SET "
				."adress_ids = '". $this->adress_ids ."', "
				."range_from = ". $this->range_from .", "
				."range_to = ". $this->range_to .", "
				."maps_zoom = ". $this->maps_zoom .", "
				."country_id = ". $this->country_id ." ";
		if($this->zipcode_id == 0) {
			$query = "INSERT INTO ". $query;
		}
		else {
			$query = "UPDATE ". $query ." WHERE zipcode_id = ". $this->zipcode_id;
		}

		$result = rex_sql::factory();
		$result->setQuery($query);
		if($this->zipcode_id == 0) {
			$this->zipcode_id = $result->getLastId();
			$error = $result->hasError();
		}

		return $error;
	}
}