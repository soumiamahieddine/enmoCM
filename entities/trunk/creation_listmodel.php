<?php
/**
* File : creation_listmodel.php
*
* Pop up used to create and modify diffusion lists models
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
* @author  Cyril Vazquez  <dev@maarch.org>
*/
require_once 'modules/entities/class/class_manage_listdiff.php';
require_once 'modules/entities/entities_tables.php';
require_once 'core/core_tables.php';

$core_tools = new core_tools();
$core_tools->load_lang();
$func = new functions();

$db = new dbquery();
$db->connect();

$difflist = new diffusion_list();
if (isset($_POST['valid'])) {
    $_SESSION['popup_suite'] = true;
    # Reload caller with new list in session ?>
    <script type="text/javascript">
        window.parent.opener.location.reload();
        self.close();
    </script>
    <?php
    exit;
}

# *****************************************************************************
# Search functions / filter users and entities avilable for list composition
# *****************************************************************************
if (isset($_POST['what_users']) && !empty($_POST['what_users']) ) {
    $_GET['what_users'] = $_POST['what_users'];
}
if (isset($_POST['what_services']) && ! empty($_POST['what_services']) ) {
    $_GET['what_services'] = $_POST['what_services'];
}
$users = array();
$entities = array();
if (isset($_GET['what_users']) 
    && ! empty($_GET['what_users']) 
) {
    $what_users = $func->protect_string_db(
        $func->wash($_GET['what_users'], 'no', '', 'no'));
    $user_expr = 
        " and ( "
            . "lower(u.lastname) like lower('%" . $what_users . "%') "
            . " or lower(u.firstname) like lower('%" . $what_users . "%') "
            . " or lower(u.user_id) like lower('%" . $what_users . "%')"
        . ")";
}
if (isset($_GET['what_services']) 
    && ! empty($_GET['what_services'])
) {
    $what_services = addslashes(
        $func->wash($_GET['what_services'], 'no', '', 'no')
    );
    $entity_expr = 
        " and ("
            . " lower(e.entity_label) like lower('%" . $what_services . "%') "
            . " or lower(e.entity_id) like lower('%" . $what_services . "%')"
        .")";
    
}
$users_query = 
    "select u.user_id, u.firstname, u.lastname, e.entity_id, e.entity_label "
    . "FROM " . $_SESSION['tablename']['users'] . " u, " . ENT_ENTITIES . " e, "
    . ENT_USERS_ENTITIES . " ue WHERE u.status <> 'DEL' and u.enabled = 'Y' and"
    . " e.entity_id = ue.entity_id and u.user_id = ue.user_id and"
    . " e.enabled = 'Y' " . $user_expr . $entity_expr
    . " order by u.user_id asc, u.lastname asc, u.firstname asc, e.entity_label asc";
    
$db->query($users_query);
while ($line = $db->fetch_object()) {
    array_push(
        $users,
        array(
            'ID'     => $db->show_string($line->user_id),
            'PRENOM' => $db->show_string($line->firstname),
            'NOM'    => $db->show_string($line->lastname),
            'DEP_ID' => $db->show_string($line->entity_id),
            'DEP'    => $db->show_string($line->entity_label)
        )
    );
}
$entity_query =
    "select e.entity_id,  e.entity_label FROM "
        . $_SESSION['tablename']['users'] . " u, " . ENT_ENTITIES . " e, "
        . ENT_USERS_ENTITIES . " ue WHERE u.status <> 'DEL' and u.enabled = 'Y'"
        . "and  e.entity_id = ue.entity_id and u.user_id = ue.user_id and "
        . "e.enabled = 'Y' " . $user_expr . $entity_expr
        . " group by e.entity_id, e.entity_label order by e.entity_label asc";
$db->query($entity_query);
while ($line = $db->fetch_object()) {
    array_push(
        $entities,
        array(
            'ID' => $db->show_string($line->entity_id),
            'DEP' =>$db->show_string($line->entity_label)
        )
    );
}

$origin = $_REQUEST['origin'];

$roles = $difflist->get_listinstance_roles();

# *****************************************************************************
# Manage request paramaters
# *****************************************************************************
// Object type
$objectType = $_REQUEST['objectType'];
$_SESSION['m_admin']['entity']['listmodel_objectType'] = $objectType;

   
// Object id
$objectId = $_REQUEST['objectId'];
$_SESSION['m_admin']['entity']['listmodel_objectId'] = $objectId;

// Action ?
if (isset($_GET['action']))
    $action = $_GET['action'];
else  
    $action = false;

// Id ?
if(isset($_GET['id']))
    $id = $_GET['id'];
else  
    $id = false;

// Rank for remove/move ?
if(isset($_GET['rank']))
    $rank = $_GET['rank'];
else
    $rank = false;

// Mode (dest/copy or custom copy mode)
if(isset($_GET['role']) && !empty($_GET['role']))
    $role_id = $_GET['role'];
else 
    $role_id = 'dest';

// Workflow mode    
$role_workflow_mode = $roles[$role_id]['workflow_mode'];     
    
// Dest user    
if(isset($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']) 
    && !empty($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']))
    $dest_is_set = true;
else
    $dest_is_set = false;

#****************************************************************************************
# SWITCH ON ACTION REQUEST
#**************************************************************************************** 
switch($action) {
// ADDS
//***************************************************************************************
// Add USER AS dest/copy/custom mode
case "add_user":
    $db->query(
        "SELECT u.firstname, u.lastname, e.entity_id, e.entity_label "
        . " FROM " . USERS_TABLE . " u "
        . " LEFT JOIN " . ENT_USERS_ENTITIES . " ue ON u.user_id = ue.user_id "
        . " LEFT JOIN " . ENT_ENTITIES . " e ON ue.entity_id = e.entity_id" 
        . " WHERE u.user_id='" . $db->protect_string_db($id) . "'"
    );
    $line = $db->fetch_object();
    # IF DEST, set unique dest
    if($role_id == 'dest') {
        if(! isset($_SESSION['m_admin']['entity']['listmodel']['dest']))
            $_SESSION['m_admin']['entity']['listmodel']['dest'] = array();
        $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'] = $db->show_string($id);
        $_SESSION['m_admin']['entity']['listmodel']['dest']['firstname'] = $db->show_string($line->firstname);
        $_SESSION['m_admin']['entity']['listmodel']['dest']['lastname'] = $db->show_string($line->lastname);
        $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_id'] = $db->show_string($line->entity_id);
        $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_label'] = $db->show_string($line->entity_label);
        $dest_is_set = true;
    } else {
    # OTHER ROLES
        if(! isset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']))
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'] = array();
        array_push(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'],
            array(
                'user_id' => $db->show_string($id),
                'firstname' => $db->show_string($line->firstname),
                'lastname' => $db->show_string($line->lastname),
                'entity_id' => $db->show_string($line->entity_id),
                'entity_label' => $db->show_string($line->entity_label),
            )
        ); 
    }
    break;

// ADD ENTITY AS copy/custom mode
case 'add_entity':
    $db->query(
        "SELECT entity_id, entity_label FROM " . ENT_ENTITIES
        . " WHERE entity_id = '" . $db->protect_string_db($id) . "'"
    );
    $line = $db->fetch_object();
    if(! isset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']))
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'] = array();
    array_push(
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'],
        array(
            'entity_id'    => $db->show_string($id),
            'entity_label' => $db->show_string($line->entity_label)
        )
    );
    break;    

// REMOVE
//***************************************************************************************
// Remove DEST
case 'remove_dest':
    unset($_SESSION['m_admin']['entity']['listmodel']['dest']);
    $dest_is_set = false;
    break;

// Remove USER
case 'remove_user':
    if($rank !== false && $id && $role_id
        && $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['user_id'] == $id
    ) {
        unset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]);
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'] = array_values(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']
        );
    }
    break;

// Remove ENTITY
case 'remove_entity':
    if($rank !== false && $id && $role_id
        && $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank]['entity_id'] == $id
    ) {
        unset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank]);
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'] = array_values(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']
        );
    }
    break;

// MOVE
//***************************************************************************************    
case 'dest_to_copy':
    if ($dest_is_set) {
        if(! isset($_SESSION['m_admin']['entity']['listmodel']['copy']['users']))
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'] = array();
        array_push(
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'],
            array(
                'user_id' => $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'],
                'firstname' => $_SESSION['m_admin']['entity']['listmodel']['dest']['firstname'],
                'lastname' => $_SESSION['m_admin']['entity']['listmodel']['dest']['lastname'],
                'entity_id' => $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_id'],
                'entity_label' => $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_label'],
            )
        );
        unset($_SESSION['m_admin']['entity']['listmodel']['dest']);
        $dest_is_set = false;
    }
    break;

case 'copy_to_dest':
    if ($dest_is_set) {
        if(! isset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']))
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'] = array();
        array_push(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'],
            array(
                'user_id' => $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'],
                'firstname' => $_SESSION['m_admin']['entity']['listmodel']['dest']['firstname'],
                'lastname' => $_SESSION['m_admin']['entity']['listmodel']['dest']['lastname'],
                'entity_id' => $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_id'],
                'entity_label' => $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_label'],
            )
        );
        unset($_SESSION['m_admin']['entity']['listmodel']['dest']);
        
    }
    if (isset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['user_id'])
        && !empty($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['user_id'])
    ) {
        $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'] = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['user_id'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['firstname'] = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['firstname'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['lastname'] = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['lastname'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_id'] = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['entity_id'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_label'] = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['entity_label'];
        unset( $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]);
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'] = array_values(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']
        );
        $dest_is_set = true;
    }
    break;    

case 'move_user_down':
    $downUser = 
        array_splice(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'], 
            $rank,
            1,
            $preserve_keys = true
        );
    $upUser = 
        array_splice(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'], 
            ($rank + 1),
            1,
            $preserve_keys = true
        );
    if($upUser[0] && $downUser[0]) {
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank] = $upUser[0];
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][($rank+1)] = $downUser[0];
    }
    break;

case 'move_entity_down':
    $downEntity = 
        array_splice(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'], 
            $rank,
            1,
            $preserve_keys = true
        );
    $upEntity = 
        array_splice(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'], 
            ($rank + 1),
            1,
            $preserve_keys = true
        );
    if($upEntity[0] && $downEntity[0]) {
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank] = $upEntity[0];
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][($rank+1)] = $downEntity[0];
    }
    break;
}
 
// 1.4 create indexed array of existing diffusion to search for users/entities easily
$indexed_diff_list = array();
if(isset($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'])) {
    $user_id = $_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'];
    $indexed_diff_list['users'][$user_id] = _PRINCIPAL_RECIPIENT;
}
foreach($roles as $role_id => $role_config) {
    for($i=0, $l=count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']); $i<$l; $i++) {
        $user_id = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$i]['user_id'];
        $indexed_diff_list['users'][$user_id] = $role_config['role_label'];
    }
    for($i=0, $l=count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']); $i<$l; $i++) {
        $entity_id = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$i]['entity_id'];
        $indexed_diff_list['entities'][$entity_id] = $role_config['role_label'];
    }
}

$core_tools->load_html();
$core_tools->load_header(_USER_ENTITIES_TITLE);
$time = $core_tools->get_session_time_expire();
$link = $_SESSION['config']['businessappurl']."index.php?display=true&module=entities&page=creation_listmodel";
$linkwithwhat =  
    $link 
    . '&what_users=' . $whatUsers 
    . '&what_services=' . $whatServices 
    . '&objectType=' . $objectType 
    . '&objectId=' . $objectId;
#******************************************************************************
# DISPLAY EXISTING LIST
#******************************************************************************
?>
<body onload="setTimeout(window.close, <?php echo $time;?>*60*1000);">
    <script type="text/javascript">
        function add_user(id) {
            user_id = $('user_id_' + id).value;
            role = $('user_role_' + id).value;
            goTo('<?php echo $linkwithwhat; ?>&action=add_user&id='+user_id+'&role='+role);        
        }
        function add_entity(id) {
            entity_id = $('entity_id_' + id).value;
            role = $('entity_role_' + id).value;
            goTo('<?php echo $linkwithwhat; ?>&action=add_entity&id='+entity_id+'&role='+role);        
        }
    </script>
    <br/>
    <div id="diff_list" align="center">
        <h2 class="tit"><?php echo _DIFFUSION_LIST . ' - '; 
        
            switch($objectType) {
            case "entity_id"     : echo _ENTITY;    break;
            case "type_id"       : echo _DOCTYPE;   break;
            case "foldertype_id" : echo _FOLDERTYPE;break;
            }
            echo ' ' . $objectId;
        ?></h2><?php 
        #**************************************************************************
        # DEST USER
        #**************************************************************************
        if(isset($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']) 
            && !empty($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'])
        ) { ?>
        <h2 class="sstit"><?php echo _PRINCIPAL_RECIPIENT;?></h2>
        <table cellpadding="0" cellspacing="0" border="0" class="listing spec">
            <tr>
                <td>
                    <img src="<?php echo $_SESSION['config']['businessappurl'].'static.php?filename=manage_users_entities_b.gif&module=entities';?>" alt="<?php echo _USER;?>" title="<?php echo _USER;?>" />
                </td>
                <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['dest']['lastname'];?></td>
                <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['dest']['firstname'];?></td>
                <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['dest']['entity_label']; ?></td>
                <td class="action_entities"><!-- Remove dest -->
                    <a href="<?php echo $linkwithwhat; ?>&action=remove_dest" class="delete"><?php echo _DELETE;?></a>
                </td>
                <td class="action_entities"><!-- Move dest to copy -->
                    <a href="<?php echo $linkwithwhat; ?>&action=dest_to_copy&role=copy" class="down"><?php echo _TO_CC;?></a>
                </td>
            </tr>
        </table><?php 
        } ?>
        <br/> <?php 
        #**************************************************************************
        # OTHER ROLES
        #**************************************************************************
        foreach($roles as $role_id => $role_config) {
            $workflow_mode = $role_config['workflow_mode'];
            if (count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']) > 0
             || count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']) > 0
            ) { ?>
                <h2 class="sstit"><?php echo $role_config['list_label'];?></h2>
                <table cellpadding="0" cellspacing="0" border="0" class="listing liste_diff spec"><?php
                #**************************************************************************
                # OTHER ROLE USERS
                #**************************************************************************
                $color = ' class="col"';
                for ($i=0, $l=count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']) ; $i<$l ; $i++) {
                    if ($color == ' class="col"') $color = '';
                    else $color = ' class="col"'; ?>
                    <tr <?php echo $color; ?> >
                        <td>
                            <img src="<?php echo $_SESSION['config']['businessappurl'] ?>static.php?filename=manage_users_entities_b.gif&module=entities" alt="<?php echo _USER . " " . $role_config['role_label'] ;?>" title="<?php echo _USER . " " . $role_config['role_label'] ; ?>" />
                        </td>
                        <td ><?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$i]['lastname']; ?></td>
                        <td ><?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$i]['firstname'];?></td>
                        <td><?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$i]['entity_label']; ?></td>
                        <td class="action_entities"><!-- Remove user -->
                            <a href="<?php echo $linkwithwhat; ?>&action=remove_user&role=<?php echo $role_id ?>&rank=<?php echo $i; ?>&id=<?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$i]['user_id'];?>" class="delete"><?php echo _DELETE; ?></a>
                        </td>
                        <td class="action_entities"><!-- Switch copy to dest --><?php
                        if($role_id == 'copy') { ?>
                            <a href="<?php echo $linkwithwhat;?>&action=copy_to_dest&role=copy&rank=<?php echo $i;?>" class="up"><?php echo _TO_DEST;?></a><?php
                        } ?>
                        </td>
                        <td class="action_entities"><!-- Move up in list --><?php 
                        if($l>1 && $i>0 && $workflow_mode == 'sequential') { ?>
                            <a href="<?php echo $linkwithwhat;?>&action=move_user_up&role=<?php echo $role_id ?>&rank=<?php echo $i;?>" class="up"></a><?php 
                        } ?>
                        </td>
                        <td class="action_entities"><!-- Move down in list --><?php 
                        if($l>1 && $i<($l-1) && $workflow_mode == 'sequential') { ?>
                            <a href="<?php echo $linkwithwhat;?>&action=move_user_down&role=<?php echo $role_id ?>&rank=<?php echo $i;?>" class="down"></a><?php 
                        } ?>
                        </td>
                    </tr> <?php
                }
                #**************************************************************************
                # OTHER ROLE ENTITIES
                #**************************************************************************
                for($i=0, $l=count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']); 
                    $i<$l;
                    $i++
                ) {
                    if ($color == ' class="col"') $color = '';
                    else $color = ' class="col"'; ?>
                    <tr <?php echo $color; ?> >
                        <td>
                            <img src="<?php echo $_SESSION['config']['businessappurl'] ?>static.php?filename=manage_entities_b.gif&module=entities" alt="<?php echo _ENTITY . " " . $role_config['role_label'] ;?>" title="<?php echo _ENTITY . " " . $role_config['role_label'] ; ?>" />
                        </td>
                        <td ><?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$i]['entity_id']; ?></td>
                        <td ><?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$i]['entity_label']; ?></td>
                        <td>&nbsp;</td>
                        <td class="action_entities">
                            <a href="<?php echo $linkwithwhat; ?>&action=remove_entity&role=<?php echo $role_id ?>&rank=<?php echo $i; ?>&id=<?php echo $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$i]['entity_id'];?>" class="delete">
                                <?php echo _DELETE; ?>
                            </a>
                        </td>
                        <td class="action_entities">&nbsp;</td>
                        <td class="action_entities"><!-- Move up in list --><?php 
                        if($l>1 && $i>0 && $workflow_mode == 'sequential') { ?>
                            <a href="<?php echo $linkwithwhat;?>&action=move_entity_up&role=<?php echo $role_id ?>&rank=<?php echo $i;?>" class="up"></a><?php 
                        } ?>
                        </td>
                        <td class="action_entities"><!-- Move down in list --><?php 
                        if($l>1 && $i<($l-1) && $workflow_mode == 'sequential') { ?>
                            <a href="<?php echo $linkwithwhat;?>&action=move_entity_down&role=<?php echo $role_id ?>&rank=<?php echo $i;?>" class="down"></a><?php 
                        } ?>
                        </td>
                    </tr><?php
                } ?>
                </table>
                <br/><?php
            }
        }
        #******************************************************************************
        # LIST LINK WITH OBJECT + VALIDATION
        #******************************************************************************?>      
        <form name="pop_diff" method="post" >
            <div align="center"> <?php
                # Mode dest + copy but no dest : can't save
                if((empty($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'])
                    && (count($_SESSION['m_admin']['entity']['listmodel']['copy']['entities']) > 0
                        || count($_SESSION['m_admin']['entity']['listmodel']['copy']['users']) > 0)
                    )
                    || count($_SESSION['m_admin']['entity']['listmodel']) == 0
                ) { ?>
                    <div class="error"><?php echo _MUST_CHOOSE_DEST; ?></div>
                    <?php 
                }
                else { ?>
                    <input align="middle" type="submit" value="<?php echo _VALIDATE;?>" class="button" name="valid"  /><?php
                } ?>
                <input align="middle" type="button" value="<?php echo _CANCEL;?>"  onclick="self.close();" class="button"/>
            </div>
        </form>
        <br/>
        <br/><?php
        #******************************************************************************
        # LIST OF AVAILABLE ENTITIES / USERS
        #******************************************************************************?>
        <hr align="center" color="#6633CC" size="5" width="60%">
        <div align="center">
            <form action="#" name="search_diff_list" >
                <input type="hidden" name="display" value="true" />
                <input type="hidden" name="module" value="entities" />
                <input type="hidden" name="page" value="creation_listmodel" />
                <input type="hidden" name="origin" id="origin" value="<?php echo $origin; ?>" />
                <table cellpadding="2" cellspacing="2" border="0">
                    <tr>
                        <th>
                            <label for="what_users" class="bold"><?php echo _USER;?></label>
                        </th>
                        <th>
                            <input name="what_users" id="what_users" type="text" <?php if(isset($_GET["what_users"])) echo "value ='".$_GET["what_users"]."'"; ?> />
                        </th>
                    </tr>
                    <tr>
                        <th>
                            <label for="what_services" class="bold"><?php echo _DEPARTMENT;?></label>
                        </th>
                        <th>
                            <input name="what_services" id="what_services" type="text" <?php if(isset($_GET["what_services"])) echo "value ='".$_GET["what_services"]."'"; ?>/>
                        </th>
                    </tr>
                </table>
            </form>     
        </div> 
        <script type="text/javascript">
            repost('<?php echo $link;?>',new Array('diff_list_items'),new Array('what_users','what_services'),'keyup',250);
        </script>
        <br/>
        <div id="diff_list_items"> <?php
        #******************************************************************************
        # LIST OF AVAILABLE USERS
        #******************************************************************************?> 
            <div align="center">
                <h2 class="tit"><?php echo _USERS_LIST;?></h2>
                <table cellpadding="0" cellspacing="0" border="0" class="listing spec">
                    <thead>
                        <tr>
                            <th ><?php echo _LASTNAME;?> </th>
                            <th ><?php echo _FIRSTNAME;?></th>
                            <th><?php echo _DEPARTMENT;?></th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead><?php 
                    $color = ' class="col"';
                    for($j=0, $m=count($users);
                        $j<$m; 
                        $j++
                    ) {
                        $user_id = $users[$j]['ID'];
                        if(isset($indexed_diff_list['users'][$user_id]))
                            $already_in_list_as = $indexed_diff_list['users'][$user_id];
                        else 
                            $already_in_list_as = false;
                            
                        if ($color == ' class="col"') $color = '';
                        else $color = ' class="col"'; ?>
                        <tr <?php echo $color; ?> id="user_<?php echo $j; ?>">
                        <td><?php echo $users[$j]['NOM']; ?></td>
                        <td><?php echo $users[$j]['PRENOM']; ?></td>
                        <td><?php echo $users[$j]['DEP'];?></td>
                        <td class="action_entities"><?php
                        if($already_in_list_as) {
                            echo $already_in_list_as;
                        } else { ?>
                            <input type="hidden" id="user_id_<?php echo $j; ?>" value="<?php echo $users[$j]['ID'];?>" />
                            <select name="role" id="user_role_<?php echo $j; ?>"><?php
                            if(!$dest_is_set) { ?>
                                <option value="dest"><?php echo _DEST; ?></option><?php
                            }
                            foreach($roles as $role_id => $role_config) { ?>
                                <option value="<?php echo $role_id; ?>"><?php echo $role_config['role_label']; ?></option><?php 
                            } 
                            if($dest_is_set) { ?>
                                <option value="dest"><?php echo _DEST; ?></option><?php
                            } ?>
                            </select>&nbsp;
                            <span onclick="add_user(<?php echo $j; ?>);" class="change"/> 
                                <?php echo _ADD;?>
                            </span><?php 
                        } ?>
                        </td>
                    </tr><?php 
                    } ?>
                </table>
                <br/>
            </div> <?php
            #******************************************************************************
            # LIST OF AVAILABLE ENTITIES
            #****************************************************************************** ?>
            <div align="center"> 
                <h2 class="tit"><?php echo _ENTITIES_LIST;?></h2>
                <table cellpadding="0" cellspacing="0" border="0" class="listing spec">
                    <thead>
                        <tr>
                            <th><?php echo _ID;?></th>
                            <th><?php echo _LABEL;?></th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead><?php 
                $color = ' class="col"';
                for ($j=0, $m=count($entities); $j<$m ; $j++) {
                    $entity_id = $entities[$j]['ID'];
                    if(isset($indexed_diff_list['entities'][$entity_id]))
                        $already_in_list_as = $indexed_diff_list['entities'][$entity_id];
                    else 
                        $already_in_list_as = false;
                        
                    if($color == ' class="col"') $color = '';
                    else $color = ' class="col"'; ?>
                    <tr <?php echo $color; ?>>
                        <td><?php echo $entities[$j]['ID'];?></td>
                        <td><?php echo $entities[$j]['DEP']; ?></td>
                        <td class="action_entities"><?php
                        if($already_in_list_as) {
                            echo $already_in_list_as;
                        } else { ?>
                            <input type="hidden" id="entity_id_<?php echo $j; ?>" value="<?php echo $entities[$j]['ID'];?>" />
                            <select name="role" id="entity_role_<?php echo $j; ?>"><?php 
                            foreach($roles as $role_id => $role_config) { 
                                if($role_config['allow_entities']) { ?>
                                    <option value="<?php echo $role_id; ?>"><?php echo $role_config['role_label']; ?></option><?php 
                                }
                            } ?>
                            </select>&nbsp;
                            <span onclick="add_entity(<?php echo $j; ?>);" class="change"/> 
                                <?php echo _ADD;?>
                            </span> <?php 
                        } ?>
                
                        </td>
                    </tr> <?php
                }?>
                </table>
            </div>
        </div>  
    </div>
</body>
</html>
