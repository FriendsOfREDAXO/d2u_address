<?php
/*
 * Modules
 */
$d2u_module_manager = new D2UModuleManager(D2UAddressModules::getModules(), "modules/", "d2u_address");

// D2UModuleManager actions
$d2u_module_id = rex_request('d2u_module_id', 'string');
$paired_module = rex_request('pair_'. $d2u_module_id, 'int');
$function = rex_request('function', 'string');
if($d2u_module_id != "") {
	$d2u_module_manager->doActions($d2u_module_id, $function, $paired_module);
}

// D2UModuleManager show list
$d2u_module_manager->showManagerList();
?>
<h2>Beispielseiten</h2>
<ul>
	<li>Adressen Addon: <a href="https://www.kaltenbach.com/de/unternehmen/kontakt/" target="_blank">
		Kaltenbach GmbH + Co KG</a>.</li>
</ul>
<h2>Support</h2>
<p>Fehlermeldungen bitte im <a href="https://github.com/TobiasKrais/d2u_address" target="_blank">GitHub Repository</a> melden.</p>
<h2>Changelog</h2>
<p>1.0.1:</p>
<ul>
	<li>Editierrechte für Übersetzer eingeschränkt.</li>
	<li>Anpassungen an Übersetzungshilfe im D2U Helper Addon.</li>
</ul>
<p>1.0.0:</p>
<ul>
	<li>Initiale Version.</li>
</ul>