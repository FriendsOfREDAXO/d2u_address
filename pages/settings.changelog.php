<h2>Support</h2>
<p>Fehlermeldungen bitte im <a href="https://github.com/FriendsOfRedaxo/d2u_address" target="_blank">GitHub Repository</a> melden.</p>
<fieldset style='padding: 1em; border: 1px solid #dfe3e9;'>
	<p style="margin-bottom: 0.5em;">Sag einfach Danke und unterstütze die Weiterentwicklung durch deine Spende:</p>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
		<input type="hidden" name="cmd" value="_s-xclick" />
		<input type="hidden" name="hosted_button_id" value="CB7B6QTLM76N6" />
		<input type="image" src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donateCC_LG.gif" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Spenden mit dem PayPal-Button" />
		<img alt="" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
	</form>
</fieldset>

<h2>Changelog</h2>
<p>1.5.1-DEV:</p>
<ul>
	<li>Fix bug in install and uninstall file.</li>
</ul>
<p>1.5.0:</p>
<ul>
	<li>README hinzugefügt.</li>
	<li>Vorbereitung auf R6: Folgende Klassen werden ab Version 2 dieses Addons umbenannt. Schon jetzt stehen die neuen Klassen für die Übergangszeit zur Verfügung:
		<ul>
			<li><code>FriendsOfRedaxo\D2UAddress\Address</code> wird zu <code>FriendsOfRedaxo\D2UAddress\Address</code>.</li>
			<li><code>FriendsOfRedaxo\D2UAddress\AddressType</code> wird zu <code>FriendsOfRedaxo\D2UAddress\AddressType</code>.</li>
			<li><code>FriendsOfRedaxo\D2UAddress\Country</code> wird zu <code>FriendsOfRedaxo\D2UAddress\Country</code>.</li>
			<li><code>FriendsOfRedaxo\D2UAddress\Continent</code> wird zu <code>FriendsOfRedaxo\D2UAddress\Continent</code>.</li>
			<li><code>FriendsOfRedaxo\D2UAddress\ZipCode</code> wird zu <code>FriendsOfRedaxo\D2UAddress\ZipCode</code>.</li>
		</ul>
		Folgende interne Klassen wurden wurden ebenfalls umbenannt. Hier gibt es keine Übergangszeit, da sie nicht öffentlich sind:
		<ul>
			<li><code>d2u_address_lang_helper</code> wird zu <code>FriendsOfRedaxo\D2UAddress\LangHelper</code>.</li>
			<li><code>D2UAddressModules</code> wird zu <code>FriendsOfRedaxo\D2UAddress\Module</code>.</li>
		</ul>
	</li>
</ul>
<p>1.4.0:</p>
<ul>
	<li>PHP-CS-Fixer Verbesserungen.</li>
	<li>rexstan Abgleich.</li>
	<li>Modul 20-1 "D2U Adressen - Adressausgabe": Kann nun auch Karten des Geolocation Addons Verion 2 verwenden und zeigt in den Infoboxen der Karten nun auch Telefonnummern an.</li>
	<li>Modul 20-3 "D2U Adressen - Weltkarte" hinzugefügt. Es werden alle Kontakte einer Kontaktart angezeigt.</li>
</ul>
<p>1.3.0:</p>
<ul>
	<li>Modul 20-1 "D2U Adressen - Adressausgabe": Kann nun auch Karten des Geolocation Addons verwenden.</li>
	<li>Modul 20-2 "D2U Adressen - Kontaktbox": Verschiedene Fehlerbehebungen.</li>
	<li>Ca. 250 rexstan Verbesserungen.</li>
	<li>install.php und update.php auf Redaxo Stil umgeschrieben.</li>
</ul>
<p>1.2.2:</p>
<ul>
	<li>Fehlerbehebung Installer Action.</li>
</ul>
<p>1.2.1:</p>
<ul>
	<li>Anpassungen an Publish Github Release to Redaxo.</li>
	<li>Sortiung der Adressen bei allen Methoden vereinheitlicht: Sortierung erfolgt ausschließlich nach Priorität.</li>
	<li>Bugfix: Beim Löschen von Ländern wurde bei einer Warnmeldung der Name des Kontakts nicht korrekt angezeigt.</li>
	<li>Bugfix: Beim Löschen von Medien die vom Addon verlinkt werden wurde der Name der verlinkenden Quelle in der Warnmeldung nicht immer korrekt angegeben.</li>
</ul>
<p>1.2:</p>
<ul>
	<li>Kontinente hinzugefügt. Ländern können Kontinenten zugeordnet werden (nicht im Beispielmodul verfügbar).</li>
	<li>Einige Frontendübersetzungen ins D2U Helper Addon umgezogen.</li>
	<li>Bugfix: beim Löschen der Adresse wurde in der Fehlermeldung der Link zur PLZ nicht korrekt gesetzt.</li>
	<li>Bugfix: bei Adressarten wurden die beiden Checkbox Felder nicht korrekt aus der Datenbank ausgelesen.</li>
	<li>Bugfix: beim Ändern einer Priorität einer Adresse wurden die anderen Prioritäten nicht korrekt angepasst.</li>
	<li>Notice entfernt.</li>
	<li>Modul 20-1 "D2U Adressen - Adressausgabe": Adresse für Option "Weitere Länder" wählbar und PHP Warnungen entfernt.</li>
</ul>
<p>1.1.3:</p>
<ul>
	<li>Neues Feld Mobilfunknummer hinzugefügt.</li>
	<li>Modul 20-1 "D2U Adressen - Adressausgabe": CSS Fehler bei PLZ Eingabe behoben.</li>
	<li>Modul 20-1 "D2U Adressen - Adressausgabe": Kartenart kann nun auch OpenStreetMap ausgewählt werden (Addon OSM proxy wird benötigt).</li>
</ul>
<p>1.1.2:</p>
<ul>
	<li>Spanische Frontend Übersetzungen aktualisiert.</li>
	<li>Backend: Einstellungen und Setup Tabs rechts eingeordnet um sie vom Inhalt besser zu unterscheiden.</li>
	<li>Bugfix beim Speichern von Straßennamen mit einfachem Anführungszeichen.</li>
	<li>Alle Module: wenn Google Analytics in den Einstellungen aktiviert ist wird der Google Code nicht ausgegeben, wenn search_it die Seite zur Indexierung aufruft.</li>
</ul>
<p>1.1.1:</p>
<ul>
	<li>Beschriftung Länderauswahl in einer Adresse korrigiert.</li>
	<li>Listen im Backend werden jetzt nicht mehr in Seiten unterteilt.</li>
	<li>Funktion address_type::getCountries() gibt jetzt standardmäßig nur noch Länder zurück, die auch eine online Adresse haben.</li>
	<li>Konvertierung der Datenbanktabellen zu utf8mb4.</li>
	<li>Sprachdetails werden ausgeblendet, wenn Speicherung der Sprache nicht vorgesehen ist.</li>
	<li>Bugfix: Es wurden nur positive Werte bei den Geokoordinaten gespeichert.</li>
</ul>
<p>1.1.0:</p>
<ul>
	<li>Bei der Eingabe einer Adresse gibt es jetzt die Möglichkeit eine Adresse direkt zu geocodieren wenn im D2U Helper Addon ein Google Maps API Key mit Zugriff auf die Geocoding API hinterlegt ist.
		Geocodierte Adressen werden auf der Karte schneller geladen und belasten das Budget des Google Kontos weniger.</li>
	<li>Adress-Länder-Zuordnung kann nun sowohl in der Adresse als auch in dem Land vorgenommen werden.</li>
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