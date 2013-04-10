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
* @brief   Displays document list in search mode
*
* @file
* @author Yves Christian Kpakpo <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php";
require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php";
require_once "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
            ."class".DIRECTORY_SEPARATOR."class_contacts.php";
require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_status.php";
require_once "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
            ."class".DIRECTORY_SEPARATOR."class_lists.php";
 
$status_obj = new manage_status();
$sec        = new security();
$core_tools = new core_tools();
$request    = new request();
$contact    = new contacts();
$list       = new lists();

//Labels
if($core_tools->is_module_loaded('labels')) {
    require_once "modules" . DIRECTORY_SEPARATOR . "labels" . DIRECTORY_SEPARATOR
    . "class" . DIRECTORY_SEPARATOR
    . "class_modules_tools.php";
    $labels     = new labels();
}

//Include definition fields
include_once('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'definition_mail_categories.php');

//Parameters
$urlParameters = '';
    //Mode
    $mode = 'normal';
    if(isset($_REQUEST['mode'])&& !empty($_REQUEST['mode']))
    {
        $mode = $core_tools->wash($_REQUEST['mode'], "alphanum", _MODE);
    }
     $urlParameters .= '&mode='.$mode;
    //No details
    $showIconDetails = true;
    if(isset($_REQUEST['nodetails'])) {
        $showIconDetails = false;
        $urlParameters .= '&nodetails';
    }
    //module
    if(isset($_REQUEST['modulename'])) {
        $urlParameters .= '&modulename='.$_REQUEST['modulename'];
    }

    //Form
    if(isset($_REQUEST['action_form'])) {
        $urlParameters .= '&action_form='.$_REQUEST['action_form'];
    }

//Start    
if($mode == 'normal') {

    $saveTool       = true;
    $useTemplate    = true;
    $exportTool     = true;
    $bigPageTitle   = true;
    $standaloneForm = false;
    $radioButton    = false;
    
    //Templates
    $defaultTemplate = 'documents_list_search_adv';
    $selectedTemplate = $list->getTemplate();
    if  (empty($selectedTemplate)) {
        if (!empty($defaultTemplate)) {
            $list->setTemplate($defaultTemplate);
            $selectedTemplate = $list->getTemplate();
        }
    }
    $template_list = array();
    array_push($template_list, 'documents_list_search_adv');
    if($core_tools->is_module_loaded('cases')) array_push($template_list, 'cases_list_search_adv');
    
    //For status icon
    $extension_icon = '';
    if($selectedTemplate <> 'none') $extension_icon = "_big";
    
    //error and search url
    $url_error = $_SESSION['config']['businessappurl'].'index.php?page=search_adv_error&dir=indexing_searching';
    $url_search = $_SESSION['config']['businessappurl'].'index.php?page=search_adv&dir=indexing_searching';

     //error 
    $_SESSION['error_search'] = '<p class="error"><img src="'
        .$_SESSION['config']['businessappurl'].'static.php?filename=noresult.gif" /><br />'
        ._NO_RESULTS.'</p><br/><br/><div align="center"><strong><a href="javascript://" '
        .' onclick = "window.top.location.href=\''.$url_search.'\'">'._MAKE_NEW_SEARCH.'</a></strong></div>';


} elseif($mode == 'popup' || $mode == 'frame') {

    $saveTool       = false;
    $useTemplate    = false;
    $exportTool     = false;
    $bigPageTitle   = false;
    $radioButton    = true;
    
    if($mode == 'popup') {
        //Form object
        $standaloneForm = true;
        $formMethod = 'get';
        $hiddenFormFields = array();    
        array_push($hiddenFormFields, array( "ID" => "display", "NAME" => "display", "VALUE"=> "true"));
        array_push($hiddenFormFields, array( "ID" => "page", "NAME" => "page", "VALUE"=> $_REQUEST['action_form']));
        if(isset($_REQUEST['modulename'])&& !empty($_REQUEST['modulename'])){
            array_push($hiddenFormFields, array( "ID" => "module", "NAME" => "module", "VALUE"=> $_REQUEST['modulename']));
             $formAction = $_SESSION['config']['businessappurl']
                ."index.php?display=true&page="
                .$_REQUEST['action_form']."&module=".$_REQUEST['modulename'];
        } else {
            $formAction = $_SESSION['config']['businessappurl']
                ."index.php?display=true&page="
                .$_REQUEST['action_form'];
        }
        
        $buttons = array();                                        
        array_push( $buttons, array('ID'        => 'valid', 
                                    'LABEL'     => _VALIDATE, 
                                    'ACTION'    => 'formList.submit();'
                                   )
                    );
        array_push( $buttons, array('ID'        => 'close', 
                                    'LABEL'     => _CLOSE_WINDOW, 
                                    'ACTION'    => 'window.top.close();'
                                   )
                    );
    }
           
    //error and search url
    $url_error = $_SESSION['config']['businessappurl']
        .'index.php?display=true&dir=indexing_searching'
        .'&page=search_adv_error';
    $url_search = $_SESSION['config']['businessappurl']
        .'index.php?display=true&dir=indexing_searching'
        .'&page=search_adv&load&mode='.$mode.$urlParameters;
    
    //Displayed error text
    $_SESSION['error_search'] = '<p class="error"><img src="'
        .$_SESSION['config']['businessappurl'].'static.php?filename=noresult.gif" /><br />'
        ._NO_RESULTS.'</p><br/><br/><div align="center"><strong><a href="javascript://" '
        .' onclick = "window.top.location.href=\''.$url_search.'\'">'._MAKE_NEW_SEARCH.'</a></strong></div>';
}

/************Construction de la requete*******************/
//Table or view
    $_SESSION['collection_id_choice'] = 'letterbox_coll';
    $view = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
    $select = array();
    $select[$view]= array();

//Fields
    //Documents
    array_push($select[$view],  "res_id as is_labeled", "res_id", "status", "subject", "category_id as category_img", 
                                "contact_firstname", "contact_lastname", "contact_society", 
                                "user_lastname", "user_firstname", "dest_user", "type_label", 
                                "creation_date", "entity_label", "category_id, exp_user_id");
    //Cases
    if($core_tools->is_module_loaded("cases") == true) {
        array_push($select[$view], "case_id", "case_label", "case_description");
    }
    
//Where clause
    $where_tab = array();
    //From search
    if (!empty($_SESSION['searching']['where_request'])) $where_tab[] = $_SESSION['searching']['where_request']. '(1=1)';
    
    //From popup excluding some id
    if ($_REQUEST['mode'] == 'popup' && isset($_SESSION['excludeId'])) {
        $where_tab[] = 'res_id <> '.$_SESSION['excludeId'].' and '
                        . '(res_id not in (SELECT res_parent FROM res_linked WHERE res_child = '.$_SESSION['excludeId'].') and '
                        . 'res_id not in (SELECT res_child FROM res_linked WHERE res_parent = '.$_SESSION['excludeId'].'))';
        unset($_SESSION['excludeId']);
    }
    
    //From searching comp query
    if(isset($_SESSION['searching']['comp_query']) && trim($_SESSION['searching']['comp_query']) <> '') {

        $where_clause = $sec->get_where_clause_from_coll_id($_SESSION['collection_id_choice']);

        if(count($where_tab) <> 0) {
            $where = implode(' and ', $where_tab);
            $where_request = '('.$where.') and (('.$where_clause.') or ('.$_SESSION['searching']['comp_query'].'))';
        } else {
            $where_request = '('.$where_clause.' or '.$_SESSION['searching']['comp_query'].')';
        }
        $add_security = false;
        
    } else {
        $status = $status_obj->get_not_searchable_status();   

        if(count($status) > 0) {    
            $status_tab = array();
            $status_str = '';
            for($i=0; $i<count($status);$i++){
                    array_push($status_tab, "'".$status[$i]['ID']."'");
            }
            $status_str = implode(' ,', $status_tab);
            $where_tab[] = "status not in (".$status_str.")";
        }

        $where_request = implode(' and ', $where_tab);
        $add_security = true;
    }
    
//Order
    $order = $order_field = '';
    $order = $list->getOrder();
    $order_field = $list->getOrderField();
    if (!empty($order_field) && !empty($order)) 
        $orderstr = "order by ".$order_field." ".$order;
    else  {
        $list->setOrder();
        $list->setOrderField('creation_date');
        $orderstr = "order by creation_date desc";
    }
    
//URL extra Parameters  
    $parameters = '';
    $start = $list->getStart();
    if (!empty($order_field) && !empty($order)) $parameters .= '&order='.$order.'&order_field='.$order_field;
    if (!empty($what)) $parameters .= '&what='.$what;
    if (!empty($selectedTemplate)) $parameters .= '&template='.$selectedTemplate;
    if (!empty($start)) $parameters .= '&start='.$start;
        
//Query    
    $tab=$request->select($select,$where_request,$orderstr,$_SESSION['config']['databasetype'],"default", false, "", "", "", $add_security);
    // $request->show();
    
//Result array
    for ($i=0;$i<count($tab);$i++)
    {
        for ($j=0;$j<count($tab[$i]);$j++)
        {
            foreach(array_keys($tab[$i][$j]) as $value)
            {
                if($tab[$i][$j][$value]=='is_labeled' 
                    && $core_tools->is_module_loaded('labels')
                    && (isset($_SESSION['user']['services']['labels'])
                    && $_SESSION['user']['services']['labels'] === true)
                )
                {
                    $str_label = $labels->get_labels_resid($tab[$i][$j]['value'], $_SESSION['collection_id_choice']);
                    if (!empty($str_label))  $tab[$i][$j]['value'] = '<img src="'
                        .$_SESSION['config']['businessappurl']
                        .'static.php?module=labels&filename=labels.gif" width="24px" height="24px" title="'
                        .$str_label.'"/>'; else  $tab[$i][$j]['value'] = '&nbsp;';
                    $tab[$i][$j]["label"]='<img src="'
                        .$_SESSION['config']['businessappurl']
                        .'static.php?filename=star.png" width="16px" height="16px" title="'
                        ._LABELS.'"/>';
                    $tab[$i][$j]["size"]="4";
                    $tab[$i][$j]["label_align"]="left";
                    $tab[$i][$j]["align"]="left";
                    $tab[$i][$j]["valign"]="bottom";
                    $tab[$i][$j]["show"]=true;
                    $tab[$i][$j]["order"]=false;
                }

                if($tab[$i][$j][$value]=='res_id')
                {
                    $tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
                    $tab[$i][$j]["label"]=_GED_NUM;
                    $tab[$i][$j]["size"]="4";
                    $tab[$i][$j]["label_align"]="left";
                    $tab[$i][$j]["align"]="left";
                    $tab[$i][$j]["valign"]="bottom";
                    $tab[$i][$j]["show"]=true;
                    $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                    $tab[$i][$j]["order"]='res_id';
                    $_SESSION['mlb_search_current_res_id'] = $tab[$i][$j]['value'];
                }
                
                if($tab[$i][$j][$value]=="type_label")
                {
                    $tab[$i][$j]["label"]=_TYPE;
                    $tab[$i][$j]['value'] = $request->show_string($tab[$i][$j]['value']);
                    $tab[$i][$j]["size"]="15";
                    $tab[$i][$j]["label_align"]="left";
                    $tab[$i][$j]["align"]="left";
                    $tab[$i][$j]["valign"]="bottom";
                    $tab[$i][$j]["show"]=true;
                    $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                    $tab[$i][$j]["order"]="type_label";
                }
                
                if($tab[$i][$j][$value]=="status")
                {
                    $tab[$i][$j]["label"]=_STATUS;
                    $res_status = $status_obj->get_status_data($tab[$i][$j]['value'],$extension_icon);
                    $tab[$i][$j]['value'] = '<img src = "'.$res_status['IMG_SRC'].'" alt = "'.$res_status['LABEL'].'" title = "'.$res_status['LABEL'].'">';
                    $tab[$i][$j]["size"]="5";
                    $tab[$i][$j]["label_align"]="left";
                    $tab[$i][$j]["align"]="left";
                    $tab[$i][$j]["valign"]="bottom";
                    $tab[$i][$j]["show"]=true;
                    $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                    $tab[$i][$j]["order"]="status";
                }
                
                if($tab[$i][$j][$value]=="subject")
                {
                    $tab[$i][$j]["label"]=_SUBJECT;
                    $tab[$i][$j]["value"] = $request->cut_string($request->show_string($tab[$i][$j]["value"]), 250);
                    $tab[$i][$j]["size"]="25";
                    $tab[$i][$j]["label_align"]="left";
                    $tab[$i][$j]["align"]="left";
                    $tab[$i][$j]["valign"]="bottom";
                    $tab[$i][$j]["show"]=true;
                    $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                    $tab[$i][$j]["order"]="subject";
                }
                
                if($tab[$i][$j][$value]=="dest_user")
                {
                    $tab[$i][$j]["label"]=_DEST_USER;
                    $tab[$i][$j]["size"]="10";
                    $tab[$i][$j]["label_align"]="left";
                    $tab[$i][$j]["align"]="left";
                    $tab[$i][$j]["valign"]="bottom";
                    $tab[$i][$j]["show"]=false;
                    $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                    $tab[$i][$j]["order"]="dest_user";
                }
                
                if($tab[$i][$j][$value]=="creation_date")
                {
                    $tab[$i][$j]["label"]=_REG_DATE;
                    $tab[$i][$j]["size"]="10";
                    $tab[$i][$j]["label_align"]="left";
                    $tab[$i][$j]["align"]="left";
                    $tab[$i][$j]["valign"]="bottom";
                    $tab[$i][$j]["show"]=true;
                    $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                    $tab[$i][$j]["value"] = $request->format_date_db($tab[$i][$j]['value'], false);
                    $tab[$i][$j]["order"]="creation_date";
                }
                
                if($tab[$i][$j][$value]=="entity_label")
                {
                    $tab[$i][$j]["label"]=_ENTITY;
                    $tab[$i][$j]['value'] = $request->show_string($tab[$i][$j]['value']);
                    $tab[$i][$j]["size"]="10";
                    $tab[$i][$j]["label_align"]="left";
                    $tab[$i][$j]["align"]="left";
                    $tab[$i][$j]["valign"]="bottom";
                    $tab[$i][$j]["show"]=false;
                    $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                    $tab[$i][$j]["order"]="entity_label";
                }
                
                if($tab[$i][$j][$value]=="category_id")
                {
                    $_SESSION['mlb_search_current_category_id'] = $tab[$i][$j]['value'];
                    $tab[$i][$j]["value"] = $_SESSION['coll_categories']['letterbox_coll'][$tab[$i][$j]['value']];
                    $tab[$i][$j]["label"]=_CATEGORY;
                    $tab[$i][$j]["size"]="10";
                    $tab[$i][$j]["label_align"]="left";
                    $tab[$i][$j]["align"]="left";
                    $tab[$i][$j]["valign"]="bottom";
                    $tab[$i][$j]["show"]=true;
                    $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                    $tab[$i][$j]["order"]="category_id";
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
                    $tab[$i][$j]['value'] = "<img src = '".$my_imgcat."' alt = '' title = ''>";
                    $tab[$i][$j]["value"] = $tab[$i][$j]['value'];
                    $tab[$i][$j]["order"]="category_id";
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
                
                if($tab[$i][$j][$value]=="$template_to_use exp_user_id")
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
                    $tab[$i][$j]["show"]=true;
                    $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                    $tab[$i][$j]["order"]="case_id";
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
                
            }
        }
    }

if (count($tab) > 0) {

    /************Construction de la liste*******************/
    //Clé de la liste
    $listKey = 'res_id';

    //Initialiser le tableau de paramètres
    $paramsTab = array();
    $paramsTab['bool_modeReturn'] = false;                                              //Desactivation du mode return (vs echo)
    $paramsTab['urlParameters'] =  $urlParameters.'&dir=indexing_searching';            //Parametres supplémentaires
    $paramsTab['pageTitle'] =  _RESULTS." : ".count($tab).' '._FOUND_DOCS;              //Titre de la page
    $paramsTab['pagePicto'] =  $_SESSION['config']['businessappurl']
        ."static.php?filename=picto_search_b.gif";                                      //Image de la page
    $paramsTab['bool_bigPageTitle'] = $bigPageTitle;                                    //Titre de la page en grand
    $paramsTab['bool_showIconDocument'] =  true;                                        //Affichage de l'icone du document
    $paramsTab['bool_showIconDetails'] =  $showIconDetails;                             //Affichage de l'icone de la page de details
    $paramsTab['bool_showAttachment'] = true;                                           //Affichage du nombre de document attaché (mode étendu)
    if ($radioButton) {                                                                 //Boutton radio
        $paramsTab['bool_radioButton'] = $radioButton;
    }                                 
    $paramsTab['defaultTemplate'] = $defaultTemplate;                                   //Default template
    if ($useTemplate && count($template_list) >0 ) {                                    //Templates
        $paramsTab['templates'] = array();
        $paramsTab['templates'] = $template_list;
    }
    $paramsTab['bool_showTemplateDefaultList'] = true;                                  //Default list (no template)
    
    //Form attributs
        //Standalone form
        $paramsTab['bool_standaloneForm'] = $standaloneForm;   
        //Method
        if (isset($formMethod) && !empty($formMethod)) $paramsTab['formMethod'] = $formMethod;
        //Action
        if (isset($formAction) && !empty($formAction)) $paramsTab['formAction'] = $formAction;
        //Hiden fields
        if (isset($hiddenFormFields) && count($hiddenFormFields) > 0) {                 //Champs hidden supplémentaire | mots clés = id, name, value
            $paramsTab['hiddenFormFields'] = array();                             
            $paramsTab['hiddenFormFields'] = $hiddenFormFields;                             
        }
        //Buttons
        if (isset($buttons) && count($buttons) > 0) {                                   //Liste des boutons de formulaire
            $paramsTab['buttons'] = array();  
            $paramsTab['buttons'] = $buttons;  
            
        }
        
    //Toolbar
        $paramsTab['tools'] = array();                                                  //Icones dans la barre d'outils
        
        //Fileplan
		if ($core_tools->test_service('fileplan', 'fileplan', false)) {
            if ($mode = 'normal')  {
                require_once "modules" . DIRECTORY_SEPARATOR . "fileplan" . DIRECTORY_SEPARATOR
                    . "class" . DIRECTORY_SEPARATOR . "class_modules_tools.php";
                $fileplan     = new fileplan();
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
                                                    . "&mode=setPosition&origin=search&coll_id=".$_SESSION['collection_id_choice']
                                                    . $parameters."', 'formList', '600px', '510px', '"
                                                    . _CHOOSE_ONE_DOC."')",
                            "icon"          =>  $_SESSION['config']['businessappurl']."static.php?module=fileplan&filename=tool_fileplan.gif",
                            "tooltip"       =>  _FILEPLAN,
                            "disabledRules" =>  count($tab)." == 0 || ".$selectedTemplate." == 'cases_list_search_adv'"
                            );      
                    array_push($paramsTab['tools'],$positions);
                }
            }
        }
        
        if($saveTool) {
            $save = array(
                    "script"        =>  "createModal(form_txt);window.location.href='#top';",
                    "icon"          =>  $_SESSION['config']['businessappurl']."static.php?filename=tool_save.gif",
                    "tooltip"       =>  _SAVE_QUERY,
                    "disabledRules" =>  count($tab)." == 0"
                    );      
            array_push($paramsTab['tools'],$save);   
        }
        
        if($exportTool) { 
            $export = array(
                    "script"        =>  "window.open('".$_SESSION['config']['businessappurl']."index.php?display=true&page=export', '_blank');",
                    "icon"          =>  $_SESSION['config']['businessappurl']."static.php?filename=tool_export.gif",
                    "tooltip"       =>  _EXPORT_LIST,
                    "disabledRules" =>  count($tab)." == 0"
                    );
            array_push($paramsTab['tools'],$export);   
        }

    //Afficher la liste
        $list->showList($tab, $paramsTab, $listKey);
        // $list->debug();

    /*************************Extra javascript***********************/
    ?>
    <script type="text/javascript">
        var form_txt='<form name="frm_save_query" id="frm_save_query" action="#" method="post" class="forms" onsubmit="send_request(this.id);" ><h2><?php 
			echo _SAVE_QUERY_TITLE;?></h2><p><label for="query_name"><?php echo _QUERY_NAME;
			?></label><input type="text" name="query_name" id="query_name" style="width:200px;" value=""/></p><br/><p class="buttons"><input type="submit" name="submit" id="submit" value="<?php 
			echo _VALIDATE;?>" class="button"/> <input type="button" name="cancel" id="cancel" value="<?php echo _CANCEL;
			?>" class="button" onclick="destroyModal();"/></p></form>';

        function send_request(form_id)
        {
            var form = $(form_id);
            if(form)
            {
                var q_name = form.query_name.value;
                $('modal').innerHTML = '<img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=loading.gif" />';

                new Ajax.Request('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&dir=indexing_searching&page=manage_query',
                {
                    method:'post',
                    parameters: {name: q_name,
                                action : "creation"},
                    onSuccess: function(answer){
                        eval("response = "+answer.responseText)
                        if(response.status == 0)
                        {
                            $('modal').innerHTML ='<h2><?php echo _QUERY_SAVED;?></h2><br/><input type="button" name="close" value="<?php echo _CLOSE_WINDOW;?>" onclick="destroyModal();" class="button" />';
                        }
                        else if(response.status == 2)
                        {
                            $('modal').innerHTML = '<div class="error"><?php echo _SQL_ERROR;?></div>'+form_txt;
                            form.query_name.value = this.name;
                        }
                        else if(response.status == 3)
                        {
                            $('modal').innerHTML = '<div class="error"><?php echo _QUERY_NAME.' '._IS_EMPTY;?></div>'+form_txt;
                            form.query_name.value = this.name;
                        }
                        else
                        {
                            $('modal').innerHTML = '<div class="error"><?php echo _SERVER_ERROR;?></div>'+form_txt;
                            form.query_name.value = this.name;
                        }
                    },
                    onFailure: function(){
                        $('modal').innerHTML = '<div class="error"><?php echo _SERVER_ERROR;?></div>'+form_txt;
                        form.query_name.value = this.name;
                       }
                });
            }
        }
    </script>
    <?php
     exit();
} else {
    
    echo '<script type="text/javascript">window.top.location.href=\''.$url_error.'\';</script>';
    exit();
}