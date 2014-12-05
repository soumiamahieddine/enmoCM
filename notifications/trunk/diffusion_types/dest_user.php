<?php
switch ($request) {
case 'form_content':
    require_once 'core/class/class_request.php' ;

    //Get list of selected status
    $choosen_status_tab = explode(",",$_SESSION['m_admin']['notification']['selected_status']);
    $choosen_status_sring = "'" . implode("','", $choosen_status_tab) . "'";

    //Get list of aff availables status
    $select["status"] = array();
    array_push($select["status"], 'id', 'label_status');
    $request = new request();
    $where = 'id NOT IN ('.$choosen_status_sring.')';
    $what = '';
    $tab = $request->select(
        $select, $where, $orderstr, $_SESSION['config']['databasetype']
    );
    $status_list = $tab;
	$form_content .= '<p class="sstit">' . _NOTIFICATIONS_DEST_USER_DIFF_TYPE_WITH_STATUS . '</p>';
    $form_content .= '<table>';
        $form_content .= '<tr>';
            $form_content .= '<td>';
                $form_content .= '<select name="statuseslist[]" id="statuseslist" size="7" ondblclick=\'moveclick(document.frmevent.elements["statuseslist[]"],document.frmevent.elements["selected_status[]"]);\' multiple="multiple" >';
                foreach ($status_list as $this_status) {
                    $form_content .=  '<option value="'.$this_status[0]['value'].'" selected="selected" >'.$this_status[0]['value'].'</option>';
                }
                
                $form_content .= '</select><br/>';
                $form_content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["statuseslist[]"]);\' >'._SELECT_ALL.'</a></em>';
            $form_content .= '</td>';
            $form_content .= '<td>';
            $form_content .= '<input type="button" class="button" value="'._ADD.'&gt;&gt;" onclick=\'Move(document.frmevent.elements["statuseslist[]"],document.frmevent.elements["selected_status[]"]);\' />';
                $form_content .= '<br />';
                $form_content .= '<br />';
                $form_content .= '<input type="button" class="button" value="&lt;&lt;'._REMOVE.'"  onclick=\'Move(document.frmevent.elements["selected_status[]"],document.frmevent.elements["statuseslist[]"]);selectall(document.forms["frmevent"].elements["selected_status[]"]);\' />';
            $form_content .= '</td>';
            $form_content .= '<td>';
                $form_content .= '<select name="selected_status[]" id="selected_status" size="7" ondblclick=\'moveclick(document.frmevent.elements["selected_status[]"],document.frmevent.elements["statuseslist"]);selectall(document.forms["frmevent"].elements["selected_status[]"]);\' multiple="multiple" >';
                
                foreach ($choosen_status_tab as $this_status) {
                    if($this_status!=''){
                        $form_content .=  '<option value="'.$this_status.'" selected="selected" >'.$this_status.'</option>';
                    }
                }   
                $form_content .= '</select><br/>';
                $form_content .= '<em><a href=\'javascript:selectall(document.forms["frmevent"].elements["selected_status[]"]);\' >'._SELECT_ALL.'</a></em>';
            $form_content .= '</td>';
        $form_content .= '</tr>';
    $form_content .= '</table>';
	break;

case 'recipients':
    $recipients = array();
    $dbRecipients = new dbquery();
    $dbRecipients->connect();
    
    $select = "SELECT distinct us.*";
	$from = " FROM listinstance li JOIN users us ON li.item_id = us.user_id";
    $where = " WHERE li.coll_id = 'letterbox_coll'   AND li.item_mode = 'dest'";

    switch($event->table_name) {
    case 'notes':
        $from .= " JOIN notes ON notes.coll_id = li.coll_id AND notes.identifier = li.res_id";
        $from .= " JOIN res_letterbox lb ON lb.res_id = notes.identifier";
		$where .= " AND notes.id = " . $event->record_id . " AND li.item_id != notes.user_id"
            . " AND ("
                . " notes.id not in (SELECT DISTINCT note_id FROM note_entities) "
                . " OR us.user_id IN (SELECT ue.user_id FROM note_entities ne JOIN users_entities ue ON ne.item_id = ue.entity_id WHERE ne.note_id = " . $event->record_id . ")"
            . ")";
        if($notification->selected_status!=''){$status_tab=explode(",",$notification->selected_status);$status_str=implode("','",$status_tab); $where .= " AND lb.status in ('".$status_str."')";}
        break;
    
    case 'res_letterbox':
    case 'res_view_letterbox':
        echo $where;exit();
        $from .= " JOIN res_letterbox lb ON lb.res_id = li.res_id";
        $where .= " AND lb.res_id = " . $event->record_id ;
        if($notification->selected_status!=''){$status_tab=explode(",",$notification->selected_status);$status_str=implode("','",$status_tab); $where .= " AND lb.status in ('".$status_str."')";}
        break;
    
    case 'listinstance':
    default:
        //$where .= " AND listinstance_id = " . $event->record_id;
		$from .= " JOIN res_letterbox lb ON lb.res_id = li.res_id";
        $where .= " AND listinstance_id = " . $event->record_id;
        if($notification->selected_status!=''){$status_tab=explode(",",$notification->selected_status);$status_str=implode("','",$status_tab); $where .= " AND lb.status in ('".$status_str."')";}
    }

    $query = $select . $from . $where;
    
    if($GLOBALS['logger']) {
        $GLOBALS['logger']->write($query , 'DEBUG');
    }
	$dbRecipients->query($query);
	
	while($recipient = $dbRecipients->fetch_object()) {
		$recipients[] = $recipient;
	}
	break;

case 'attach':
	$attach = false;
	break;

case 'res_id':

    $select = "SELECT li.res_id";
    $from = " FROM listinstance li";
    $where = " WHERE li.coll_id = 'letterbox_coll'   ";
    
    switch($event->table_name) {
    case 'notes':
        $from .= " JOIN notes ON notes.coll_id = li.coll_id AND notes.identifier = li.res_id";
		$from .= " JOIN res_letterbox lb ON lb.res_id = notes.identifier";
		$where .= " AND notes.id = " . $event->record_id . " AND li.item_id != notes.user_id";
        if($notification->selected_status!=''){$status_tab=explode(",",$notification->selected_status);$status_str=implode("','",$status_tab); $where .= " AND lb.status in ('".$status_str."')";}
        break;
        
    case 'res_letterbox':
    case 'res_view_letterbox':
        $from .= " JOIN res_letterbox lb ON lb.res_id = li.res_id";
        $where .= " AND lb.res_id = " . $event->record_id;
        if($notification->selected_status!=''){$status_tab=explode(",",$notification->selected_status);$status_str=implode("','",$status_tab); $where .= " AND lb.status in ('".$status_str."')";}
        break;
    
    case 'listinstance':
    default:
        //$where .= " AND listinstance_id = " . $event->record_id;
		$from .= " JOIN res_letterbox lb ON lb.res_id = li.res_id";
        $where .= " AND listinstance_id = " . $event->record_id;
        if($notification->selected_status!=''){$status_tab=explode(",",$notification->selected_status);$status_str=implode("','",$status_tab); $where .= " AND lb.status in ('".$status_str."')";}
    }
    
    $query = $query = $select . $from . $where;
    
    if($GLOBALS['logger']) {
        $GLOBALS['logger']->write($query , 'DEBUG');
    }
	$dbResId = new dbquery();
    $dbResId->connect();
	$dbResId->query($query);
	$res_id_record = $dbResId->fetch_object();
    $res_id = $res_id_record->res_id;
    break;
    
}
?>
