<?php
/*
*
*   Copyright 2008-2013 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief   Displays document list in search mode
*
* @file
* @author Yves Christian Kpakpo <dev@maarch.org>
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

require_once 'core/class/class_request.php';
require_once 'core/class/class_security.php';
require_once 'core/class/class_manage_status.php';
require_once 'apps/' . $_SESSION['config']['app_id'] . '/class/class_contacts_v2.php';
require_once 'apps/' . $_SESSION['config']['app_id'] . '/class/class_lists.php';
 
$status_obj = new manage_status();
$sec        = new security();
$core_tools = new core_tools();
$request    = new request();
$contact    = new contacts_v2();
$list       = new lists();

//Labels
if($core_tools->is_module_loaded('labels')) {
    require_once 'modules/labels/class/class_modules_tools.php';
    $labels     = new labels();
}

//Include definition fields
include_once('apps/' . $_SESSION['config']['app_id'] . '/definition_mail_categories_business.php');

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
    $printTool      = true;
    $bigPageTitle   = true;
    $standaloneForm = false;
    $radioButton    = false;
    
    //Templates
    $defaultTemplate = 'documents_business_list';
    $selectedTemplate = $list->getTemplate();
    if  (empty($selectedTemplate)) {
        if (!empty($defaultTemplate)) {
            $list->setTemplate($defaultTemplate);
            $selectedTemplate = $list->getTemplate();
        }
    }
    $template_list = array();
    array_push($template_list, 'documents_business_list');
    
    //For status icon
    $extension_icon = '';
    if($selectedTemplate <> 'none') $extension_icon = "_big";
    
    //error and search url
    $url_error = $_SESSION['config']['businessappurl'].'index.php?page=search_adv_error_business&dir=indexing_searching';
    $url_search = $_SESSION['config']['businessappurl'].'index.php?page=search_adv_business&dir=indexing_searching';

     //error 
    $_SESSION['error_search'] = '<p class="error"><i class="fa fa-remove fa-2x"></i><br />'
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
        .'&page=search_adv_error_business';
    $url_search = $_SESSION['config']['businessappurl']
        .'index.php?display=true&dir=indexing_searching'
        .'&page=search_adv_business&load&mode='.$mode.$urlParameters;
    
    //Displayed error text
    $_SESSION['error_search'] = '<p class="error"><i class="fa fa-remove fa-2x"></i><br />'
        ._NO_RESULTS.'</p><br/><br/><div align="center"><strong><a href="javascript://" '
        .' onclick = "window.top.location.href=\''.$url_search.'\'">'._MAKE_NEW_SEARCH.'</a></strong></div>';
}

/************Construction de la requete*******************/
    //Table or view
    $_SESSION['collection_id_choice'] = 'business_coll';
    $view = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
    $select = array();
    $select[$view]= array();

    //Fields
    //Documents
    
    array_push($select[$view], 'res_id', 'status', 'category_id', 'category_id as category_img', 'type_label', 'subject',
        'contact_firstname', 'contact_lastname', 'contact_society', 'contact_id', 'contact_id as contact_img', 'identifier', 'doc_date', 'creation_date', 
        'currency', 'total_sum', 'process_limit_date', 'entity_label', 'dest_user', 'count_attachment'
    );
    
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
    //$request->show();exit;
//var_dump($tab);
for ($i=0;$i<count($tab);$i++) {
    $catId = '';
    for ($j=0;$j<count($tab[$i]);$j++) {
        //foreach(array_keys($tab[$i][$j]) as $value) {
            //echo "KEY " . $value . " CONTENT " . $tab[$i][$j]['column'] . "<br>";
            if($tab[$i][$j]['column']=='is_labeled' 
                && $core_tools->is_module_loaded('labels')
                && (isset($_SESSION['user']['services']['labels'])
                && $_SESSION['user']['services']['labels'] === true)
            )
            {
                $str_label = $labels->get_labels_resid($tab[$i][$j]['value'], $_SESSION['collection_id_choice']);
                if (!empty($str_label))  $tab[$i][$j]['value'] = ''; else  $tab[$i][$j]['value'] = '&nbsp;';
                $tab[$i][$j]["label"]=_LABELS;
                $tab[$i][$j]["size"]="4";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]=false;
            }
            if ($tab[$i][$j]['column']=="res_id") {
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
                          $query .= "tablename = 'res_business' ";
                         $query .= "AND "; 
                          $query .= "coll_id = '". $_SESSION['collection_id_choice'] ."' ";
                         $query .= "AND ";
                          $query .= "identifier = " . $tab[$i][$j]['value'] . " ";
                         $query .= "AND ";
                          $query .= "( ";
                            $query .= "( ";
                              $query .= "item_id IN (";
                              
                               foreach($_SESSION['user']['entities'] as $entitiestmpnote) {
                                $query .= "'" . $entitiestmpnote['ENTITY_ID'] . "', ";
                               }

                                if ($_SESSION['user']['UserId'] == 'superadmin') {
                                    $query .= " null ";
                                } else {
                                    $query = substr($query, 0, -2);
                                }
                              
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
            if ($tab[$i][$j]['column']=="status") {
                //echo $value . ' ' . $tab[$i][$j]['column'];
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
                //echo $tab[$i][$j]["column"];
                
                //$core_tools->show_array($tab[$i][$j]);
            }
            if ($tab[$i][$j]['column']=="category_id") {
                $_SESSION['mlb_search_current_category_id'] = $tab[$i][$j]["value"];
                $catId = $tab[$i][$j]["value"];
                $tab[$i][$j]["value"] = $_SESSION['coll_categories']['business_coll'][$tab[$i][$j]["value"]];
                $tab[$i][$j]["label"]=_CATEGORY;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='category_id';
            }
            if ($tab[$i][$j]['column']=="category_img") {
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
            if ($tab[$i][$j]['column']=="type_label") {
                $tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
                $tab[$i][$j]["label"]=_TYPE;
                $tab[$i][$j]["size"]="12";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='type_label';
            }
            if ($tab[$i][$j]['column']=="subject") {
                $tab[$i][$j]["value"] = $request->cut_string($request->show_string($tab[$i][$j]["value"]), 100);
                $tab[$i][$j]["label"]=_SUBJECT;
                $tab[$i][$j]["size"]="12";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='subject';
            }
            if ($tab[$i][$j]['column']=="contact_firstname") {
                $contact_firstname = $tab[$i][$j]["value"];
                $tab[$i][$j]["show"]=false;
            }
            if ($tab[$i][$j]['column']=="contact_lastname") {
                $contact_lastname = $tab[$i][$j]["value"];
                $tab[$i][$j]["show"]=false;
            }
            if ($tab[$i][$j]['column']=="contact_society") {
                $contact_society = $tab[$i][$j]["value"];
                $tab[$i][$j]["show"]=false;
            }
            if ($tab[$i][$j]['column']=="contact_id") {
                $tab[$i][$j]["label"]=_CONTACT;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=false;
                $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                $tab[$i][$j]["value"] = $contact->get_contact_information_from_view(
                    $_SESSION['mlb_search_current_category_id'], 
                    $contact_lastname, 
                    $contact_firstname, 
                    $contact_society, 
                    $user_lastname, 
                    $user_firstname
                );
                $tab[$i][$j]["order"]=false;
            }
            if ($tab[$i][$j]['column']=="contact_img") {
                $tab[$i][$j]["label"]=_CONTACT;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=false;
                $tab[$i][$j]['value'] = $contactImg;
                $tab[$i][$j]["value"] = $tab[$i][$j]['value'];
                $tab[$i][$j]["order"]=false;
            }
            if ($tab[$i][$j]['column']=="identifier") {
                $tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
                $tab[$i][$j]["label"]=_IDENTIFIER;
                $tab[$i][$j]["size"]="12";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='identifier';
            }
            if ($tab[$i][$j]['column']=="doc_date") {
                $tab[$i][$j]["value"]=$core_tools->format_date_db($tab[$i][$j]["value"], false);
                $tab[$i][$j]["label"]=_DOC_DATE;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=false;
                $tab[$i][$j]["order"]='doc_date';
            }
            if ($tab[$i][$j]['column']=="creation_date") {
                $tab[$i][$j]["value"]=$core_tools->format_date_db($tab[$i][$j]["value"], false);
                $tab[$i][$j]["label"]=_CREATION_DATE;
                $tab[$i][$j]["size"]="10";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='creation_date';
            }
             if ($tab[$i][$j]['column']=="currency") {
                $tab[$i][$j]["value"] =  $tab[$i][$j]["value"];
                $currency = $tab[$i][$j]["value"];
                $tab[$i][$j]["label"]=_CURRENCY;
                $tab[$i][$j]["size"]="12";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='currency';
            }
            if ($tab[$i][$j]['column']=="total_sum") {
                $tab[$i][$j]["value"] =  $core_tools->formatAmount($currency, $request->show_string($tab[$i][$j]["value"]));
                $tab[$i][$j]["label"]=_TOTAL_SUM;
                $tab[$i][$j]["size"]="12";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='total_sum';
            }
            if ($tab[$i][$j]['column']=="process_limit_date") {
                $tab[$i][$j]["value"]=$core_tools->format_date_db($tab[$i][$j]["value"], false);
                $compareDate = "";
                if ($tab[$i][$j]["value"] <> "" && ($statusCmp == "NEW" || $statusCmp == "COU" || $statusCmp == "VAL" || $statusCmp == "RET"))
                {
                    $compareDate = $core_tools->compare_date($tab[$i][$j]["value"], date("d-m-Y"));
                    if ($compareDate == "date2")
                    {
                        $tab[$i][$j]["value"] = "<span style='color:red;'><b>".$tab[$i][$j]["value"]."<br><small>(".$core_tools->nbDaysBetween2Dates($tab[$i][$j]["value"], date("d-m-Y"))." "._DAYS.")</small></b></span>";
                    }
                    elseif ($compareDate == "date1")
                    {
                        $tab[$i][$j]["value"] = $tab[$i][$j]["value"]."<br><small>(".$core_tools->nbDaysBetween2Dates(date("d-m-Y"), $tab[$i][$j]["value"])." "._DAYS.")</small>";
                    }
                    elseif ($compareDate == "equal")
                    {
                        $tab[$i][$j]["value"] = "<span style='color:blue;'><b>".$tab[$i][$j]["value"]."<br><small>("._LAST_DAY.")</small></b></span>";
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
            if ($tab[$i][$j]['column']=="entity_label") {
                $tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
                $tab[$i][$j]["label"]=_DESTINATION;
                $tab[$i][$j]["size"]="12";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='entity_label';
            }
            if ($tab[$i][$j]['column']=="dest_user") {
                $tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]);
                $tab[$i][$j]["label"]=_DEST_USER;
                $tab[$i][$j]["size"]="12";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=true;
                $tab[$i][$j]["order"]='dest_user';
            }
            if ($tab[$i][$j]['column']=="count_attachment") {
                $tab[$i][$j]["label"]=_ATTACHMENTS;
                $tab[$i][$j]["size"]="12";
                $tab[$i][$j]["label_align"]="left";
                $tab[$i][$j]["align"]="left";
                $tab[$i][$j]["valign"]="bottom";
                $tab[$i][$j]["show"]=false;
                $tab[$i][$j]["order"]='count_attachment';
            }
        //}
    }
}
//$core_tools->show_array($tab);
if (count($tab) > 0) {

    /************Construction de la liste*******************/
    //Clé de la liste
    $listKey = 'res_id';

    //Initialiser le tableau de paramètres
    $paramsTab = array();
    $paramsTab['bool_modeReturn'] = false;                                              //Desactivation du mode return (vs echo)
    $paramsTab['listCss'] = 'listing largerList spec';                                  //css
    $paramsTab['urlParameters'] =  $urlParameters.'&dir=indexing_searching';            //Parametres supplémentaires
    $paramsTab['pageTitle'] =  _RESULTS." : ".count($tab).' '._FOUND_DOCS;              //Titre de la page
    $paramsTab['pagePicto'] =  'search';                                      //Image de la page
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
    $paramsTab['viewDetailsLink'] = 'index.php?page=details_business'
        . '&dir=indexing_searching&coll_id=' . $_SESSION['collection_id_choice'];   //Link to the details page
    
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
            if ($mode == 'normal')  {
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
                            "icon"          =>  'bookmark',
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
                    "icon"          =>  'save',
                    "tooltip"       =>  _SAVE_QUERY,
                    "disabledRules" =>  count($tab)." == 0"
                    );      
            array_push($paramsTab['tools'],$save);   
        }
        
        if($exportTool) { 
            $export = array(
                    "script"        =>  "window.open('".$_SESSION['config']['businessappurl']."index.php?display=true&page=export', '_blank');",
                    "icon"          =>  'cloud-download',
                    "tooltip"       =>  _EXPORT_LIST,
                    "disabledRules" =>  count($tab)." == 0"
                    );
            array_push($paramsTab['tools'],$export);   
        }
		
		if($printTool && $core_tools->test_service('print_doc_details_from_list', 'apps', false)) {  
            $print = array(
                    "script"        =>  "window.open('".$_SESSION['config']['businessappurl']."index.php?display=true&page=print', '_blank');",
                    "icon"          =>  'print',
                    "tooltip"       =>  _PRINT_LIST,
                    "disabledRules" =>  count($tab)." == 0"
                    );
            array_push($paramsTab['tools'], $print);   
        }

    //Afficher la liste
        $list->showList($tab, $paramsTab, $listKey);
        // $list->debug();

    /*************************Extra javascript***********************/
    ?>
    <script type="text/javascript">
        var form_txt='<form name="frm_save_query" id="frm_save_query" action="#" method="post" class="forms" onsubmit="send_request(this.id);" ><h2><?php echo _SAVE_QUERY_TITLE;?></h2><p><label for="query_name"><?php echo _QUERY_NAME;?></label><input type="text" name="query_name" id="query_name" style="width:200px;" value=""/></p><br/><p class="buttons"><input type="submit" name="submit" id="submit" value="<?php echo _VALIDATE;?>" class="button"/> <input type="button" name="cancel" id="cancel" value="<?php echo _CANCEL;?>" class="button" onclick="destroyModal();"/></p></form>';

        function send_request(form_id)
        {
            var form = $(form_id);
            if(form)
            {
                var q_name = form.query_name.value;
                $('modal').innerHTML = '<i class="fa fa-spinner fa-2x"></i>';

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
