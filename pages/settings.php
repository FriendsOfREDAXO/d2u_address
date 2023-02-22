<?php
// save settings
if ('save' === filter_input(INPUT_POST, 'btn_save')) {
    $settings = rex_post('settings', 'array', []);

    // Checkbox also need special treatment if empty
    $settings['analytics_emailevent_activate'] = array_key_exists('analytics_emailevent_activate', $settings) ? 'true' : 'false';
    $settings['lang_wildcard_overwrite'] = array_key_exists('lang_wildcard_overwrite', $settings) ? 'true' : 'false';

    // Save settings
    if (rex_config::set('d2u_address', $settings)) {
        echo rex_view::success(rex_i18n::msg('form_saved'));

        // Install / update language replacements
        d2u_address_lang_helper::factory()->install();
    } else {
        echo rex_view::error(rex_i18n::msg('form_save_error'));
    }
}
?>
<form action="<?= rex_url::currentBackendPage() ?>" method="post">
	<div class="panel panel-edit">
		<header class="panel-heading"><div class="panel-title"><?= rex_i18n::msg('d2u_helper_settings') ?></div></header>
		<div class="panel-body">
			<fieldset>
				<legend><small><i class="rex-icon fa-book"></i></small> <?= rex_i18n::msg('d2u_helper_settings') ?></legend>
				<div class="panel-body-wrapper slide">
					<?php
                        $country_options = [];
                        $countries = D2U_Address\Country::getAll((int) rex_config::get('d2u_helper', 'default_lang', rex_clang::getStartId()));
                        foreach ($countries as $country) {
                            $country_options[$country->country_id] = $country->name;
                        }
                        d2u_addon_backend_helper::form_select('d2u_address_default_country_id', 'settings[default_country_id]', $country_options, [(int) rex_config::get('d2u_address', 'default_country_id')]);
                    ?>
				</div>
			</fieldset>
			<fieldset>
				<legend><small><i class="rex-icon rex-icon-language"></i></small> <?= rex_i18n::msg('d2u_helper_lang_replacements') ?></legend>
				<div class="panel-body-wrapper slide">
					<?php
                        d2u_addon_backend_helper::form_checkbox('d2u_helper_lang_wildcard_overwrite', 'settings[lang_wildcard_overwrite]', 'true', 'true' === rex_config::get('d2u_address', 'lang_wildcard_overwrite'));
                        foreach (rex_clang::getAll() as $rex_clang) {
                            echo '<dl class="rex-form-group form-group">';
                            echo '<dt><label>'. $rex_clang->getName() .'</label></dt>';
                            echo '<dd>';
                            echo '<select class="form-control" name="settings[lang_replacement_'. $rex_clang->getId() .']">';
                            $replacement_options = [
                                'd2u_helper_lang_english' => 'english',
                                'd2u_helper_lang_chinese' => 'chinese',
                                'd2u_helper_lang_czech' => 'czech',
                                'd2u_helper_lang_dutch' => 'dutch',
                                'd2u_helper_lang_french' => 'french',
                                'd2u_helper_lang_german' => 'german',
                                'd2u_helper_lang_italian' => 'italian',
                                'd2u_helper_lang_polish' => 'polish',
                                'd2u_helper_lang_portuguese' => 'portuguese',
                                'd2u_helper_lang_russian' => 'russian',
                                'd2u_helper_lang_spanish' => 'spanish',
                            ];
                            foreach ($replacement_options as $key => $value) {
                                $selected = $value === (string) (rex_config::get('d2u_address', 'lang_replacement_'. $rex_clang->getId())) ? ' selected="selected"' : '';
                                echo '<option value="'. $value .'"'. $selected .'>'. rex_i18n::msg('d2u_helper_lang_replacements_install') .' '. rex_i18n::msg($key) .'</option>';
                            }
                            echo '</select>';
                            echo '</dl>';
                        }
                    ?>
				</div>
			</fieldset>
			<fieldset>
				<legend><small><i class="rex-icon fa-google"></i></small> <?= rex_i18n::msg('d2u_address_settings_analytics') ?></legend>
				<div class="panel-body-wrapper slide">
					<?php
                        d2u_addon_backend_helper::form_checkbox('d2u_address_settings_analytics_emailevent_activate', 'settings[analytics_emailevent_activate]', 'true', 'true' === rex_config::get('d2u_address', 'analytics_emailevent_activate'));
                        d2u_addon_backend_helper::form_input('d2u_address_settings_analytics_emailevent_category', 'settings[analytics_emailevent_category]', (string) rex_config::get('d2u_address', 'analytics_emailevent_category'), false, false, 'text');
                        d2u_addon_backend_helper::form_input('d2u_address_settings_analytics_emailevent_action', 'settings[analytics_emailevent_action]', (string) rex_config::get('d2u_address', 'analytics_emailevent_action'), false, false, 'text');
                    ?>
					<script>
						function changeType() {
							if($('input[name="settings\\[analytics_emailevent_activate\\]"]').is(':checked')) {
								$('#settings\\[analytics_emailevent_category\\]').fadeIn();
								$('#settings\\[analytics_emailevent_action\\]').fadeIn();
							}
							else {
								$('#settings\\[analytics_emailevent_category\\]').hide();
								$('#settings\\[analytics_emailevent_action\\]').hide();
							}
						}

						// On init
						changeType();
						// On change
						$('input[name="settings\\[analytics_emailevent_activate\\]"]').on('change', function() {
							changeType();
						});
					</script>
				</div>
			</fieldset>
		</div>
		<footer class="panel-footer">
			<div class="rex-form-panel-footer">
				<div class="btn-toolbar">
					<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="save"><?= rex_i18n::msg('form_save') ?></button>
				</div>
			</div>
		</footer>
	</div>
</form>
<?php
    echo d2u_addon_backend_helper::getCSS();
    echo d2u_addon_backend_helper::getJS();
    echo d2u_addon_backend_helper::getJSOpenAll();
