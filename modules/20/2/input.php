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
print "<br />";
print "<br />";

$addresses = Address::getAll(TRUE);

if(count($addresses) > 0) {
	print 'Die eMailadresse welches Kontakts soll direkt angezeigt werden? <select name="VALUE[2]">';
	foreach($addresses as $address) {
		echo '<option value="'. $address->address_id .'" ';

		if ("REX_VALUE[2]" == $address->address_id) {
			echo 'selected="selected" ';
		}
		echo '>'. $address->company .' ('. $address->contact_name .')</option>';
	}
	print '</select>';
}
?>