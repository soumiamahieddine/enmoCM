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
	[notes] = detail of notes added
*/

$dbDatasource = new dbquery();
$dbDatasource->connect();

$datasources['recipient'][0] = (array)$recipient;

$datasources['notes'] = array();

foreach($events as $event) {
	$note = array();
	
	// Query
	$query = "SELECT mlb.*, "
		. "notes.*, "
		. "users.* " 
		. "FROM listinstance li JOIN " . $res_view . " mlb ON mlb.res_id = li.res_id "
		. "JOIN notes on li.coll_id=notes.coll_id AND notes.identifier = li.res_id "
		. "JOIN users on users.user_id = notes.user_id "
		. "WHERE li.coll_id = '" . $coll_id . "' "
		. "AND li.item_id = '" . $recipient->user_id . "' "
		. "AND li.item_mode = 'dest' "
		. "AND li.item_type = 'user_id' "
		. "AND li.res_id = " . $event->record_id;
	/*
	$query = "SELECT mlb.*, "
		. "notes.*, "
		. "users.* " 
		. "FROM listinstance li JOIN " . $res_view . " mlb ON mlb.res_id = li.res_id "
		. "JOIN notes on li.coll_id=notes.coll_id AND notes.identifier = li.res_id "
		. "JOIN users on users.user_id = notes.user_id "
		. "WHERE li.coll_id = '" . $coll_id . "' "
		. "AND li.item_id = '" . $recipient->user_id . "' "
		. "AND li.item_mode = 'dest' "
		. "AND li.item_type = 'user_id' "
		. "AND notes.id = " . $event->record_id;
	*/
	$dbDatasource->query($query);
	$note = $dbDatasource->fetch_object();

	// Insertion
	$datasources['notes'][] = $note;
}

$datasources['images'][0]['imgdetail'] = str_replace('//', '/', $maarchUrl . '/apps/' . $maarchApps . '/img/object.gif');
$datasources['images'][0]['imgdoc'] = str_replace('//', '/', $maarchUrl . '/apps/' . $maarchApps . '/img/picto_dld.gif');

?>