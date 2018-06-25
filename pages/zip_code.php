<?php
$func = rex_request('func', 'string');
$entry_id = rex_request('entry_id', 'int');
$message = rex_get('message', 'string');

// messages
if($message != "") {
	print rex_view::success(rex_i18n::msg($message));
}

// save settings
if (filter_input(INPUT_POST, "btn_save") == 1 || filter_input(INPUT_POST, "btn_apply") == 1) {
	$form = (array) rex_post('form', 'array', []);

	$zipcode = new D2U_Address\ZipCode($form['zipcode_id'], rex_config::get('d2u_helper', 'default_lang'));
	$zipcode->range_from = $form['range_from'];
	$zipcode->range_to = $form['range_to'];
	$zipcode->country = new D2U_Address\Country($form['country_id'], rex_config::get('d2u_helper', 'default_lang'));
	$zipcode->address_ids = isset($form['address_ids']) ? $form['address_ids'] : [];

	// message output
	$message = 'form_save_error';
	if($zipcode->save() == 0) {
		$message = 'form_saved';
	}
	
	// Redirect to make reload and thus double save impossible
	if(filter_input(INPUT_POST, "btn_apply") == 1 && $zipcode !== FALSE) {
		header("Location: ". rex_url::currentBackendPage(array("entry_id"=>$zipcode->zipcode_id, "func"=>'edit', "message"=>$message), FALSE));
	}
	else {
		header("Location: ". rex_url::currentBackendPage(array("message"=>$message), FALSE));
	}
	exit;
}
// Delete
else if(filter_input(INPUT_POST, "btn_delete") == 1 || $func == 'delete') {
	$zipcode_id = $entry_id;
	if($zipcode_id == 0) {
		$form = (array) rex_post('form', 'array', []);
		$zipcode_id = $form['zipcode_id'];
	}
	$zipcode = new D2U_Address\ZipCode($zipcode_id, rex_config::get("d2u_helper", "default_lang"));
	$zipcode->delete();
	
	$func = '';
}

// Eingabeformular
if ($func == 'edit' || $func == 'clone' || $func == 'add') {
?>
	<form action="<?php print rex_url::currentBackendPage(); ?>" method="post">
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?php print rex_i18n::msg('d2u_address_zip_codes'); ?></div></header>
			<div class="panel-body">
				<input type="hidden" name="form[zipcode_id]" value="<?php echo ($func == 'edit' ? $entry_id : 0); ?>">
				<fieldset>
					<legend><?php echo rex_i18n::msg('d2u_address_zip_codes'); ?></legend>
					<div class="panel-body-wrapper slide">
						<?php
							// Do not use last object from translations, because you don't know if it exists in DB
							$zipcode = new D2U_Address\ZipCode($entry_id, rex_config::get("d2u_helper", "default_lang"));
							$readonly = TRUE;
							if(\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]')) {
								$readonly = FALSE;
							}

							d2u_addon_backend_helper::form_input('d2u_address_range_from', "form[range_from]", $zipcode->range_from, TRUE, $readonly, "number");
							d2u_addon_backend_helper::form_input('d2u_address_range_to', "form[range_to]", $zipcode->range_to, TRUE, $readonly, "number");
							$countries = D2U_Address\Country::getAll(rex_config::get('d2u_helper', 'default_lang'));
							$options_countries = [];
							foreach ($countries as $country) {
								$options_countries[$country->country_id] = $country->name;
							}
							d2u_addon_backend_helper::form_select('d2u_address_country', 'form[country_id]', $options_countries, [$zipcode->country->country_id], 1, FALSE, $readonly);
							$options_address_ids = [];
							$addresses = D2U_Address\Address::getAll(rex_config::get('d2u_helper', 'default_lang'), FALSE, FALSE);
							foreach ($addresses as $address) {
								$options_address_ids[$address->address_id] = $address->company . ($address->contact_name != '' ? ' ('. trim($address->contact_name) .')' : '');
							}
							d2u_addon_backend_helper::form_select('d2u_address_address', 'form[address_ids][]', $options_address_ids, $zipcode->address_ids, 15, TRUE, $readonly);
						?>
					</div>
				</fieldset>
			</div>
			<footer class="panel-footer">
				<div class="rex-form-panel-footer">
					<div class="btn-toolbar">
						<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="1"><?php echo rex_i18n::msg('form_save'); ?></button>
						<button class="btn btn-apply" type="submit" name="btn_apply" value="1"><?php echo rex_i18n::msg('form_apply'); ?></button>
						<button class="btn btn-abort" type="submit" name="btn_abort" formnovalidate="formnovalidate" value="1"><?php echo rex_i18n::msg('form_abort'); ?></button>
						<?php
							if(\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]')) {
								print '<button class="btn btn-delete" type="submit" name="btn_delete" formnovalidate="formnovalidate" data-confirm="'. rex_i18n::msg('form_delete') .'?" value="1">'. rex_i18n::msg('form_delete') .'</button>';
							}
						?>
					</div>
				</div>
			</footer>
		</div>
	</form>
	<br>
	<script type='text/javascript'>
		jQuery(document).ready(function($) {
			$('legend').each(function() {
				$(this).addClass('open');
				$(this).next('.panel-body-wrapper.slide').slideToggle();
			});
		});
	</script>
	<?php
		print d2u_addon_backend_helper::getCSS();
//		print d2u_addon_backend_helper::getJS();
}

if ($func == '') {
	$query = 'SELECT zipcode_id, range_from, range_to, name '
		. 'FROM '. \rex::getTablePrefix() .'d2u_address_zipcodes AS zipcodes '
		. 'LEFT JOIN '. \rex::getTablePrefix() .'d2u_address_countries_lang AS country '
			. 'ON zipcodes.country_id = country.country_id AND country.clang_id = '. rex_config::get("d2u_helper", "default_lang") .' '
		. 'ORDER BY name, range_from ASC';
    $list = rex_list::factory($query);

    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon fa-flag"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . rex_i18n::msg('add') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'entry_id' => '###zipcode_id###']);

    $list->setColumnLabel('zipcode_id', rex_i18n::msg('id'));
    $list->setColumnLayout('zipcode_id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);

    $list->setColumnLabel('range_from', rex_i18n::msg('d2u_address_range_from'));
    $list->setColumnParams('range_from', ['func' => 'edit', 'entry_id' => '###zipcode_id###']);

    $list->setColumnLabel('range_to', rex_i18n::msg('d2u_address_range_to'));
    $list->setColumnParams('range_to', ['func' => 'edit', 'entry_id' => '###zipcode_id###']);

	$list->setColumnLabel('name', rex_i18n::msg('d2u_address_country'));

	$list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###zipcode_id###']);

	if(\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]')) {
		$list->addColumn(rex_i18n::msg('d2u_helper_clone'), '<i class="rex-icon fa-copy"></i> ' . rex_i18n::msg('d2u_helper_clone'));
		$list->setColumnLayout(rex_i18n::msg('d2u_helper_clone'), ['', '<td class="rex-table-action">###VALUE###</td>']);
		$list->setColumnParams(rex_i18n::msg('d2u_helper_clone'), ['func' => 'clone', 'entry_id' => '###zipcode_id###']);

		$list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
		$list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
		$list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###zipcode_id###']);
		$list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('d2u_helper_confirm_delete'));
	}

	$list->setNoRowsMessage(rex_i18n::msg('d2u_address_zip_codes_no_zipcodes_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('d2u_address_zip_codes'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}