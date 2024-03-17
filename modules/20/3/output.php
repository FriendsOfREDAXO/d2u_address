<?php
$address_type_id = (int) 'REX_VALUE[1]'; /** @phpstan-ignore-line */
$address_type = new FriendsOfREDAXO\D2UAddress\AddressType($address_type_id, rex_clang::getCurrentId());
$maps_zoom = (int) 'REX_VALUE[2]'; /** @phpstan-ignore-line */
$map_type = 'REX_VALUE[3]';

// Get addresses
$addresses = $address_type->getAddresses(true);

// Output
?>
<div class="col-12">
	<?php
        if ('google' === $map_type) { /** @phpstan-ignore-line */
            $d2u_helper = rex_addon::get('d2u_helper');
            $api_key = '';
            if ('' !== $d2u_helper->getConfig('maps_key', '')) {
                $api_key = '?key='. $d2u_helper->getConfig('maps_key');
            }
    ?>

	<script src="https://maps.googleapis.com/maps/api/js<?= $api_key ?>"></script>
	<div id="map_canvas" style="display: block; width: 100%; height: 700px;"></div>
	<script>
		var map;
		var infowindow = new google.maps.InfoWindow();
		var address = [<?php
            foreach ($addresses as $address) {
                echo '[';
                // Address for Geocoder
                echo '"'. $address->street .', '. $address->zip_code .' '. $address->city .'", ';
                // Infotext
                $infotext = $address->company .'<br />';
                if ('' !== $address->contact_name) {
                    $infotext .= $address->contact_name .'<br />';
                }
                $infotext .= ($address->country instanceof FriendsOfREDAXO\D2UAddress\Country ? $address->country->name .' - ' : '') . $address->zip_code .' '. $address->city;
                if ('' !== $address->phone) {
                    $infotext .= '<br />'. \Sprog\Wildcard::get('d2u_address_phone') .' '. $address->phone;
                }
                if ('' !== $address->mobile) {
                    $infotext .= '<br />'. \Sprog\Wildcard::get('d2u_address_mobile') .' '. $address->mobile;
                }
                $infotext .= '"';

                echo $infotext .', ';
                // Latitude and Longitude
                echo $address->latitude .', '. $address->longitude;
                echo '],'. PHP_EOL;
            }
        ?>];
		var address_position = 0;
		var timeout = 100;
		var center = true;

		/**
		 * Initialize map
		 */
		function initialize() {
			// Default center of map
			var latlng = new google.maps.LatLng(47.6242,7.67378);
			// Map settings
			var myOptions = {
			  zoom: <?= $maps_zoom ?>,
			  center: latlng,
			  mapTypeId: 'hybrid'
			};
			// create map
			map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

			// Address position
			calcPosition(address_position);
		}

		/**
		 * Calculate address map position
		 * @param {int} position address array position
		 */
		function calcPosition(position) {
			if(address[position][2] > 0 && address[position][3] > 0) {
				// Use given latitude and longitude
				addMarker(new google.maps.LatLng(address[position][2], address[position][3]), position);
			}
			else {
				// Geocode
				var geocoder = new google.maps.Geocoder();
				geocoder.geocode({'address': address[position][0]}, function(results, status) {
					if (status === google.maps.GeocoderStatus.OK) {
						addMarker(results[0].geometry.location, position);
					}
					else {
						if (status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {
							// Too many queries, just wait a while and then retry geocoding again
							setTimeout(function() { calcPosition(position); }, (timeout * 3));
						}
					}
				});
			}

			// Go to next address
			address_position++;
			if (address_position < address.length) {
				setTimeout(function() { calcPosition(address_position); }, (timeout));
			}
		}

		/**
		 * Add marker with infowindow on map
		 * @param {google.maps.LatLng} location
		 * @param {int} position address array position
		 */
		function addMarker(location, position) {
			// Create marker
			var marker = new google.maps.Marker({
				position: location,
				map: map
			});
			// Show info window
			google.maps.event.addListener(marker, 'click', function() {
				infowindow.setContent('<div id="infoWindow" style="white-space: nowrap;">' + address[position][1] + '</div>');
				infowindow.open(map, marker);
			});
			// Set map center on first address
			if(center === true) {
				map.setCenter(location);
				center = false;
			}
		}

		initialize();
	</script>
	<?php
        } elseif ('osm' === $map_type && rex_addon::get('osmproxy')->isAvailable()) { /** @phpstan-ignore-line */
            $map_id = random_int(0, getrandmax());
            $latitude_max = 0;
            $latitude_min = 0;
            $longitude_max = 0;
            $longitude_min = 0;
            $address_counter = 0;
            foreach ($addresses as $address) {
                if ($address->address_id > 0) {
                    // Max and min values needed to calculate map center
                    if ($address->latitude > $latitude_max || 0 === $latitude_max) {
                        $latitude_max = $address->latitude;
                    }
                    if ($address->latitude < $latitude_min || 0 === $latitude_min) {
                        $latitude_min = $address->latitude;
                    }
                    if ($address->longitude > $longitude_max || 0 === $longitude_max) {
                        $longitude_max = $address->longitude;
                    }
                    if ($address->longitude < $longitude_min || 0 === $longitude_min) {
                        $longitude_min = $address->longitude;
                    }

                    ++$address_counter;
                }
            }

            if ($address_counter > 0) {
                $leaflet_js_file = 'modules/04-2/leaflet.js';
                echo '<script src="'. rex_url::addonAssets('d2u_helper', $leaflet_js_file) .'?buster='. filemtime(rex_path::addonAssets('d2u_helper', $leaflet_js_file)) .'"></script>' . PHP_EOL;
    ?>
		<div id="map-<?= $map_id ?>" style="width:100%; height: 700px"></div>
		<script type="text/javascript" async="async">
			<?= "var map = L.map('map-". $map_id ."').setView([". (($latitude_max + $latitude_min) / 2) .', '. (($longitude_max + $longitude_min) / 2) .'], '. $maps_zoom .');';
            ?>
			L.tileLayer('/?osmtype=german&z={z}&x={x}&y={y}', {
				attribution: 'Map data &copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
			}).addTo(map);
			map.scrollWheelZoom.disable();
			var myIcon = L.icon({
				iconUrl: '<?= rex_url::addonAssets('d2u_helper', 'modules/04-2/marker-icon.png') ?>',
				shadowUrl: '<?= rex_url::addonAssets('d2u_helper', 'modules/04-2/marker-shadow.png') ?>',

				iconSize:     [25, 41], // size of the icon
				shadowSize:   [41, 41], // size of the shadow
				iconAnchor:   [12, 40], // point of the icon which will correspond to marker's location
				shadowAnchor: [13, 40], // the same for the shadow
				popupAnchor:  [0, -41]  // point from which the popup should open relative to the iconAnchor
			});

			<?php
                foreach ($addresses as $address) {
                    if (($address->latitude > 0 || $address->latitude < 0) && ($address->longitude > 0 || $address->longitude < 0)) {
                        $infotext = $address->company .'<br />';
                        if ('' !== $address->contact_name) {
                            $infotext .= $address->contact_name .'<br />';
                        }
                        $infotext .= ($address->country instanceof FriendsOfREDAXO\D2UAddress\Country ? $address->country->name .' - ' : ''). $address->zip_code .' '. $address->city;
                        if ('' !== $address->phone) {
                            $infotext .= '<br />'. \Sprog\Wildcard::get('d2u_address_phone') .' '. $address->phone;
                        }
                        if ('' !== $address->mobile) {
                            $infotext .= '<br />'. \Sprog\Wildcard::get('d2u_address_mobile') .' '. $address->mobile;
                        }

                        echo 'var marker = L.marker(['. $address->latitude .', '. $address->longitude .'], {'
                                .'draggable: false,'
                                .'icon: myIcon'
                                ."}).addTo(map).bindPopup('". addslashes($infotext) ."');";
                    }
                }
            ?>
		</script>
	<?php
            }
        } elseif (rex_addon::get('geolocation')->isAvailable()) {
            $modInUse = (int) rex::getProperty('d2u_module_geolocation_used', 0);
            rex::setProperty('d2u_module_geolocation_used', ++$modInUse);
            if (1 === $modInUse) {
                try {
                    if (rex::isFrontend()) {
                        if (rex_version::compare('2.0.0', rex_addon::get('geolocation')->getVersion(), '<=')) {
                            // Geolocation 2.x
                            \FriendsOfRedaxo\Geolocation\Tools::echoAssetTags();
                        } else {
							/** @deprecated remove in version 2 */
                            // Geolocation 1.x
                            \Geolocation\tools::echoAssetTags(); /** @phpstan-ignore-line */
                        }
                    }
?>
<script>
	Geolocation.default.positionColor = '<?= (string) rex_config::get('d2u_helper', 'article_color_h') ?>';

	// adjust zoom level
	Geolocation.Tools.Center = class extends Geolocation.Tools.Template{
		constructor ( ...args){
			super(args);
			this.zoom = this.zoomDefault = Geolocation.default.zoom;
			this.center = this.centerDefault = L.latLngBounds( Geolocation.default.bounds ).getCenter();
			return this;
		}
		setValue( data ){
			super.setValue( data );
			this.center = L.latLng( data[0] ) || this.centerDefault;
			this.zoom = data[1] || this.zoomDefault;
			this.radius = data[2];
			this.circle = null;
			if( data[2] ) {
				let options = Geolocation.default.styleCenter;
				options.color = data[3] || options.color;
				options.radius = this.radius;
				this.circle = L.circle( this.center, options );
			}
			if( this.map ) this.show( this.map );
			return this;
		}
		show( map ){
			super.show( map );
			map.setView( this.center, this.zoom );
			if( this.circle instanceof L.Circle ) this.circle.addTo( map );
			return this;
		}
		remove(){
			if( this.circle instanceof L.Circle ) this.circle.remove();
			super.remove();
			return this;
		}
		getCurrentBounds(){
			if( this.circle instanceof L.Circle ) {
				return this.radius ? this.circle.getBounds() : this.circle.getLatLng();
			}
			return this.center;
		}
	};
	Geolocation.tools.center = function(...args) { return new Geolocation.Tools.Center(args); };

	// add info box
	Geolocation.Tools.Infobox = class extends Geolocation.Tools.Position{
		setValue( dataset ) {
			// keine Koordinaten => Abbruch
			if( !dataset[0] ) return this;

			// GGf. Default-Farbe temporär ändern, normalen Position-Marker erzeugen
			let color = Geolocation.default.positionColor;
			Geolocation.default.positionColor = dataset[2] || Geolocation.default.positionColor;
			super.setValue(dataset[0]);
			Geolocation.default.positionColor = color;

			// Wenn angegeben: Text als Popup hinzufügen
			if( this.marker && dataset[1] ) {
				this.marker.bindPopup(dataset[1]);
				this.marker.on('click', function (e) {
					this.openPopup();
				});
			}
			return this;
		}
	};
	Geolocation.tools.infobox = function(...args) { return new Geolocation.Tools.Infobox(args); };
</script>
<?php
                } catch (Exception $e) {
                }
            }

            $mapsetId = (int) 'REX_VALUE[9]';

            $rex_map = null;
            if (rex_version::compare('2.0.0', rex_addon::get('geolocation')->getVersion(), '<=')) {
                // Geolocation 2.x
                $rex_map = \FriendsOfRedaxo\Geolocation\Mapset::take($mapsetId)
                    ->attributes('id', (string) $mapsetId);
            } else {
				/** @deprecated remove in version 2 */
                // Geolocation 1.x
                $rex_map = \Geolocation\mapset::take($mapsetId) /** @phpstan-ignore-line */
                    ->attributes('id', (string) $mapsetId);
            }

            $latitude_max = 0;
            $latitude_min = 0;
            $longitude_max = 0;
            $longitude_min = 0;
            foreach ($addresses as $address) {
                if (($address->latitude > 0 || $address->latitude < 0) && ($address->longitude > 0 || $address->longitude < 0)) {
                    // Max and min values needed to calculate map center
                    if ($address->latitude > $latitude_max || 0 === $latitude_max) {
                        $latitude_max = $address->latitude;
                    }
                    if ($address->latitude < $latitude_min || 0 === $latitude_min) {
                        $latitude_min = $address->latitude;
                    }
                    if ($address->longitude > $longitude_max || 0 === $longitude_max) {
                        $longitude_max = $address->longitude;
                    }
                    if ($address->longitude < $longitude_min || 0 === $longitude_min) {
                        $longitude_min = $address->longitude;
                    }

                    $infotext = $address->company .'<br />';
                    if ('' !== $address->contact_name) {
                        $infotext .= $address->contact_name .'<br />';
                    }
                    $infotext .= ($address->country instanceof FriendsOfREDAXO\D2UAddress\Country ? $address->country->name .' - ' : ''). $address->zip_code .' '. $address->city;
                    if ('' !== $address->phone) {
                        $infotext .= '<br />'. \Sprog\Wildcard::get('d2u_address_phone') .' '. $address->phone;
                    }
                    if ('' !== $address->mobile) {
                        $infotext .= '<br />'. \Sprog\Wildcard::get('d2u_address_mobile') .' '. $address->mobile;
                    }

                    $rex_map->dataset('infobox|'. $address->address_id, [[$address->latitude, $address->longitude], $infotext]);
                }
            }
            echo $rex_map->dataset('center', [[($latitude_max + $latitude_min) / 2, ($longitude_max + $longitude_min) / 2], $maps_zoom])->parse();
        }
    ?>
</div>