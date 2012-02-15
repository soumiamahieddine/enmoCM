<?php

/*********************************************************************************
** Get aditionnal data to merge template using notification structure
**  - $notification['template_association'] = templates_association object
**  - $notification['recipient'] = user object as recipient
**	- $notification['events'] = array of event objects from event_stack
*********************************************************************************/
function getAdditionalContent($notification) {
	$notification['destinataire']->Nom = $notification['recipient']->lastname;
	$notification['destinataire']->Prenom = $notification['recipient']->firstname;
	$notification['destinataire']->Email = $notification['recipient']->email;
	$notification['destinataire']->Telephone = $notification['recipient']->phone;

	$notification['evenements'] = array();
	foreach($notification['events'] as $event) {
		$evenement = null;
		$evenement->Date = substr($event->event_date, 0, 19);
		$evenement->Description = $event->event_info;
		$evenement->Utilisateur = $event->user_id;
		$evenement->Objet = $event->record_id;
		$notification['evenements'][] = $evenement;
	}
	return $notification;
}
?>
