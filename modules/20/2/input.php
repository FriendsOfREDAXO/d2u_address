<?php 
$query = "SELECT * FROM ". $REX['TABLE_PREFIX'] ."d2u_ktb_adressen_adressarten ORDER BY name ASC";
$result = rex_sql::factory();
$result->setQuery($query);
$num_rows = $result->getRows();

if ($num_rows > 0) {
	print 'Auf welche Kontaktart soll in dieser Box verlinkt werden? <select name="VALUE[1]">';
	for($i = 1; $i <= $num_rows; $i++) {
		echo '<option value="'. $result->getValue("adressart_id") .'" ';

		if ("REX_VALUE[1]" == $result->getValue("adressart_id")) {
			echo 'selected="selected" ';
		}
		echo '>'. $result->getValue("name") .'</option>';
		
		$result->next();
	}
	print '</select>';
}
print "<br />";
print "<br />";

$query = "SELECT * FROM ". $REX['TABLE_PREFIX'] ."d2u_ktb_adressen_adressen ORDER BY firma ASC";
$result = rex_sql::factory();
$result->setQuery($query);
$num_rows = $result->getRows();

if ($num_rows > 0) {
	print 'Die eMailadresse welches Kontakts soll direkt angezeigt werden? <select name="VALUE[2]">';
	for($i = 1; $i <= $num_rows; $i++) {
		echo '<option value="'. $result->getValue("adress_id") .'" ';

		if ("REX_VALUE[2]" == $result->getValue("adress_id")) {
			echo 'selected="selected" ';
		}
		echo '>'. $result->getValue("firma") .'</option>';
		
		$result->next();
	}
	print '</select>';
}
?>