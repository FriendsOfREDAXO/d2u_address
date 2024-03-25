<?php
$func = rex_request('func', 'string');
$entry_id = rex_request('entry_id', 'int');
$message = rex_get('message', 'string');

// messages
if ('' !== $message) {
    echo rex_view::success(rex_i18n::msg($message));
}

// save settings
if (1 === (int) filter_input(INPUT_POST, 'btn_save') || 1 === (int) filter_input(INPUT_POST, 'btn_apply')) {
    $form = rex_post('form', 'array', []);

    $zipcode = new FriendsOfREDAXO\D2UAddress\ZipCode($form['zipcode_id'], (int) rex_config::get('d2u_helper', 'default_lang'));
    $zipcode->range_from = $form['range_from'];
    $zipcode->range_to = $form['range_to'];
    $zipcode->country = new FriendsOfREDAXO\D2UAddress\Country($form['country_id'], (int) rex_config::get('d2u_helper', 'default_lang'));
    $zipcode->address_ids = $form['address_ids'] ?? [];

    // message output
    $message = 'form_save_error';
    if (!$zipcode->save()) {
        $message = 'form_saved';
    }

    // Redirect to make reload and thus double save impossible
    if (1 === (int) filter_input(INPUT_POST, 'btn_apply', FILTER_VALIDATE_INT) && $zipcode->zipcode_id > 0) {
        header('Location: '. rex_url::currentBackendPage(['entry_id' => $zipcode->zipcode_id, 'func' => 'edit', 'message' => $message], false));
    } else {
        header('Location: '. rex_url::currentBackendPage(['message' => $message], false));
    }
    exit;
}
// Delete
if (1 === (int) filter_input(INPUT_POST, 'btn_delete', FILTER_VALIDATE_INT) || 'delete' === $func) {
    $zipcode_id = $entry_id;
    if (0 === $zipcode_id) {
        $form = rex_post('form', 'array', []);
        $zipcode_id = $form['zipcode_id'];
    }
    $zipcode = new FriendsOfREDAXO\D2UAddress\ZipCode($zipcode_id, (int) rex_config::get('d2u_helper', 'default_lang'));
    $zipcode->delete();

    $func = '';
}

// Eingabeformular
if ('edit' === $func || 'clone' === $func || 'add' === $func) {
?>
	<form action="<?= rex_url::currentBackendPage() ?>" method="post">
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('d2u_address_zip_codes') ?></div></header>
			<div class="panel-body">
				<input type="hidden" name="form[zipcode_id]" value="<?= 'edit' === $func ? $entry_id : 0 ?>">
				<fieldset>
					<legend><?= rex_i18n::msg('d2u_address_zip_codes') ?></legend>
					<div class="panel-body-wrapper slide">
						<?php
                            // Do not use last object from translations, because you don't know if it exists in DB
                            $zipcode = new FriendsOfREDAXO\D2UAddress\ZipCode($entry_id, (int) rex_config::get('d2u_helper', 'default_lang'));
                            $readonly = true;
                            if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
                                $readonly = false;
                            }

                            \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_address_range_from', 'form[range_from]', $zipcode->range_from, true, $readonly, 'number');
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_address_range_to', 'form[range_to]', $zipcode->range_to, true, $readonly, 'number');
                            $countries = FriendsOfREDAXO\D2UAddress\Country::getAll((int) rex_config::get('d2u_helper', 'default_lang'));
                            $options_countries = [];
                            foreach ($countries as $country) {
                                $options_countries[$country->country_id] = $country->name;
                            }
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('d2u_address_country', 'form[country_id]', $options_countries, [$zipcode->country->country_id], 1, false, $readonly);
                            $options_address_ids = [];
                            $addresses = FriendsOfREDAXO\D2UAddress\Address::getAll((int) rex_config::get('d2u_helper', 'default_lang'), false, false);
                            foreach ($addresses as $address) {
                                $options_address_ids[$address->address_id] = $address->company . ('' !== $address->contact_name ? ' ('. trim($address->contact_name) .')' : '');
                            }
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('d2u_address_address', 'form[address_ids][]', $options_address_ids, $zipcode->address_ids, 15, true, $readonly);
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
        echo \TobiasKrais\D2UHelper\BackendHelper::getCSS();
//		print \TobiasKrais\D2UHelper\BackendHelper::getJS();
}

if ('' === $func) {
    $query = 'SELECT zipcode_id, range_from, range_to, name '
        . 'FROM '. \rex::getTablePrefix() .'d2u_address_zipcodes AS zipcodes '
        . 'LEFT JOIN '. \rex::getTablePrefix() .'d2u_address_countries_lang AS country '
            . 'ON zipcodes.country_id = country.country_id AND country.clang_id = '. (int) rex_config::get('d2u_helper', 'default_lang') .' '
        . 'ORDER BY name, range_from ASC';
    $list = rex_list::factory($query, 1000);

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

    if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
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
