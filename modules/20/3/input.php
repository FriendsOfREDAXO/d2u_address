<?php
$address_types = \D2U_Address\AddressType::getAll(rex_clang::getCurrentId());

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
	<div class="col-xs-4">Art der Karte:</div>
	<div class="col-xs-8">
		<?php
            $map_types = [];
            if (rex_addon::get('geolocation')->isAvailable()) {
                $map_types['geolocation'] = 'Geolocation Addon: Standardkarte';
                $mapsets = [];
				if(rex_version::compare('2.0.0', rex_addon::get('geolocation')->getVersion(), '<=')) {
					// Geolocation 2.x
                	$mapsets = \FriendsOfRedaxo\Geolocation\Mapset::query()
                    	->orderBy('title')
                    	->findValues('title', 'id');
				}
				else {
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
		Zoomstufe:
	</div>
	<div class="col-xs-8">
		<select name="REX_INPUT_VALUE[2]" class="form-control">
			<?php
            foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18] as $value) {
                echo '<option value="'.$value.'" ';

                if ((int) 'REX_VALUE[2]' === $value) { /** @phpstan-ignore-line */
                    echo 'selected="selected" ';
                }
                echo '>'. $value .'</option>';
            }
            ?>
		</select>
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