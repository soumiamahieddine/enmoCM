<?php

//WF general view for the agent
if ($myTurnInTheWF) {
    $countRoles = count($rolesInTheWF);
    for ($cptR=0;$cptR<$countRoles;$cptR++) {
         $frm_str .= '<h3 onclick="new Effect.toggle(\'wf_div' . $cptR . '\', \'blind\', {delay:0.2});'
            . 'whatIsTheDivStatus(\'wf_div' . $cptR . '\', \'divStatus_wf_div' . $cptR . '\');return false;" '
            . 'onmouseover="this.style.cursor=\'pointer\';" class="categorie" style="width:90%;">';
        $frm_str .= ' <span id="divStatus_wf_div' . $cptR . '" style="color:#1C99C5;">>></span>&nbsp;<b>'
            . _WF  . '</b> : <small><small>' . _ROLE . ' ' . $rolesInTheWF[$cptR]['role'] . '</small></small>';
        $frm_str .= '<span class="lb1-details">&nbsp;</span>';
        $frm_str .= '</h3>';
        $frm_str .= '<div id="wf_div' . $cptR . '">';
        $frm_str .= '<table width="98%" align="center" border="0" cellspacing="5" cellpadding="5">';
        if ($rolesInTheWF[$cptR]['isThereSomeoneAfterMeInTheWF']) {
            $frm_str .= '<tr>';
            $frm_str .= '<td class="tdButtonGreen" onmouseover="this.style.cursor=\'pointer\';" '
                . 'onclick="moveInWF(\'forward\', \''
                . $coll_id . '\', \''
                . $res_id . '\', \''
                . $rolesInTheWF[$cptR]['role'] . '\', \''
                . $_SESSION['user']['UserId'] . '\');">';
                $frm_str .= '>> ' . _ADVANCE_TO . ' ' . $rolesInTheWF[$cptR]['theNextInTheWF'] . ' >>';
            $frm_str .= '</td>';
            $frm_str .= '</tr>';
        } else {
            $frm_str .= '<tr>';
            $frm_str .= '<td class="tdButtonGreen" onmouseover="this.style.cursor=\'pointer\';" '
                . 'onclick="moveInWF(\'forward\', \''
                . $coll_id . '\', \''
                . $res_id . '\', \''
                . $rolesInTheWF[$cptR]['role'] . '\', \''
                . $_SESSION['user']['UserId'] . '\');">';
                $frm_str .= _VALID_STEP . ' ' . $rolesInTheWF[$cptR]['role'];
            $frm_str .= '</td>';
            $frm_str .= '</tr>';
        }
        if ($rolesInTheWF[$cptR]['isThereSomeoneBeforeMeInTheWF']) {
            $frm_str .= '<tr>';
            $frm_str .= '<td class="tdButtonRed" onmouseover="this.style.cursor=\'pointer\';" '
                 . 'onclick="moveInWF(\'back\', \''
                . $coll_id . '\', \''
                . $res_id . '\', \''
                . $rolesInTheWF[$cptR]['role'] . '\', \''
                . $_SESSION['user']['UserId'] . '\');">';
                $frm_str .= '<< ' . _BACK_TO . ' ' . $rolesInTheWF[$cptR]['thePreviousInTheWF'] . ' <<';
            $frm_str .= '</td>';
            $frm_str .= '</tr>';
        }
        $frm_str .= '</table>';
        $frm_str .= '</div>';
    }
    $frm_str .= '<small>' . _COMBINATED_ACTION . ' : <span id="combinatedAction" name="combinatedAction"></span></small>';
    $frm_str .= '<hr />';
}
