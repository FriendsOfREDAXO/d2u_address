<?php
$address_type_id = intval("REX_VALUE[1]"); /** @phpstan-ignore-line */
$address_type = new D2U_Address\AddressType($address_type_id, rex_clang::getCurrentId());
$show_fax = "REX_VALUE[2]" === 'true' ? true : false; /** @phpstan-ignore-line */
$map_type = "REX_VALUE[3]" === '' ? 'google' : "REX_VALUE[3]"; // Backward compatibility /** @phpstan-ignore-line */
$default_contact = intval("REX_VALUE[4]") > 0 ? new D2U_Address\Address(intval("REX_VALUE[4]"), rex_clang::getCurrentId()) : false; /** @phpstan-ignore-line */

// Get placeholder wildcard tags and other presets
$sprog = rex_addon::get("sprog");
$tag_open = $sprog->getConfig('wildcard_open_tag');
$tag_close = $sprog->getConfig('wildcard_close_tag');

$country = (int) rex_request('country_id', 'int') > 0 ? new D2U_Address\Country((int) rex_request('country_id', 'int'), rex_clang::getCurrentId()) : false; /** @phpstan-ignore-line */
$zip_code = (int) rex_request('zip_code', 'int') > 0 && $country instanceof D2U_Address\Country ? D2U_Address\ZipCode::get($country, (int) rex_request('zip_code', 'int')) : false; /** @phpstan-ignore-line */

$d2u_address = rex_addon::get('d2u_address');
$default_country_id = $d2u_address->hasConfig('default_country_id') ? intval($d2u_address->getConfig('default_country_id')) : 0;

$maps_zoom = $address_type->maps_zoom;

// Form selections
$addresses = [];
if($address_type->show_country_select) {
	if(rex_request('country_id', 'int') === -1 && $default_contact instanceof D2U_Address\Address && $default_contact->address_id > 0) { /** @phpstan-ignore-line */
		$addresses[] = $default_contact;
	}
	else if($zip_code instanceof D2U_Address\ZipCode) {
		$addresses = $zip_code->getAdresses(true);
	}
	else if($country !== false) {
		$addresses = $country->getAddresses($address_type, true);
	}
	else if(rex_request::server('HTTP_ACCEPT_LANGUAGE', 'string') !== '') {
		$accepted_lang = explode(";", rex_request::server('HTTP_ACCEPT_LANGUAGE', 'string'));
		$accepted_lang = explode(",", $accepted_lang[0]);
		$countries = D2U_Address\Country::getByLangCode($accepted_lang[0], rex_clang::getCurrentId());
		if(count($countries) > 0) {
			$country = new D2U_Address\Country($countries[0]->country_id, rex_clang::getCurrentId());
			foreach ($countries as $cur_country) {
				// show all addresses with this ISO lang code
				$countries_addresses = $cur_country->getAddresses($address_type, true);
				foreach ($countries_addresses as $key => $countries_address) {
					$addresses[$key] = $countries_address;
				}
			}
		}
	}
	if($country !== false) {
		$maps_zoom = $country->maps_zoom;
	}
}
else {
	$addresses = $address_type->getAddresses(true);
}

// Fallback if no address was found, there should be at least one address
if(count($addresses) === 0) {
	$country = new D2U_Address\Country($default_country_id, rex_clang::getCurrentId());
	$addresses = $country->getAddresses($address_type, true);
	$maps_zoom = $country->maps_zoom;
}

// Output
if($address_type->show_country_select) {
	// Only if coutry selection should be available
	print '<div class="col-12">';
	print '<h1>'. $tag_open .'d2u_address_local_servicepartner'. $tag_close .'</h1>';
	print '<p>'. $tag_open .'d2u_address_specialists'. $tag_close .'</p>';
	print '<br />';

	print '<div class="country-box">';
	print '<form method="post">';
	print '<div class="row">';
	print '<div class="col-12 col-md-6">';
	print '<select name="country_id" class="country_select" onChange="this.form.submit()">';
	$countries = $address_type->getCountries();
	foreach($countries as $cur_country) {
		$selected = "";
		if(rex_request('country_id', 'int') > -1 && (($country instanceof D2U_Address\Country && $country->country_id === $cur_country->country_id) || ($country === false && $cur_country->country_id === $default_country_id))) {
			$selected = ' selected="selected"';
		}
		print '<option value="'. $cur_country->country_id .'" '. $selected .'>'. $cur_country->name .'</option>';
	}
	print '<option value="-1" '. (rex_request('country_id', 'int') === -1 ? 'selected="selected"' : '') .'>'. $tag_open .'d2u_address_other_countries'. $tag_close .'</option>';
	print '</select>';
	print '</div>';

	if(rex_request('country_id', 'int') > -1 && $country !== false) {
		$country_zip_codes = $country->getZipCodes();
		if(count($country_zip_codes) > 0) {
			$show_zip_code_field = false;
			foreach($country_zip_codes as $country_zip_code) {
				if($country_zip_code->isOnline()) {
					$show_zip_code_field = true;
					break;
				}
			}
			if($show_zip_code_field) {
				print '<div class="col-12 col-md-6">';
				$placeholder = $zip_code === false ? $tag_open .'d2u_helper_module_form_zip'. $tag_close : rex_request('zip_code', 'int');
				print '<input type="text" value="'. ($zip_code !== false ? rex_request('zip_code', 'int') : '') .'" name="zip_code" placeholder="'. $placeholder .'">';
				print '<input type="submit" value="»" class="zip_code">';
				print '</div>';
			}
		}
	}

	print '</div>';
	print '</form>';
	print '</div>';
	print '</div>';
	print '<br />';
} // END if country selection should be available

if(count($addresses) > 0) {
	if($address_type->show_country_select) {
		// Only if coutry selection should be available
		print '<div class="col-12"><h2>'. $tag_open .'d2u_address_nearby'. $tag_close .'</h2><br></div>';
	}
	foreach($addresses as $address) {
		print '<div class="col-12 col-md-6">';
		print '<div class="country-box" data-height-watch>';
		print '<div class="row">';
		print '<div class="col-3">';
		$a_href_open = $address->article_id > 0 ? '<a href="'. rex_getUrl($address->article_id) .'">' : '';
		$a_href_close = $address->article_id > 0 ? '</a>' : '';
		print $a_href_open .'<img src="'.
			($address->picture !== "" ? 'index.php?rex_media_type=d2u_address_120x150&rex_media_file='. $address->picture : \rex_addon::get('d2u_address')->getAssetsUrl("noavatar.jpg"))
			.'" alt="'. $address->company . $address->contact_name .'">'. $a_href_close;
		print '</div>';
		print '<div class="col-9">';
		if($address->contact_name !== "") {
			print $a_href_open .'<h3>'. $address->contact_name .'</h3>'. $a_href_close;
			print $address->company .'<br>';
		}
		else {
			print $a_href_open .'<h3>'. $address->company .'</h3>'. $a_href_close;
		}
		if($address->company_appendix !== "") {
			print $address->company_appendix .'<br>';
		}
		if($address_type->show_address_details) {
			print ($address->additional_address !== "" ? $address->additional_address .'<br>' : '');
			print $address->street .'<br>';
			print $address->zip_code .' '. $address->city .'<br>';
		}
		print '<br />';
		if($address->mobile !== "") {
			print $tag_open .'d2u_address_mobile'. $tag_close .' '. $address->mobile .'<br>';
		}
		if($address->phone !== "") {
			print $tag_open .'d2u_helper_module_form_phone'. $tag_close .' '. $address->phone .'<br>';
		}
		if($show_fax && $address->fax !== "") { /** @phpstan-ignore-line */
			print $tag_open .'d2u_address_fax'. $tag_close .' '. $address->fax .'<br>';
		}

		// Google Analytics Event
		$google_analytics = "";
		if(rex_config::get('d2u_address', 'analytics_emailevent_activate', 'false') === 'true' &&
				rex_config::get('d2u_address', 'analytics_emailevent_category', '') !== '' &&
				rex_config::get('d2u_address', 'analytics_emailevent_action', '') !== '' &&
				rex_request('search_it_build_index', 'int', false) === false) {
			$google_analytics = " onClick=\"ga('send', 'event', '". rex_config::get('d2u_address', 'analytics_emailevent_category') ."', '". rex_config::get('d2u_address', 'analytics_emailevent_action') ."', '". $address->email ."');\"";
		}
		print '<a href="mailto:'. $address->email .'"'. $google_analytics .'>'. $address->email .'</a>';
		print '</div>';
		print '</div>';
		print '</div>';
		print '</div>';
	}
	print '<br />';
}
?>

<div class="col-12">
	<?php
		if($map_type === "google") { /** @phpstan-ignore-line */
			$d2u_helper = rex_addon::get("d2u_helper");
			$api_key = "";
			if($d2u_helper->getConfig("maps_key", "") !== "") {
				$api_key = '?key='. $d2u_helper->getConfig("maps_key");
			}
	?>

	<script src="https://maps.googleapis.com/maps/api/js<?php echo $api_key; ?>"></script>
	<div id="map_canvas" style="display: block; width: 100%; height: 500px;"></div> 
	<script>
		var map;
		var infowindow = new google.maps.InfoWindow();
		var address = [<?php
			foreach($addresses as $address) {
				print "[";
				// Address for Geocoder
				print '"'. $address->street .', '. $address->zip_code .' '. $address->city .'", ';
				// Infotext
				$infotext = '"'. $address->company .'<br />';
				if($address->contact_name !== "") {
					$infotext .= $address->contact_name .'<br />';			
				}
				$infotext .= ($address->country instanceof D2U_Address\Country ? $address->country->name .' - ' : '') . $address->zip_code .' '. $address->city.'"';
				print $infotext .", ";
				// Latitude and Longitude
				print $address->latitude .', '. $address->longitude;
				print "],". PHP_EOL;
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
			  zoom: <?php print $maps_zoom; ?>,
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
		}
		elseif($map_type === "osm" && rex_addon::get('osmproxy')->isAvailable()) { /** @phpstan-ignore-line */
			$map_id = rand();
			$latitude_max = 0;
			$latitude_min = 0;
			$longitude_max = 0;
			$longitude_min = 0;
			$address_counter = 0;
			foreach($addresses as $address) {
				if($address->address_id > 0) {
					// Max and min values needed to calculate map center
					if($address->latitude > $latitude_max || $latitude_max === 0) {
						$latitude_max = $address->latitude;
					}
					if($address->latitude < $latitude_min || $latitude_min === 0) {
						$latitude_min = $address->latitude;
					}
					if($address->longitude > $longitude_max || $longitude_max === 0) {
						$longitude_max = $address->longitude;
					}
					if($address->longitude < $longitude_min || $longitude_min === 0) {
						$longitude_min = $address->longitude;
					}

					$address_counter++;
				}
			}

			if ($address_counter > 0) {
				$leaflet_js_file = 'modules/04-2/leaflet.js';
				print '<script src="'. rex_url::addonAssets('d2u_helper', $leaflet_js_file) .'?buster='. filemtime(rex_path::addonAssets('d2u_helper', $leaflet_js_file)) .'"></script>' . PHP_EOL;
	?>
		<div id="map-<?php echo $map_id; ?>" style="width:100%; height: 500px"></div>
		<script type="text/javascript" async="async">
			<?php
				print "var map = L.map('map-". $map_id ."').setView([". (($latitude_max + $latitude_min) / 2) .", ". (($longitude_max + $longitude_min) / 2) ."], ". $maps_zoom .");";
			?>
			L.tileLayer('/?osmtype=german&z={z}&x={x}&y={y}', {
				attribution: 'Map data &copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors'
			}).addTo(map);
			map.scrollWheelZoom.disable();
			var myIcon = L.icon({
				iconUrl: '<?php echo rex_url::addonAssets('d2u_helper', 'modules/04-2/marker-icon.png'); ?>',
				shadowUrl: '<?php echo rex_url::addonAssets('d2u_helper', 'modules/04-2/marker-shadow.png'); ?>',

				iconSize:     [25, 41], // size of the icon
				shadowSize:   [41, 41], // size of the shadow
				iconAnchor:   [12, 40], // point of the icon which will correspond to marker's location
				shadowAnchor: [13, 40], // the same for the shadow
				popupAnchor:  [0, -41]  // point from which the popup should open relative to the iconAnchor
			});

			<?php
				foreach($addresses as $address) {
					if(($address->latitude > 0 || $address->latitude < 0) && ($address->longitude > 0 || $address->longitude < 0)) {
						$infotext = '"'. $address->company .'<br />';
						if($address->contact_name !== "") {
							$infotext .= $address->contact_name .'<br />';			
						}
						$infotext .= ($address->country instanceof D2U_Address\Country ? $address->country->name .' - ' : ''). $address->zip_code .' '. $address->city.'"';

						print "var marker = L.marker([". $address->latitude .", ". $address->longitude ."], {"
								."draggable: false,"
								."icon: myIcon"
								."}).addTo(map).bindPopup('". addslashes($infotext) ."');";
					}
				}
			?>
		</script>
	<?php
			}
		}
		elseif(rex_addon::get('geolocation')->isAvailable()) {
			$modInUse = intval(rex::getProperty("d2u_module_geolocation_used", 0));
			rex::setProperty("d2u_module_geolocation_used", ++$modInUse);
			if($modInUse === 1) {
				try {
					if(rex::isFrontend()) {
						\Geolocation\tools::echoAssetTags();
					}
?>
<script>
	Geolocation.default.positionColor = '<?= strval(rex_config::get('d2u_helper', 'article_color_h')); ?>';

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
				}
				catch (Exception $e) {}
			}

			$mapsetId = (int) 'REX_VALUE[9]';

			$rex_map = \Geolocation\mapset::take($mapsetId)
				->attributes('id', (string) $mapsetId);

			$latitude_max = 0;
			$latitude_min = 0;
			$longitude_max = 0;
			$longitude_min = 0;
			foreach($addresses as $address) {
				if(($address->latitude > 0 || $address->latitude < 0) && ($address->longitude > 0 || $address->longitude < 0)) {
					// Max and min values needed to calculate map center
					if($address->latitude > $latitude_max || $latitude_max === 0) {
						$latitude_max = $address->latitude;
					}
					if($address->latitude < $latitude_min || $latitude_min === 0) {
						$latitude_min = $address->latitude;
					}
					if($address->longitude > $longitude_max || $longitude_max === 0) {
						$longitude_max = $address->longitude;
					}
					if($address->longitude < $longitude_min || $longitude_min === 0) {
						$longitude_min = $address->longitude;
					}

					$infotext = '"'. $address->company .'<br />';
					if($address->contact_name !== "") {
						$infotext .= $address->contact_name .'<br />';			
					}
					$infotext .= ($address->country instanceof D2U_Address\Country ? $address->country->name .' - ' : ''). $address->zip_code .' '. $address->city.'"';

					$rex_map->dataset('infobox|'. $address->address_id, [[$address->latitude, $address->longitude], $infotext]);
				}
			}						
			echo $rex_map->dataset('center', [[($latitude_max + $latitude_min) / 2, ($longitude_max + $longitude_min) / 2], $maps_zoom])->parse();
		}
	?>
</div>