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

    $address_type = new FriendsOfRedaxo\D2UAddress\AddressType($form['address_type_id'], (int) rex_config::get('d2u_helper', 'default_lang'));
    $address_type->name = $form['name'];
    $address_type->show_address_details = array_key_exists('show_address_details', $form);
    $address_type->show_country_select = array_key_exists('show_country_select', $form);
    $address_type->maps_zoom = $form['maps_zoom'];
    $address_type->default_address_id = (int) $form['default_address_id'];
    $address_type->article_id = is_array($link_ids['REX_INPUT_LINK']) ? $link_ids['REX_INPUT_LINK'][1] : 0;

    // message output
    $message = 'form_save_error';
    if (!$address_type->save()) {
        $message = 'form_saved';
    }

    // Redirect to make reload and thus double save impossible
    if (1 === (int) filter_input(INPUT_POST, 'btn_apply', FILTER_VALIDATE_INT) && $address_type->address_type_id > 0) {
        header('Location: '. rex_url::currentBackendPage(['entry_id' => $address_type->address_type_id, 'func' => 'edit', 'message' => $message], false));
    } else {
        header('Location: '. rex_url::currentBackendPage(['message' => $message], false));
    }
    exit;
}
// Delete
if (1 === (int) filter_input(INPUT_POST, 'btn_delete', FILTER_VALIDATE_INT) || 'delete' === $func) {
    $address_type_id = $entry_id;
    if (0 === $address_type_id) {
        $form = rex_post('form', 'array', []);
        $address_type_id = $form['address_type_id'];
    }
    $address_type = new FriendsOfRedaxo\D2UAddress\AddressType($address_type_id, (int) rex_config::get('d2u_helper', 'default_lang'));

    // Check if category is used
    $uses_addresses = $address_type->getAddresses();

    // If not used, delete
    if (0 === count($uses_addresses)) {
        $address_type->delete();
    } else {
        $message = '<ul>';
        foreach ($uses_addresses as $uses_address) {
            $message .= '<li><a href="index.php?page=d2u_address/address&func=edit&entry_id='. $uses_address->address_id .'">'. $uses_address->company . ('' !== $uses_address->contact_name ? ' ('. $uses_address->contact_name .')' : '') .'</a></li>';
        }
        $message .= '</ul>';

        echo rex_view::error(rex_i18n::msg('d2u_helper_could_not_delete') . $message);
    }

    $func = '';
}

// Eingabeformular
if ('edit' === $func || 'add' === $func) {
?>
	<form action="<?= rex_url::currentBackendPage() ?>" method="post">
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('d2u_address_address_types') ?></div></header>
			<div class="panel-body">
				<input type="hidden" name="form[address_type_id]" value="<?= $entry_id ?>">
				<fieldset>
					<legend><?= rex_i18n::msg('d2u_address_address_type') ?></legend>
					<div class="panel-body-wrapper slide">
						<?php
                            $address_type = new FriendsOfRedaxo\D2UAddress\AddressType($entry_id, (int) rex_config::get('d2u_helper', 'default_lang'));
                            $readonly = true;
                            if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
                                $readonly = false;
                            }

                            \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_helper_name', 'form[name]', $address_type->name, true, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_checkbox('d2u_address_show_address_details', 'form[show_address_details]', 'true', $address_type->show_address_details, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_checkbox('d2u_address_show_country_select', 'form[show_country_select]', 'true', $address_type->show_country_select, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_address_maps_zoom', 'form[maps_zoom]', $address_type->maps_zoom, true, $readonly, 'number');
                            $addresses = FriendsOfRedaxo\D2UAddress\Address::getAll((int) rex_config::get('d2u_helper', 'default_lang'), false, false);
                            $options_address_id = [];
                            foreach ($addresses as $address) {
                                $options_address_id[$address->address_id] = $address->company . ('' !== $address->contact_name ? ' ('. trim($address->contact_name) .')' : '');
                            }
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('d2u_address_default_address', 'form[default_address_id]', $options_address_id, [$address_type->default_address_id], 1, false, $readonly);
                            \TobiasKrais\D2UHelper\BackendHelper::form_linkfield('d2u_helper_article_id', '1', $address_type->article_id, (int) rex_config::get('d2u_helper', 'default_lang'));
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
    $query = 'SELECT address_type_id, name '
        . 'FROM '. \rex::getTablePrefix() .'d2u_address_types '
        . 'ORDER BY name';
    $list = rex_list::factory($query, 1000);

    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon fa-address-book"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . rex_i18n::msg('add') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'entry_id' => '###address_type_id###']);

    $list->setColumnLabel('address_type_id', rex_i18n::msg('id'));
    $list->setColumnLayout('address_type_id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('d2u_helper_name'));
    $list->setColumnParams('name', ['func' => 'edit', 'entry_id' => '###address_type_id###']);

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###address_type_id###']);

    if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
        $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
        $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
        $list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###address_type_id###']);
        $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('d2u_helper_confirm_delete'));
    }

    $list->setNoRowsMessage(rex_i18n::msg('d2u_address_no_address_types_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('d2u_address_address_types'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}
