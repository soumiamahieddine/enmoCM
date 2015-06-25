<?php
/**
*
*
* @since 04/2015
* @license GPL
* @author <dev@maarch.org>
*/

    $contactAddresses = array();
    $db = new dbquery();
    $db->connect();

	require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_contacts_v2.php");
	$contact = new contacts_v2();
	$core_tools = new core_tools('');
	$core_tools->test_user();

	$query = "SELECT ca.id, ca.lastname as ca_lastname, ca.firstname, ca.contact_purpose_id, cp.label 
				FROM ".$_SESSION['tablename']['contact_addresses']." ca
				LEFT JOIN contact_purposes cp on ca.contact_purpose_id = cp.id	
				WHERE ca.contact_id = ".$_POST['contact_id'];

	$query .= " order by ca_lastname";
	$db->query($query);
	  // $db->show();

	$listArray = array();
	while($line = $db->fetch_object())
	{
		$contactAddress = $contact->get_label_contact($line->contact_purpose_id, $_SESSION['tablename']['contact_purposes']);
		
		if ($line->ca_lastname <> "" || $line->firstname) {
			$contactAddress .= " :";
			if ($line->ca_lastname <> "") {
				$contactAddress .= " " . $line->ca_lastname;
			}
			if ($line->firstname <> "") {
				$contactAddress .= " " . $line->firstname;
			}
		}
		array_push($contactAddresses,array('contact_id' => $line->id,'name' => $contactAddress ));
	}

    $frmStr .= '<select name="selectContactAddress_'.$_POST['select'].'" id="selectContactAddress_'.$_POST['select'].'" > ';
	
    $countsContactAddress = count($contactAddresses);
	$frmStr .= '<option value="">Sélectionner une adresse</option>';  
    for ($cptsContacts = 0;$cptsContacts< $countsContactAddress;$cptsContacts++) {
	
            $frmStr .= '<option value="'.$contactAddresses[$cptsContacts]['contact_id'].'">'
		.  $db->show_string($contactAddresses[$cptsContacts]['name'])
		
		. '</option>';
    }
    $frmStr .= '</select></td>';
    
	echo $frmStr;
	