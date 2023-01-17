<?php
$cols_sm = intval("REX_VALUE[20]") === 0 ? 12 : intval("REX_VALUE[20]"); /** @phpstan-ignore-line */
$cols_md = intval("REX_VALUE[19]") === 0 ? 4 : intval("REX_VALUE[19]"); /** @phpstan-ignore-line */
$cols_lg = intval("REX_VALUE[18]") === 0 ? 4 : intval("REX_VALUE[18]"); /** @phpstan-ignore-line */
$offset_lg = intval("REX_VALUE[17]") > 0 ? " mr-lg-auto ml-lg-auto " : ""; /** @phpstan-ignore-line */

// Get placeholder wildcard tags and other presets
$sprog = rex_addon::get("sprog");
$tag_open = $sprog->getConfig('wildcard_open_tag');
$tag_close = $sprog->getConfig('wildcard_close_tag');

$adress_id = intval("REX_VALUE[2]");
$address = new D2U_Address\Address($adress_id, rex_clang::getCurrentId());

print '<div class="col-12 col-sm-'. $cols_sm .' col-md-'. $cols_md .' col-lg-'. $cols_lg . $offset_lg .'" abstand>';
?>
	<h3><?php print $tag_open .'d2u_address_contact'. $tag_close; ?></h3>
	<div class="mod-20-2-box-grey">
		<?php
			if($address->address_id > 0) {
				print '<p><b>'. $address->contact_name .'</b></p>';
				// Google Analytics Event
				$google_analytics = "";
				if(strval(rex_config::get('d2u_address', 'analytics_emailevent_activate', 'false')) === 'true' &&
						rex_config::get('d2u_address', 'analytics_emailevent_category', '') !== '' &&
						rex_config::get('d2u_address', 'analytics_emailevent_action', '') !== '' &&
						rex_request('search_it_build_index', 'int', false) === false) {
					$google_analytics = " onClick=\"ga('send', 'event', '". rex_config::get('d2u_address', 'analytics_emailevent_category') ."', '". rex_config::get('d2u_address', 'analytics_emailevent_action') ."', '". $address->email ."');\"";
				}
				print '<p><a href="mailto:'. $address->email .'"'. $google_analytics .'>'. $address->email .'</a></p>';
			}
		?>
	</div>
</div>