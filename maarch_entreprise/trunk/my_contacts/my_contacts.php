<?php
/**
* File : my_contacts.php
*
* contacts list of the current user
*
* @package Maarch LetterBox 2.3
* @version 2.5
* @since 06/2007
* @license GPL
* @author  Claire Figueras <dev@maarch.org>
*/
$_SESSION['m_admin'] = array();
$admin = new core_tools();
$admin->test_service('my_contacts', 'apps');
$func = new functions();
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
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=my_contacts&dir=my_contacts';
$page_label = _CONTACTS_LIST;
$page_id = "my_contacts";
$admin->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
$select[$_SESSION['tablename']['contacts']] = array();
array_push($select[$_SESSION['tablename']['contacts']],"contact_id", "society","lastname","firstname");
$what = "";
$where =" user_id  = '".$_SESSION['user']['UserId']."'  and enabled = 'Y' ";
if(isset($_REQUEST['what']))
{
    $what = $func->protect_string_db($func->wash($_REQUEST['what'], "alphanum", "", "no"));
    if($_SESSION['config']['databasetype'] == "POSTGRESQL")
    {
        $where .= " and (lastname ilike '".$func->protect_string_db($what,$_SESSION['config']['databasetype'])."%'  or society ilike '".$func->protect_string_db($what,$_SESSION['config']['databasetype'])."%' ) ";
    }
    else
    {
        $where .= " and (lastname like '".$func->protect_string_db($what,$_SESSION['config']['databasetype'])."%'  or society like '".$func->protect_string_db($what,$_SESSION['config']['databasetype'])."%' ) ";
    }
}
$orderby = "order by lastname, society asc";
$request= new request;
$tab=$request->select($select,$where,$orderby,$_SESSION['config']['databasetype']);
for ($i=0;$i<count($tab);$i++)
{
    for ($j=0;$j<count($tab[$i]);$j++)
    {
        foreach(array_keys($tab[$i][$j]) as $value)
        {
            if($tab[$i][$j][$value]=="contact_id")
            {
                $tab[$i][$j]["contact_id"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]= _ID;
                $tab[$i][$j]["size"]="18";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]= "contact_id";
            }
            if($tab[$i][$j][$value]=="society")
            {
                $tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["society"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_SOCIETY;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]= "society";
            }
            if($tab[$i][$j][$value]=="lastname")
            {
                $tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["lastname"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_LASTNAME;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]= "lastname";
            }
            if($tab[$i][$j][$value]=="firstname")
            {
                $tab[$i][$j]["firstname"]= $tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_FIRSTNAME;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="center";
                $tab[$i][$j]["align"]="center";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]= "firstname";
            }
        }
    }
}
$page_name = "my_contacts";
$page_name_up = "my_contact_up";
$page_name_del = "my_contact_del";
$page_name_val= "";
$page_name_ban = "";
$page_name_add = "my_contact_add";
$label_add = _CONTACT_ADDITION;
$_SESSION['m_admin']['init'] = true;
$title = _CONTACTS_LIST." : ".$i." "._CONTACTS;
$list = new list_show();
$autoCompletionArray = array();
$autoCompletionArray["list_script_url"] = $_SESSION['config']['businessappurl']."index.php?display=true&dir=my_contacts&page=contact_list_by_name";
$autoCompletionArray["number_to_begin"] = 1;
$list->admin_list($tab, $i, $title, 'contact_id','my_contacts','my_contacts','contact_id', false, $page_name_up, $page_name_val, $page_name_ban, $page_name_del, $page_name_add, $label_add, false, false, _ALL_CONTACTS, _CONTACT, $_SESSION['config']['businessappurl'].'static.php?filename=manage_contact_b.gif', false, true, false, true, $what, true, $autoCompletionArray, true);

$_SESSION['m_admin']['contacts'] = array();
$_SESSION['m_admin']['contacts']['id'] = "";
$_SESSION['m_admin']['contacts']['title'] = "";
$_SESSION['m_admin']['contacts']['lastname'] = "";
$_SESSION['m_admin']['contacts']['firtsname'] = "";
$_SESSION['m_admin']['contacts']['society'] = "";
$_SESSION['m_admin']['contacts']['function'] = "";
$_SESSION['m_admin']['contacts']['address_num'] = "";
$_SESSION['m_admin']['contacts']['address_street'] = "";
$_SESSION['m_admin']['contacts']['address_complement'] = "";
$_SESSION['m_admin']['contacts']['address_town'] = "";
$_SESSION['m_admin']['contacts']['address_postal_code'] = "";
$_SESSION['m_admin']['contacts']['address_country'] = "";
$_SESSION['m_admin']['contacts']['email'] = "";
$_SESSION['m_admin']['contacts']['phone'] = "";
$_SESSION['m_admin']['contacts']['other_data'] = "";
$_SESSION['m_admin']['contacts']['is_corporate_person'] = "";
?>
