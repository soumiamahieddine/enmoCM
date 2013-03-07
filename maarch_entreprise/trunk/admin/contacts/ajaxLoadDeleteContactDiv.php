<?php

require_once('core/class/class_core_tools.php');
$Core_Tools = new core_tools;
$Core_Tools->load_lang();
$db = new dbquery();
$db->connect();
$return = '';
if ($_REQUEST['society_label'] <> '') {
    $selectDuplicates = "SELECT contact_id, society, lower(society) as lowsoc, "
        . "is_corporate_person, lastname, firstname, "
        . "address_num, address_street, address_town "
        . "from contacts "
        . "WHERE lower(society) in ("
        . "SELECT lower(society) FROM contacts GROUP BY lower(society) "
        . "     HAVING Count(*) > 1 and lower(society) <> '') and contact_id <> "
        . $_REQUEST['contact_id'] . " and lower(society) = '" 
        . mb_strtolower($db->protect_string_db($_REQUEST['society_label']), 'utf-8') . "' "
        . "order by lower(society)";
}
if ($_REQUEST['name'] <> '') {
    $selectDuplicates = "SELECT contact_id, lower(lastname||' '||firstname) as lastname_firstname, society, "
        . "is_corporate_person, lastname, firstname, "
        . "address_num, address_street, address_town "
        . "from contacts "
        . "WHERE lower(lastname||' '||firstname) in ("
        . "SELECT lower(lastname||' '||firstname) as lastname_firstname FROM contacts GROUP BY lastname_firstname "
        . "     HAVING Count(*) > 1 and lower(lastname||' '||firstname) <> ' ') and contact_id <> "
        . $_REQUEST['contact_id'] . " and lower(lastname||' '||firstname) = '" 
        . mb_strtolower($db->protect_string_db($_REQUEST['name']), 'utf-8') . "' "
        . "order by lower(lastname||' '||firstname)";
}
if (isset($_REQUEST['contact_id'])) {
    //test if res attached to the contact
    $query = "select res_id from res_view_letterbox where (exp_contact_id = " 
        . $_REQUEST['contact_id'] . " or dest_contact_id = " 
        . $_REQUEST['contact_id'] . ") and status <> 'DEL'";
    $db->query($query);
    $flagResAttached = false;
    $return_db = $db->fetch_object();
    if ($return_db->res_id <> '') {
        $flagResAttached = true;
        $db->query($selectDuplicates);
        //$db->show();
        $contactList = array();
        while($lineDoubl = $db->fetch_object()) {
            array_push($contactList, $lineDoubl->contact_id);
        }
    }
    if ($flagResAttached) {
        $return .= _RES_ATTACHED . ' ' . _SELECT_CONTACT_TO_REPLACE;
        $return .= '&nbsp;<select id="selectContact_'
            . $_REQUEST['contact_id'] . '" name="selectContact_'
            . $_REQUEST['contact_id'] . '">"';
        for ($cpt=0;$cpt<count($contactList);$cpt++) {
            $return .= '<option value="' . $contactList[$cpt] . '">' 
                . $contactList[$cpt] . '</option>';
        }
        $return .= '</select><br/>';
    }
    $return .= _ARE_YOU_SURE_TO_DELETE_CONTACT;
    if ($flagResAttached) {
        $return .= '&nbsp;<input type="button" value="' . _YES . '"'
            . ' onclick="deleteContact(' . $_REQUEST['contact_id'] 
            . ', $(\'selectContact_' . $_REQUEST['contact_id'] . '\').value);" />';
    } else {
        $return .= '&nbsp;<input type="button" value="' . _YES . '"'
            . ' onclick="deleteContact(' . $_REQUEST['contact_id'] 
            . ', false);" />';
    }
    $return .= '&nbsp;<input type="button" value="' . _NO . '"'
        . ' onclick="new Effect.toggle(\'deleteContactDiv_\'+' 
        . $_REQUEST['contact_id'] . ', \'blind\' , {delay:0.2});" />';
    
    $status = 0;
} else {
    $status = 1;
    $return .= '<td colspan="8" style="background-color: red;">';
        $return .= '<p style="padding: 10px; color: black;">';
            $return .= 'Error loading documents';
        $return .= '</p>';
    $return .= '</td>';
}

echo "{status : " . $status . ", toShow : '" . addslashes($return) . "'}";
exit ();
