<?php

$sql = \rex_sql::factory();

// Delete views
$sql->setQuery('DROP VIEW IF EXISTS ' . \rex::getTablePrefix() . 'd2u_references_url_referencs');
$sql->setQuery('DROP VIEW IF EXISTS ' . \rex::getTablePrefix() . 'd2u_references_url_tags2ref');
$sql->setQuery('DROP VIEW IF EXISTS ' . \rex::getTablePrefix() . 'd2u_references_url_tags');
// Delete url schemes
if (\rex_addon::get('url')->isAvailable()) {
    $sql->setQuery('DELETE FROM `'. \rex::getTablePrefix() ."url_generate` WHERE `table` LIKE '%d2u_references_url_%'");
}

// Delete Media Manager media types
$sql->setQuery('DELETE FROM '. \rex::getTablePrefix() ."media_manager_type WHERE name LIKE 'd2u_references%'");
$sql->setQuery('DELETE FROM '. \rex::getTablePrefix() ."media_manager_type_effect WHERE createuser = 'd2u_references'");

// Delete tables
$sql->setQuery('DROP TABLE IF EXISTS ' . \rex::getTablePrefix() . 'd2u_references_tag2refs');
$sql->setQuery('DROP TABLE IF EXISTS ' . \rex::getTablePrefix() . 'd2u_references_tags');
$sql->setQuery('DROP TABLE IF EXISTS ' . \rex::getTablePrefix() . 'd2u_references_tags_lang');
$sql->setQuery('DROP TABLE IF EXISTS ' . \rex::getTablePrefix() . 'd2u_references_references');
$sql->setQuery('DROP TABLE IF EXISTS ' . \rex::getTablePrefix() . 'd2u_references_references_lang');

// Delete language replacements
if (!class_exists(FriendsOfRedaxo\D2UAddress\LangHelper::class)) {
    // Load class in case addon is deactivated
    require_once 'lib/LangHelper.php';
}
FriendsOfRedaxo\D2UAddress\LangHelper::factory()->uninstall();
