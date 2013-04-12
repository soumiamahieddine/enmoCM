<?php

require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php";
require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_status.php";
require_once "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
            ."class".DIRECTORY_SEPARATOR."class_lists.php";

$status_obj = new manage_status();
$request    = new request();
$func       = new functions();
$list       = new lists();

$_SESSION['error_page'] = '';

//Table or view
    $view = $_SESSION['view']['view_folders'];
    $select[$view]= array();

//Fields
    array_push($select[$view],"folders_system_id", "status", 
            "foldertype_label", "custom_t2", "folder_id", 
            "folder_name",  "count_document",  "creation_date");

//Where
    $where_tab = array();
    //From search
    if (!empty($_SESSION['searching']['where_request'])) $where_tab[] = $_SESSION['searching']['where_request']. '(1=1)';
    //Add on
    $where_tab[] = "status <> 'DEL' ";
    //Build where
    $where = implode(' and ', $where_tab);
    
//Order
    $order = $order_field = '';
    $order = $list->getOrder();
    $order_field = $list->getOrderField();
    if (!empty($order_field) && !empty($order)) 
        $orderstr = "order by ".$order_field." ".$order;
    else  {
        $list->setOrder('asc');
        $list->setOrderField('folder_name');
        $orderstr = "order by folder_name asc";
    }
    
//Query 
    $tab=$request->select($select,$where,$orderstr,$_SESSION['config']['databasetype']);
    // $request->show();
    // echo $_SESSION['last_select_query'];
//Result Array
    if (count($tab) > 0)
    {
                for ($i=0;$i<count($tab);$i++)
        {
            for ($j=0;$j<count($tab[$i]);$j++)
            {
                foreach(array_keys($tab[$i][$j]) as $value)
                {
                    if($tab[$i][$j][$value]=='folders_system_id')
                    {
                        $tab[$i][$j]['folders_system_id']=$tab[$i][$j]['value'];
                        $tab[$i][$j]["label"]='';
                        $tab[$i][$j]["size"]="4";
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="center";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=false;
                        $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                        $tab[$i][$j]["order"]='folders_system_id';
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
                    if ($tab[$i][$j][$value] == "foldertype_label")
                    {
                        $tab[$i][$j]["label"]=_FOLDERTYPE;
                        $tab[$i][$j]["size"]="10";
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                        $tab[$i][$j]["order"]="foldertype_label";
                    }
                    if ($tab[$i][$j][$value] == "custom_t2")
                    {
                        $tab[$i][$j]["label"]=_FOLDERTYPE;
                        $tab[$i][$j]["size"]="5";
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=false;
                        $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                        $tab[$i][$j]["order"]="custom_t2";
                    }
                    if ($tab[$i][$j][$value] == "folder_id")
                    {
                        $tab[$i][$j]["label"]=_FOLDERID;
                        $tab[$i][$j]["size"]="10";
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                        $tab[$i][$j]["order"]="folder_id";
                    }
                    if ($tab[$i][$j][$value] == "folder_name")
                    {
                        $tab[$i][$j]["label"]=_FOLDERNAME;
                        $tab[$i][$j]["size"]="20";
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                        $tab[$i][$j]["order"]="folder_name";
                    }
                    if($tab[$i][$j][$value]=="count_document")
                    {
                        $tab[$i][$j]["label"]=_NB_DOCS_IN_FOLDER;
                        $tab[$i][$j]["value"] = $tab[$i][$j]['value'];
                        $tab[$i][$j]["size"]="5";
                        $tab[$i][$j]["label_align"]="right";
                        $tab[$i][$j]["align"]="right";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                        $tab[$i][$j]["order"]="count_document";
                    }
                    if($tab[$i][$j][$value]=="creation_date")
                    {
                        $tab[$i][$j]["label"]=_FOLDERDATE;
                        $tab[$i][$j]["value"] = $func->format_date($tab[$i][$j]["value"]);
                        $tab[$i][$j]["size"]="5";
                        $tab[$i][$j]["label_align"]="right";
                        $tab[$i][$j]["align"]="right";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
                        $tab[$i][$j]["order"]="creation_date";
                    }
                }
            }
        }
        
        //Initialiser le tableau de paramètres
        $paramsTab = array();
        $paramsTab['bool_modeReturn'] = false;                                          //Desactivation du mode return (vs echo)
        $paramsTab['pageTitle'] =  _RESULTS." : ".count($tab).' '._FOUND_FOLDER;        //Titre de la page
        $paramsTab['bool_sortColumn'] = true;                                           //Affichage Tri
        $paramsTab['pagePicto'] =  $_SESSION['config']['businessappurl']
        ."static.php?filename=picto_search_b.gif";                                      //Image de la page
        $paramsTab['tools'] = array();                                                  //Icones dans la barre d'outils
        $export = array(
            "script"        =>  "window.open('".$_SESSION['config']['businessappurl']."index.php?display=true&page=export', '_blank');",
            "icon"          =>  $_SESSION['config']['businessappurl']."static.php?filename=tool_export.gif",
            "tooltip"       =>  _EXPORT_LIST,
            "disabledRules" =>  count($tab)." == 0"
            );
        // array_push($paramsTab['tools'],$export);  
        
        //Action icons array
        $paramsTab['actionIcons'] = array();
        $details = array(
                "script"    => "window.top.location='".$_SESSION['config']['businessappurl']
                                ."index.php?page=show_folder&module=folder&id=@@folders_system_id@@'",
                "icon"      => $_SESSION['config']['businessappurl']."static.php?filename=picto_infos.gif",
                "tooltip"   => _DETAILS
                );
        array_push($paramsTab['actionIcons'], $details);
         
        //Afficher la liste
        echo '<br/>';
        $list->showList($tab, $paramsTab, $listKey);

    }
    else
    {
        $func->echo_error(_ADV_SEARCH_FOLDER_TITLE,"<p class=\"error\"><img src=\""
            .$_SESSION['config']['businessappurl']."static.php?filename=noresult.gif\" /><br />"
            ._NO_RESULTS."</p>", 'title',  $_SESSION['config']['businessappurl']
            ."static.php?module=folder&filename=picto_search_b.gif");
    }

?>
