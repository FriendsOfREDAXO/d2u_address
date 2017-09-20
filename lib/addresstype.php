<?php
/**
 * Address type
 */
class AddressType {
	/**
	 * @var int Database ID
	 */
	var $address_type_id = 0;
	
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
	 */
	 public function __construct($address_type_id) {
		$query = "SELECT * FROM ". rex::getTablePrefix() ."d2u_address_types "
				."WHERE address_type_id = ". $address_type_id;
		$result = rex_sql::factory();
		$result->setQuery($query);

		if ($result->getRows() > 0) {
			$this->address_type_id = $result->getValue("address_type_id");
			$this->name = $result->getValue("name");
			$this->show_address_details = $result->getValue("show_address_details");
			$this->show_country_select = $result->getValue("show_country_select");
			if($result->getValue("maps_zoom") != "") {
				$this->maps_zoom = $result->getValue("maps_zoom");
			}
			$this->article_id = $result->getValue("article_id");
			$this->article_id = $result->getValue("default_address_id");
		}
	}
	
	/**
	 * Get all address types
	 * @return AddressTypes[] Array with all address types
	 */
	public static function getAll() {
		$query = 'SELECT address_type_id FROM '. rex::getTablePrefix() .'d2u_address_types';
		$result = rex_sql::factory();
		$result->setQuery($query);

		$address_types = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$address_types[] = new AddressType($result->getValue("address_type_id"));
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
		$query = "SELECT address_id FROM ". rex::getTablePrefix() ."d2u_address_address "
				."WHERE address_type_ids LIKE '%|". $this->address_type_id ."|%' ";
		if($online_only) {
			$query .= "AND online_status = 'online' ";
		}
		$query .= "ORDER BY priority";
		$result = rex_sql::factory();
		$result->setQuery($query);
		$num_rows = $result->getRows();

		$addresses = [];
		for($i = 0; $i < $num_rows; $i++) {
			$addresses[] = new Address($result->getValue("address_id"));
			$result->next();
		}
		
		return $addresses;
	}

	/**
	 * Updates or inserts the object into database.
	 * @return boolean TRUE if error occured
	 */
	public function save() {
		$error = 0;

		$query = rex::getTablePrefix() ."d2u_address_types SET "
				."name = '". $this->name ."', "
				."show_address_details = '". $this->show_address_details ."', "
				."show_country_select = '". $this->show_country_select ."', "
				."maps_zoom = ". $this->maps_zoom .", "
				."default_address_id = ". $this->default_address_id .", "
				."article_id = ". $this->article_id ." ";
		if($this->address_type_id == 0) {
			$query = "INSERT INTO ". $query;
		}
		else {
			$query = "UPDATE ". $query ." WHERE address_type_id = ". $this->address_type_id;
		}

		$result = rex_sql::factory();
		$result->setQuery($query);
		if($this->address_type_id == 0) {
			$this->address_type_id = $result->getLastId();
			$error = $result->hasError();
		}

		return $error;
	}
}