<?php
/**
* File : manage_listmodel.php
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
require_once 'core/class/usergroups_controler.php';
require_once 'modules/entities/class/class_manage_listdiff.php';
require_once 'modules/entities/entities_tables.php';
require_once 'core/core_tables.php';

$core_tools = new core_tools();
$core_tools->load_lang();
$func = new functions();

$db = new dbquery();
$db->connect();

$difflist = new diffusion_list();
$usergroups_controler = new usergroups_controler();

# *****************************************************************************
# Manage request paramaters
# *****************************************************************************
// Origin not used
$origin = $_REQUEST['origin'];

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
    $role_id = false;
# *****************************************************************************
# Manage SESSION paramaters
# *****************************************************************************
// Object/list type
$objectType = $_SESSION['m_admin']['entity']['listmodel']['object_type'];
$objectId = $_SESSION['m_admin']['entity']['listmodel']['object_id'];
$description = $_SESSION['m_admin']['entity']['listmodel']['description'];

# Load roles
$difflistType = $difflist->get_difflist_type($objectType);
$roles = $difflist->get_difflist_type_roles($difflistType);

if($difflistType->allow_entities == 'Y')
    $allow_entities = true;
else 
    $allow_entities = false;
   
// Dest user    
if(isset($_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]) 
    && !empty($_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]))
    $dest_is_set = true;
else
    $dest_is_set = false;
    
// 1.4 create indexed array of existing diffusion to search for users/entities easily
$user_roles = array();
$entity_roles = array();
foreach($roles as $role_id_local => $role_label) {
    for($i=0, $l=count($_SESSION['m_admin']['entity']['listmodel'][$role_id_local]['users']); 
        $i<$l; $i++
    ) {
        $user_id = $_SESSION['m_admin']['entity']['listmodel'][$role_id_local]['users'][$i]['user_id'];
        $user_roles[$user_id][] = $role_id_local;
    }
    for($i=0, $l=count($_SESSION['m_admin']['entity']['listmodel'][$role_id_local]['entities']); 
        $i<$l; 
        $i++
    ) {
        $entity_id = $_SESSION['m_admin']['entity']['listmodel'][$role_id_local]['entities'][$i]['entity_id'];
        $entity_roles[$entity_id][] = $role_id_local;
    }
}
# *****************************************************************************
# Search functions / filter users and entities avilable for list composition
# *****************************************************************************
if (isset($_POST['what_users']) && !empty($_POST['what_users'])) {
    $_GET['what_users'] = $_POST['what_users'];
}
if (isset($_POST['what_services']) && ! empty($_POST['what_services']) ) {
    $_GET['what_services'] = $_POST['what_services'];
}
if (isset($_REQUEST['no_filter'])) {
    $_GET['what_users'] = '%';
    $_GET['what_services'] = '%';
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
    . " e.enabled = 'Y' and ue.primary_entity='Y' " . $user_expr . $entity_expr
    . " order by u.lastname asc, u.firstname asc, u.user_id asc, e.entity_label asc";

if ($user_expr == '' && $entity_expr == '') {
    //no query
} else {
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
}

$entity_query =
    "select e.entity_id,  e.entity_label FROM "
        . $_SESSION['tablename']['users'] . " u, " . ENT_ENTITIES . " e, "
        . ENT_USERS_ENTITIES . " ue WHERE u.status <> 'DEL' and u.enabled = 'Y'"
        . "and  e.entity_id = ue.entity_id and u.user_id = ue.user_id and "
        . "e.enabled = 'Y' " . $user_expr . $entity_expr
        . " group by e.entity_id, e.entity_label order by e.entity_label asc";

if ($user_expr == '' && $entity_expr == '') {
    //no query
} else {
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
}
#****************************************************************************************
# RELOAD PARENT ID VALIDATION OF LIST
#****************************************************************************************     
if (isset($_POST['valid'])) {
    $_SESSION['popup_suite'] = true;
    # Reload caller with new list in session ?>
    <script type="text/javascript">
        window.parent.opener.location.reload();
        self.close();
    </script><?php
    exit;
}
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
        . " WHERE u.user_id='" . $db->protect_string_db($id) . "' and ue.primary_entity = 'Y'"
    );
    $line = $db->fetch_object();
    
    $visible = 'Y';
    if(!isset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'])) {
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'] = array();  
    } else {
        if($lastUser = end($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']))
            $visible = $lastUser['visible'];
    }
    
    # If dest is set && role is dest, move current dest to copy (legacy)
    if ($role_id == 'dest' && $dest_is_set) {
        if(!isset($_SESSION['m_admin']['entity']['listmodel']['copy']['users']))
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'] = array();
        
        $old_dest = $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['user_id'];
        if(!in_array('copy', $user_roles[$old_dest])) {
            array_push(
                $_SESSION['m_admin']['entity']['listmodel']['copy']['users'],
                array(
                    'user_id' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['user_id'],
                    'firstname' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['firstname'],
                    'lastname' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['lastname'],
                    'entity_id' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['entity_id'],
                    'entity_label' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['entity_label'],
                    'visible' => 'Y',
                )
            );
        }
        unset($_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]);
    }
    
    array_push(
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'],
        array(
            'user_id' => $db->show_string($id),
            'firstname' => $db->show_string($line->firstname),
            'lastname' => $db->show_string($line->lastname),
            'entity_id' => $db->show_string($line->entity_id),
            'entity_label' => $db->show_string($line->entity_label),
            'visible' => $visible,
        )
    ); 
    $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'] = array_values(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']
    );
    break;

// ADD ENTITY AS copy/custom mode
case 'add_entity':
    $db->query(
        "SELECT entity_id, entity_label FROM " . ENT_ENTITIES
        . " WHERE entity_id = '" . $db->protect_string_db($id) . "'"
    );
    $line = $db->fetch_object();
    $visible = 'Y';
    if(! isset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'])) {
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'] = array();
    }  else {  
        if($lastEntity = end($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']))
            $visible = $lastEntity['visible'];
    }
    array_push(
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'],
        array(
            'entity_id'    => $db->show_string($id),
            'entity_label' => $db->show_string($line->entity_label),
            'visible' => $visible,
        )
    );
    break;    

// REMOVE
//***************************************************************************************
// Remove USER
case 'remove_user':
    if($rank !== false && $id && $role_id
        && $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['user_id'] == $id
    ) {
        $visible = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['visible'];
        unset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]);
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'] = array_values(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']
        );
        if(isset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]))
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['visible'] = $visible;
        if($role_id == 'dest') $dest_is_set = false;
    }
    break;

// Remove ENTITY
case 'remove_entity':
    if($rank !== false && $id && $role_id
        && $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank]['entity_id'] == $id
    ) {
        $visible = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank]['visible'];
        unset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank]);
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'] = array_values(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']
        );
        if(isset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank]))
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank]['visible'] = $visible;
    }
    break;

// MOVE
//***************************************************************************************    
case 'dest_to_copy':
    if ($dest_is_set) {
        if(! isset($_SESSION['m_admin']['entity']['listmodel']['copy']['users']))
            $_SESSION['m_admin']['entity']['listmodel']['copy']['users'] = array();
        
        $old_dest = $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['user_id'];
        if(!in_array('copy', $user_roles[$old_dest])) {
            array_push(
                $_SESSION['m_admin']['entity']['listmodel']['copy']['users'],
                array(
                    'user_id' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['user_id'],
                    'firstname' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['firstname'],
                    'lastname' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['lastname'],
                    'entity_id' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['entity_id'],
                    'entity_label' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['entity_label'],
                    'visible' => 'Y',
                )
            );
        }
        unset($_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]);
        $_SESSION['m_admin']['entity']['listmodel']['dest']['users'] = array_values(
            $_SESSION['m_admin']['entity']['listmodel']['dest']['users']
        );
        $dest_is_set = false;
    }
    break;

case 'copy_to_dest':
    if ($dest_is_set) {
        if(! isset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']))
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'] = array();
        $old_dest = $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['user_id'];
        if(!in_array('copy', $user_roles[$old_dest])) {
            array_push(
                $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'],
                array(
                    'user_id' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['user_id'],
                    'firstname' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['firstname'],
                    'lastname' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['lastname'],
                    'entity_id' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['entity_id'],
                    'entity_label' => $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['entity_label'],
                    'visible' => 'Y',
                )
            );
        }
        unset($_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]);
        $_SESSION['m_admin']['entity']['listmodel']['dest']['users'] = array_values(
            $_SESSION['m_admin']['entity']['listmodel']['dest']['users']
        );
    }
    if (isset($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['user_id'])
        && !empty($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['user_id'])
    ) {
        $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['user_id'] = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['user_id'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['firstname'] = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['firstname'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['lastname'] = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['lastname'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['entity_id'] = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['entity_id'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['entity_label'] = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['entity_label'];
        $_SESSION['m_admin']['entity']['listmodel']['dest']['users'][0]['visible'] = 'Y';
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
    $newRank = $rank+1;
    $upUser = 
        array_splice(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'], 
            $newRank,
            1,
            $preserve_keys = true
        );
    if($upUser[0] && $downUser[0]) {
        # Switch visible values
        $downUserVisible = $downUser[0]['visible'];
        $upUserVisible = $upUser[0]['visible'];
        $upUser[0]['visible'] = $downUserVisible;
        $downUser[0]['visible'] = $upUserVisible;
        # Switch positions
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank] = $upUser[0];
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$newRank] = $downUser[0];
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
    $newRank = $rank+1;
    $upEntity = 
        array_splice(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'], 
            $newRank,
            1,
            $preserve_keys = true
        );
    if($upEntity[0] && $downEntity[0]) {
        # Switch visible values
        $downEntityVisible = $downEntity[0]['visible'];
        $upEntityVisible = $upEntity[0]['visible'];
        $upEntity[0]['visible'] = $downEntityVisible;
        $downEntity[0]['visible'] = $upEntityVisible;
        # Switch positions
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank] = $upEntity[0];
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$newRank] = $downEntity[0];
    }
    break;
    
case 'move_user_up':
    $upUser = 
        array_splice(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'], 
            $rank,
            1,
            $preserve_keys = true
        );
    $newRank = $rank-1;
    $downUser = 
        array_splice(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'], 
            $newRank,
            1,
            $preserve_keys = true
        );
    if($upUser[0] && $downUser[0]) {
        # Switch visible values
        $downUserVisible = $downUser[0]['visible'];
        $upUserVisible = $upUser[0]['visible'];
        $upUser[0]['visible'] = $downUserVisible;
        $downUser[0]['visible'] = $upUserVisible;
        # Switch positions
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank] = $downUser[0]; 
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$newRank] = $upUser[0];
    }
    break;

case 'move_entity_up':
    $upEntity = 
    array_splice(
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'], 
        $rank,
        1,
        $preserve_keys = true
    );
    $newRank = $rank-1;
    $downEntity = 
        array_splice(
            $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'], 
            $newRank,
            1,
            $preserve_keys = true
        );
    
    
    if($upEntity[0] && $downEntity[0]) {
        # Switch visible values
        $downEntityVisible = $downEntity[0]['visible'];
        $upEntityVisible = $upEntity[0]['visible'];
        $upEntity[0]['visible'] = $downEntityVisible;
        $downEntity[0]['visible'] = $upEntityVisible;
        # Switch positions
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank] = $downEntity[0];
        $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$newRank] = $upEntity[0];
    }
    break;   
    
// VISIBLE
//*************************************************************************************** 
case 'make_user_visible':
    $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['visible'] = 'Y'; 
    break;
    
case 'make_user_unvisible':
    $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$rank]['visible'] = 'N'; 
    break;    
    
case 'make_entity_visible':
    $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank]['visible'] = 'Y'; 
    break;
    
case 'make_entity_unvisible':
    $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$rank]['visible'] = 'N'; 
    break;  
# END SWITCH ACTION
}

// 1.4 create indexed array of existing diffusion to search for users/entities easily
$user_roles = array();
$entity_roles = array();
foreach($roles as $role_id => $role_label) {
    for($i=0, $l=count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']); 
        $i<$l; $i++
    ) {
        $user_id = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$i]['user_id'];
        $user_roles[$user_id][] = $role_id;
    }
    for($i=0, $l=count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']); 
        $i<$l; 
        $i++
    ) {
        $entity_id = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$i]['entity_id'];
        $entity_roles[$entity_id][] = $role_id;
    }
}

$core_tools->load_html();
$core_tools->load_header(_LISTMODEL);
$time = $core_tools->get_session_time_expire();
$link = $_SESSION['config']['businessappurl']."index.php?display=true&module=entities&page=manage_listmodel";
if ($onlyCc) $link .= '&only_cc';
if ($noDelete) $link .= '&no_delete';

$linkwithwhat =  
    $link 
    . '&what_users=' . $whatUsers 
    . '&what_services=' . $whatServices;
#******************************************************************************
# DISPLAY EXISTING LIST
#******************************************************************************
?>
<body onload="setTimeout(window.close, <?php echo $time;?>*60*1000);">
    <script type="text/javascript">
        function add_user(id) {
            var user_id = $('user_id_' + id).value;
            var role_select = $('user_role_' + id);
			var role = role_select.options[role_select.selectedIndex].value;
            goTo('<?php echo $linkwithwhat; ?>&action=add_user&id='+user_id+'&role='+role);        
        }
        function add_entity(id) {
            var entity_id = $('entity_id_' + id).value;
            var role_select = $('entity_role_' + id);
			var role = role_select.options[role_select.selectedIndex].value;
            goTo('<?php echo $linkwithwhat; ?>&action=add_entity&id='+entity_id+'&role='+role);        
        }
    </script>
    <br/>
    <div id="diff_list" align="center">
        <h2 class="tit"><?php 
            echo _DIFFUSION_LIST . ' - ';
            echo $description;
        ?></h2><?php 
        #**************************************************************************
        # DEST USER
        #**************************************************************************
        if(1==2 && isset($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id']) 
            && !empty($_SESSION['m_admin']['entity']['listmodel']['dest']['user_id'])
        ) { ?>
        <h2 class="sstit"><?php echo _PRINCIPAL_RECIPIENT;?></h2>
        <table cellpadding="0" cellspacing="0" border="0" class="listing spec">
            <tr>
                <td>
                    <img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=manage_users_entities_b.gif&module=entities" alt="<?php echo _USER;?>" title="<?php echo _USER;?>" />
                </td>
                <td><?php
                if($_SESSION['m_admin']['entity']['listmodel']['dest']['visible'] == 'Y') { ?>
                    <img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=picto_authorize.gif&module=entities" alt="<?php echo _VISIBLE; ?>" title="<?php echo _VISIBLE;?>" /> <?php
                } ?>
                </td>
                <td><?php echo $_SESSION['m_admin']['entity']['listmodel']['dest']['lastname'] . " " . $_SESSION['m_admin']['entity']['listmodel']['dest']['firstname'];?></td>
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
        foreach($roles as $role_id => $role_label) {
            if (count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']) > 0
             || count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities']) > 0
            ) { ?>
                <h2 class="sstit"><?php echo $role_label;?></h2>
                <table cellpadding="0" cellspacing="0" border="0" class="listing liste_diff spec"><?php
                #**************************************************************************
                # OTHER ROLE USERS
                #**************************************************************************
                $color = ' class="col"';
                for($i=0, $l=count($_SESSION['m_admin']['entity']['listmodel'][$role_id]['users']); 
                    $i<$l; 
                    $i++
                ) {
                    $user = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['users'][$i];
                    
                    if ($color == ' class="col"') $color = '';
                    else $color = ' class="col"'; ?>
                    <tr <?php echo $color; ?> >
                        <td>
                            <img src="<?php echo $_SESSION['config']['businessappurl'] ?>static.php?filename=manage_users_entities_b.gif&module=entities" alt="<?php echo _USER . " " . $role_label;?>" title="<?php echo _USER . " " . $role_label; ?>" />
                        </td>
                        <td><?php
                        if($user['visible'] == 'Y') { ?>
                            <a href="<?php echo $linkwithwhat;?>&action=make_user_unvisible&role=<?php echo $role_id ?>&rank=<?php echo $i;?>">
                                <img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=picto_authorize.gif&module=entities" alt="<?php echo _VISIBLE; ?>" title="<?php echo _VISIBLE;?>" />
                            </a><?php
                        } else {?>
                            <a href="<?php echo $linkwithwhat;?>&action=make_user_visible&role=<?php echo $role_id ?>&rank=<?php echo $i;?>">
                                <img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=picto_delete.gif&module=entities" alt="<?php echo _NOT_VISIBLE; ?>" title="<?php echo _NOT_VISIBLE;?>" />
                            </a><?php
                        } ?>
                        </td>
                        <td ><?php echo $user['lastname'] . " " . $user['firstname'];?></td>
                        <td><?php echo $user['entity_label']; ?></td>
                        <td class="action_entities"><!-- Remove user -->
                            <a href="<?php echo $linkwithwhat; ?>&action=remove_user&role=<?php echo $role_id ?>&rank=<?php echo $i; ?>&id=<?php echo $user['user_id'];?>" class="delete"><?php echo _DELETE; ?></a>
                        </td>
                        <td class="action_entities"><!-- Switch copy to dest --><?php
                        if($role_id == 'dest'  && isset($roles['copy']) ) { ?>
                            <a href="<?php echo $linkwithwhat; ?>&action=dest_to_copy&role=copy" class="down"><?php echo _TO_CC;?></a><?php
                        } elseif($role_id == 'copy' && isset($roles['dest'])) { ?>
                            <a href="<?php echo $linkwithwhat;?>&action=copy_to_dest&role=copy&rank=<?php echo $i;?>" class="up"><?php echo _TO_DEST;?></a><?php
                        } else echo '&nbsp;'?>
                        </td>
                        <td class="action_entities"><!-- Move up in list --><?php 
                        if($i > 0) { ?>
                            <a href="<?php echo $linkwithwhat;?>&action=move_user_up&role=<?php echo $role_id ?>&rank=<?php echo $i;?>" class="up"></a><?php
                        } ?>
                        </td>
                        <td class="action_entities"><!-- Move down in list --><?php 
                        if($i < $l-1) { ?>
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
                    $entity = $_SESSION['m_admin']['entity']['listmodel'][$role_id]['entities'][$i];
                    if ($color == ' class="col"') $color = '';
                    else $color = ' class="col"'; ?>
                    <tr <?php echo $color; ?> >
                        <td>
                            <img src="<?php echo $_SESSION['config']['businessappurl'] ?>static.php?filename=manage_entities_b.gif&module=entities" alt="<?php echo _ENTITY . " " . $role_label ;?>" title="<?php echo _ENTITY . " " . $role_label; ?>" />
                        </td>
                        <td><?php
                        if($entity['visible'] == 'Y') { ?>
                            <a href="<?php echo $linkwithwhat;?>&action=make_entity_unvisible&role=<?php echo $role_id ?>&rank=<?php echo $i;?>">
                                <img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=picto_authorize.gif&module=entities" alt="<?php echo _VISIBLE; ?>" title="<?php echo _VISIBLE;?>" />
                            </a><?php
                        } else {?>
                            <a href="<?php echo $linkwithwhat;?>&action=make_entity_visible&role=<?php echo $role_id ?>&rank=<?php echo $i;?>">
                                <img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=picto_delete.gif&module=entities" alt="<?php echo _NOT_VISIBLE; ?>" title="<?php echo _NOT_VISIBLE;?>" />
                            </a><?php
                        } ?>
                        </td>
                        <td ><?php echo $entity['entity_id']; ?></td>
                        <td ><?php echo $entity['entity_label']; ?></td>
                        <td class="action_entities">
                            <a href="<?php echo $linkwithwhat; ?>&action=remove_entity&role=<?php echo $role_id ?>&rank=<?php echo $i; ?>&id=<?php echo $entity['entity_id'];?>" class="delete">
                                <?php echo _DELETE; ?>
                            </a>
                        </td>
                        <td class="action_entities">&nbsp;</td>
                        <td class="action_entities"><!-- Move up in list --><?php 
                        if($i > 0) { ?>
                            <a href="<?php echo $linkwithwhat;?>&action=move_entity_up&role=<?php echo $role_id ?>&rank=<?php echo $i;?>" class="up"></a><?php
                        } ?>
                        </td>
                        <td class="action_entities"><!-- Move down in list --><?php 
                        if($i < $l-1) { ?>
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
        # ACTIONS BUTTONS
        #******************************************************************************?>      
        <form name="pop_diff" method="post" >
            <div align="center">
                <input align="middle" type="submit" value="<?php echo _VALIDATE;?>" class="button" name="valid"  />
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
                <input type="hidden" name="page" value="manage_listmodel" />
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
                    <tr>
						<th>
							<label for="no_filter" class="bold">&nbsp;</label>
						</th>
						<th>
							<input class="button" name="no_filter" id="no_filter" type="button" onclick="$('what_services').value='';$('what_users').value='';" value="<?php echo _NO_FILTER; ?>"/>
						</th>
					</tr>
                </table>
            </form>     
        </div> 
        <script type="text/javascript">
            repost('<?php echo $link;?>',new Array('diff_list_items'),new Array('what_users','what_services'),'keyup',250);
            repost('<?php echo $link;?>',new Array('diff_list_items'),new Array('no_filter'), 'click',250);
        </script>
        <br/>
        <div id="diff_list_items"> <?php
        #******************************************************************************
        # LIST OF AVAILABLE USERS
        #******************************************************************************
        if (count($users) > 0) {
        ?> 
            <div align="center">
                <h2 class="tit"><?php echo _USERS_LIST;?></h2>
                <table cellpadding="0" cellspacing="0" border="0" class="listing spec">
                    <thead>
                        <tr>
                            <th ><?php echo _LASTNAME . " " . _FIRSTNAME;?></th>
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
                        $possible_roles = array();
                        foreach($roles as $role_id => $role_label) {
							if(isset($user_roles[$user_id]) 
									&& (in_array($role_id, $user_roles[$user_id]) 
										|| in_array('dest', $user_roles[$user_id]) 
										|| in_array('copy', $user_roles[$user_id])
										)
								)
							{
								continue;
							}
                            if($role_id == 'copy' || $role_id == 'dest'
                                    || $usergroups_controler->inGroup($users[$j]['ID'], $role_id))
                                $possible_roles[$role_id] = $role_label;
                        } 
                        
                        if ($color == ' class="col"') $color = '';
                        else $color = ' class="col"'; ?>
                        <tr <?php echo $color; ?> id="user_<?php echo $j; ?>">
                        <td><?php echo $users[$j]['NOM'] . " ". $users[$j]['PRENOM']; ?></td>
                        <td><?php echo $users[$j]['DEP'];?></td>
                        <td class="action_entities"><?php
                        if(count($possible_roles) > 0) { ?>
                            <input type="hidden" id="user_id_<?php echo $j; ?>" value="<?php echo $users[$j]['ID'];?>" />
                            <select name="role" id="user_role_<?php echo $j; ?>"><?php
                            foreach($possible_roles as $role_id => $role_label) { ?>
                                <option value="<?php echo $role_id; ?>"><?php echo $role_label; ?></option><?php 
                            } ?>
                            </select>&nbsp;
                            <span onclick="add_user(<?php echo $j; ?>);" class="change"/> 
                                <?php echo _ADD;?>
                            </span><?php
                        } else echo _NO_AVAILABLE_ROLE; ?>
                        </td>
                    </tr><?php 
                    } ?>
                </table>
                <br/>
            </div> <?php
            }
            #******************************************************************************
            # LIST OF AVAILABLE ENTITIES
            #****************************************************************************** 
            if (count($entities) > 0) {
                if($allow_entities) { ?>
                <div align="center"> 
                    <h2 class="tit"><?php echo _ENTITIES_LIST;?></h2>
                    <table cellpadding="0" cellspacing="0" border="0" class="listing spec">
                        <thead>
                            <tr>
                                <th><?php echo _ID;?></th>
                                <th><?php echo _DEPARTMENT;?></th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead><?php 
                    $color = ' class="col"';
                    for ($j=0, $m=count($entities); $j<$m ; $j++) {
                        $entity_id = $entities[$j]['ID'];
                        
                        # Check if at least one role can be added
                        $possible_roles = array();
                        foreach($roles as $role_id => $role_label) {
                            if(isset($entity_roles[$entity_id]) && in_array($role_id, $entity_roles[$entity_id]))
                                continue;
                            if($role_id == 'dest')
                                continue;
                            $possible_roles[$role_id] = $role_label;
                        } 
                        
                        if($color == ' class="col"') $color = '';
                        else $color = ' class="col"'; ?>
                        <tr <?php echo $color; ?>>
                            <td><?php echo $entities[$j]['ID'];?></td>
                            <td><?php echo $entities[$j]['DEP']; ?></td>
                            <td class="action_entities"><?php
                            if(count($possible_roles) > 0) { ?>
                                <input type="hidden" id="entity_id_<?php echo $j; ?>" value="<?php echo $entities[$j]['ID'];?>" />
                                <select name="role" id="entity_role_<?php echo $j; ?>"><?php 
                                foreach($possible_roles as $role_id => $role_label) { ?>
                                    <option value="<?php echo $role_id; ?>"><?php echo $role_label; ?></option><?php 
                                } ?>
                                </select>&nbsp;
                                <span onclick="add_entity(<?php echo $j; ?>);" class="change"/> 
                                    <?php echo _ADD;?>
                                </span><?php
                            } else echo _NO_AVAILABLE_ROLE; ?>               
                            </td>
                        </tr> <?php
                    } ?>
                    </table>
                </div><?php
                }
            }
            ?>
        </div>  
    </div>
</body>
</html>
