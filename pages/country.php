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

    $success = true;
    $country = false;
    $country_id = $form['country_id'];
    foreach (rex_clang::getAll() as $rex_clang) {
        if (false === $country) {
            $country = new FriendsOfRedaxo\D2UAddress\Country($country_id, $rex_clang->getId());
            $country->country_id = $country_id; // Ensure correct ID in case first language has no object
            $country->iso_lang_codes = array_map('trim', explode(',', $form['iso_lang_codes']));
            $country->maps_zoom = $form['maps_zoom'];
            $country->address_ids = $form['address_ids'] ?? [];
        } else {
            $country->clang_id = $rex_clang->getId();
        }
        $country->name = $form['lang'][$rex_clang->getId()]['name'];
        $country->translation_needs_update = $form['lang'][$rex_clang->getId()]['translation_needs_update'];

        if ('delete' === $country->translation_needs_update) {
            $country->delete(false);
        } elseif ($country->save() > 0) {
            $success = false;
        } else {
            // remember id, for each database lang object needs same id
            $country_id = $country->country_id;
        }
    }

    // message output
    $message = 'form_save_error';
    if ($success) {
        $message = 'form_saved';
    }

    // Redirect to make reload and thus double save impossible
    if (1 === (int) filter_input(INPUT_POST, 'btn_apply', FILTER_VALIDATE_INT) && false !== $country) {
        header('Location: '. rex_url::currentBackendPage(['entry_id' => $country->country_id, 'func' => 'edit', 'message' => $message], false));
    } else {
        header('Location: '. rex_url::currentBackendPage(['message' => $message], false));
    }
    exit;
}
// Delete
if (1 === (int) filter_input(INPUT_POST, 'btn_delete', FILTER_VALIDATE_INT) || 'delete' === $func) {
    $country_id = $entry_id;
    if (0 === $country_id) {
        $form = rex_post('form', 'array', []);
        $country_id = $form['country_id'];
    }
    $country = new FriendsOfRedaxo\D2UAddress\Country($country_id, (int) rex_config::get('d2u_helper', 'default_lang'));
    $country->country_id = $country_id; // Ensure correct ID in case language has no object

    // Check if object is used
    $reffering_addresses = $country->getAddresses(false, false);

    // If not used, delete
    if (0 === count($reffering_addresses) && (int) rex_config::get('d2u_address', 'default_country_id') !== $country->country_id) {
        $country->delete(true);
    } else {
        $message = '<ul>';
        foreach ($reffering_addresses as $reffering_address) {
            $message .= '<li><a href="index.php?page=d2u_address/address&func=edit&entry_id='. $reffering_address->address_id .'">'. $reffering_address->company . ('' !== $reffering_address->contact_name ? ' - '. $reffering_address->contact_name : '') .'</a></li>';
        }
        if ((int) rex_config::get('d2u_address', 'default_country_id') === $country->country_id) {
            $message .= '<li><a href="index.php?page=d2u_address/settings">'. rex_i18n::msg('d2u_helper_settings') .'</a></li>';
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
			<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('d2u_address_country') ?></div></header>
			<div class="panel-body">
				<input type="hidden" name="form[country_id]" value="<?= $entry_id ?>">
				<?php
                    foreach (rex_clang::getAll() as $rex_clang) {
                        $country = new FriendsOfRedaxo\D2UAddress\Country($entry_id, $rex_clang->getId());
                        $required = $rex_clang->getId() === (int) (rex_config::get('d2u_helper', 'default_lang')) ? true : false;

                        $readonly_lang = true;
                        if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || (\rex::getUser()->hasPerm('d2u_address[edit_lang]') && \rex::getUser()->getComplexPerm('clang') instanceof rex_clang_perm && \rex::getUser()->getComplexPerm('clang')->hasPerm($rex_clang->getId())))) {
                            $readonly_lang = false;
                        }
                ?>
					<fieldset>
						<legend><?= rex_i18n::msg('d2u_helper_text_lang') .' "'. $rex_clang->getName() .'"' ?></legend>
						<div class="panel-body-wrapper slide">
							<?php
                                if ($rex_clang->getId() !== (int) rex_config::get('d2u_helper', 'default_lang')) {
                                    $options_translations = [];
                                    $options_translations['yes'] = rex_i18n::msg('d2u_helper_translation_needs_update');
                                    $options_translations['no'] = rex_i18n::msg('d2u_helper_translation_is_uptodate');
                                    $options_translations['delete'] = rex_i18n::msg('d2u_helper_translation_delete');
                                    \TobiasKrais\D2UHelper\BackendHelper::form_select('d2u_helper_translation', 'form[lang]['. $rex_clang->getId() .'][translation_needs_update]', $options_translations, [$country->translation_needs_update], 1, false, $readonly_lang);
                                } else {
                                    echo '<input type="hidden" name="form[lang]['. $rex_clang->getId() .'][translation_needs_update]" value="">';
                                }
                            ?>
							<script>
								// Hide on document load
								$(document).ready(function() {
									toggleClangDetailsView(<?= $rex_clang->getId() ?>);
								});

								// Hide on selection change
								$("select[name='form[lang][<?= $rex_clang->getId() ?>][translation_needs_update]']").on('change', function(e) {
									toggleClangDetailsView(<?= $rex_clang->getId() ?>);
								});
							</script>
							<div id="details_clang_<?= $rex_clang->getId() ?>">
								<?php
                                    \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_helper_name', 'form[lang]['. $rex_clang->getId() .'][name]', $country->name, $required, $readonly_lang, 'text');
                                ?>
							</div>
						</div>
					</fieldset>
				<?php
                    }
                ?>
				<fieldset>
					<legend><?= rex_i18n::msg('d2u_helper_data_all_lang') ?></legend>
					<div class="panel-body-wrapper slide">
						<?php
                            // Do not use last object from translations, because you don't know if it exists in DB
                            $country = new FriendsOfRedaxo\D2UAddress\Country($entry_id, (int) rex_config::get('d2u_helper', 'default_lang'));
                            $readonly = true;
                            if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
                                $readonly = false;
                            }

                            \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_address_iso_lang_codes_comma', 'form[iso_lang_codes]', implode(',', $country->iso_lang_codes), false, $readonly, 'text');
                            \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_address_maps_zoom', 'form[maps_zoom]', $country->maps_zoom, false, $readonly, 'number');
                            \TobiasKrais\D2UHelper\BackendHelper::form_infotext('d2u_address_hint_address_select', 'hint_address_select');
                            $options_address_ids = [];
                            $addresses = FriendsOfRedaxo\D2UAddress\Address::getAll((int) rex_config::get('d2u_helper', 'default_lang'), false, false);
                            foreach ($addresses as $address) {
                                $options_address_ids[$address->address_id] = $address->company . ('' !== $address->contact_name ? ' ('. trim($address->contact_name) .')' : '');
                            }
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('d2u_address_address', 'form[address_ids][]', $options_address_ids, $country->address_ids, 15, true, $readonly);
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
	<?php
        echo \TobiasKrais\D2UHelper\BackendHelper::getCSS();
        echo \TobiasKrais\D2UHelper\BackendHelper::getJS();
}

if ('' === $func) {
    $query = 'SELECT countries.country_id, name, iso_lang_codes '
        . 'FROM '. \rex::getTablePrefix() .'d2u_address_countries AS countries '
        . 'LEFT JOIN '. \rex::getTablePrefix() .'d2u_address_countries_lang AS lang '
            . 'ON countries.country_id = lang.country_id AND lang.clang_id = '. (int) rex_config::get('d2u_helper', 'default_lang') .' '
        . 'ORDER BY name ASC';
    $list = rex_list::factory($query, 1000);

    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon fa-flag"></i>';
    $thIcon = '';
    if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
        $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . rex_i18n::msg('add') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    }
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'entry_id' => '###country_id###']);

    $list->setColumnLabel('country_id', rex_i18n::msg('id'));
    $list->setColumnLayout('country_id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('d2u_helper_name'));
    $list->setColumnParams('name', ['func' => 'edit', 'entry_id' => '###country_id###']);

    $list->setColumnLabel('iso_lang_codes', rex_i18n::msg('d2u_address_iso_lang_codes'));

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###country_id###']);

    if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
        $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
        $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
        $list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###country_id###']);
        $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('d2u_helper_confirm_delete'));
    }

    $list->setNoRowsMessage(rex_i18n::msg('d2u_address_countries_no_countries_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('d2u_address_countries'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}
