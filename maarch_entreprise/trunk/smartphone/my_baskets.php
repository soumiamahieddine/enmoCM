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
$core = new core_tools();
$core->load_lang();
require_once('core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_request.php');
$core_tools = new core_tools();
$core_tools->test_user();
//$core_tools->load_lang();
if (!isset($_REQUEST['noinit']))
{
    $_SESSION['current_basket'] = array();
}
require_once('modules' . DIRECTORY_SEPARATOR . 'basket' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_modules_tools.php');
/************/
$bask = new basket();
$db = new dbquery();
$db->connect();
?>
<?php 
if ($core_tools->test_service('display_basket_list', 'basket', false)) {
    if (isset($_SESSION['user']['baskets']) && count($_SESSION['user']['baskets']) > 0) {
        ?>
        <ul class="basket_elem" selected="true">
        <?php
        $abs_basket = false;
        for ($i=0;$i<count($_SESSION['user']['baskets']);$i++) {
            if ($_SESSION['user']['baskets'][$i]['abs_basket'] == true && !$abs_basket) {
                echo '</ul><h3>'._OTHER_BASKETS.' :</h3><ul class="basket_elem">';
                $abs_basket = true;
            }
        if($_SESSION['user']['baskets'][$i]['is_visible'] === 'Y') {
            $nb = '';
            if (preg_match('/^CopyMailBasket/', $_SESSION['user']['baskets'][$i]['id']) && !empty($_SESSION['user']['baskets'][$i]['view'])) {
                $db->query('select res_id from '.$_SESSION['user']['baskets'][$i]['view']." where ".$_SESSION['user']['baskets'][$i]['clause']);
                $nb = $db->nb_result();
            } elseif (!empty($_SESSION['user']['baskets'][$i]['table'])) {
                if ( trim($_SESSION['user']['baskets'][$i]['clause']) <> '' && $_SESSION['user']['baskets'][$i]['view']!='view_folders') {
                    $db->query('select res_id from '.$_SESSION['user']['baskets'][$i]['view']." where ".$_SESSION['user']['baskets'][$i]['clause'], true);
                    $nb = $db->nb_result();
                }else{
                    $db->query('select folder_id from '.$_SESSION['user']['baskets'][$i]['view']." where ".$_SESSION['user']['baskets'][$i]['clause'], true);
                    $nb = $db->nb_result();
                }
            }
            if ($nb <> 0) {
                if (!preg_match('/^IndexingBasket/', $_SESSION['user']['baskets'][$i]['id'])) {
                    echo '<li style="color: #58585A;"><a style="width:100%;display:inline-block;overflow:hidden;text-overflow: ellipsis;word-wrap: break-word;" href="view_baskets.php?baskets='.$_SESSION['user']['baskets'][$i]['id'].'"><i style="width:30px;height:30px;float:left;" class="fa fa-inbox mCdarkGrey"></i>'.$_SESSION['user']['baskets'][$i]['name'].'<span class="bubble" style="display:inline-block;background: #FFC200;margin-right:5px;">'.$nb.'</span> </a> </li>';
                }
            }else{
                if (!preg_match('/^IndexingBasket/', $_SESSION['user']['baskets'][$i]['id'])) {
                    echo '<li style="color: #58585A;"><a style="width:100%;display:inline-block;overflow:hidden;text-overflow: ellipsis;word-wrap: break-word;" href="view_baskets.php?baskets='.$_SESSION['user']['baskets'][$i]['id'].'"><i style="width:30px;height:30px;float:left;" class="fa fa-inbox"></i>'.$_SESSION['user']['baskets'][$i]['name'].' <span class="bubble" style="display:inline-block;background: #666;margin-right:5px;">'.$nb.'</span> </a></li>';
                }
            }
        }
        }
        ?>
        </ul>
        <?php
    }
}
?>
