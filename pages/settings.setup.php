<?php
/*
 * Modules
 */
$d2u_module_manager = new D2UModuleManager(D2UAddressModules::getModules(), 'modules/', 'd2u_address');

// D2UModuleManager actions
$d2u_module_id = rex_request('d2u_module_id', 'string');
$paired_module = (int) rex_request('pair_'. $d2u_module_id, 'int');
$function = rex_request('function', 'string');
if ('' !== $d2u_module_id) {
    $d2u_module_manager->doActions($d2u_module_id, $function, $paired_module);
}

// D2UModuleManager show list
$d2u_module_manager->showManagerList();
?>