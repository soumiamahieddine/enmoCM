<?php

/*
* @requires
*   $params
*		['recipient']	= user object
*		['events']		= array of notif_event objects
*
* @returns
	[recipient]	= one user recipient array
	[events]	= array of events arrays
*/

$datasources['recipient'] = (array)$recipient;

$datasources['events'] = array();
foreach($events as $event) {
	$datasources['events'][] = (array)$event;
}

?>
