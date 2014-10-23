<?php

require_once('core/class/class_core_tools.php');

$db = new dbquery();
$db->connect();

$purpose = $_REQUEST['contact_purpose'];
$purpose_id = $_REQUEST['contact_purpose_id'];

if ($purpose_id <> "") {
	$db->query("SELECT label FROM contact_purposes WHERE id=".$purpose_id);
	$res = $db->fetch_object();
	if ($res->label == $purpose) {
		echo "{status : 0}";  // le user a clique sur la liste en autocompletion
		exit();
	} else {
		echo "{status : 1}"; // le label a été modifié apres autocompletion : on cree la nouvelle denomination
		exit();
	}
} else {
	echo "{status : 1}"; // nouvelle denomination
	exit ();

}