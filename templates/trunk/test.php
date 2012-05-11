<?php

//$_SESSION['config']['tmppath'] = "C:\\xampp\\htdocs\\maarch_entreprise_trunk\\apps\\maarch_entreprise\\tmp\\";

// SET TRUE IN DEBUG MODE
$debug = true;

require_once 'modules/templates/class/templates_controler.php';
$templates_controler = new templates_controler();

// Test notif letterbox (index)
/*$templateId = 21;
$event1->record_id = 132; 
$event2->record_id = 133; 
$event3->record_id = 134; 

$params = array(
	'dummy' => 'dummy',
	'res_view'	=> 'res_view_letterbox',
	'maarchApps' => 'maarch_entreprise',
	'maarchUrl'	=> 'http://localhost/maarch_trunk/',
	'recipient' => array(
		"firstname" => "Prénom",
		"lastname"	=> "Nom"
	),
	'events' => array(
		$event1, 
		$event2, 
		$event3
	)
);
*/

// Test réponse letterbox
$templateId = 105;
$params = array(
	'dummy' => 'dummy',
	'res_view'	=> 'res_view_letterbox',
	'coll_id' => 'letterbox_coll',
	'res_id' => 103
);

// Test divers
//~ $templateId = 23;
//~ $params = array(
	//~ 'dummy' => 'dummy',
	//~ 'res_view'	=> 'res_view_letterbox',
	//~ 'coll_id' => 'letterbox_coll',
	//~ 'res_id' => 132
//~ );

$content = $templates_controler->merge($templateId, $params, 'content');



echo $content;
?>

