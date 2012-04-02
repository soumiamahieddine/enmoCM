<?php
/**
* File : templates.php
*
* Models list
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

$_SESSION['m_admin'] = array();
$admin = new core_tools();
$admin->test_admin('admin_templates', 'templates');
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=templates&module=templates';
$page_label = _TEMPLATES_LIST;
$page_id = "templates";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");

$func = new functions();

$select[$_SESSION['tablename']['temp_templates']] = array();
array_push($select[$_SESSION['tablename']['temp_templates']],"id","label", 'template_comment' );
$_SESSION['origin'] = 'templates';
$what = "";
$where ="";
if(isset($_REQUEST['what']) && !empty($_REQUEST['what']))
{
    $what = addslashes($func->wash($_REQUEST['what'], "alphanum", "", "no"));
    $where .= " (lower(label) like lower('".$func->protect_string_db($what,$_SESSION['config']['databasetype'])."%')) ";
}
$list = new list_show();
$order = 'asc';
if(isset($_REQUEST['order']) && !empty($_REQUEST['order']))
{
    $order = trim($_REQUEST['order']);
}
$field = 'label';
if(isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field']))
{
    $field = trim($_REQUEST['order_field']);
}

$orderstr = $list->define_order($order, $field);

$request= new request;
$tab=$request->select($select,$where,$orderstr ,$_SESSION['config']['databasetype']);
//$request->show_array($tab);
for ($i=0;$i<count($tab);$i++)
{
    for ($j=0;$j<count($tab[$i]);$j++)
    {
        foreach(array_keys($tab[$i][$j]) as $value)
        {

            if($tab[$i][$j][$value]=="id")
            {
                $tab[$i][$j]["id"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]= _ID;
                $tab[$i][$j]["size"]="20";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='id';
            }
            if($tab[$i][$j][$value]=="label")
            {
                $tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["label"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_NAME;
                $tab[$i][$j]["size"]="20";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='label';
            }
            if($tab[$i][$j][$value]=="template_comment")
            {
                $tab[$i][$j]["value"]=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["label"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_DESC;
                $tab[$i][$j]["size"]="45";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='template_comment';
            }
        }
    }
}

$page_name = "templates";
$page_name_up = "template_up";
$page_name_del = "template_del";
$page_name_val= "";
$page_name_ban = "";
$page_name_add = "template_add";
$label_add = _TEMPLATE_ADDITION;

$_SESSION['m_admin']['init'] = true;
$_SESSION['m_admin']['template'] = array();
$_SESSION['m_admin']['template']['ID'] = "";
$_SESSION['m_admin']['template']['LABEL'] = "";
$_SESSION['m_admin']['template']['COMMENT'] = "";
$_SESSION['m_admin']['template']['DATE'] = "";
$_SESSION['m_admin']['template']['CONTENT'] = "";

$admin->execute_modules_services($_SESSION['modules_services'], 'templates.php', "include");

$title = _TEMPLATES_LIST." : ".$i." "._TEMPLATES;
$list = new list_show();

$list->admin_list($tab, $i, $title, 'id','templates','templates','id', true, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, FALSE, FALSE, _ALL_TEMPLATES, _TEMPLATE, $_SESSION['config']['businessappurl'].'static.php?filename=picto_add_b.gif', TRUE, true, false, true, $what);

?>
