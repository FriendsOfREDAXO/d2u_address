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
	<li>Adressen Addon: <a href="https://www.inotec-gmbh.com/kontakt/unser-service-netz/" target="_blank">
		Inotec GmbH</a>.</li>
</ul>
<h2>Support</h2>
<p>Fehlermeldungen bitte im <a href="https://github.com/TobiasKrais/d2u_address" target="_blank">GitHub Repository</a> melden.</p>
<h2>Changelog</h2>
<p>1.0.5-DEV:</p>
<ul>
	<li>Bei der Eingabe einer Adresse gibt es jetzt die Möglichkeit eine Adresse direkt zu geocodieren wenn im D2U Helper Addon ein Google Maps API Key mit Zugriff auf die Geocoding API hinterlegt ist.
		Geocodierte Adressen werden auf der Karte schneller geladen.</li>
</ul>
<p>1.0.4:</p>
<ul>
	<li>Modul 20-1 "D2U Adressen - Adressausgabe": Wenn Längen und Breitengrade eingegeben sind, wird kein Geocoder mehr verwendet.</li>
	<li>Moduleingabe nun im Redaxo Stil.</li>
	<li>Hinweis Geocoding erneuert.</li>
	<li>Bugfix: Längen- und Breitengrad konnte nicht gespeichert werden.</li>
</ul>
<p>1.0.3:</p>
<ul>
	<li>Bugfix: Deaktiviertes Addon zu deinstallieren führte zu fatal error.</li>
	<li>In den Einstellungen gibt es jetzt eine Option, eigene Übersetzungen in SProg dauerhaft zu erhalten.</li>
	<li>Es können nun Google Analytics Events beim Klick auf E-Mails gesendet werden. Details hierzu in den Einstellungen.</li>
	<li>Adressen lassen sich jetzt klonen.</li>
	<li>Beispielmodul jetzt optional mit Faxanzeige.</li>
	<li>Postleitzahlen können jetzt geklont werden.</li>
	<li>Bugfix: Sortierung bei Ländern mit Umlauten wie Ägypten war nicht korrekt.</li>
	<li>Modul Detailverbesserungen.</li>
	<li>Bugfix: Fehler beim Speichern von Namen mit einfachem Anführungszeichen behoben.</li>
</ul>
<p>1.0.2:</p>
<ul>
	<li>Bugfix Module: Auswahl wurde nicht gespeichert.</li>
	<li>Module auf Bootstrap 4 umgestellt.</li>
	<li>Englische Backend Übersetzung hinzugefügt.</li>
	<li>Bugfix beim Anlegen neuer Länder.</li>
	<li>Bugfix beim Speichern der AdressTyp Zuordnung von Adressen.</li>
</ul>
<p>1.0.1:</p>
<ul>
	<li>Editierrechte für Übersetzer eingeschränkt.</li>
	<li>Anpassungen an Übersetzungshilfe im D2U Helper Addon.</li>
</ul>
<p>1.0.0:</p>
<ul>
	<li>Initiale Version.</li>
</ul>