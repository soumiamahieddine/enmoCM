<?php

/*
* @requires
*   $res_view	= Name of res view
*   $maarchApps = name of app
*   $maarchUrl	= Url to maarch (root url)
* 	$recipient	= recipient of notification
*	$events 	= array of events related to letterbox mails
*
* @returns
	[res_letterbox]	= record of view + link to detail/doc page
*/

$dbDatasource = new dbquery();
$dbDatasource->connect();

$datasources['recipient'][0] = (array)$recipient;

$datasources['res_letterbox'] = array();

foreach($events as $event) {
	$res = array();
	
	// Main document resource from view
	$dbDatasource->query("SELECT * FROM " . $res_view . " WHERE res_id = " . $event->record_id . "");
	$res = $dbDatasource->fetch_assoc();
	
	// Lien vers la page détail
	$urlToApp = $maarchUrl . '/apps/' . $maarchApps . '/index.php?';
	$res['linktodoc'] = $urlToApp . 'display=true&page=view_resource_controler&dir=indexing_searching&id=' . $event->record_id;
	$res['linktodetail'] = $urlToApp . 'page=details&dir=indexing_searching&id=' . $event->record_id;

	// Insertion
	$datasources['res_letterbox'][] = $res;
}

$datasources['images'][0]['imgdetail'] = $maarchUrl . '/apps/' . $maarchApps . '/img/object.gif';
$datasources['images'][0]['imgdoc'] = $maarchUrl . '/apps/' . $maarchApps . '/img/picto_dld.gif';

?>
