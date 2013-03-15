<?php
$core_tools = new core_tools();
$dbActions= new dbquery();
$dbActions->connect();
require_once('modules/basket/class/class_admin_basket.php');
$adminBasket = new admin_basket();

$_SESSION['m_admin']['basket']['coll_id'] = $_REQUEST['coll_id'];

//SELECT ACTIONS
$_SESSION['m_admin']['basket']['all_actions'] = array();
if ($_REQUEST['is_reload_groups'] == 'true') {
    $_SESSION['m_admin']['basket']['groups'] = array();
}
$dbActions->query("select id, label_action, keyword, create_id, action_page from " 
    . $_SESSION['tablename']['actions'] . " where enabled = 'Y' order by label_action"
);

while ($line = $dbActions->fetch_object()) {
    if ($core_tools->is_action_defined($line->id) && $line->action_page == '') {
        array_push(
            $_SESSION['m_admin']['basket']['all_actions'] ,
            array(
                'ID' => $line->id, 
                'LABEL' => $line->label_action, 
                'KEYWORD' => $line->keyword, 
                'CREATE_ID' => $line->create_id
            )
        );
    } elseif ($adminBasket->isAnActionOfMyBasketCollection($line->action_page, $_REQUEST['coll_id'])) {
        array_push(
            $_SESSION['m_admin']['basket']['all_actions'] ,
            array(
                'ID' => $line->id, 
                'LABEL' => $line->label_action, 
                'KEYWORD' => $line->keyword, 
                'CREATE_ID' => $line->create_id
            )
        );
    }
}

//var_dump($_SESSION['m_admin']['basket']['all_actions']);
