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
$core = new core_tools();
$core->load_lang();
$db = new dbquery();
$db->connect();
$db->query(
    "SELECT 
    contacts_v2.society as society,
    contact_addresses.lastname as lastname, 
	contact_addresses.firstname as firstname,
	contact_addresses.contact_purpose_id as contact_purpose_id,
	contact_purposes.label as label,
	contact_addresses.contact_id as contact_id,
	contact_addresses.is_private as is_private,
	contact_addresses.email as email,
	contact_addresses.phone as phone,
	contact_addresses.address_num as address_num,
	contact_addresses.address_street as address_street,
	contact_addresses.address_town as address_town,
	contact_addresses.address_postal_code as address_postal_code,
	contact_addresses.address_country as address_country,
	contact_addresses.address_complement as address_complement,
	contact_addresses.website as website,
	contact_addresses.departement as departement
	FROM " .APPS_CONTACTS_ADDRESSES.",".APPS_CONTACTS_V2.",".APPS_CONTACTS_PURPOSES."
	WHERE contact_addresses.id = ".$_REQUEST['id']. " and contact_addresses.contact_purpose_id=contact_purposes.id
	");
//$db->show();

$db2 = new dbquery();
$db2->connect();

$db2->query(
    "SELECT contact_purposes.id as id, contact_purposes.label as label from contact_purposes");

$notesList = '';
if ($db->nb_result() < 1) {
    $notesList = 'no contact or error query';
}
else {
    while($line = $db->fetch_object()) {
		?> <div id="settings2" title="Profile" class="panel">
				<h2><img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=author.gif" alt=""/>&nbsp;</a>Infos</h2>
				<form selected="true" class="panel" method="post" action="query_modify_contact.php" target="_self">
					<fieldset> 
							<div id="person2" class="row" style="display: block;">
								<div class="row">
									<table>
										<tr>
											<td width="50%" align="left">
												<label><b><?php echo _LASTNAME ?></b></label>
											</td>
											<td width="50%" align="right">
												<input id="lastname2" name="lastname" value="<?php echo $line->lastname; ?>"/>
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
												<input id="firstname2" name="firstname"  value="<?php echo $line->firstname; ?>"/>
											</td>
										</tr>
									</table>
								</div>
							</div>
	
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _SOCIETY ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="society" readonly="readonly" value="<?php echo $line->society; ?>"/>
									</td>
								</tr>
							</table>
						</div>
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _PHONE_NUMBER ?></b></label>
									</td>
									<td width="50%" align="right">
										<input type="tel" name="phone"  value="<?php echo $line->phone; ?>"/>
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
										<font>
											<input type="email" name="mail"  value="<?php echo $line->email; ?>"/>
										</font>
									</td>
								</tr>
							</table>
						</div>
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="center" colspan='2'>
										<label><b><?php echo _ADDRESS ?></b></label>
									</td>
								</tr>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _NUMBER ?></b></label>
									</td>
									<td width="50%" align="right">
										<input type="number" name="number"  value="<?php echo $line->address_num; ?>"/>
									</td>
								</tr>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _STREET ?></b></label>
									</td>
									<td width="50%" align="right">
										<font>
											<textarea name="street" rows="2"><?php echo $line->address_street; ?></textarea>
										</font>
									</td>
								</tr>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _COMPLEMENT ?></b></label>
									</td>
									<td width="50%" align="right">
										<font>
											<textarea name="complement" rows="2"><?php echo $line->address_complement; ?></textarea>
										</font>
									</td>
								</tr>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _TOWN ?></b></label>
									</td>
									<td width="50%" align="right">
										<font>
											<textarea name="town" rows="2"><?php echo $line->address_town; ?></textarea>
										</font>
									</td>
								</tr>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _POSTAL_CODE ?></b></label>
									</td>
									<td width="50%" align="right">
										<input type="number" name="postal_code" value="<?php echo $line->address_postal_code; ?>"/>
									</td>
								</tr>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _COUNTRY ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="country" value="<?php echo $line->address_country; ?>"/>
									</td>
								</tr>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo 'Département' ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="country" value="<?php echo $line->address_departement; ?>"/>
									</td>
								</tr>
							</table>
						</div>
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _CHOOSE_CONTACT_PURPOSES; ?></b></label>
									</td>
									<td width="50%" align="right">
										<SELECT NAME='contact_purpose_id'>
										<OPTION VALUE=<?php echo $line->contact_purpose_id. ">".$line->label ?> </OPTION>
										<?php 
										while ( $line = $db2->fetch_object()) {
											echo "<OPTION VALUE='".$line->id."'>".$line->label."</OPTION>";
										}
										?>
									</SELECT>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
					<input type="hidden" id="id" name="id" value="<?php echo $_REQUEST['id']; ?>">
					<input type="submit" value="<?php echo _MODIFY ?>" class="whiteButton">
				</form>
			</div>
	<?php
	}
}
	?>