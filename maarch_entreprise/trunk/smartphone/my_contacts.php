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
	contacts_v2.society as society, 
	contacts_v2.contact_id as contact_id,
	contacts_v2.is_corporate_person as is_corporate_person,
	contacts_v2.firstname as firstname,
	contacts_v2.lastname as lastname
	FROM " .APPS_CONTACTS_V2. "
	 ORDER BY contacts_v2.lastname, contacts_v2.society
	");
//$db->show();
$i=0;
$notesList = '';
if ($db->nb_result() < 1) {
    $notesList .= '<span id="noContacts">no contacts</span>';
} else {
	    while ($line = $db->fetch_object()) {
		if(($line->user_id == $_SESSION['user']['UserId']) || ($line->user_id == "")) {
			if($line->firstname != '' or $line->lastname != '') {
				$notesList .=		'<div class="row"  id="ViewContacts">
									<a href="contact_list.php?
										&is_corporate_person=' .$line->is_corporate_person. '
										&contact_id=' .$line->contact_id. '
										&user_id=' .$line->user_id. '">
																	
										<label><p>Particulier</p>'
												.$line->lastname. ' ' 
												.$line->firstname. '
										</label>
									</a>
									<table>
										<tr>
											<td align="left" width="70%"><a href="mailto:' .$line->email. '">' .$line->email. '</a></td>
											<td align="right"><a href="tel:' .$line->phone. '">' .$line->phone. '</a></td>
										</tr>
									</table>
								</div>
				';
			} else { var_dump($line->user_id);
				$notesList .=		'<div class="row"  id="ViewContacts">
									<a href="contact_list.php?
										&is_corporate_person=' .$line->is_corporate_person. '
										&contact_id=' .$line->contact_id. '">
																	
										<label><p>Collectivité</p>'
												.$line->society. '
										</label>
									</a>
								</div>
				';
			}
		}
    }
}
?>

<div id="contacts" title="<?php echo _MY_CONTACTS_MENU;?>" class="panel">

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


