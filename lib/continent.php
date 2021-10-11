<?php
namespace D2U_Address;

/**
 * Data of country.
 */
class Continent implements \D2U_Helper\ITranslationHelper {
	/**
	 * @var int ID 
	 */
	var $continent_id = 0;
	
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
	var $country_ids = [];
	
	/**
	 * @var string "yes" if translation needs update
	 */
	var $translation_needs_update = "delete";
	
	/**
	 * Constructor.
	 * @param int $continent_id Continent ID.
	 * @param int $clang_id Redaxo language ID
	 */
	 public function __construct($continent_id, $clang_id = 0) {
		$this->clang_id = $clang_id;
		$query = "SELECT * FROM ". \rex::getTablePrefix() ."d2u_address_continents_lang AS lang "
				."LEFT JOIN ". \rex::getTablePrefix() ."d2u_address_continents AS continents "
					."ON lang.continent_id = continents.continent_id "
				."WHERE lang.continent_id = ". $continent_id ." "
					."AND clang_id = ". $clang_id;
		$result = \rex_sql::factory();
		$result->setQuery($query);
		$num_rows = $result->getRows();

		if ($num_rows > 0) {
			$this->continent_id = $result->getValue("continent_id");
			$this->country_ids = preg_grep('/^\s*$/s', explode("|", $result->getValue("country_ids")), PREG_GREP_INVERT);
			$this->name = stripslashes($result->getValue("name"));
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
		$query_lang = "DELETE FROM ". \rex::getTablePrefix() ."d2u_address_continents_lang "
			."WHERE continent_id = ". $this->continent_id
			. ($delete_all ? '' : ' AND clang_id = '. $this->clang_id) ;
		$result_lang = \rex_sql::factory();
		$result_lang->setQuery($query_lang);
		
		// If no more lang objects are available, delete
		$query_main = "SELECT * FROM ". \rex::getTablePrefix() ."d2u_address_continents_lang "
			."WHERE continent_id = ". $this->continent_id;
		$result_main = \rex_sql::factory();
		$result_main->setQuery($query_main);
		if($result_main->getRows() == 0) {
			$result = \rex_sql::factory();
			$result->setQuery("DELETE FROM ". \rex::getTablePrefix() ."d2u_address_continents "
				."WHERE continent_id = ". $this->continent_id);
		}
	}
	
	/**
	 * Gets all continents.
	 * @param int $clang_id Redaxo language ID
	 * @param int $address_type_id AddressType ID
	 * @return Continent[] Array with country objects.
	 */
	public static function getAll($clang_id = 0) {
		$query = 'SELECT continent_id FROM '. \rex::getTablePrefix() .'d2u_address_continents_lang '
				."WHERE clang_id = ". ($clang_id == 0 ? \rex_clang::getCurrentId() : $clang_id) ." "
				.'ORDER BY name';
		$result = \rex_sql::factory();
		$result->setQuery($query);

		$continents = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$continents[] = new Continent($result->getValue("continent_id"), $clang_id);
			$result->next();
		}
		
		return $continents;
    }
	
	/**
	 * Get objects concerning translation updates
	 * @param int $clang_id Redaxo language ID
	 * @param string $type 'update' or 'missing'
	 * @return Continent[] Array with country objects.
	 */
	public static function getTranslationHelperObjects($clang_id, $type) {
		$query = 'SELECT continent_id FROM '. \rex::getTablePrefix() .'d2u_address_continents_lang '
				."WHERE clang_id = ". $clang_id ." AND translation_needs_update = 'yes' "
				.'ORDER BY name';
		if($type == 'missing') {
			$query = 'SELECT main.continent_id FROM '. \rex::getTablePrefix() .'d2u_address_continents AS main '
					.'LEFT JOIN '. \rex::getTablePrefix() .'d2u_address_continents_lang AS target_lang '
						.'ON main.continent_id = target_lang.continent_id AND target_lang.clang_id = '. $clang_id .' '
					.'LEFT JOIN '. \rex::getTablePrefix() .'d2u_address_continents_lang AS default_lang '
						.'ON main.continent_id = default_lang.continent_id AND default_lang.clang_id = '. \rex_config::get('d2u_helper', 'default_lang') .' '
					."WHERE target_lang.continent_id IS NULL "
					.'ORDER BY default_lang.name';
			$clang_id = \rex_config::get('d2u_helper', 'default_lang');
		}
		$result = \rex_sql::factory();
		$result->setQuery($query);

		$objects = [];
		for($i = 0; $i < $result->getRows(); $i++) {
			$objects[] = new Continent($result->getValue("continent_id"), $clang_id);
			$result->next();
		}
		
		return $objects;
    }

	/**
	 * Updates or inserts the object into database.
	 * @return boolean TRUE if error occured
	 */
	public function save() {
		$error = FALSE;

		// Save the not language specific part
		$pre_save_country = new Continent($this->continent_id, $this->clang_id);
	
		$result = \rex_sql::factory();
		if($this->continent_id == 0 || $pre_save_country != $this) {
			$query = \rex::getTablePrefix() ."d2u_address_continents SET "
					."country_ids = '|". implode("|", $this->country_ids) ."|' ";

			if($this->continent_id == 0) {
				$query = "INSERT INTO ". $query;
			}
			else {
				$query = "UPDATE ". $query ." WHERE continent_id = ". $this->continent_id;
			}

			$result->setQuery($query);
			if($this->continent_id == 0) {
				$this->continent_id = $result->getLastId();
				$error = $result->hasError();
			}
		}
		
		if($error == 0) {
			// Save the language specific part
			$pre_save_country = new Continent($this->continent_id, $this->clang_id);
			if($pre_save_country != $this) {
				$query = "REPLACE INTO ". \rex::getTablePrefix() ."d2u_address_continents_lang SET "
						."continent_id = ". $this->continent_id .", "
						."clang_id = ". $this->clang_id .", "
						."name = '". addslashes($this->name) ."', "
						."translation_needs_update = '". $this->translation_needs_update ."' ";
				$result->setQuery($query);
				$error = $result->hasError();
			}
		}
		
		return $error;
	}
}