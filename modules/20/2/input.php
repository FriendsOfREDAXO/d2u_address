<div class="row">
	<div class="col-xs-4">Anzuzeigende Kontaktart</div>
	<div class="col-xs-8">
		<?php 
			$address_types = \D2U_Address\AddressType::getAll(rex_clang::getCurrentId());

			if(count($address_types) > 0) {
				print ' <select name="REX_INPUT_VALUE[1]" class="form-control">';
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
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">Die eMailadresse welches Kontakts soll direkt angezeigt werden?</div>
	<div class="col-xs-8">
		<?php 
			$addresses = D2U_Address\Address::getAll(TRUE);

			if(count($addresses) > 0) {
				print 'Die eMailadresse welches Kontakts soll direkt angezeigt werden? <select name="REX_INPUT_VALUE[2]" class="form-control">';
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
	</div>
</div>