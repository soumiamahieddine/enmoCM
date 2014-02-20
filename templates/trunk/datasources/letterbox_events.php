<?php

/*
* @requires
*   $res_view   = Name of res view
*   $maarchApps = name of app
*   $maarchUrl  = Url to maarch (root url)
*   $recipient  = recipient of notification
*   $events     = array of events related to letterbox mails
*
* @returns
    [res_letterbox] = record of view + link to detail/doc page
*/

$dbDatasource = new dbquery();
$dbDatasource->connect();

$datasources['recipient'][0] = (array)$recipient;

$datasources['res_letterbox'] = array();

foreach($events as $event) {
    $res = array();
    
    $select = "SELECT lb.*";
    $from = " FROM ".$res_view." lb ";
    $where = " WHERE ";
    
    switch($event->table_name) {
    case 'notes':
        $from .= " JOIN notes ON notes.identifier = lb.res_id";
        $where .= " notes.id = " . $event->record_id;
        break;
    
    case 'listinstance':
        $from .= " JOIN listinstance li ON lb.res_id = li.res_id";
        $where .= " li.coll_id = '".$coll_id."'   AND listinstance_id = " . $event->record_id;
        break;
        
    case 'res_letterbox':
    case 'res_view_letterbox':
    default:
        $where .= " lb.res_id = " . $event->record_id;
    }

    $query = $select . $from . $where;
    
    if($GLOBALS['logger']) {
        $GLOBALS['logger']->write($query , 'DEBUG');
    }
    
    // Main document resource from view
    $dbDatasource->query($query);
    $res = $dbDatasource->fetch_assoc();
    
    // Lien vers la page détail
    $urlToApp = str_replace('//', '/', $maarchUrl . '/apps/' . $maarchApps . '/index.php?');
    $res['linktodoc'] = $urlToApp . 'display=true&page=view_resource_controler&dir=indexing_searching&id=' . $res['res_id'];
    $res['linktodetail'] = $urlToApp . 'page=details&dir=indexing_searching&id=' . $res['res_id'];
    $res['linktoprocess'] = $urlToApp . 'page=view_baskets&module=basket&baskets=MyBasket&directLinkToAction&resid=' . $res['res_id'];

    // Insertion
    $datasources['res_letterbox'][] = $res;
}

$datasources['images'][0]['imgdetail'] = $maarchUrl . '/apps/' . $maarchApps . '/img/object.gif';
$datasources['images'][0]['imgdoc'] = $maarchUrl . '/apps/' . $maarchApps . '/img/picto_dld.gif';

?>
