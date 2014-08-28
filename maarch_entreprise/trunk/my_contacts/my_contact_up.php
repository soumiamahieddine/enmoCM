<?php
/**
* File : my_contact_up.php
*
* Form to modify a contact
*
* @package Maarch LetterBox 2.3
* @version 2.0
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->test_service('my_contacts', 'apps');

require("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_contacts_v2.php");
require_once "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_lists.php";

$func = new functions();
$list2 = new lists(); 

if(isset($_GET['id']))
{
    $id = addslashes($func->wash($_GET['id'], "alphanum", _THE_CONTACT));
    $_SESSION['contact']['current_contact_id'] = $id;
}else if ($_SESSION['contact']['current_contact_id'] <> ''){
	$id = $_SESSION['contact']['current_contact_id'];
}
else
{
    $id = "";
}
 /****************Management of the location bar  ************/
$init = false;
if(isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == "true")
{
    $init = true;
}
$level = "";
if(isset($_REQUEST['level']) && ($_REQUEST['level'] == 2 || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4 || $_REQUEST['level'] == 1))
{
    $level = $_REQUEST['level'];
}
$page_path = $_SESSION['config']['businessappurl'].'index.php?page=my_contact_up&dir=my_contacts';
$page_label = _MODIFICATION;
$page_id = "my_contact_up";
$core_tools->manage_location_bar($page_path, $page_label, $page_id, $init, $level);
/***********************************************************/

$contact = new contacts_v2();
if ($from_iframe) {
	$contact->formcontact("up",$id, false, true);
} else {
	$contact->formcontact("up",$id, false);
}

// GESTION DES ADDRESSES

echo _MANAGE_CONTACT_ADDRESSES;

require_once "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    . "class_request.php";
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    . "class_list_show.php";
$func = new functions();

$select[$_SESSION['tablename']['contact_addresses']] = array();
array_push(
    $select[$_SESSION['tablename']['contact_addresses']],
    "id", "contact_id", "contact_purpose_id", "departement", "lastname", "firstname", "function", "address_town", "phone", "email"
);
$what = "";
$where = "contact_id = " . $id;
if (isset($_REQUEST['what']) && ! empty($_REQUEST['what'])) {
    $what = $func->protect_string_db($_REQUEST['what']);
    $where .= " and lower(lastname) like lower('%" . $what. "%')";
}

$list = new list_show();
$order = 'asc';
if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
    $order = trim($_REQUEST['order']);
}
$field = 'lastname';
if (isset($_REQUEST['order_field']) && ! empty($_REQUEST['order_field'])) {
    $field = trim($_REQUEST['order_field']);
}

$orderstr = $list->define_order($order, $field);

$request = new request;
$tab = $request->select(
    $select, $where, $orderstr, $_SESSION['config']['databasetype']
);
for ($i = 0; $i < count($tab); $i ++) {
    for ($j = 0; $j < count($tab[$i]); $j ++) {
        foreach (array_keys($tab[$i][$j]) as $value) {
            if ($tab[$i][$j][$value] == "id") {
                $tab[$i][$j]["id"] = $tab[$i][$j]['value'];
                $tab[$i][$j]["label"] = _ID;
                $tab[$i][$j]["size"] = "30";
                $tab[$i][$j]["label_align"] = "left";
                $tab[$i][$j]["align"] = "left";
                $tab[$i][$j]["valign"] = "bottom";
                $tab[$i][$j]["show"] = false;
                $tab[$i][$j]["order"] = 'id';
            }
            if ($tab[$i][$j][$value] == "contact_id") {
                $tab[$i][$j]["contact_id"] = $tab[$i][$j]['value'];
                $tab[$i][$j]["label"] = _CONTACT_ID;
                $tab[$i][$j]["size"] = "30";
                $tab[$i][$j]["label_align"] = "left";
                $tab[$i][$j]["align"] = "left";
                $tab[$i][$j]["valign"] = "bottom";
                $tab[$i][$j]["show"] = false;
                $tab[$i][$j]["order"] = 'contact_id';
            }
            if ($tab[$i][$j][$value] == "contact_purpose_id") {
                $tab[$i][$j]["value"]= $contact->get_label_contact($tab[$i][$j]['value'], $_SESSION['tablename']['contact_purposes']);
                $tab[$i][$j]["contact_purpose_id"] = $tab[$i][$j]['value'];
                $tab[$i][$j]["label"] = _CONTACT_PURPOSE;
                $tab[$i][$j]["size"] = "20";
                $tab[$i][$j]["label_align"] = "left";
                $tab[$i][$j]["align"] = "left";
                $tab[$i][$j]["valign"] = "bottom";
                $tab[$i][$j]["show"] = true;
                $tab[$i][$j]["order"] = 'contact_purpose_id';
            }
            if ($tab[$i][$j][$value] == "departement") {
                $tab[$i][$j]['value'] = $request->show_string(
                    $tab[$i][$j]['value']
                );
                $tab[$i][$j]["departement"] = $tab[$i][$j]['value'];
                $tab[$i][$j]["label"] = _SERVICE;
                $tab[$i][$j]["size"] = "20";
                $tab[$i][$j]["label_align"] = "left";
                $tab[$i][$j]["align"] = "left";
                $tab[$i][$j]["valign"] = "bottom";
                $tab[$i][$j]["show"] = true;
                $tab[$i][$j]["order"] = 'departement';
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
                $tab[$i][$j]["firstname"]= $request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["label"]=_FIRSTNAME;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]= "firstname";
            }
            if($tab[$i][$j][$value]=="function")
            {
                $tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["function"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_FUNCTION;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]= "function";
            }
            if($tab[$i][$j][$value]=="address_town")
            {
                $tab[$i][$j]["address_town"]= $request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["label"]=_TOWN;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]= "address_town";
            }
            if($tab[$i][$j][$value]=="phone")
            {
                $tab[$i][$j]['value']=$request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["phone"]=$tab[$i][$j]['value'];
                $tab[$i][$j]["label"]=_PHONE;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]= "phone";
            }
            if($tab[$i][$j][$value]=="email")
            {
                $tab[$i][$j]["email"]= $request->show_string($tab[$i][$j]['value']);
                $tab[$i][$j]["label"]=_MAIL;
                $tab[$i][$j]["size"]="15";
                $tab[$i][$j]["label_align"]="center";
                $tab[$i][$j]["align"]="center";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]= "email";
            }
        }
    }
}

//List parameters
    $paramsTab = array();
    $paramsTab['bool_modeReturn'] = false;                                              //Desactivation du mode return (vs echo)
    $paramsTab['pageTitle'] =  '';           											//Titre de la page
    $paramsTab['listCss'] =  'listing largerList spec';
    $paramsTab['urlParameters'] = '&dir=my_contacts';                                   //parametre d'url supplementaire
//    $paramsTab['pagePicto'] = $_SESSION['config']['businessappurl']
//            ."static.php?filename=manage_contact_b.gif";                                //Image (pictogramme) de la page
    $paramsTab['bool_sortColumn'] = true;                                               //Affichage Tri
    $paramsTab['bool_showSearchTools'] = true;                                          //Afficle le filtre alphabetique et le champ de recherche
    $paramsTab['searchBoxAutoCompletionUrl'] = $_SESSION['config']['businessappurl']
        ."index.php?display=true&page=contact_addresses_list_by_name";            		//Script pour l'autocompletion
    $paramsTab['searchBoxAutoCompletionMinChars'] = 2;                                  //Nombre minimum de caractere pour activer l'autocompletion (1 par defaut)
    $paramsTab['bool_showAddButton'] = true;                                            //Affichage du bouton Nouveau
    $paramsTab['addButtonLabel'] = _NEW_CONTACT_ADDRESS;                                //Libellé du bouton Nouveau
    if ($from_iframe) {
	    $paramsTab['addButtonScript'] = "window.location='".$_SESSION['config']['businessappurl']
	        ."index.php?display=false&dir=my_contacts&page=create_address_iframe&iframe=iframe_up_add'";
    } else {
	    $paramsTab['addButtonScript'] = "window.top.location='".$_SESSION['config']['businessappurl']
	        ."index.php?page=contact_addresses_add&mycontact=Y'";                          	//Action sur le bouton nouveau (2)
    }

    //Action icons array
    $paramsTab['actionIcons'] = array();
        //get start
        $start = $list2->getStart();
       
       if ($from_iframe) {
	        $update = array(
	                "script"        => "window.location='".$_SESSION['config']['businessappurl']
	                                        ."index.php?display=false&dir=my_contacts&page=update_address_iframe&id=@@id@@'",
	                "class"         =>  "change",
	                "label"         =>  _MODIFY,
	                "tooltip"       =>  _MODIFY
	                );
        } else {
	        $update = array(
	                "script"        => "window.top.location='".$_SESSION['config']['businessappurl']
	                                        ."index.php?page=contact_addresses_up&mycontact=Y&id=@@id@@&what=".$what."&start=".$start."'",
	                "class"         =>  "change",
	                "label"         =>  _MODIFY,
	                "tooltip"       =>  _MODIFY
	                );        	
        }

        array_push($paramsTab['actionIcons'], $update); 

		if ($from_iframe) {
	        $use = array(
	                "script"        => "set_new_contact_address('".$_SESSION['config']['businessappurl'] . "index.php?display=false&dir=my_contacts&page=get_last_contact_address&contactid=".$id."&addressid=@@id@@', 'info_contact_div')",
	                "class"         =>  "change",
	                "label"         =>  _USE
	                );
	        array_push($paramsTab['actionIcons'], $use); 
		} else {
	        $delete = array(
	                "href"          => $_SESSION['config']['businessappurl']
	                                    ."index.php?page=contact_addresses_del&mycontact=Y&what=".$what."&start=".$start,
	                "class"         =>  "delete",
	                "label"         =>  _DELETE,
	                "tooltip"       =>  _DELETE,
	                "alertText"     =>  _REALLY_DELETE.": @@lastname@@ @@firstname@@ ?"
	                );
	        array_push($paramsTab['actionIcons'], $delete);
		}         
   
//Afficher la liste
    echo '<br/>';
    $list2->showList($tab, $paramsTab, 'id');
?>
