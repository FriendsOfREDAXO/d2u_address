<?php
// Update language replacements
d2u_address_lang_helper::factory()->install();

// Update modules
if(class_exists(D2UModuleManager)) {
	$modules = [];
	$modules[] = new D2UModule("20-1",
		"D2U Adressen - Adressausgabe",
		1);
	$modules[] = new D2UModule("20-2",
		"D2U Adressen - Kontaktbox",
		1);
	$d2u_module_manager = new D2UModuleManager($modules, "", "d2u_address");
	$d2u_module_manager->autoupdate();
}