<?php
// Get placeholder wildcard tags and other presets
$sprog = rex_addon::get("sprog");
$tag_open = $sprog->getConfig('wildcard_open_tag');
$tag_close = $sprog->getConfig('wildcard_close_tag');
	
$address_type_id = "REX_VALUE[1]";
$address_type = new D2U_Address\AddressType($address_type_id);
$adress_id = "REX_VALUE[2]";
$address = new D2U_Address\Address($adress_id);
?>
<div class="col-12 col-lg-4 columns">
	<h1><?php print $tag_open .'d2u_address_contact'. $tag_close; ?></h1>
	<div class="box-grey">
		<?php
			if($address_type->article_id > 0) {
		?>
		<h4><?php print $tag_open .'d2u_address_phone'. $tag_close; ?></h4>
		<form method="post" action="<?php print rex_getUrl($address_type->article_id); ?>">
			<select name="land_id" class="white darkerHover select-margin" onChange="this.form.submit()">
				<option selected="" disabled=""><?php print $tag_open .'d2u_address_failure_country'. $tag_close; ?></option>
				<?php
					$countries = $address_type->getCountries();
					foreach($countries as $country) {
						print '<option value="'. $country->country_id .'">'. $country->name .'</option>';
					}
				?>
			</select>
		</form>
		<?php
			}
			if($address->adress_id > 0) {
				print '<br><br>';
				print '<h4>'. $tag_open .'d2u_address_email'. $tag_close .'</h4>';
				// Google Analytics Event
				$google_analytics = "";
				if(rex_config::get('d2u_address', 'analytics_emailevent_activate', 'false') == 'true' &&
						rex_config::get('d2u_address', 'analytics_emailevent_category', '') !== '' &&
						rex_config::get('d2u_address', 'analytics_emailevent_action', '') !== '' &&
						rex_request('search_it_build_index', 'int', FALSE) === FALSE) {
					$google_analytics = " onClick=\"ga('send', 'event', '". rex_config::get('d2u_address', 'analytics_emailevent_category') ."', '". rex_config::get('d2u_address', 'analytics_emailevent_action') ."', '". $address->email ."');\"";
				}
				print '<a href="mailto:'. $address->email .'"'. $google_analytics .'>'. $address->email .'</a>';
			}
		?>
	</div>
</div>