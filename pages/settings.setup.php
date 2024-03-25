<?php
/*
 * Modules
 */
$d2u_module_manager = new \TobiasKrais\D2UHelper\ModuleManager(FriendsOfREDAXO\D2UAddress\Module::getModules(), 'modules/', 'd2u_address');

// \TobiasKrais\D2UHelper\ModuleManager actions
$d2u_module_id = rex_request('d2u_module_id', 'string');
$paired_module = rex_request('pair_'. $d2u_module_id, 'int');
$function = rex_request('function', 'string');
if ('' !== $d2u_module_id) {
    $d2u_module_manager->doActions($d2u_module_id, $function, $paired_module);
}

// \TobiasKrais\D2UHelper\ModuleManager show list
$d2u_module_manager->showManagerList();