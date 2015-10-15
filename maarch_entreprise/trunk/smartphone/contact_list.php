<?php
if (file_exists('../../../core/init.php')) {
    include_once '../../../core/init.php';
}
if (!isset($_SESSION['config']['coreurl'])) {
    header('location: ../../../');
}
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
require_once('core/class/class_db.php');
require_once('core/core_tables.php');
require_once('apps/maarch_entreprise/apps_tables.php');
require_once('modules/notes/notes_tables.php');
require_once('modules/notes/lang/fr.php');
$core = new core_tools();
$core->load_lang();
$db = new dbquery();
$db->connect();

$db->query(
    "SELECT 
    contact_addresses.lastname as lastname, 
	contact_addresses.firstname as firstname,
	contact_addresses.contact_id as contact_id,
	contact_addresses.is_private as is_private,
	contact_addresses.email as email,
	contact_addresses.phone as phone,
	contact_addresses.address_postal_code as address_postal_code,
	contact_addresses.id as id,
	contact_addresses.address_num as address_num,
	contact_addresses.address_street as address_street,
	contact_addresses.address_complement as address_complement,
	contact_addresses.address_town as address_town,
	contact_addresses.departement as address_departement,
	contact_addresses.address_country as address_country
	FROM " .APPS_CONTACTS_ADDRESSES. "
	WHERE contact_addresses.contact_id = (select contact_id from ".APPS_CONTACTS_V2." where contact_id = " .$_REQUEST['contact_id']. "
	)");
//$db->show();

$i=0;
$notesList = '';
if ($db->nb_result() < 1) {
    $notesList .= '<span id="noContacts">no contacts</span>';
} else { $notesList .= '<ul id="ok">';
    while ($line = $db->fetch_object()) { $society= $line->society;
		if(($line->user_id == $_SESSION['user']['UserId']) || ($line->user_id == "")) {
				$notesList .=		'<div class="row"  id="ViewContacts">
									<a href="contact_form.php?
										&contact_id=' .$line->contact_id. '
										&id=' .$line->id. '">
																	
										<label>'
												._LASTNAME.' :'.$line->lastname. '</br> ' 
												._FIRSTNAME.' :'.$line->firstname. '</br>'
												._ADDRESS.' :'.$line->address_num.' '.$line->address_street.' '.$line->address_postal_code.' '.$line->address_town. '</br>'	
												._COMPLEMENT.' :'.$line->address_complement. '</br>'
												._DEPARTEMENT.' :'.$line->address_departement. '</br>'
												. '
										</label>
									</a>
									<table>
										<tr>
											<td align="left" width="70%"><a href="mailto:' .$line->email. '">' .$line->email. '</a></td>
										</tr>
										<tr>
											<td align="left"><a href="tel:' .$line->phone. '">' .$line->phone. '</a></td>
										</tr>										
									</table>
								</div>
				';
		}
    }
$noteList .= '</ul>';
}
?>

<div id="contact2" title="<?php echo _MY_CONTACTS;?>" class="panel">
		<form selected="true" title="Addcontact" class="panel" method="post" action="add_contact.php">
			<a href="#" type="submit" style="text-decoration:none"><input type="submit" class="whiteButton" value="<?php echo _ADD_CONTACT ?>" >
			<input type="hidden" name="id" class="whiteButton" value="<?php echo $_REQUEST['contact_id']; ?>">
			<input type="hidden" name="society" class="whiteButton" value="<?php echo $society; ?>">
			</a>
		</form>
		<br/>
		<input name="contact" type="search" id="contact" placeholder="Rechercher" onKeyUp="searchContacts($('contact').value);" style="padding:5px;width:100%;"/>
		<br/><br/>
	<fieldset>
        <div id="allContacts">
            <?php
                echo $notesList;
            ?>
        </div>
    </fieldset>
</div>


