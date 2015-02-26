<?php
/*
*
*    Copyright 2008,2012 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief   Displays document list in baskets
*
* @file
* @author Yves Christian Kpakpo <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php";
require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php";
require_once "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
            ."class".DIRECTORY_SEPARATOR."class_contacts_v2.php";
require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_status.php";
require_once "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
            ."class".DIRECTORY_SEPARATOR."class_lists.php";

$status_obj = new manage_status();
$security   = new security();
$core_tools = new core_tools();
$request    = new request();
$contact    = new contacts_v2();
$list       = new lists();
$db         = new dbquery();

//Include definition fields
include_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'definition_mail_categories.php');

//Keep some parameters
$parameters = '';
if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
    $parameters .= '&order='.$_REQUEST['order'];
    if (isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])) $parameters 
		.= '&order_field='.$_REQUEST['order_field'];
}
if (isset($_REQUEST['what']) && !empty($_REQUEST['what'])) $parameters .= '&what='.$_REQUEST['what'];
if (isset($_REQUEST['template']) && !empty($_REQUEST['template'])) $parameters .= '&template='.$_REQUEST['template'];
if (isset($_REQUEST['start']) && !empty($_REQUEST['start'])) $parameters .= '&start='.$_REQUEST['start'];

//URL extra parameters
$urlParameters = '';

//origin
if ($_REQUEST['origin'] == 'searching') $urlParameters .= '&origin=searching';

//Create sql request
if(!empty($_SESSION['current_basket']['view'])) {
	$table = $_SESSION['current_basket']['view'];
} else {
	$table = $_SESSION['current_basket']['table'];
}
$_SESSION['origin'] = 'basket';
$_SESSION['collection_id_choice'] = $_SESSION['current_basket']['coll_id'];//Collection

$db->connect();

//Ressource table
    $select[$table]= array(); 
    //Ressource fields
    array_push($select[$table],"res_id", "res_id as is_persistent", "status", "category_id", "category_id as category_img", 
                            "contact_firstname", "contact_lastname", "contact_society", "user_lastname", 
                            "user_firstname", "priority", "creation_date", "admission_date", "subject", 
                            "process_limit_date", "entity_label", "dest_user", "type_label", 
                            "exp_user_id", "count_attachment", "viewed", "is_multicontacts");
    //Additionnal fields                        
    if($core_tools->is_module_loaded("cases") == true) {
        array_push($select[$table], "case_id", "case_label", "case_description");
    }

//Listinstance table and fields
    // $select[$_SESSION['tablename']['ent_listinstance']] = array();
    // array_push($select[$_SESSION['tablename']['ent_listinstance']], "viewed");

//Where clause
    $where_tab = array();
    
    //From basket
    if (!empty($_SESSION['current_basket']['clause'])) $where_tab[] = stripslashes($_SESSION['current_basket']['clause']); //Basket clause
    
    //From filters
    $filterClause = $list->getFilters(); 
    if (!empty($filterClause)) $where_tab[] = $filterClause;//Filter clause
    
    //From search
    if (
        (isset($_REQUEST['origin']) && $_REQUEST['origin'] == 'searching') 
        && !empty($_SESSION['searching']['where_request'])
    ) $where_tab[] = $_SESSION['searching']['where_request']. '(1=1)'; 
        
    //Get entities limitation 
    /*
    if($_SESSION['current_basket']['basket_owner'] <> "") {
        $db->query("select entity_id from ".$_SESSION['tablename']['ent_users_entities']
                    ." where user_id = '".$this->protect_string_db(trim($_SESSION['current_basket']['basket_owner']))."'");
        $entitiesArray = array();
        while($res = $db->fetch_object()) {
            array_push($entitiesArray,  "'".$res->entity_id."'");
        }
        for($cptEnt=0; $cptEnt<count($_SESSION['user']['entities']); $cptEnt++) {
            array_push($entitiesArray,  "'" . $_SESSION['user']['entities'][$cptEnt]['ENTITY_ID'] . "'");
        }

        $where_tab[] =  $table.".res_id = ".$_SESSION['tablename']['ent_listinstance'].".res_id";
        
        $where_tab[] =  "(".$_SESSION['tablename']['ent_listinstance']
                        .".item_id = '".$_SESSION['current_basket']['basket_owner']
                        ."' or ".$_SESSION['tablename']['ent_listinstance']
                        .".item_id in (" . (implode(', ', $entitiesArray)) . "))";
    } else {
        $entitiesArray = array();
        for($cptEnt=0; $cptEnt<count($_SESSION['user']['entities']); $cptEnt++) {
             array_push($entitiesArray,  "'" . $_SESSION['user']['entities'][$cptEnt]['ENTITY_ID'] . "'");
        }
        $where_tab[] =  $table.".res_id = ".$_SESSION['tablename']['ent_listinstance'].".res_id";
        
        $where_tab[] =  "(".$_SESSION['tablename']['ent_listinstance']
                        .".item_id = '".$_SESSION['user']['UserId']
                        ."' or ".$_SESSION['tablename']['ent_listinstance']
                        .".item_id in (" . (implode(', ', $entitiesArray)) . "))";
    }
    */
    //Build where
    $where = implode(' and ', $where_tab);
    
    //Keep where clause
    if(isset($_REQUEST['origin']) && $_REQUEST['origin'] == 'searching') {
        $where = $_SESSION['searching']['where_request'] . ' '. $where;
    }
    
//Order
    $order = $order_field = '';
    $order = $list->getOrder();
    $order_field = $list->getOrderField();
    if (!empty($order_field) && !empty($order)) 
        $orderstr = "order by ".$order_field." ".$order;
    else  {
        $list->setOrder();
        $list->setOrderField('priority, creation_date, res_id');
        $orderstr = "order by priority, creation_date, res_id desc";
    }
//Templates
    $defaultTemplate = 'documents_list_copies';
    $selectedTemplate = $list->getTemplate();
    if  (empty($selectedTemplate)) {
        if (!empty($defaultTemplate)) {
            $list->setTemplate($defaultTemplate);
            $selectedTemplate = $list->getTemplate();
        }
    }
    $template_list = array();
    array_push($template_list, 'documents_list_copies');
    
    //For status icon
    $extension_icon = '';
    if($selectedTemplate <> 'none') $extension_icon = "_big"; 
    
//Request
    $tab = $request->select($select, $where, $orderstr, $_SESSION['config']['databasetype'], $_SESSION['config']['databasesearchlimit'], false,"", "", "", false, false, 'distinct');
    // $request->show();

 //Result array
for ($i=0;$i<count($tab);$i++)
{
	for ($j=0;$j<count($tab[$i]);$j++)
	{
		foreach(array_keys($tab[$i][$j]) as $value)
		{
			if($tab[$i][$j][$value]=="res_id")
			{
				$tab[$i][$j]["res_id"]=$tab[$i][$j]['value'];
				$tab[$i][$j]["label"]=_GED_NUM;
				$tab[$i][$j]["size"]="4";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='res_id';
				$_SESSION['mlb_search_current_res_id'] = $tab[$i][$j]['value'];
				
				// notes
				$db = new dbquery();
				$db->connect();
				$query = "select ";
				 $query .= "notes.id ";
				$query .= "from ";
				 $query .= "notes "; 
				$query .= "left join "; 
				 $query .= "note_entities "; 
				$query .= "on "; 
				 $query .= "notes.id = note_entities.note_id ";
				$query .= "where ";
				  $query .= "tablename = 'res_letterbox' ";
				 $query .= "AND "; 
				  $query .= "coll_id = 'letterbox_coll' ";
				 $query .= "AND ";
				  $query .= "identifier = " . $tab[$i][$j]['value'] . " ";
				 $query .= "AND ";
				  $query .= "( ";
					$query .= "( ";
					  $query .= "item_id IN (";
					  
					   foreach($_SESSION['user']['entities'] as $entitiestmpnote) {
						$query .= "'" . $entitiestmpnote['ENTITY_ID'] . "', ";
					   }
					   $query = substr($query, 0, -2);
					  
					  $query .= ") ";
					 $query .= "OR "; 
					  $query .= "item_id IS NULL ";
					$query .= ") ";
				   $query .= "OR ";
					$query .= "user_id = '" . $_SESSION['user']['UserId'] . "' ";
				  $query .= ") ";
				  //echo $query . '<br />';
				$db->query($query);
				$tab[$i][$j]['hasNotes'] = $db->fetch_object();
			}
            if($tab[$i][$j][$value]=="is_persistent")
			{
                $db->query("SELECT distinct(res_id) FROM basket_persistent_mode WHERE res_id = "
                        .$_SESSION['mlb_search_current_res_id']." and user_id = '"
                        .$_SESSION['user']['UserId']."' and is_persistent = 'Y'");
                $nb = $db->nb_result();
                if ($nb > 0) {
                    $tab[$i][$j]["value"] = "true";
                } else {
                    $tab[$i][$j]["value"] = "false";
                }
				$tab[$i][$j]["label"]=_IS_PERSISTENT;
				$tab[$i][$j]["size"]="4";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=false;
				$tab[$i][$j]["order"]='is_persistent';
			}
            if($tab[$i][$j][$value]=="creation_date")
            {
                $tab[$i][$j]["value"]=$core_tools->format_date_db($tab[$i][$j]["value"], false);
                $tab[$i][$j]["label"]=_CREATION_DATE;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='creation_date';
            }
			if($tab[$i][$j][$value]=="admission_date")
			{
				$tab[$i][$j]["value"]=$core_tools->format_date_db($tab[$i][$j]["value"], false);
				$tab[$i][$j]["label"]=_ADMISSION_DATE;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=false;
				$tab[$i][$j]["order"]='admission_date';
			}
			if($tab[$i][$j][$value]=="process_limit_date")
			{
				$tab[$i][$j]["value"]=$core_tools->format_date_db($tab[$i][$j]["value"], false);
				$compareDate = "";
				if($tab[$i][$j]["value"] <> "" && ($statusCmp == "NEW" || $statusCmp == "COU" || $statusCmp == "VAL" || $statusCmp == "RET"))
				{
					$compareDate = $core_tools->compare_date($tab[$i][$j]["value"], date("d-m-Y"));
					if($compareDate == "date2")
					{
						$tab[$i][$j]["value"] = "<span style='color:red;'><b>".$tab[$i][$j]["value"]."<br><small>(".$core_tools->nbDaysBetween2Dates($tab[$i][$j]["value"], date("d-m-Y"))." "._DAYS.")<small></b></span>";
					}
					elseif($compareDate == "date1")
					{
						$tab[$i][$j]["value"] = $tab[$i][$j]["value"]."<br><small>(".$core_tools->nbDaysBetween2Dates(date("d-m-Y"), $tab[$i][$j]["value"])." "._DAYS.")<small>";
					}
					elseif($compareDate == "equal")
					{
						$tab[$i][$j]["value"] = "<span style='color:blue;'><b>".$tab[$i][$j]["value"]."<br><small>("._LAST_DAY.")<small></b></span>";
					}
				}
				$tab[$i][$j]["label"]=_PROCESS_LIMIT_DATE;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='process_limit_date';
			}
			if($tab[$i][$j][$value]=="category_id")
			{
				$_SESSION['mlb_search_current_category_id'] = $tab[$i][$j]["value"];
				// $tab[$i][$j]["value"] = $_SESSION['mail_categories'][$tab[$i][$j]["value"]];
				$tab[$i][$j]["label"]=_CATEGORY;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='category_id';
			}
			if($tab[$i][$j][$value]=="priority")
			{
				$tab[$i][$j]["value"] = $_SESSION['mail_priorities'][$tab[$i][$j]["value"]];
				$tab[$i][$j]["label"]=_PRIORITY;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=false;
				$tab[$i][$j]["order"]='priority';
			}
			if($tab[$i][$j][$value]=="subject")
			{
				$tab[$i][$j]["value"] = $request->cut_string($request->show_string($tab[$i][$j]["value"]), 250);
				$tab[$i][$j]["label"]=_SUBJECT;
				$tab[$i][$j]["size"]="12";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='subject';
			}
            if($tab[$i][$j][$value]=="category_id")
            {
                $_SESSION['mlb_search_current_category_id'] = $tab[$i][$j]["value"];
                $tab[$i][$j]["value"] = $_SESSION['mail_categories'][$tab[$i][$j]["value"]];
                $tab[$i][$j]["label"]=_CATEGORY;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='category_id';
            }
            if($tab[$i][$j][$value]=="contact_firstname")
            {
                $contact_firstname = $tab[$i][$j]["value"];
                $tab[$i][$j]["show"]=false;
            }
            if($tab[$i][$j][$value]=="contact_lastname")
            {
                $contact_lastname = $tab[$i][$j]["value"];
                $tab[$i][$j]["show"]=false;
            }
            if($tab[$i][$j][$value]=="contact_society")
            {
                $contact_society = $tab[$i][$j]["value"];
                $tab[$i][$j]["show"]=false;
            }
            if($tab[$i][$j][$value]=="user_firstname")
            {
                $user_firstname = $tab[$i][$j]["value"];
                $tab[$i][$j]["show"]=false;
            }
            if($tab[$i][$j][$value]=="user_lastname")
            {
                $user_lastname = $tab[$i][$j]["value"];
                $tab[$i][$j]["show"]=false;
            }
            if($tab[$i][$j][$value]=="exp_user_id")
            {
                $tab[$i][$j]["label"]=_CONTACT;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=false;
                $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                $tab[$i][$j]["value"] = $contact->get_contact_information_from_view($_SESSION['mlb_search_current_category_id'], $contact_lastname, $contact_firstname, $contact_society, $user_lastname, $user_firstname);
                $tab[$i][$j]["order"]=false;
            }
            if($tab[$i][$j][$value]=="is_multicontacts")
            {
				if($tab[$i][$j]['value'] == 'Y'){
					$tab[$i][$j]["label"]=_CONTACT;
					$tab[$i][$j]["size"]="10";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=false;
					$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
					$tab[$i][$j]["value"] = _MULTI_CONTACT;
					$tab[$i][$j]["order"]=false;
				}
            }
			if($tab[$i][$j][$value]=="type_label")
			{
				$tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
				$tab[$i][$j]["label"]=_TYPE;
				$tab[$i][$j]["size"]="12";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='type_label';
			}
			if($tab[$i][$j][$value]=="status")
			{
				$res_status = $status_obj->get_status_data($tab[$i][$j]['value'],$extension_icon);
				$statusCmp = $tab[$i][$j]['value'];
				$tab[$i][$j]['value'] = "<img src = '".$res_status['IMG_SRC']."' alt = '".$res_status['LABEL']."' title = '".$res_status['LABEL']."'>";
				$tab[$i][$j]["label"]=_STATUS;
				$tab[$i][$j]["size"]="4";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=true;
				$tab[$i][$j]["order"]='status';
			}
			if($tab[$i][$j][$value]=="category_img")
			{
				$tab[$i][$j]["label"]=_CATEGORY;
				$tab[$i][$j]["size"]="10";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=false;
				$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
				$my_imgcat = get_img_cat($tab[$i][$j]['value'],$extension_icon);
				$tab[$i][$j]['value'] = $my_imgcat;
				$tab[$i][$j]["value"] = $tab[$i][$j]['value'];
				$tab[$i][$j]["order"]="category_id";
			}
            if($tab[$i][$j][$value]=="count_attachment")
			{
				$tab[$i][$j]["label"]=_ATTACHMENTS;
				$tab[$i][$j]["size"]="12";
				$tab[$i][$j]["label_align"]="left";
				$tab[$i][$j]["align"]="left";
				$tab[$i][$j]["valign"]="bottom";
				$tab[$i][$j]["show"]=false;
				$tab[$i][$j]["order"]='count_attachment';
			}
            if($tab[$i][$j][$value]=="case_id" && $core_tools->is_module_loaded("cases") == true)
            {
                $tab[$i][$j]["label"]=_CASE_NUM;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=false;
                $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                $tab[$i][$j]["value"] = "<a href='".$_SESSION['config']['businessappurl']."index.php?page=details_cases&module=cases&id=".$tab[$i][$j]['value']."'>".$tab[$i][$j]['value']."</a>";
                $tab[$i][$j]["order"]="case_id";
            }
            if($tab[$i][$j][$value]=="case_label" && $core_tools->is_module_loaded("cases") == true)
            {
                $tab[$i][$j]["label"]=_CASE_LABEL;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=false;
                $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                $tab[$i][$j]["order"]="case_label";
            }
            if($tab[$i][$j][$value]=="viewed")
            {
                $tab[$i][$j]["label"]=_VIEWED;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='viewed';
				$db->query("select viewed from listinstance where res_id = " 
					. $_SESSION['mlb_search_current_res_id'] 
					. " and coll_id = '" . $_SESSION['current_basket']['coll_id'] . "'"
					. " and item_id = '".$_SESSION['user']['UserId']."'");
				$lineViewed = $db->fetch_object();
				$tab[$i][$j]['value'] = $lineViewed->viewed;
            }
		}
	}
}
//var_dump($tab);exit;
//Clé de la liste
$listKey = 'res_id';

//Initialiser le tableau de paramètres
$paramsTab = array();
$paramsTab['pageTitle'] =  _RESULTS." : ".count($tab).' '._FOUND_DOCS;              //Titre de la page
$paramsTab['listCss'] = 'listing largerList spec';                                  //css
// $paramsTab['bool_sortColumn'] = true;                                               //Affichage Tri
$paramsTab['bool_bigPageTitle'] = false;                                            //Affichage du titre en grand
$paramsTab['bool_showIconDocument'] = true;                                         //Affichage de l'icone du document
$paramsTab['bool_showIconDetails'] = true;                                          //Affichage de l'icone de la page de details
$paramsTab['bool_showAttachment'] = true;                                           //Affichage du nombre de document attaché (mode étendu)
$paramsTab['urlParameters'] = 'baskets='.$_SESSION['current_basket']['id']
        .$urlParameters;                                                            //Parametres d'url supplementaires
$paramsTab['filters'] = array('entity', 'category', 'isViewed', 'contact');         //Filtres    
if (count($template_list) > 0 ) {                                                   //Templates
    $paramsTab['templates'] = array();
    $paramsTab['templates'] = $template_list;
}
$paramsTab['defaultTemplate'] = $defaultTemplate;                                   //Default template
$paramsTab['bool_showTemplateDefaultList'] = true;                                          //Default list (no template)
$paramsTab['tools'] = array();                                                      //Icones dans la barre d'outils
//Fileplan
if ($core_tools->test_service('fileplan', 'fileplan', false)) {
    require_once "modules" . DIRECTORY_SEPARATOR . "fileplan" . DIRECTORY_SEPARATOR
        . "class" . DIRECTORY_SEPARATOR . "class_modules_tools.php";
    $fileplan = new fileplan();
    if (
		count($fileplan->getUserFileplan()) > 0 
		|| (count($fileplan->getEntitiesFileplan()) > 0 
			&& $core_tools->test_service('put_doc_in_fileplan', 'fileplan', false)
			)
	) {
        $paramsTab['bool_checkBox'] = true;
        $paramsTab['bool_standaloneForm'] = true;
        $positions = array(
                "script"        =>  "showFileplanList('".$_SESSION['config']['businessappurl']  
                                        . "index.php?display=true&module=fileplan&page=fileplan_ajax_script"
                                        . "&mode=setPosition&origin=basket&coll_id=".$_SESSION['current_basket']['coll_id']
                                        . $parameters."', 'formList', '600px', '510px', '"
                                        . _CHOOSE_ONE_DOC."')",
                "icon"          =>  $_SESSION['config']['businessappurl']."static.php?module=fileplan&filename=tool_fileplan.gif",
                "tooltip"       =>  _FILEPLAN,
                "disabledRules" =>  count($tab)." == 0 || ".$selectedTemplate." == 'cases_list_search_adv'"
                );      
        array_push($paramsTab['tools'],$positions);
    }
}
if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == 'searching')  {
    $save = array(
            "script"        =>  "createModal(form_txt, 'save_search', '100px', '500px');window.location.href='#top';",
            "icon"          =>  $_SESSION['config']['businessappurl']."static.php?filename=tool_save.gif",
            "tooltip"       =>  _SAVE_QUERY,
            "disabledRules" =>  count($tab)." == 0"
            );      
    array_push($paramsTab['tools'],$save); 
}
$export = array(
        "script"        =>  "window.open('".$_SESSION['config']['businessappurl']."index.php?display=true&page=export', '_blank');",
        "icon"          =>  $_SESSION['config']['businessappurl']."static.php?&filename=tool_export.gif",
        "tooltip"       =>  _EXPORT_LIST,
        "disabledRules" =>  count($tab)." == 0"
        );
array_push($paramsTab['tools'],$export);
if ($core_tools->test_service('print_doc_details_from_list', 'apps', false)) { 
	$print = array(
			"script"        =>  "window.open('".$_SESSION['config']['businessappurl']."index.php?display=true&page=print', '_blank');",
			"icon"          =>  $_SESSION['config']['businessappurl']."static.php?filename=tool_print.gif",
			"tooltip"       =>  _PRINT_LIST,
			"disabledRules" =>  count($tab)." == 0"
			);
	array_push($paramsTab['tools'], $print);   
}
//Afficher la liste
$status = 0;
$content = $list->showList($tab, $paramsTab, $listKey, $_SESSION['current_basket']);
// $debug = $list->debug();

$content .= "<script>$$('#container')[0].setAttribute('style', 'width: 90%; min-width: 1000px;');".
                    "$$('#content')[0].setAttribute('style', 'width: auto; min-width: 1000px;');".
                    "$$('#inner_content')[0].setAttribute('style', 'width: auto; min-width: 1000px;');".
                    // "$$('table#extended_list')[0].setAttribute('style', 'width: 100%; min-width: 900px; margin: 0;');".
            "</script>";

echo "{status : " . $status . ", content : '" . addslashes($debug.$content) . "', error : '" . addslashes($error) . "'}";
?>
