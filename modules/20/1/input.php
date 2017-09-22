<?php 
$address_types = AddressType::getAll(rex_clang::getCurrentId());

if(count($address_types) > 0) {
	print 'Welche Kontaktart soll in diesem Artikel angezeigt werden? <select name="VALUE[1]">';
	foreach ($address_types as $address_type) {
		echo '<option value="'. $address_type->address_type_id .'" ';

		if ("REX_VALUE[1]" == $address_type->address_type_id) {
			echo 'selected="selected" ';
		}
		echo '>'. $address_type->name .'</option>';
	}
	print '</select>';
}

?>
<p><br />Alle weiteren Daten werden im <a href="index.php?page=d2u_address">D2U Adressen Addon</a> verwaltet.</p>