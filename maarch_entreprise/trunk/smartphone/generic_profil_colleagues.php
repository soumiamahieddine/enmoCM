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
    "SELECT u.group_desc FROM " . USERGROUP_CONTENT_TABLE . " uc, "
    . USERGROUPS_TABLE ." u where uc.user_id ='"
    . $_REQUEST['user_id'] . "' and uc.group_id = u.group_id"
    . " order by u.group_desc"
);
$groupList = '';
if ($db->nb_result() < 1) {
    $groupList = 'no group';
} else {
    while ($line = $db->fetch_object()) {
        $groupList .= '<div class="row">
        <br>
            ' . $line->group_desc . '&nbsp;&nbsp;
        </div>';
    }
}
if($core->is_module_loaded("entities")) {
    $db->query(
        "SELECT e.entity_label FROM " . $_SESSION['tablename']['ent_users_entities'] 
        . " ue, ".$_SESSION['tablename']['ent_entities'] 
        . " e where ue.user_id ='".$_REQUEST['user_id'] 
        . "' and ue.entity_id = e.entity_id order by e.entity_label"
    );
    $entityList = '';
    if ($db->nb_result() < 1) {
        $entityList = 'no entity';
    } else {
        while ($line = $db->fetch_object()) {
            $entityList .= '<div class="row">
            <br>
                ' . $line->entity_label . '&nbsp;&nbsp;
            </div>';
        }
    }
}

if(isset($_REQUEST['lastname']) && isset($_REQUEST['firstname']) && isset($_REQUEST['mail']) && isset($_REQUEST['phone']) && isset($_REQUEST['user_id']))?> {
	<div id="settings" title="Profile" class="panel">
		<h2><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=author.gif" alt=""/>&nbsp;</a>Infos</h2>
		<fieldset>
			<div class="row">
				<table>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _LASTNAME ?></b></label>
						</td>
						<td width="50%" align="right">
							<input name="Lastname" readonly="readonly" value="<?php echo $_REQUEST['lastname']; ?>"/>
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
							<input name="Firstname" readonly="readonly" value="<?php echo $_REQUEST['firstname']; ?>"/>
						</td>
					</tr>
				</table>
			</div>
			<!--<div class="row">
				<table>
					<tr>
						<td width="50%" align="left">
							<label><b>Department</b></label>
						</td>
						<td width="50%" align="right">
							<input name="Department" readonly="readonly" value="<?php echo $_REQUEST['department']; ?>"/>
						</td>
					</tr>
				</table>
			</div>-->
			<div class="row">
				<table>
					<tr>
						<td width="50%" align="left">
							<label><b><?php echo _PHONE_NUMBER ?></b></label>
						</td>
						<td width="50%" align="left">
								<a href="tel: <?php echo '+'.$_REQUEST['phone'] ?>"><input name="Phone" readonly="readonly" value="<?php echo '+'.$_REQUEST['phone']; ?> "/></a>
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
						<td width="50%" align="left">
								<a href="mailto: <?php $_REQUEST['mail'] ?>"><input type="text" align="left" name="Mail" readonly="readonly" value="<?php echo $_REQUEST['mail']; ?> "/></a>
						</td>
					</tr>
				</table>
			</div>
		</fieldset>
		
		<h2><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=groupsmall.gif" alt=""/>&nbsp;Groups</h2>
		<fieldset>
			<?php
			echo $groupList;
			?>
		</fieldset>
		<?php
		if($core->is_module_loaded("entities")) {
			?>
			<h2><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=manage_entities_b_small.gif&module=entities" alt=""/>&nbsp;Entities</h2>
			<fieldset>
				<?php
				echo $entityList;
				?>
			</fieldset>
			<?php
		}
		?>
	</div>
}