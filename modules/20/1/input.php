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
	<div class="col-xs-4">
		<input type="checkbox" name="REX_INPUT_VALUE[2]" value="true" <?php echo "REX_VALUE[2]" == 'true' ? ' checked="checked"' : ''; ?> style="float: right;" />
	</div>
	<div class="col-xs-8">
		Faxnummer anzeigen - wenn vorhanden <br />
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<p>Alle weiteren Daten werden im <a href="index.php?page=d2u_address">D2U Adressen Addon</a> verwaltet.</p>
	</div>
</div>