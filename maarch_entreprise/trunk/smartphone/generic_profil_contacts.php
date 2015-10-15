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
    "SELECT	contacts.lastname as lastname, 
	contacts.firstname as firstname, 
	contacts.function as function, 	
	contacts.society as society, 
	contacts.function as function, 
	contacts.address_num as address_num, 
	contacts.address_street as address_street, 
	contacts.address_complement as address_complement, 
	contacts.address_town as address_town, 
	contacts.address_postal_code as address_postal_code, 
	contacts.address_country as address_country, 
	contacts.email as email, 
	contacts.phone as phone, 
	contacts.other_data as other_data, 
	contacts.title as title, 
	contacts.enabled as enabled 
	FROM " .APPS_CONTACTS. "
	WHERE contact_id =" .$_REQUEST['contact_id']. "
	");
//$db->show();
$notesList = '';
if ($db->nb_result() < 1) {
    $notesList = 'no contact or error query';
}
else {
    while($line = $db->fetch_object()) {
		?> <div id="settings" title="Profile" class="panel">
			<h2><img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=author.gif" alt=""/>&nbsp;</a>Infos</h2>
			<fieldset>
				<?php if( $_REQUEST['is_corporate_person']  == 'N') { ?>
					<?php if( $line->lastname  != "") { ?>
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _LASTNAME ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="lastname" readonly="readonly" value="<?php echo $line->lastname; ?>"/>
									</td>
								</tr>
							</table>
						</div>
					<?php } ?>
					<?php if( $line->firstname  != "") { ?>
						<div class="row">
							<table>
								<tr>
									<td width="50%" align="left">
										<label><b><?php echo _FIRSTNAME ?></b></label>
									</td>
									<td width="50%" align="right">
										<input name="firstname" readonly="readonly" value="<?php echo $line->firstname; ?>"/>
									</td>
								</tr>
							</table>
						</div>
					<?php } ?>
				<?php } ?>
				<?php if( $line->society  != "") { ?>
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
				<?php } ?>
				<?php if( $line->phone  != "") { ?>
					<div class="row">
						<table>
							<tr>
								<td width="50%" align="left">
									<label><b><?php echo _PHONE_NUMBER ?></b></label>
								</td>
								<td width="50%" align="right">
									<a href="tel: <?php echo $line->phone; ?>"><input name="Phone" readonly="readonly" value="<?php echo $line->phone; ?> "/></a>
								</td>
							</tr>
						</table>
					</div>
				<?php } ?>
				<?php if( $line->email  != "") { ?>
					<div class="row">
						<table>
							<tr>
								<td width="50%" align="left">
									<label><b><?php echo _EMAIL ?></b></label>
								</td>
								<td width="50%" align="right">
									<a href="mailto: <?php echo $line->email; ?>"><input type="email" name="Mail" readonly="readonly" value="<?php echo $line->email; ?> "/></a>
								</td>
							</tr>
						</table>
					</div>
				<?php } ?>
				<div class="row">
					<table>
						<tr>
							<td width="50%" align="left">
								<label><b><?php echo _ADDRESS ?></b></label>
							</td>
							<td width="50%" align="right">
								<textarea name="Address" readonly="readonly" rows="4" cols="25"><?php echo ($line->address_num . ' '
																						. $line->address_street . ' '
																						.$line->address_complement . ' '
																						. $line->address_postal_code . ' '
																						. $line->address_town . ' '
																						. $line->address_country); ?>
								</textarea>
							</td>
						</tr>
					</table>
				</div>
				<?php if( $line->other_data  != "") { ?>
					<div class="row">
						<table>
							<tr>
								<td width="50%" align="left">
									<label><b><?php echo _OTHER ?></b></label>
								</td>
								<td width="50%" align="right">
									<font>
										<textarea name="Other" readonly="readonly" rows="4" cols="25">
											<?php echo $line->other_data; ?>
										</textarea>
									</font>
								</td>
							</tr>
						</table>
					</div>
				<?php } ?>
			</fieldset>
			<form selected="true" title="modifyContact" class="panel" method="post" action="modify_contact.php">
				<a href="#" type="submit" style="text-decoration:none"><input class="whiteButton" value="<?php echo _MODIFY ?>"></a>
				<input type="hidden" name="contact_id" class="whiteButton" value="<?php echo $_REQUEST['contact_id']; ?>">
			</form>
			<form selected="true" title="deleteContact" class="panel" method="post" action="query_delete_contact.php">
				<input type="hidden" name="contact_id" class="whiteButton" value="<?php echo $_REQUEST['contact_id']; ?>">
				<input type="submit" class="whiteButton" value="<?php echo _DELETE ?>">
			</form>
		</div>
						
	<?php
    }
}
?>
