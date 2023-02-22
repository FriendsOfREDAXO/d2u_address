<?php
$func = rex_request('func', 'string');
$entry_id = rex_request('entry_id', 'int');
$message = rex_get('message', 'string');

// Print comments
if ('' !== $message) {
    echo rex_view::success(rex_i18n::msg($message));
}

// save settings
if (1 === (int) filter_input(INPUT_POST, 'btn_save') || 1 === (int) filter_input(INPUT_POST, 'btn_apply')) {
    $form = rex_post('form', 'array', []);

    // Linkmap Link and media needs special treatment
    $link_ids = filter_input_array(INPUT_POST, ['REX_INPUT_LINK' => ['filter' => FILTER_VALIDATE_INT, 'flags' => FILTER_REQUIRE_ARRAY]]);
    $input_media = rex_post('REX_INPUT_MEDIA', 'array', []);

    $address = new D2U_Address\Address($form['address_id'], (int) rex_config::get('d2u_helper', 'default_lang'));
    $address->company = $form['company'];
    $address->company_appendix = $form['company_appendix'];
    $address->contact_name = $form['contact_name'];
    $address->street = $form['street'];
    $address->additional_address = $form['additional_address'];
    $address->zip_code = $form['zip_code'];
    $address->city = $form['city'];
    $address->country = new D2U_Address\Country($form['country_id'], (int) rex_config::get('d2u_helper', 'default_lang'));
    $address->latitude = $form['latitude'];
    $address->longitude = $form['longitude'];
    $address->email = $form['email'];
    $address->url = $form['url'];
    $address->phone = $form['phone'];
    $address->mobile = $form['mobile'];
    $address->fax = $form['fax'];
    $address->picture = $input_media[1];
    $address->address_type_ids = $form['address_type_ids'] ?? [];
    $address->article_id = !is_array($link_ids) ? 0 : $link_ids['REX_INPUT_LINK'][1];
    $address->priority = $form['priority'];
    $address->online_status = array_key_exists('online_status', $form) ? 'online' : 'offline';
    $address->country_ids = $form['country_ids'] ?? [];

    // message output
    $message = 'form_save_error';
    if (false === $address->save()) {
        $message = 'form_saved';
    }

    // Redirect to make reload and thus double save impossible
    if (1 === (int) filter_input(INPUT_POST, 'btn_apply', FILTER_VALIDATE_INT) && $address->address_id > 0) {
        header('Location: '. rex_url::currentBackendPage(['entry_id' => $address->address_id, 'func' => 'edit', 'message' => $message], false));
    } else {
        header('Location: '. rex_url::currentBackendPage(['message' => $message], false));
    }
    exit;
}
// Delete
if (1 === (int) filter_input(INPUT_POST, 'btn_delete', FILTER_VALIDATE_INT) || 'delete' === $func) {
    $address_id = $entry_id;
    if (0 === $address_id) {
        $form = rex_post('form', 'array', []);
        $address_id = $form['address_id'];
    }
    $address = new D2U_Address\Address($address_id, (int) rex_config::get('d2u_helper', 'default_lang'));

    // Check if object is used
    $address_types = $address->getReferringAddressTypes();
    $countries = $address->getReferringCountries();
    $zip_codes = $address->getReferringZipCodes();

    // If not used, delete
    if (0 === count($address_types) && 0 === count($countries) && 0 === count($zip_codes)) {
        $address->delete();
    } else {
        $message = '<ul>';
        foreach ($address_types as $address_type) {
            $message .= '<li><a href="index.php?page=d2u_address/address_type&func=edit&entry_id='. $address_type->address_type_id .'">'. $address_type->name .'</a></li>';
        }
        foreach ($countries as $country) {
            $message .= '<li><a href="index.php?page=d2u_address/country&func=edit&entry_id='. $country->country_id .'">'. $country->name .'</a></li>';
        }
        foreach ($zip_codes as $zip_code) {
            $message .= '<li><a href="index.php?page=d2u_address/zip_code&func=edit&entry_id='. $zip_code->zipcode_id .'">'. $zip_code->range_from .' - '. $zip_code->range_to .'</a></li>';
        }
        $message .= '</ul>';

        echo rex_view::error(rex_i18n::msg('d2u_helper_could_not_delete') . $message);
    }

    $func = '';
}
// Change online status of machine
elseif ('changestatus' === $func) {
    $address = new D2U_Address\Address($entry_id, (int) rex_config::get('d2u_helper', 'default_lang'));
    $address->changeStatus();

    header('Location: '. rex_url::currentBackendPage());
    exit;
}

// Eingabeformular
if ('edit' === $func || 'clone' === $func || 'add' === $func) {
?>
	<form action="<?= rex_url::currentBackendPage() ?>" method="post">
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('d2u_address_address') ?></div></header>
			<div class="panel-body">
				<input type="hidden" name="form[address_id]" value="<?= 'edit' === $func ? $entry_id : 0 ?>">
				<fieldset>
					<legend><?= rex_i18n::msg('d2u_address_address_type') ?></legend>
					<div class="panel-body-wrapper slide">
						<?php
                            $address = new D2U_Address\Address($entry_id, (int) rex_config::get('d2u_helper', 'default_lang'));
                            $readonly = true;
                            if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
                                $readonly = false;
                            }

                            d2u_addon_backend_helper::form_input('d2u_address_company', 'form[company]', $address->company, true, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_address_company_appendix', 'form[company_appendix]', $address->company_appendix, false, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_address_contact_name', 'form[contact_name]', $address->contact_name, false, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_address_street', 'form[street]', $address->street, true, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_address_additional_address', 'form[additional_address]', $address->additional_address, false, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_address_zip_codes', 'form[zip_code]', $address->zip_code, false, $readonly, 'number');
                            d2u_addon_backend_helper::form_input('d2u_address_city', 'form[city]', $address->city, true, $readonly);
                            $countries = D2U_Address\Country::getAll((int) rex_config::get('d2u_helper', 'default_lang'));
                            $options_countries = [];
                            foreach ($countries as $country) {
                                $options_countries[$country->country_id] = $country->name;
                            }
                            d2u_addon_backend_helper::form_select('d2u_address_country', 'form[country_id]', $options_countries, [$address->country instanceof D2U_Address\Country ? $address->country->country_id : ''], 1, false, $readonly);

                            $d2u_helper = rex_addon::get('d2u_helper');
                            $api_key = '';
                            if ($d2u_helper->hasConfig('maps_key')) {
                                $api_key = '?key='. $d2u_helper->getConfig('maps_key');

                        ?>
								<script src="https://maps.googleapis.com/maps/api/js<?= $api_key ?>"></script>
								<script>
									function geocode() {
										if($("input[name='form[street]']").val() === "" || $("input[name='form[city]']").val() === "") {
											alert("<?= rex_i18n::msg('d2u_helper_geocode_fields') ?>");
											return;
										}

										// Geocode
										var geocoder = new google.maps.Geocoder();
										geocoder.geocode({'address': $("input[name='form[street]']").val() + ", " + $("input[name='form[zip_code]']").val() + " " + $("input[name='form[city]']").val()}, function(results, status) {
											if (status === google.maps.GeocoderStatus.OK) {
												$("input[name='form[latitude]']").val(results[0].geometry.location.lat);
												$("input[name='form[longitude]']").val(results[0].geometry.location.lng);
												// Show check geolocation button and set link to button
												$("#check_geocode").attr('href', "https://maps.google.com/?q=" + $("input[name='form[latitude]']").val() + "," + $("input[name='form[longitude]']").val() + "&z=17");
												$("#check_geocode").parent().show();
											}
											else {
												alert("<?= rex_i18n::msg('d2u_helper_geocode_failure') ?>");
											}
										});
									}
								</script>
						<?php
                                echo '<dl class="rex-form-group form-group" id="geocode">';
                                echo '<dt><label></label></dt>';
                                echo '<dd><input type="submit" value="'. rex_i18n::msg('d2u_helper_geocode') .'" onclick="geocode(); return false;" class="btn btn-save">'
                                    . ' <div class="btn btn-abort"><a href="https://maps.google.com/?q='. $address->latitude .','. $address->longitude .'&z=17" id="check_geocode" target="_blank">'. rex_i18n::msg('d2u_helper_geocode_check') .'</a></div>'
                                    . '</dd>';
                                echo '</dl>';
                                if (0.0 === $address->latitude && 0.0 === $address->longitude) {
                                    echo '<script>jQuery(document).ready(function($) { $("#check_geocode").parent().hide(); });</script>';
                                }
                            }
                            d2u_addon_backend_helper::form_infotext('d2u_helper_geocode_hint', 'hint_geocoding');
                            d2u_addon_backend_helper::form_input('d2u_address_latitude', 'form[latitude]', (string) $address->latitude, true, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_address_longitude', 'form[longitude]', (string) $address->longitude, true, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_address_email', 'form[email]', $address->email, false, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_address_url', 'form[url]', $address->url, false, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_address_phone', 'form[phone]', $address->phone, false, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_address_mobile', 'form[mobile]', $address->mobile, false, $readonly);
                            d2u_addon_backend_helper::form_input('d2u_address_fax', 'form[fax]', $address->fax, false, $readonly);
                            d2u_addon_backend_helper::form_mediafield('d2u_helper_picture', '1', $address->picture, $readonly);
                            $adress_types = D2U_Address\AddressType::getAll((int) rex_config::get('d2u_helper', 'default_lang'));
                            $options_address_types = [];
                            foreach ($adress_types as $adress_type) {
                                $options_address_types[$adress_type->address_type_id] = $adress_type->name;
                            }
                            d2u_addon_backend_helper::form_select('d2u_address_address_types', 'form[address_type_ids][]', $options_address_types, $address->address_type_ids, 4, true, $readonly);
                            d2u_addon_backend_helper::form_linkfield('d2u_helper_article_id', '1', $address->article_id, (int) rex_config::get('d2u_helper', 'default_lang'));
                            d2u_addon_backend_helper::form_input('d2u_address_priority', 'form[priority]', $address->priority, true, $readonly, 'number');
                            $options_status = ['online' => rex_i18n::msg('clang_online'),
                                'offline' => rex_i18n::msg('clang_offline')];
                            d2u_addon_backend_helper::form_checkbox('d2u_helper_online_status', 'form[online_status]', 'online', 'online' === $address->online_status, $readonly);
                            d2u_addon_backend_helper::form_select('d2u_address_countries_assigned', 'form[country_ids][]', $options_countries, $address->country_ids, 15, true, $readonly);
                        ?>
					</div>
				</fieldset>
			</div>
			<footer class="panel-footer">
				<div class="rex-form-panel-footer">
					<div class="btn-toolbar">
						<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="1"><?= rex_i18n::msg('form_save') ?></button>
						<button class="btn btn-apply" type="submit" name="btn_apply" value="1"><?= rex_i18n::msg('form_apply') ?></button>
						<button class="btn btn-abort" type="submit" name="btn_abort" formnovalidate="formnovalidate" value="1"><?= rex_i18n::msg('form_abort') ?></button>
						<?php
                            if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
                                echo '<button class="btn btn-delete" type="submit" name="btn_delete" formnovalidate="formnovalidate" data-confirm="'. rex_i18n::msg('form_delete') .'?" value="1">'. rex_i18n::msg('form_delete') .'</button>';
                            }
                        ?>
					</div>
				</div>
			</footer>
		</div>
	</form>
	<br>
	<script>
		jQuery(document).ready(function($) {
			$('legend').each(function() {
				$(this).addClass('open');
				$(this).next('.panel-body-wrapper.slide').slideToggle();
			});
		});
	</script>
	<?php
        echo d2u_addon_backend_helper::getCSS();
//		print d2u_addon_backend_helper::getJS();
}

if ('' === $func) {
    $query = 'SELECT address_id, company, contact_name, city, address_type_ids, priority, online_status '
        . 'FROM '. \rex::getTablePrefix() .'d2u_address_address '
        . 'ORDER BY priority';
    $list = rex_list::factory($query, 1000);

    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon fa-address-card"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . rex_i18n::msg('add') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'entry_id' => '###address_id###']);

    $list->setColumnLabel('address_id', rex_i18n::msg('id'));
    $list->setColumnLayout('address_id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);

    $list->setColumnLabel('company', rex_i18n::msg('d2u_address_company'));
    $list->setColumnParams('company', ['func' => 'edit', 'entry_id' => '###address_id###']);

    $list->setColumnLabel('contact_name', rex_i18n::msg('d2u_address_contact_name'));

    $list->setColumnLabel('city', rex_i18n::msg('d2u_address_city'));

    $list->setColumnLabel('address_type_ids', rex_i18n::msg('d2u_address_address_types'));
    $list->setColumnFormat('address_type_ids', 'custom', static function ($params) {
        $list_params = $params['list'];
        $address_type_names = [];
        $address_type_ids = preg_grep('/^\s*$/s', explode('|', $list_params->getValue('address_type_ids')), PREG_GREP_INVERT);
        if (is_array($address_type_ids)) {
            foreach ($address_type_ids as $address_type_id) {
                $address_type = new \D2U_Address\AddressType($address_type_id, (int) rex_config::get('d2u_helper', 'default_lang'));
                $address_type_names[] = $address_type->address_type_id > 0 ? $address_type->name : '';
            }
        }
        return implode(', ', $address_type_names);
    });

    $list->setColumnLabel('priority', rex_i18n::msg('header_priority'));

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###address_id###']);

    $list->removeColumn('online_status');
    if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
        $list->addColumn(rex_i18n::msg('status_online'), '<a class="rex-###online_status###" href="' . rex_url::currentBackendPage(['func' => 'changestatus']) . '&entry_id=###address_id###"><i class="rex-icon rex-icon-###online_status###"></i> ###online_status###</a>');
        $list->setColumnLayout(rex_i18n::msg('status_online'), ['', '<td class="rex-table-action">###VALUE###</td>']);

        $list->addColumn(rex_i18n::msg('d2u_helper_clone'), '<i class="rex-icon fa-copy"></i> ' . rex_i18n::msg('d2u_helper_clone'));
        $list->setColumnLayout(rex_i18n::msg('d2u_helper_clone'), ['', '<td class="rex-table-action">###VALUE###</td>']);
        $list->setColumnParams(rex_i18n::msg('d2u_helper_clone'), ['func' => 'clone', 'entry_id' => '###address_id###']);

        $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
        $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
        $list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###address_id###']);
        $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('d2u_helper_confirm_delete'));
    }

    $list->setNoRowsMessage(rex_i18n::msg('d2u_address_no_address_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('d2u_address_address_types'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}
