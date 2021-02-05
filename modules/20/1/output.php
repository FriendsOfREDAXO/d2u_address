<?php
$address_type_id = "REX_VALUE[1]" == "" ? 0 : "REX_VALUE[1]";
$address_type = new D2U_Address\AddressType($address_type_id, rex_clang::getCurrentId());
$show_fax = "REX_VALUE[2]" == 'true' ? TRUE : FALSE;

if(\rex::isBackend()) {
	// BACKEND
	print '<h1 style="font-size: 1.5em;">Adressliste</h1>';
	print "Adressart: ". $address_type->name ."<br>";
	print "Faxnummer anzeigen: ". ($show_fax ? "Ja" : "Nein") ."<br>";
}
else {
	// FRONTEND
	// Get placeholder wildcard tags and other presets
	$sprog = rex_addon::get("sprog");
	$tag_open = $sprog->getConfig('wildcard_open_tag');
	$tag_close = $sprog->getConfig('wildcard_close_tag');

	$country = rex_request('country_id', 'int') > 0 ? new D2U_Address\Country(rex_request('country_id', 'int'), rex_clang::getCurrentId()) : FALSE;
	$zip_code = rex_request('zip_code', 'int') > 0 ? D2U_Address\ZipCode::get($country, rex_request('zip_code', 'int')) : FALSE;

	$d2u_address = rex_addon::get('d2u_address');
	$default_country_id = $d2u_address->hasConfig('default_country_id') ? $d2u_address->getConfig('default_country_id') : 0;

	$maps_zoom = $address_type->maps_zoom;

	// Form selections
	$addresses = [];
	if($address_type->show_country_select == "yes") {
		if($zip_code !== FALSE) {
			$addresses = $zip_code->getAdresses(TRUE);
		}
		else if($country !== FALSE) {
			$addresses = $country->getAddresses($address_type, TRUE);
		}
		else {
			$accepted_lang = explode(";", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			$accepted_lang = explode(",", $accepted_lang[0]);
			$countries = D2U_Address\Country::getByLangCode($accepted_lang[0], rex_clang::getCurrentId());
			if(count($countries) > 0) {
				$country = new D2U_Address\Country($countries[0]->country_id, rex_clang::getCurrentId());
				foreach ($countries as $cur_country) {
					// show all addresses with this ISO lang code
					$countries_addresses = $cur_country->getAddresses($address_type, TRUE);
					foreach ($countries_addresses as $key => $countries_address) {
						$addresses[$key] = $countries_address;
					}
				}
			}
		}
		$maps_zoom = $country->maps_zoom;
	}
	else {
		$addresses = $address_type->getAddresses(TRUE);
	}

	// Fallback if no address was found, there should be at least one address
	if(count($addresses) == 0) {
		$country = new D2U_Address\Country($default_country_id, rex_clang::getCurrentId());
		$addresses = $country->getAddresses($address_type, TRUE);
		$maps_zoom = $country->maps_zoom;
	}

	// Output
	if($address_type->show_country_select == "yes") {
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
		$already_selected = FALSE;
		foreach($countries as $cur_country) {
			$selected = "";
			if($country->country_id == $cur_country->country_id) {
				$selected = ' selected="selected"';
				$already_selected = TRUE;
			}
			else if($country === FALSE && $cur_country->country_id == $default_country_id) {
				$selected = ' selected="selected"';
				$already_selected = TRUE;
			}
			print '<option value="'. $cur_country->country_id .'" '. $selected .'>'. $cur_country->name .'</option>';
		}
		print '<option value="-1" '. ($already_selected ? '' : 'selected="selected"') .'>'. $tag_open .'d2u_address_other_countries'. $tag_close .'</option>';
		print '</select>';
		print '</div>';

		$country_zip_codes = $country->getZipCodes();
		if($country !== FALSE && count($country_zip_codes) > 0) {
			$show_zip_code_field = FALSE;
			foreach($country_zip_codes as $country_zip_code) {
				if($country_zip_code->isOnline()) {
					$show_zip_code_field = TRUE;
					break;
				}
			}
			if($show_zip_code_field) {
				print '<div class="col-12 col-md-6">';
				$placeholder = $zip_code === FALSE ? $tag_open .'d2u_address_zip_code'. $tag_close : rex_request('zip_code', 'int');
				print '<input type="text" value="'. ($zip_code !== FALSE ? rex_request('zip_code', 'int') : '') .'" name="zip_code" placeholder="'. $placeholder .'">';
				print '<input type="submit" value="»" class="zip_code">';
				print '</div>';
			}
		}

		print '</div>';
		print '</form>';
		print '</div>';
		print '</div>';
		print '<br />';
	} // END if country selection should be available

	if(count($addresses) > 0) {
		if($address_type->show_country_select == "yes") {
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
				($address->picture != "" ? 'index.php?rex_media_type=d2u_address_120x150&rex_media_file='. $address->picture : \rex_addon::get('d2u_address')->getAssetsUrl("noavatar.jpg"))
				.'" alt="'. $address->company . $address->contact_name .'">'. $a_href_close;
			print '</div>';
			print '<div class="col-9">';
			if($address->contact_name != "") {
				print $a_href_open .'<h3>'. $address->contact_name .'</h3>'. $a_href_close;
				print $address->company .'<br>';
			}
			else {
				print $a_href_open .'<h3>'. $address->company .'</h3>'. $a_href_close;
			}
			if($address->company_appendix != "") {
				print $address->company_appendix .'<br>';
			}
			if($address_type->show_address_details == "yes") {
				print $address->street .'<br>';
				print $address->zip_code .' '. $address->city .'<br>';
			}
			print '<br />';
			if($address->phone != "") {
				print $tag_open .'d2u_address_phone'. $tag_close .' '. $address->phone .'<br>';
			}
			if($show_fax && $address->fax != "") {
				print $tag_open .'d2u_address_fax'. $tag_close .' '. $address->fax .'<br>';
			}
			
			// Google Analytics Event
			$google_analytics = "";
			if(rex_config::get('d2u_address', 'analytics_emailevent_activate', 'false') == 'true' &&
					rex_config::get('d2u_address', 'analytics_emailevent_category', '') !== '' &&
					rex_config::get('d2u_address', 'analytics_emailevent_action', '') !== '' &&
					rex_request('search_it_build_index', 'int', FALSE) === FALSE) {
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
		$d2u_helper = rex_addon::get("d2u_helper");
		$api_key = "";
		if($d2u_helper->getConfig("maps_key", "") != "" ) {
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
					if($address->contact_name != "") {
						$infotext .= $address->contact_name .'<br />';			
					}
					$infotext .= $address->country->name .' - '. $address->zip_code .' '. $address->city.'"';
					print $infotext .", ";
					// Latitude and Longitude
					if($address->latitude != 0 && $address->longitude != 0) {
						print $address->latitude .', '. $address->longitude;
					}
					else {
						print '0, 0';
					}
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
	</div>
<?php
	}
?>