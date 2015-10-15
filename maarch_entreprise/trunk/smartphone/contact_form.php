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
    "SELECT contact_addresses.lastname as lastname, 
	contact_addresses.firstname as firstname,
	contact_addresses.contact_id as contact_id,
	contact_addresses.is_private as is_private,
	contact_addresses.email as email,
	contact_addresses.phone as phone,
	contact_addresses.address_num as address_num,
	contact_addresses.address_street as address_street,
	contact_addresses.address_postal_code as address_postal_code,
	contact_addresses.address_town as address_town,
	contact_addresses.address_country as address_country,
	contact_addresses.address_complement as address_complement
	FROM " .APPS_CONTACTS_ADDRESSES. "
	WHERE id =" .$_REQUEST['id']. "
	");
//$db->show();

$i=0;
$notesList = '';
if ($db->nb_result() < 1) {
    $notesList .= '<span id="noContacts">no contacts</span>';
} 
else {$notesList .= '<ul id="ok">';
    while($line = $db->fetch_object()) {
		?> <div id="settings" title="Profile" class="panel">
			<h2><img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=author.gif" alt=""/>&nbsp;</a>Infos</h2>
			<form selected="true" class="panel" method="post" action="query_modify_contact.php" target="_self">
			<fieldset>
					
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _LASTNAME ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="lastname"  value="<?php echo $line->lastname; ?>"/>
									</td>
								</tr>
							</table>
						</div>
					
					
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _FIRSTNAME ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="firstname" value="<?php echo $line->firstname; ?>"/>
									</td>
								</tr>
							</table>
						</div>
				
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _PHONE ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="phone" value="<?php echo $line->phone; ?>"/>
									</td>
								</tr>
							</table>
						</div>
					
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _EMAIL ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="email" value="<?php echo $line->email; ?>"/>
									</td>
								</tr>
							</table>
						</div>
					
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _ADDRESS_NUM ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="address_num" value="<?php echo $line->address_num; ?>"/>
									</td>
								</tr>
							</table>
						</div>
					
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _ADDRESS_STREET ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="address_street" value="<?php echo $line->address_street; ?>"/>
									</td>
								</tr>
							</table>
						</div>
					
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _ADDRESS_POSTAL_CODE ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="address_postal_code" value="<?php echo $line->address_postal_code; ?>"/>
									</td>
								</tr>
							</table>
						</div>
					
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _ADDRESS_TOWN ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="address_town" value="<?php echo $line->address_town; ?>"/>
									</td>
								</tr>
							</table>
						</div>
					
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _COUNTRY ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="address_country" value="<?php echo $line->address_country; ?>"/>
									</td>
								</tr>
							</table>
						</div>
					
					
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _ADDRESS_COMPLEMENT ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="address_complement" value="<?php echo $line->address_complement; ?>"/>
									</td>
								</tr>
							</table>
						</div>
					
			</fieldset>
			
				<a href="#" type="submit" style="text-decoration:none"><input type="submit" class="whiteButton" value="<?php echo _MODIFY ?>"></a>
				<input type="hidden" name="id" class="whiteButton" value="<?php echo $_REQUEST['id']; ?>">
			</form>
			<form selected="true" title="deleteContact" class="panel" method="post" action="query_delete_contact.php">
				<input type="hidden" name="id" class="whiteButton" value="<?php echo $_REQUEST['id']; ?>">
				<input type="submit" class="whiteButton" value="<?php echo _DELETE ?>">
			</form>
		</div>
						
	<?php
$noteList .= '</ul>';
    }
}