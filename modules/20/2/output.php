<?php
$cols_sm = 0 === (int) 'REX_VALUE[20]' ? 12 : (int) 'REX_VALUE[20]'; /** @phpstan-ignore-line */
$cols_md = 0 === (int) 'REX_VALUE[19]' ? 4 : (int) 'REX_VALUE[19]'; /** @phpstan-ignore-line */
$cols_lg = 0 === (int) 'REX_VALUE[18]' ? 4 : (int) 'REX_VALUE[18]'; /** @phpstan-ignore-line */
$offset_lg = (int) 'REX_VALUE[17]' > 0 ? ' mr-lg-auto ml-lg-auto ' : ''; /** @phpstan-ignore-line */

// Get placeholder wildcard tags and other presets
$sprog = rex_addon::get('sprog');
$tag_open = $sprog->getConfig('wildcard_open_tag');
$tag_close = $sprog->getConfig('wildcard_close_tag');

$adress_id = (int) 'REX_VALUE[2]';
$address = new FriendsOfREDAXO\D2UAddress\Address($adress_id, rex_clang::getCurrentId());

echo '<div class="col-12 col-sm-'. $cols_sm .' col-md-'. $cols_md .' col-lg-'. $cols_lg . $offset_lg .'" abstand>';
?>
	<h3><?= $tag_open .'d2u_address_contact'. $tag_close ?></h3>
	<div class="mod-20-2-box-grey">
		<?php
            if ($address->address_id > 0) {
                echo '<p><b>'. $address->contact_name .'</b></p>';
                // Google Analytics Event
                $google_analytics = '';
                if ('true' === (string) rex_config::get('d2u_address', 'analytics_emailevent_activate', 'false') &&
                        '' !== rex_config::get('d2u_address', 'analytics_emailevent_category', '') &&
                        '' !== rex_config::get('d2u_address', 'analytics_emailevent_action', '') &&
                        false === rex_request('search_it_build_index', 'int', false)) {
                    $google_analytics = " onClick=\"ga('send', 'event', '". rex_config::get('d2u_address', 'analytics_emailevent_category') ."', '". rex_config::get('d2u_address', 'analytics_emailevent_action') ."', '". $address->email ."');\"";
                }
                echo '<p><a href="mailto:'. $address->email .'"'. $google_analytics .'>'. $address->email .'</a></p>';
            }
        ?>
	</div>
</div>