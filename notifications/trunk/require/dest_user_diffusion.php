<?php
$query = "SELECT distinct us.* "
	. " FROM listinstance li LEFT JOIN users us ON li.item_id = us.user_id " 
	. " WHERE coll_id = 'letterbox_coll' AND res_id = ".$event->record_id
	. " AND listinstance_type='DOC' AND item_mode = 'dest'";
?>