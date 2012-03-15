<?php

/*********************************************************************************
** Get aditionnal data to merge template using notification structure
**  - $notification['template_association'] = templates_association object
**  - $notification['recipient'] = user object as recipient
**	- $notification['events'] = array of event objects from event_stack
*********************************************************************************/
function getAdditionalContent($notification) {
	// Get res details
	foreach($notification['events'] as $event) {
		$query = "SELECT mlb.res_id as Num, "
			. "mlb.type_label as Type, " 
			. "mlb.subject as Objet, " 
			. "mlb.identifier as Numero, "
			. "notes.date_note as Date, "
			. "notes.note_text as Note, "
			. "users.firstname || ' ' || users.lastname as Auteur " 
			. "FROM listinstance li LEFT JOIN res_view_letterbox mlb ON mlb.res_id = li.res_id "
			. "LEFT JOIN notes on li.coll_id=notes.coll_id AND notes.identifier = li.res_id "
			. "LEFT JOIN users on users.user_id = notes.user_id "
			. "WHERE li.coll_id = 'letterbox_coll' "
			. "AND li.item_id = '" . $notification['recipient']->user_id . "' "
			. "AND li.item_mode = 'dest' "
			. "AND li.res_id = " . $event->record_id;
		Bt_doQuery($GLOBALS['db'], $query);
		$courrier = $GLOBALS['db']->fetch_object();
		
		// Lien vers la page détail
		$urlToApp = $notification['maarchUrl'] . '/apps/' . $notification['maarchApps'] . '/index.php?';
		$urlToDetail = $urlToApp . 'page=details&dir=indexing_searching&id=' . $event->record_id;
		$urlToDoc = $urlToApp . 'page=view_resource_controler&dir=indexing_searching&id=' . $event->record_id;
		$urlToImg = $notification['maarchUrl'] . '/apps/' . $notification['maarchApps'] . '/img/mail.gif';
		$courrier->Detail = '<a href="' . $urlToDetail . '" ><img src="'.$urlToImg.'" alt="Detail" height="22" width="30" /></a>'; 
		
		// Insertion
		$notification['courriers'][] = $courrier;
		
		// Chemins vers les fichier à joindre
		$query = "SELECT "
			. "ds.path_template ,"
			. "mlb.path, "
			. "mlb.filename " 
			. "FROM res_view_letterbox mlb LEFT JOIN docservers ds ON mlb.docserver_id = ds.docserver_id "
			. "WHERE mlb.res_id = " . $event->record_id;
		Bt_doQuery($GLOBALS['db'], $query);
		$path_parts = $GLOBALS['db']->fetch_object();
		$path = $path_parts->path_template . str_replace('#', '/', $path_parts->path) . $path_parts->filename;
		$path = str_replace('//', '/', $path);
		$notification['attachments'][] = $path;
	}
	return $notification;
}

?>
