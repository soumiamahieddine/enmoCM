<?php
# Display list
/* Requires 
    $difflist = diffusion list array 
        $_SESSION[m_admin]['entities'][listmodel]
        $_SESSION[$origin][listinstance]
    $roles = list of available roles
    $onlyCC =  hide 'dest';
*/
foreach($roles as $role_id => $role_label) {
    if($role_id == 'dest' && $onlyCC) continue;
    if(count($difflist[$role_id]['users']) > 0
        || count($difflist[$role_id]['entities']) > 0
    ) { ?>
        <h3 class="sstit"><?php echo $role_label; ?></h3><?php
    if(count($difflist[$role_id]['users']) > 0) { ?>
        <table cellpadding="0" cellspacing="0" border="0" class="listingsmall liste_diff spec"><?php
        $color = ' class="col"';
        for($i=0, $l=count($difflist[$role_id]['users']);
            $i<$l;
            $i++
        ) {
            $user = $difflist[$role_id]['users'][$i];
            
            if ($color == ' class="col"') $color = ' ';
            else $color = ' class="col"'; ?>
            <tr <?php echo $color; ?> >
                <td>
                    <img src="<?php echo $_SESSION['config']['businessappurl'] ?>static.php?filename=manage_users_entities_b_small.gif&module=entities" alt="<?php echo _USER . " " . $role_label ;?>" title="<?php echo _USER . " " . $role_label ; ?>" />
                </td>
                <td><?php
                if($user['visible'] == 'Y') { ?>
                    <img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=picto_authorize.gif&module=entities" alt="<?php echo _VISIBLE; ?>" title="<?php echo _VISIBLE;?>" /><?php
                } else {?>
                    <img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=picto_delete.gif&module=entities" alt="<?php echo _NOT_VISIBLE; ?>" title="<?php echo _NOT_VISIBLE;?>" /><?php
                } ?>
                </td>
                <td ><?php echo $user['lastname'] . " " . $user['firstname'];?></td>
                <td><?php echo $user['entity_label']; ?></td>
            </tr><?php
        } ?>
        </table><?php
    } 
    if(count($difflist[$role_id]['entities']) > 0) { ?>
        <table cellpadding="0" cellspacing="0" border="0" class="listingsmall liste_diff spec"><?php
        $color = ' class="col"';
        for ($i=0, $l=count($difflist[$role_id]['entities']);
            $i<$l;
            $i++
        ) {
            $entity = $difflist[$role_id]['entities'][$i];
            if ($color == ' class="col"') $color = '';
            else $color = ' class="col"';?>
            <tr <?php echo $color; ?> >
                <td>
                    <img src="<?php echo $_SESSION['config']['businessappurl'] ?>static.php?filename=manage_entities_b_small.gif&module=entities" alt="<?php echo _ENTITY . " " . $role_label ;?>" title="<?php echo _ENTITY . " " . $role_label ; ?>" />
                </td>
                <td><?php
                if($entity['visible'] == 'Y') { ?>
                    <img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=picto_authorize.gif&module=entities" alt="<?php echo _VISIBLE; ?>" title="<?php echo _VISIBLE;?>" /><?php
                } else {?>
                    <img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=picto_delete.gif&module=entities" alt="<?php echo _NOT_VISIBLE; ?>" title="<?php echo _NOT_VISIBLE;?>" /><?php
                } ?>
                </td>
                <td ><?php echo $entity['entity_id']; ?></td>
                <td ><?php echo $entity['entity_label']; ?></td>
            </tr> <?php
        } ?>
        </table><?php
    } ?>
    <br/><?php
    }
} ?>