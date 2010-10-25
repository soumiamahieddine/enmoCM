<?php
/**
* File : view_tree_entities.php
*
* Entities Administration view tree
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  Cédric Ndoumba  <dev@maarch.org>
*/
//require_once("core/class/class_functions.php");
//require_once("core/class/class_db.php");
//require_once("core/class/class_core_tools.php");
$admin = new core_tools();
$admin->test_admin('manage_entities', 'entities');
$db = new dbquery();
$db->connect();
/****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) && $_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1)
{
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=view_tree_entities&module=entities';
$page_label = _ENTITY_TREE;
$page_id = "view_tree_entities";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
unset($_SESSION['m_admin']);
$_SESSION['tree_entities'] = array();
//$db->query("select distinct foldertype_id, foldertype_label from ".$_SESSION['tablename']['fold_foldertypes']." order by foldertype_label");

$db->query("select entity_id, entity_label from ".$_SESSION['tablename']['ent_entities']." where parent_entity_id = '' or parent_entity_id is null order by entity_label");
while($res = $db->fetch_object())
{
    array_push($_SESSION['tree_entities'], array("ID" => $res->entity_id, "LABEL" => $res->entity_label));
}
?>
<h1><img src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?filename=entity_tree_b.gif&module=entities" alt="" /> <?php  echo _ENTITY_TREE;?></h1>
<div id="inner_content" class="clearfix">
    <table width="100%" border="0">
        <tr>
            <td>
                <iframe name="choose_tree" id="choose_tree" width="550" height="40" frameborder="0" scrolling="no" src="<?php  echo $_SESSION['config']['businessappurl']."index.php?display=true&module=entities&page=choose_tree";?>"></iframe>
            </td>
        </tr>
        <tr>
            <td>
                <iframe name="show_trees" id="show_trees" width="550" height="600" frameborder="0" scrolling="auto" src="<?php  echo $_SESSION['config']['businesappurl']."index.php?display=true&module=entities&page=show_trees";?>"></iframe>
            </td>
        </tr>
    </table>
</div>
