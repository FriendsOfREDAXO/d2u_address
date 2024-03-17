<?php
$address_types = \FriendsOfREDAXO\D2UAddress\AddressType::getAll(rex_clang::getCurrentId());

if (count($address_types) > 0) {
?>
    <div class="row">
        <div class="col-xs-4">Anzuzeigende Kontaktart</div>
        <div class="col-xs-8">
            <?php
                echo '<select name="REX_INPUT_VALUE[1]" class="form-control">';
                foreach ($address_types as $address_type) {
                    echo '<option value="'. $address_type->address_type_id .'" ';

                    if ((int) 'REX_VALUE[1]' === $address_type->address_type_id) { /** @phpstan-ignore-line */
                        echo 'selected="selected" ';
                    }
                    echo '>'. $address_type->name .'</option>';
                }
                echo '</select>';
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">&nbsp;</div>
    </div>
<?php
    }
?>
<div class="row">
	<div class="col-xs-4">Standardkontakt für Option "weitere Länder"</div>
	<div class="col-xs-8">
		<?php
            $addresses = \FriendsOfREDAXO\D2UAddress\Address::getAll(rex_clang::getCurrentId());
            echo '<select name="REX_INPUT_VALUE[4]" class="form-control">';
            echo '<option value="0">Adressen des Standardlandes aus den Einstellungen anzeigen</option>';
            if (count($addresses) > 0) {
                foreach ($addresses as $address) {
                    echo '<option value="'. $address->address_id .'" ';

                    if ((int) 'REX_VALUE[4]' === $address->address_id) { /** @phpstan-ignore-line */
                        echo 'selected="selected" ';
                    }
                    echo '>'. $address->company .' - '. $address->contact_name  .'</option>';
                }
            }
            echo '</select>';
        ?>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div><div class="row">
	<div class="col-xs-4">Art der Karte:</div>
	<div class="col-xs-8">
		<?php
            $map_types = [];
            if (rex_addon::get('geolocation')->isAvailable()) {
                $map_types['geolocation'] = 'Geolocation Addon: Standardkarte';
                $mapsets = [];
                if (rex_version::compare('2.0.0', rex_addon::get('geolocation')->getVersion(), '<=')) {
                    // Geolocation 2.x
                    $mapsets = \FriendsOfRedaxo\Geolocation\Mapset::query()
                        ->orderBy('title')
                        ->findValues('title', 'id');
                } else {
                    /** @deprecated will be removed in version 2.0.0 */
                    // Geolocation 1.x
                    $mapsets = \Geolocation\mapset::query() /** @phpstan-ignore-line */
                        ->orderBy('title')
                        ->findValues('title', 'id');
                }
                foreach ($mapsets as $id => $name) {
                    $map_types[$id] = 'Geolocation Addon: '. $name;
                }
            } elseif (rex_addon::get('osmproxy')->isAvailable()) {
                $map_types['osm'] = 'OSM Proxy Addon OpenStreetMap Karte';
            }
            $map_types['google'] = 'Google Maps'. ('' !== rex_config::get('d2u_helper', 'maps_key', '') ? '' : ' (in den Einstellung des D2U Helper Addons muss hierfür noch ein Google Maps API Key eingegeben werden)');

            echo '<select name="REX_INPUT_VALUE[3]" class="form-control">';
            foreach ($map_types as $map_type_id => $map_type_name) {
                echo '<option value="'. $map_type_id .'"';

                if ((int) 'REX_VALUE[3]' === $map_type_id) { /** @phpstan-ignore-line */
                    echo ' selected="selected" ';
                }
                echo '>'. $map_type_name .'</option>';
            }
            echo '</select>';
        ?>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">&nbsp;</div>
</div>
<div class="row">
	<div class="col-xs-4">
		<input type="checkbox" name="REX_INPUT_VALUE[2]" value="true" <?php echo 'REX_VALUE[2]' === 'true' ? ' checked="checked"' : ''; /** @phpstan-ignore-line */ ?> class="form-control d2u_helper_toggle" />
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