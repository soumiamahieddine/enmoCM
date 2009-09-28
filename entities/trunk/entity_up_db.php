<?php

/**
* File : entity_up_db.php
*
* Modify entity in database after form
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  Cédric Ndoumba  <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");

require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtomodules'].'entities'.$_SESSION['slash_env'].'class'.$_SESSION['slash_env'].'class_manage_entities.php');

require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$admin = new core_tools();

$admin->load_lang();
$admin->test_admin('manage_entities', 'entities');
$ent = new entity();

$ent->addupentity("up");
?>
