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
require_once('apps/maarch_entreprise/lang/fr.php');

$core = new core_tools();
$core->load_lang();
$db = new dbquery();
$db->connect();

$db->query(
    "SELECT contact_purposes.id as id, contact_purposes.label as label from contact_purposes");
//$db->show();

$db2 = new dbquery();
$db2->connect();
$db2->query(
    "SELECT contacts_v2.contact_id as id, contacts_v2.society as society from contacts_v2 where contacts_v2.contact_id =" .$_REQUEST['id']. "      
    ");
//$db2->show();
?>

<div id="addContact" title="<?php echo _ADD_CONTACT ?>" class="panel">
	<h2><img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=author.gif" alt=""/>&nbsp;</a>Infos</h2>
	<form selected="true" class="panel" method="post" action="query_add_contact.php">
		<fieldset>
			<div class="row">
				<label><?php echo _IS_CORPORATE_PERSON; ?></label>
			<div id="person" class="row" style="display: block;">
				<div class="row">
					<table>
						<tr>
							<td width="50%" align="left">
								<label><b><?php echo _LASTNAME; ?></b></label>
							</td>
							<td width="50%" align="right">
								<input name="lastname" id="lastname" required/>
							</td>
						</tr>
					</table>
				</div>
				<div class="row">
					<table>
						<tr>
							<td width="50%" align="left">
								<label><b><?php echo _FIRSTNAME; ?></b></label>
							</td>
							<td width="50%" align="right">
								<input name="firstname" id="firstname"/>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="row">
				<table>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _SOCIETY; ?></b></label>
						</td>
						<?php while ( $line = $db2->fetch_object()) { $society = $line->society; } ?>
						<td width="50%" align="right">
							<input name=""  value="<?php echo  $society ?>" readonly="readonly" />
							<input hidden name="society"  value="<?php echo $_REQUEST['id']; ?>" readonly="readonly" />
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
						<OPTION VALUE=1>Choisissez</OPTION>
						<?php 
						while ( $line = $db->fetch_object()) {
							$mumDep = $line->id;
							$nomDep = $line->label;
							echo "<OPTION VALUE='".$line->id."'>".$line->label."</OPTION>";
						}
						?>
					</SELECT>
					</td>
				</tr>
			</table>
			</div>
			<div class="row">
				<table>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _PHONE_NUMBER; ?></b></label>
						</td>
						<td width="50%" align="right">
							<input type="tel" name="phone"/>
						</td>
					</tr>
				</table>
			</div>
			<div class="row">
				<table>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _EMAIL; ?></b></label>
						</td>
						<td width="50%" align="right">
							<font>
								<input type="email" name="mail"/>
							</font>
						</td>
					</tr>
				</table>
			</div>
			<div class="row">
				<table>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _WEBSITE; ?></b></label>
						</td>
						<td width="50%" align="right">
							<font>
								<input type="website" name="website"/>
							</font>
						</td>
					</tr>
				</table>
			</div>			
			<div class="row">
				<table>
					<tr>
						<td width="50%" align="center" colspan='2'>
							<label><b><?php echo _ADDRESS; ?></b></label>
						</td>
					</tr>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _NUMBER; ?></b></label>
						</td>
						<td width="50%" align="right">
							<input name="address_num"/>
						</td>
					</tr>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _STREET; ?></b></label>
						</td>
						<td width="50%" align="right">
							<font>
								<textarea name="address_street" rows="2"></textarea>
							</font>
						</td>
					</tr>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _DEPARTEMENT; ?></b></label>
						</td>
						<td width="50%" align="right">
							<font>
								<textarea name="departement" rows="2"></textarea>
							</font>
						</td>
					</tr>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _COMPLEMENT; ?></b></label>
						</td>
						<td width="50%" align="right">
							<font>
								<textarea name="address_complement" rows="2"></textarea>
							</font>
						</td>
					</tr>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _TOWN; ?></b></label>
						</td>
						<td width="50%" align="right">
							<font>
								<textarea name="address_town" rows="2"></textarea>
							</font>
						</td>
					</tr>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _POSTAL_CODE; ?></b></label>
						</td>
						<td width="50%" align="right">
							<input type="number" name="address_postal_code"/>
						</td>
					</tr>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _COUNTRY; ?></b></label>
						</td>
						<td width="50%" align="right">
							<input name="address_country" value="France"/>
						</td>
					</tr>
				</table>
			</div>
			<div class="row">
				<table>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _OTHER; ?></b></label>
						</td>
						<td width="50%" align="right">
							<font>
								<textarea name="other_data" rows="4"></textarea>
							</font>
						</td>
					</tr>
				</table>
			</div>
		</fieldset>
		<input type="submit" value="<?php echo _ADD ?>" class="whiteButton">
	</form>
	
</div>