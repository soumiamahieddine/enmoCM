<?php

/*
* @requires vas
*   $recipient	= user recipient object
*	$events		= array of notif_event objects
*
* @return datasources
	[recipient]	= one user recipient array
	[events]	= array of events arrays
*/

$datasources['recipient'][0] = (array)$recipient;

$datasources['events'] = array();
foreach($events as $event) {
	$datasources['events'][] = (array)$event;
}

?>
