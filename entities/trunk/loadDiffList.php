<?php

require_once('modules/entities/class/class_manage_listdiff.php');
$listdiff = new diffusion_list();
$roles = $listdiff->get_workflow_roles();
$diffListArray = $listdiff->get_listinstance(
    $_REQUEST['res_id'], 
    false, 
    $_SESSION['collection_id_choice']
);

$status = 0;
//var_dump($diffListArray);
// 1.4 custom diffusion lists
foreach($roles as $role_id => $role_label) {
    if (count($diffListArray[$role_id]['users']) > 0
        || count($diffListArray[$role_id]['entities']) > 0
    ) {
        $return .= '<br/>' . $role_label;
        $return .= '<table cellpadding="0" cellspacing="0" border="0" class="listingsmall">';
        $color = ' class="col"';
        for ($i=0;$i<count($diffListArray[$role_id]['users']);$i++) {
            if ($color == ' class="col"') $color = '';
            else $color = ' class="col"';
            $return .= '<tr ' . $color . '>';
                $return .= '<td><img src="'
                    . $_SESSION['config']['businessappurl']
                    . 'static.php?module=entities&filename=manage_users_entities_b_small.gif" alt="'
                    . _USER . '" title="' . _USER . '" /></td>';
                $return .= '<td>' . $diffListArray[$role_id]['users'][$i]['lastname'] . '</td>';
                $return .= '<td>' . $diffListArray[$role_id]['users'][$i]['firstname'] . '</td>';
                $return .= '<td>' . $diffListArray[$role_id]['users'][$i]['entity_label'] . '</td>';
            $return .= '</tr>';
        }
        for ($i=0;$i<count($diffListArray[$role_id]['entities']);$i++) {
            if ($color == ' class="col"') $color = '';
            else $color = ' class="col"';
            $return .= '<tr '.$color.' >';
            $return .= '<td><img src="'.$_SESSION['config']['businessappurl']
                . 'static.php?module=entities&filename=manage_entities_b_small.gif" alt="'
                . _ENTITY . '" title="'._ENTITY.'" /></td>';
            $return .= '<td>' . $diffListArray[$role_id]['entities'][$i]['entity_id'] . '</td>';
            $return .= '<td colspan="2">'
                . $diffListArray[$role_id]['entities'][$i]['entity_label'] . '</td>';
            $return .= '</tr>';
        }
        $return .= '</table>';
    }
}

echo "{status : " . $status . ", toShow : '" . addslashes($return) . "'}";
exit ();
