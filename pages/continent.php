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
    $continent = false;
    $continent_id = $form['continent_id'];
    foreach (rex_clang::getAll() as $rex_clang) {
        if (false === $continent) {
            $continent = new FriendsOfRedaxo\D2UAddress\Continent($continent_id, $rex_clang->getId());
            $continent->continent_id = $continent_id; // Ensure correct ID in case first language has no object
            $continent->country_ids = $form['country_ids'] ?? [];
        } else {
            $continent->clang_id = $rex_clang->getId();
        }
        $continent->name = $form['lang'][$rex_clang->getId()]['name'];
        $continent->translation_needs_update = $form['lang'][$rex_clang->getId()]['translation_needs_update'];

        if ('delete' === $continent->translation_needs_update) {
            $continent->delete(false);
        } elseif ($continent->save() > 0) {
            $success = false;
        } else {
            // remember id, for each database lang object needs same id
            $continent_id = $continent->continent_id;
        }
    }

    // message output
    $message = 'form_save_error';
    if ($success) {
        $message = 'form_saved';
    }

    // Redirect to make reload and thus double save impossible
    if (1 === (int) filter_input(INPUT_POST, 'btn_apply', FILTER_VALIDATE_INT) && false !== $continent) {
        header('Location: '. rex_url::currentBackendPage(['entry_id' => $continent->continent_id, 'func' => 'edit', 'message' => $message], false));
    } else {
        header('Location: '. rex_url::currentBackendPage(['message' => $message], false));
    }
    exit;
}
// Delete
if (1 === (int) filter_input(INPUT_POST, 'btn_delete', FILTER_VALIDATE_INT) || 'delete' === $func) {
    $continent_id = $entry_id;
    if (0 === $continent_id) {
        $form = rex_post('form', 'array', []);
        $continent_id = $form['continent_id'];
    }
    $continent = new FriendsOfRedaxo\D2UAddress\Continent($continent_id, (int) rex_config::get('d2u_helper', 'default_lang'));
    $continent->continent_id = $continent_id; // Ensure correct ID in case language has no object

    // Delete
    $continent->delete(true);

    $func = '';
}

// Eingabeformular
if ('edit' === $func || 'add' === $func) {
?>
	<form action="<?= rex_url::currentBackendPage() ?>" method="post">
		<div class="panel panel-edit">
			<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('d2u_address_continent') ?></div></header>
			<div class="panel-body">
				<input type="hidden" name="form[continent_id]" value="<?= $entry_id ?>">
				<?php
                    foreach (rex_clang::getAll() as $rex_clang) {
                        $continent = new FriendsOfRedaxo\D2UAddress\Continent($entry_id, $rex_clang->getId());
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
                                    \TobiasKrais\D2UHelper\BackendHelper::form_select('d2u_helper_translation', 'form[lang]['. $rex_clang->getId() .'][translation_needs_update]', $options_translations, [$continent->translation_needs_update], 1, false, $readonly_lang);
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
                                    \TobiasKrais\D2UHelper\BackendHelper::form_input('d2u_helper_name', 'form[lang]['. $rex_clang->getId() .'][name]', $continent->name, $required, $readonly_lang, 'text');
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
                            $continent = new FriendsOfRedaxo\D2UAddress\Continent($entry_id, (int) rex_config::get('d2u_helper', 'default_lang'));
                            $readonly = true;
                            if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
                                $readonly = false;
                            }

                            $options_country_ids = [];
                            $countries = FriendsOfRedaxo\D2UAddress\Country::getAll((int) rex_config::get('d2u_helper', 'default_lang'));
                            foreach ($countries as $country) {
                                $options_country_ids[$country->country_id] = $country->name;
                            }
                            \TobiasKrais\D2UHelper\BackendHelper::form_select('d2u_address_countries', 'form[country_ids][]', $options_country_ids, $continent->country_ids, 15, true, $readonly);
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
    $query = 'SELECT continents.continent_id, name '
        . 'FROM '. \rex::getTablePrefix() .'d2u_address_continents AS continents '
        . 'LEFT JOIN '. \rex::getTablePrefix() .'d2u_address_continents_lang AS lang '
            . 'ON continents.continent_id = lang.continent_id AND lang.clang_id = '. (int) rex_config::get('d2u_helper', 'default_lang') .' '
        . 'ORDER BY name ASC';
    $list = rex_list::factory($query, 1000);

    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon fa-flag"></i>';
    $thIcon = '';
    if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
        $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . rex_i18n::msg('add') . '"><i class="rex-icon rex-icon-add-module"></i></a>';
    }
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'entry_id' => '###continent_id###']);

    $list->setColumnLabel('continent_id', rex_i18n::msg('id'));
    $list->setColumnLayout('continent_id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);

    $list->setColumnLabel('name', rex_i18n::msg('d2u_helper_name'));
    $list->setColumnParams('name', ['func' => 'edit', 'entry_id' => '###continent_id###']);

    $list->addColumn(rex_i18n::msg('module_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('module_functions'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('module_functions'), ['func' => 'edit', 'entry_id' => '###continent_id###']);

    if (\rex::getUser() instanceof rex_user && (\rex::getUser()->isAdmin() || \rex::getUser()->hasPerm('d2u_address[edit_data]'))) {
        $list->addColumn(rex_i18n::msg('delete_module'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
        $list->setColumnLayout(rex_i18n::msg('delete_module'), ['', '<td class="rex-table-action">###VALUE###</td>']);
        $list->setColumnParams(rex_i18n::msg('delete_module'), ['func' => 'delete', 'entry_id' => '###continent_id###']);
        $list->addLinkAttribute(rex_i18n::msg('delete_module'), 'data-confirm', rex_i18n::msg('d2u_helper_confirm_delete'));
    }

    $list->setNoRowsMessage(rex_i18n::msg('d2u_address_continents_no_continents_found'));

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('d2u_address_continents'), false);
    $fragment->setVar('content', $list->get(), false);
    echo $fragment->parse('core/page/section.php');
}
