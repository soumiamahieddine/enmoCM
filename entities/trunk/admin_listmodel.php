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

$difflist = new diffusion_list();
$roles = $difflist->get_workflow_roles();
$objectTypes = $difflist->get_listmodel_types();

# Load listmodel into session
/*
var_dump($_SESSION['m_admin']['entity']['listmodel']);
var_dump($_SESSION['m_admin']['entity']['listmodel_info']);
;*/

if(!isset($_SESSION['m_admin']['entity']['listmodel'])) {
    # Listmodel to be loaded (up action on list or reload in add mode)
    $objectType = trim(strtok($_REQUEST['id'], '|'));
    $objectId = strtok('|');
    
    $_SESSION['m_admin']['entity']['listmodel_info'] =
        $difflist->select_listmodel(
            $objectType,
            $objectId
        );
    
    $collId = $_SESSION['m_admin']['entity']['listmodel_info']['coll_id'];
    $listmodelType = $_SESSION['m_admin']['entity']['listmodel_info']['listmodel_type'];
    $description =  $_SESSION['m_admin']['entity']['listmodel_info']['description'];
    
    $_SESSION['m_admin']['entity']['listmodel'] =  
        $difflist->get_listmodel(
            $objectType,
            $objectId
        );
} else {
    # list already loaded and managed (reload after update of list)
    $objectType = $_SESSION['m_admin']['entity']['listmodel_info']['object_type'];
    $objectId = $_SESSION['m_admin']['entity']['listmodel_info']['object_id'];
    $collId = $_SESSION['m_admin']['entity']['listmodel_info']['coll_id'];
    $listmodelType = $_SESSION['m_admin']['entity']['listmodel_info']['listmodel_type'];
    $description = $_SESSION['m_admin']['entity']['listmodel_info']['description'];
}

$objectTypeLabel = $objectTypes[$objectType];

# JAVASCRIPT 
# *****************************************************************************
?>
<script type="text/javascript">
// OnChange ObjectType / onLoad
//   set value / input mode for object id
function listmodel_setObjectId(objectId) 
{
    var mode = $('mode').value;
    
    var objectType = $('objectType').value;
    var objectId_input = $('objectId_input');
			
    new Ajax.Request(
        'index.php?display=true&module=entities&page=admin_listmodel_setObjectId',
        {
            method:'post',
            parameters: 
			{ 
				mode : mode,
                objectType : objectType,
				objectId : objectId
			},
            onSuccess: function(answer){
                objectId_input.innerHTML = answer.responseText;
                objectId_input.style.display = 'block';
            }
        }
    );

}
 
function listmodel_open()
{
    var main_error = $('main_error'); 
    
    // Validate form
    var valid = listmodel_validate();
    
    if(valid == false)
        return;
    
    // Open pop up 
    window.open(
        'index.php?display=true&module=entities&page=creation_listmodel',
        '', 
        'scrollbars=yes,menubar=no,toolbar=no,status=no,resizable=yes,width=1024,height=650,location=no'
    );
                    
    
}

function listmodel_validate() {
    // Control input values
    var main_error = $('main_error'); 
       
    var mode = $('mode').value; 
    var objectType = $('objectType').value; 
    var objectId = $('objectId').value; 
    var collId = $('collId').value; 
    var listmodelType = $('listmodelType').value; 
    var description = $('description').value; 
    
    main_error.innerHTML = "";
    
    new Ajax.Request(
        'index.php?display=true&module=entities&page=admin_listmodel_validateHeader',
        {
            method:'post',
            asynchronous:false,
            parameters: 
			{ 
				mode : mode,
                objectType : objectType,
                objectId : objectId,
                collId : collId,
                listmodelType : listmodelType, 
                description : description 
			},
            onSuccess: function(answer) {
                if(answer.responseText) {
                    main_error.innerHTML += answer.responseText;
                    this.valid = false;
                } else {
                    this.valid = true;
                }
            }
        }
    ); 
    return this.valid;
}

function listmodel_save()
{
    var mode = $('mode').value;  
    var objectType = $('objectType').value; 
    var objectId = $('objectId').value; 
    var collId = $('collId').value; 
    var listmodelType = $('listmodelType').value; 
    var description = $('description').value; 
    
    // Validate form
    var valid = listmodel_validate();
    if(valid == false)
        return;
    
    // Check if type/id already used
    new Ajax.Request(
        'index.php?display=true&module=entities&page=admin_listmodel_save',
        {
            method:'post',
            parameters: 
			{ 
				mode : mode,
                objectType : objectType,
                objectId : objectId,
                collId : collId,
                listmodelType : listmodelType,
                description : description
			},
            onSuccess: function(answer){
                if(answer.responseText)
                    main_error.innerHTML = answer.responseText;
                else {
                    goTo('index.php?module=entities&page=admin_listmodels');
                }
            }
        }
    ); 
}

function listmodel_del(
    objectType,
    objectId,
    collId,
    listmodelType
) {    
    new Ajax.Request(
        'index.php?display=true&module=entities&page=admin_listmodel_save',
        {
            method:'post',
            parameters: 
            { 
                mode : 'del',
                objectType : objectType,
                objectId : objectId,
                collId : collId,
                listmodelType : listmodelType
            },
            onSuccess: function(answer){
                if(answer.responseText)
                    main_error.innerHTML = answer.responseText;
                else {
                    goTo('index.php?module=entities&page=admin_listmodels');
                }
            }
        }
    ); 

}
    
</script><?php
if($_REQUEST['mode'] != 'del') { ?>
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
    foreach($roles as $role_id => $role_label) {
        if(count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']) > 0
            || count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']) > 0
        ) { ?>
            <h2 class="sstit"><?php echo $role_label;?></h2>
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
		<input type="button" onclick="listmodel_open()" class="button" value="<?php echo _MODIFY_LIST;?>" />
	</p>
</div> 
<div>
    <table width="50%">
        <tr height="20px;">
            <td>
                <input type="hidden" id="mode" value="<?php echo $_REQUEST['mode']; ?>" />
            </td>
        </tr>
        <tr>
            <td width="33%">
                <label for="objectType" ><?php echo _OBJECT_TYPE; ?>: </label>
            </td>
            <td>
                <select id="objectType" onChange="listmodel_setObjectId(false);" style="width:300px;">
                    <option value="" ><?php echo _SELECT_OBJECT_TYPE; ?></option><?php
                    foreach($objectTypes as $objectTypeId => $objectTypeLabel) { ?>
                    <option value="<?php echo $objectTypeId; ?>" <?php if($objectType == $objectTypeId) echo "selected='true'"; ?> ><?php echo $objectTypeLabel; ?></option><?php
                    } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <label for="objectId" ><?php echo _ID; ?> : </label>
            </td>
            <td>
                <div id="objectId_input"></div>
            </td>
        </tr>
        <tr>
            <td>
                <label for="description" ><?php echo _DESCRIPTION; ?> : </label>
            </td>
            <td>
                <textarea id="description"><?php echo $description; ?></textarea>
            </td>
        </tr>
        <tr style="display:none;">
            <td >
                <label for="collId" ><?php echo _COLL_ID; ?>: </label>
            </td>
            <td>
                <select id="collId" style="width:300px;">
                    <option value="any" ><?php echo _SELECT_COLL_ID; ?></option><?php
                    foreach($_SESSION['collections'] as $collection) { ?>
                    <option value="<?php echo $collection['id']; ?>" <?php if($collId == $collection['id']) echo "selected='true'"; ?> ><?php echo $collection['label']; ?></option><?php
                    } ?>
                </select>
            </td>
        </tr>
        <tr style="display:none;" >
            <td >
                <label for="listmodelType" ><?php echo _RES_TYPE; ?>: </label>
            </td>
            <td>
                <select id="listmodelType" style="width:300px;">
                    <option value="DOC" <?php if($listmodelType == 'DOC') echo "selected='true'"; ?> ><?php echo _DOCUMENT; ?></option>
                    <option value="FLD" <?php if($listmodelType == 'FLD') echo "selected='true'"; ?> ><?php echo _FOLDER; ?></option>
                </select>
            </td>
        </tr>
    </table> 
    <br/>
    <br/>
    <p class="buttons"><?php
        if($objectType && $objectId && $collId) { ?>
		<input type="button" onclick="listmodel_save();" class="button" value="<?php echo _SAVE_LISTMODEL;?>" /><?php
        } ?>
        <input type="button" onclick="goTo('index.php?module=entities&page=admin_listmodels');" class="button" value="<?php echo _CANCEL;?>" />
	</p>
</div>
<script type="text/javascript">
    // OnLoad : set object id and label
    listmodel_setObjectId('<?php echo $objectId?>');
</script><?php
}
# DEL => REDIRECT TO AJAX SAVE
# *****************************************************************************
if($_REQUEST['mode'] == 'del') {
?>
    <script type="text/javascript">
        listmodel_del(
            '<?php echo $objectType?>',
            '<?php echo $objectId?>',
            '<?php echo $collId?>',
            '<?php echo $listmodelType?>'
        );
    </script><?php
}

