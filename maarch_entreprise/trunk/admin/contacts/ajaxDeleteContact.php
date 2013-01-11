<?php

require_once('core/class/class_core_tools.php');
$Core_Tools = new core_tools;
$Core_Tools->load_lang();
$db = new dbquery();
$db->connect();
$return = '';
$status = 0;
if (isset($_REQUEST['contactId'])) {
    $deleteContactQuery = "delete from contacts  where contact_id = " 
        . $_REQUEST['contactId'];
    //echo $_REQUEST['contactId'] . ' ' . $_REQUEST['replacedContactId'];
    if ($_REQUEST['replacedContactId'] == '') {
        $status = 1;
    } elseif (
        $_REQUEST['replacedContactId'] <> 'false' 
        && $_REQUEST['replacedContactId'] <> ''
    ) {
        $replaceQueryExpContact = "update mlb_coll_ext set exp_contact_id = " 
            . $_REQUEST['replacedContactId'] . " where exp_contact_id = "
            . $_REQUEST['contactId'];
        $db->query($replaceQueryExpContact);
        $replaceQueryDestContact = "update mlb_coll_ext set dest_contact_id = " 
            . $_REQUEST['replacedContactId'] . " where dest_contact_id = "
            . $_REQUEST['contactId'];
        $db->query($replaceQueryDestContact);
    }
    
    if ($status == 0) {
        $db->query($deleteContactQuery);
    }
}

echo "{status : " . $status . ", toShow : '" . addslashes($return) . "'}";
exit ();
