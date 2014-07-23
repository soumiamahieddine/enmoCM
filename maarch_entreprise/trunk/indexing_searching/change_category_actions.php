<?php

require_once 'modules/basket/class/class_modules_tools.php';

$b = new basket();

$_SESSION['category_id'] = $_REQUEST['category_id'];

$actions = $b->get_actions_from_current_basket(
    $_REQUEST['resId'], $_REQUEST['collId'], 'PAGE_USE', false
);

if (count($actions) > 0) {
    $frmStr .= '<b>' . _ACTIONS . ' : </b>';
    $frmStr .= '<select name="chosen_action" id="chosen_action">';
    if (count($actions) > 1) {
        $frmStr .= '<option value="" selected="selected">' . _CHOOSE_ACTION . '</option>';
    } else {
        $frmStr .= '<option value="">' . _CHOOSE_ACTION . '</option>';
    }
    if (count($actions) > 1) {
        for ($indAct = 1; $indAct < count($actions); $indAct ++) {
            $frmStr .= '<option value="' . $actions[$indAct]['VALUE'] . '"';
            /*if ($indAct == 1) {
                $frmStr .= 'selected="selected"';
            }*/
            $frmStr .= '>' . $actions[$indAct]['LABEL'] . '</option>';
        }
    } else {
        $frmStr .= '<option value="' . $actions[0]['VALUE'] . '"';
            $frmStr .= 'selected="selected"';
        $frmStr .= '>' . $actions[0]['LABEL'] . '</option>';
    }
    $frmStr .= '</select> ';
} else {
    $frmStr .= _NO_AVAILABLE_ACTIONS_FOR_THIS_BASKET;
    echo "{status : 2, error_txt : '" . addslashes($frmStr) . "'}";
    exit ();
}

echo "{status : 0, selectAction : '" . addslashes($frmStr) . "'}";
exit ();
