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

$dbDatasource = new Database();

$datasources['recipient'][0] = (array)$recipient;

$datasources['notes'] = array();

foreach($events as $event) {
	$note = array();
	
	// Query
    switch($event->table_name) {
    case 'notes':
        $query = "SELECT mlb.*, notes.*, users.* "
            . "FROM " . $res_view . " mlb "
            . "JOIN notes on notes.identifier = mlb.res_id "
            . "JOIN users on users.id = notes.user_id "
            . "WHERE notes.id = ? ";
        $arrayPDO = array($event->record_id);
        break;
    
    case "res_letterbox" :
    case "res_view_letterbox" :
        $query = "SELECT mlb.*, "
            . "notes.*, "
            . "users.* " 
            . "FROM listinstance li JOIN " . $res_view . " mlb ON mlb.res_id = li.res_id "
            . "JOIN notes on notes.identifier = li.res_id "
            . "JOIN users on users.id = notes.user_id "
            . "WHERE li.item_id = ? "
            . "AND li.item_mode = 'dest' "
            . "AND li.item_type = 'user_id' "
            . "AND li.res_id = ? ";
        $arrayPDO = array($recipient->user_id, $event->record_id);
        break;
    }
    
    if($GLOBALS['logger']) {
        $GLOBALS['logger']->write($query , 'DEBUG');
    }
    
	$stmt = $dbDatasource->query($query, $arrayPDO);
	$note = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Lien vers la page détail
    $urlToApp = trim($maarchUrl, '/').'/apps/'.trim($maarchApps, '/').'/index.php?';

    $user   = \User\models\UserModel::getByLogin(['login' => $datasources['recipient'][0]['user_id'], 'select' => ['id']]);
    $basket = \Basket\models\BasketModel::getByBasketId(['select' => ['id'], 'basketId' => 'MyBasket']);
    $preferenceBasket = \User\models\UserBasketPreferenceModel::get([
        'select'  => ['group_serial_id'],
        'where'   => ['user_serial_id = ?', 'basket_id = ?'],
        'data'    => [$user['id'], 'MyBasket']
    ]);

    $note['linktodoc']     = $urlToApp . 'linkToDoc='.$note['res_id'];
    $note['linktodetail']  = $urlToApp . 'linkToDetail='.$note['res_id'];
    if (!empty($note['res_id']) && !empty($preferenceBasket[0]['group_serial_id']) && !empty($basket['id']) && !empty($user['id'])) {
        $note['linktoprocess'] = $urlToApp . 'linkToProcess='.$note['res_id'].'&groupId='.$preferenceBasket[0]['group_serial_id'].'&basketId='.$basket['id'].'&userId='.$user['id'];
    }
    
	// Insertion
	$datasources['notes'][] = $note;
}

$datasources['images'][0]['imgdetail'] = str_replace('//', '/', $maarchUrl . '/apps/' . $maarchApps . '/img/object.gif');
$datasources['images'][0]['imgdoc'] = str_replace('//', '/', $maarchUrl . '/apps/' . $maarchApps . '/img/picto_dld.gif');

?>