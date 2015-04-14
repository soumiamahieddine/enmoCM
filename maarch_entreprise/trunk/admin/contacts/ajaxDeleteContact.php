<?php

require_once('core/class/class_core_tools.php');
$Core_Tools = new core_tools;
$Core_Tools->load_lang();
$db = new dbquery();
$db->connect();
$return = '';
$status = 0;
if (isset($_REQUEST['contactId'])) {
    $deleteContactQuery = "DELETE FROM contacts_v2 WHERE contact_id = " 
        . $_REQUEST['contactId'];
    //echo $_REQUEST['contactId'] . ' ' . $_REQUEST['replacedContactId'];
    if ($_REQUEST['replacedContactId'] == '') {
        $status = 1;
    } elseif ($_REQUEST['replacedAddressId'] == '') {
        $status = 1;
    } elseif (
        $_REQUEST['replacedContactId'] <> 'false' 
        && $_REQUEST['replacedContactId'] <> ''
        && $_REQUEST['replacedAddressId'] <> 'false' 
        && $_REQUEST['replacedAddressId'] <> ''
    ) {
        $replaceQueryExpContact = "UPDATE mlb_coll_ext SET exp_contact_id = " 
            . $_REQUEST['replacedContactId'] . ", address_id = " . $_REQUEST['replacedAddressId'] . " WHERE exp_contact_id = "
            . $_REQUEST['contactId'];
        $db->query($replaceQueryExpContact);
        $replaceQueryDestContact = "UPDATE mlb_coll_ext SET dest_contact_id = " 
            . $_REQUEST['replacedContactId'] . ", address_id = " . $_REQUEST['replacedAddressId'] . " WHERE dest_contact_id = "
            . $_REQUEST['contactId'];
        $db->query($replaceQueryDestContact);
        $db->query("UPDATE contacts_res SET contact_id = '".$_REQUEST['replacedContactId']."', address_id = ".$_REQUEST['replacedAddressId']." WHERE contact_id = '".$_REQUEST['contactId']."'");
    }
    
    if ($status == 0) {
        $db->query($deleteContactQuery);
        $db->query("DELETE FROM contact_addresses WHERE contact_id = ".$_REQUEST['contactId']);
    }
}

echo "{status : " . $status . ", toShow : '" . addslashes($return) . "'}";
exit ();
