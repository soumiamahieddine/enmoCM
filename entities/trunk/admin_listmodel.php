<?php

require_once 'modules/entities/class/class_manage_listdiff.php';
$core_tools = new core_tools();

$admin = new core_tools();
$admin->test_admin('admin_listmodels', 'entities');
 /****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) &&($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
{
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=amdin_listmodel&module=entities';
$page_label = _ADMIN_LISTMODEL;
$page_id = "amdin_listmodel";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);

$listdiff = new diffusion_list();
$roles = $listdiff->get_listinstance_roles();
$listmodel_types = $listdiff->get_listmodel_types();
/*
$params ['mode']        : 'listmodel' or 'listinstance' (mandatory)
        ['table']       : table to update (mandatory)
        ['object_id']   : Object identifier linked to the diffusion list, entity identifier (mandatory if mode =  'listmodel')
        ['coll_id']     : Collection identifier (mandatory if mode = 'listinstance')
        ['res_id']      : Resource identifier (mandatory if mode = 'listinstance')
        ['user_id']     : User identifier of the person who add an item in the list
        ['concat_list'] : True or false (can be set only in 'listinstance' mode )*/

# Load listmodel into session
switch ($_REQUEST['mode']) {
case 'add':
    if(!isset($_SESSION['m_admin']['entity']['listmodel'])) {
        # First load (add action on list)
        $objectType = '';
        $objectId = '';
    } else {
        # list already loaded and managed (reload after update of list)
        $objectType = $_SESSION['m_admin']['entity']['listmodel_objectType'];
        $objectId = $_SESSION['m_admin']['entity']['listmodel_objectId'];
        $objectTypeLabel = $listmodel_types['objectType'];
    }
    break;
    
case 'up' :
    if(!isset($_SESSION['m_admin']['entity']['listmodel'])) {
        # First load (up action on list)
        $objectType = trim(strtok($_REQUEST['id'], ' '));
        $objectId = strtok(' ');
        $_SESSION['m_admin']['entity']['listmodel'] =  
            $listdiff->get_listmodel(
                $objectType,
                $objectId
            );
        $_SESSION['m_admin']['entity']['listmodel_objectType'] = $objectType;
        $_SESSION['m_admin']['entity']['listmodel_objectId'] = $objectId;
    } else {
        # list already loaded and managed (reload after update of list)
        $objectType = $_SESSION['m_admin']['entity']['listmodel_objectType'];
        $objectId = $_SESSION['m_admin']['entity']['listmodel_objectId'];
    }
    break;
}
?>
<h1 class="tit"><?php 
    echo _ADMIN_LISTMODEL;
    if($objectType) echo ' : ' . $objectTypeLabel;
    if($objectId) echo " " . $objectId;
    ?>
</h1>
<br/>
<h2><?php echo _ADMIN_LISTMODEL_TITLE; ?></h2>
<div id="listmodel_box" class="block">
	<h2 class="tit"><?php echo _LINKED_DIFF_LIST;?> : </h2><?php
    if($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']) { ?>
	<p class="sstit"><?php echo _RECIPIENT;?></p>
	<table cellpadding="0" cellspacing="0" border="0" class="listingsmall list_diff spec">
		<tr >
			<td>
                <img src="<?php echo $_SESSION['config']['businessappurl'].'static.php?filename=manage_users_entities_b_small.gif&module=entities';?>" alt="<?php echo _USER;?>" title="<?php echo _USER;?>" />
            </td>
			<td><?php echo $_SESSION['m_admin']['entity']['listmodel']['dest']['lastname'];?></td>
            <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['dest']['firstname'];?></td>
			<td><?php echo $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_label']; ?></td>
		</tr>
	</table>
	<br/> <?php 
    }
    foreach($roles as $role_id => $role_config) {
        if(count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']) > 0
            || count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']) > 0
        ) { ?>
            <h2 class="sstit"><?php echo $role_config['list_label'];?></h2>
            <table cellpadding="0" cellspacing="0" border="0" class="listingsmall liste_diff spec">
            <?php
            $color = ' class="col"';
            for($i=0, $l=count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']);
                $i<$l;
                $i++
            ) {
                if ($color == ' class="col"') $color = ' ';
                else $color = ' class="col"'; ?>
                <tr <?php echo $color; ?> >
                    <td>
                        <img src="<?php echo $_SESSION['config']['businessappurl'] ?>static.php?filename=manage_users_entities_b_small.gif&module=entities" alt="<?php echo _USER . " " . $list_config['role_label'] ;?>" title="<?php echo _USER . " " . $list_config['role_label'] ; ?>" />
                    </td>
                    <td ><?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$i]['lastname']; ?></td>
                    <td ><?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$i]['firstname'];?></td>
                    <td><?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$i]['entity_label']; ?></td>
                </tr> <?php
            }
            $color = ' class="col"';
            for ($i=0, $l=count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']);
                $i<$l;
                $i++
            ) {
                if ($color == ' class="col"') $color = '';
                else $color = ' class="col"';?>
                <tr <?php echo $color; ?> >
                    <td>
                        <img src="<?php echo $_SESSION['config']['businessappurl'] ?>static.php?filename=manage_entities_b_small.gif&module=entities" alt="<?php echo _ENTITY . " " . $list_config['role_label'] ;?>" title="<?php echo _ENTITY . " " . $list_config['role_label'] ; ?>" />
                    </td>
                    <td ><?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$i]['entity_id']; ?></td>
                    <td ><?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$i]['entity_label']; ?></td>
                    <td>&nbsp;</td>
                </tr> <?php
            } ?>
            </table>
            <br/> <?php
        }
    } ?>
	<p class="buttons">
		<input type="button" onclick="openListmodel()" class="button" value="<?php echo _MODIFY_LIST;?>" />
	</p>
</div> 
<div>
    <table width="50%">
        <tr height="20px;"/>
        <tr>
            <td width="33%">
                <label for="objectType" ><?php echo _OBJECT_TYPE; ?>: </label>
            </td>
            <td>
                <select id="objectType" <?php if($objectId) echo "disabled='true'"; ?> onChange="getObjectIdInput()" style="width:300px;">
                    <option value="" ><?php echo _SELECT_OBJECT_TYPE; ?></option><?php
                    foreach($listmodel_types as $listmodel_type_id => $listmodel_type_label) { ?>
                    <option value="<?php echo $listmodel_type_id; ?>" <?php if($objectType == $listmodel_type_id) echo "selected='true'"; ?> ><?php echo $listmodel_type_label; ?></option><?php
                    } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <label for="objectId" ><?php echo _ID; ?> : </label>
            </td>
            <td>
                <div id="objectId_input"><?php
                if(!$objectId) { ?>
                    <select id="objectId" style="width:300px;">
                        <option value=""><?php echo _SELECT_OBJECT_TYPE; ?></option>
                    </select><?php
                } else if($objectId) { ?>
                    <input type="text" id="objectId" readonly='true' style="width:300px;" value="<?php echo $objectId; ?>" /><?php
                } ?> 
                </div>
            </td>
        </tr>
    </table> 
    <br/>
    <br/>
    <p class="buttons"><?php
        if($objectId) { ?>
		<input type="button" onclick="saveListmodel();" class="button" value="<?php echo _SAVE_LISTMODEL;?>" /><?php
        } ?>
        <input type="button" onclick="goTo('index.php?module=entities&page=admin_listmodels');" class="button" value="<?php echo _CANCEL;?>" />
	</p>
</div>
<script type="text/javascript">
    function openListmodel()
{
    var main_error = $('main_error'); 
    var objectType = $('objectType').value; 
    var objectId = $('objectId').value; 

    if(objectType && objectId) {
        var idValid = isIdToken(objectId);
        if(idValid == false) {
            main_error.innerHTML = '<?php echo _OBJECT_ID_IS_NOT_VALID_ID; ?>';
            return;
        }
        main_error.innerHTML = '';
        window.open(
            '<?php echo $_SESSION['config']['businessappurl']?>index.php?display=true&module=entities&page=creation_listmodel&objectType='+objectType+'&objectId='+objectId,
            '', 
            'scrollbars=yes,menubar=no,toolbar=no,status=no,resizable=yes,width=1024,height=650,location=no'
        );
    }else 
        main_error.innerHTML = '<?php echo _SELECT_OBJECT_TYPE_AND_ID; ?>';
}
    
</script>

