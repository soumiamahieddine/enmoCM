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
    . $_SESSION['user']['UserId'] . "' and uc.group_id = u.group_id"
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
        . " e where ue.user_id ='".$_SESSION['user']['UserId'] 
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
?>
<div id="settings" title="<?php echo _MY_INFO;?>" class="panel">
    <h2 style="color: #58585A;"><i class="fa fa-user fa-2x" title="<?php echo _MY_INFO;?>"></i>&nbsp;</a></li><?php echo _MY_INFO;?></h2>
    <fieldset>
		<div class="row">
			<table>
				<tr>
					<td width="50%" align="left">
						<label><b>ID</b></label>
					</td>
					<td width="50%" align="right">
						<input type="text" name="userId" readonly="readonly" value="<?php echo $_SESSION['user']['UserId']; ?>" style="width:100%;padding:5px;"/>
					</td>
				</tr>
			</table>
		</div>
        <div class="row">
			<table>
				<tr>
					<td width="50%" align="left">
						<label><b><?php echo _LASTNAME ?></b></label>
					</td>
					<td width="50%" align="right">
						<input name="Lastname" readonly="readonly" value="<?php echo $core->show_string($_SESSION['user']['LastName']); ?>" style="width:100%;padding:5px;"/>
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
						<input name="Firstname" readonly="readonly" value="<?php echo $core->show_string($_SESSION['user']['FirstName']); ?>" style="width:100%;padding:5px;"/>
					</td>
				</tr>
			</table>
        </div>
        <!--div class="row">
            <label>Department</label>
            <input name="Department" readonly="readonly" value="<?php echo $core->show_string($_SESSION['user']['department']); ?>"/>
        </div-->
        <div class="row">
			<table>
				<tr>
					<td width="50%" align="left">
						<label><b><?php echo _PHONE_NUMBER ?></b></label>
					</td>
					<td width="50%" align="right">
						<input name="Phone" readonly="readonly" value="<?php echo $core->show_string($_SESSION['user']['Phone']); ?>" style="width:100%;padding:5px;"/>
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
						<input name="Mail" readonly="readonly" value="<?php echo $core->show_string($_SESSION['user']['Mail']); ?>" style="width:100%;padding:5px;"/>
					</td>
				</tr>
			</table>
        </div>
    </fieldset>
    <h2 style="color: #58585A;"><i class="fa fa-users fa-2x"></i>&nbsp;<?php echo _GROUPS;?></h2>
    <fieldset>
        <?php
        echo $groupList;
        ?>
    </fieldset>
    <?php
    if($core->is_module_loaded("entities")) {
        ?>
        <h2 style="color: #58585A;"><i class="fa fa-sitemap fa-2x"></i>&nbsp;<?php echo _ENTITIES;?></h2>
        <fieldset>
            <?php
            echo $entityList;
            ?>
        </fieldset>
        <?php
    }
    ?>
</div>
