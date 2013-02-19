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
* @brief    Displays notes list in details folder
*
* @file     notes_list.php
* @author   Yves Christian Kpakpo <dev@maarch.org>
* @date     $date$
* @version  $Revision$
* @ingroup  folder
*/

require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php";
require_once "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
            ."class".DIRECTORY_SEPARATOR."class_lists.php";
require_once "modules".DIRECTORY_SEPARATOR."notes".DIRECTORY_SEPARATOR."notes_tables.php";

$core_tools = new core_tools();
$request    = new request();
$list       = new lists();

if(isset($_REQUEST['id']) && $_REQUEST['id'] <> "") {

    $id = $_REQUEST['id'];
    
    if(isset($_REQUEST['coll_id']) && !empty($_REQUEST['coll_id'])) {
         $extendUrl = "&coll_id=".$_REQUEST['coll_id'];
    }
    
    //If size is full change some parameters
    if ($_REQUEST['size'] == "full") {
        $sizeSmall = "15";
        $sizeFull = "40";
        $css = "listing spec";
        $cutString = 100;
        $extendUrl = "&size=full";
    } else {
        $sizeSmall = "10";
        $sizeFull = "30";
        $css = "listingsmall";
        $cutString = 20;
        $extendUrl = "";
    }

    //Table or view
        $select[NOTES_TABLE] = array(); //Notes
        $select[USERS_TABLE] = array(); //Users
        
    //Fields
        array_push($select[NOTES_TABLE], "id", "identifier", "date_note", "note_text", "user_id", "coll_id");    //Notes
        array_push($select[USERS_TABLE], "user_id", "firstname", "lastname");           //Users
        
    //Where clause
        $where_tab = array();
        //
        $where_tab[] = " identifier = " . $id . " ";
        //From filters
        $filterClause = $list->getFilters(); 
        if (!empty($filterClause)) $where_tab[] = $filterClause;//Filter clause
        //Build where
        $where = implode(' and ', $where_tab);
    
    //Order
        $order = $order_field = '';
        $order = $list->getOrder();
        $order_field = $list->getOrderField();
        if (!empty($order_field) && !empty($order)) 
            $orderstr = "order by ".$order_field." ".$order;
        else  {
            $list->setOrder();
            $list->setOrderField('date_note');
            $orderstr = "order by date_note desc";
        }
    
    //Request
        $tab=$request->select(
            $select, $where, $orderstr,
            $_SESSION['config']['databasetype'], "500", true, NOTES_TABLE, USERS_TABLE,
            "user_id"
        );
        // $request->show();
        
    //Result Array
        for ($i=0;$i<count($tab);$i++)
        {
            for ($j=0;$j<count($tab[$i]);$j++)
            {
                foreach(array_keys($tab[$i][$j]) as $value)
                {
                    if($tab[$i][$j][$value]=="id")
                    {
                        $tab[$i][$j]["id"]=$tab[$i][$j]['value'];
                        $tab[$i][$j]["label"]='ID';
                        $tab[$i][$j]["size"]="5";
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["order"]='id';
                    }
                    if($tab[$i][$j][$value]=="date_note")
                    {
                        $tab[$i][$j]["value"]=$request->dateformat($tab[$i][$j]["value"]);
                        $tab[$i][$j]["label"]=_DATE;
                        $tab[$i][$j]["size"]=$sizeSmall;
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["order"]='date_note';
                    }
                    if($tab[$i][$j][$value]=="firstname")
                    {
                        $firstname =  $request->show_string($tab[$i][$j]["value"]);
                    }
                    if($tab[$i][$j][$value]=="lastname")
                    {
                        $tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]). ' ' .$firstname ;
                        $tab[$i][$j]["label"]=_USER;
                        $tab[$i][$j]["size"]="20";
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["order"]='lastname';
                    }
                    if($tab[$i][$j][$value]=="note_text")
                    {
                        $tab[$i][$j]["value"] = $request->cut_string( $request->show_string($tab[$i][$j]["value"]), $cutString);
                        $tab[$i][$j]["label"]=_NOTES;
                        $tab[$i][$j]["size"]=$sizeFull;
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["order"]='note_text';
                    }
                }
            }
        }
        
        //List
        $listKey = 'id';                                                                    //Clé de la liste
        $paramsTab = array();                                                               //Initialiser le tableau de paramètres
        $paramsTab['bool_sortColumn'] = true;                                               //Affichage Tri
        $paramsTab['pageTitle'] ='';                                                        //Titre de la page
        $paramsTab['bool_bigPageTitle'] = false;                                            //Affichage du titre en grand
        $paramsTab['urlParameters'] = 'id='.$_REQUEST['id'].'&display=true'.$extendUrl;     //Parametres d'url supplementaires
         // $paramsTab['filters'] = array('user');                                             //Filtres    
        $paramsTab['listHeight'] = '540px';                                                 //Hauteur de la liste
        // $paramsTab['bool_showSmallToolbar'] = true;                                         //Mini barre d'outils
        $paramsTab['linesToShow'] = 15;                                                     //Nombre de ligne a afficher
        $paramsTab['listCss'] = $css;                                                       //CSS
        
        $paramsTab['tools'] = array();                                                      //Icones dans la barre d'outils
        $add = array(
                "script"        =>  "ouvreFenetre('".$_SESSION['config']['businessappurl']
                                    ."index.php?display=true&module=notes&page=note_add"
                                    ."&identifier=".$id."&table=folders"
                                    . $extendUrl."', 800, 480);",
                "icon"          =>  $_SESSION['config']['businessappurl']."static.php?filename=tool_note.gif&module=notes",
                "tooltip"       =>  _ADD_NOTE
                );
    
        array_push($paramsTab['tools'],$add);   
        
        //Action icons array
        $paramsTab['actionIcons'] = array();
        $read = array(
            "script"        => "ouvreFenetre('".$_SESSION['config']['businessappurl']
                                    ."index.php?display=true&module=notes&page=note_details"
                                    ."&id=@@id@@&identifier=".$id."&table=folders"
                                    . $extendUrl."', 800, 480);",
            "class"         =>  "read",
            "icon"          =>  $_SESSION['config']['businessappurl']."static.php?filename=picto_view.gif",
            "label"         =>  _READ,
            "tooltip"       =>  _READ
            );
        array_push($paramsTab['actionIcons'], $read);     
         
        //Output
        $status = 0;
        $content = $list->showList($tab, $paramsTab, $listKey);
        // $debug = $list->debug();
}

echo "{status : " . $status . ", content : '" . addslashes($debug.$content) . "', error : '" . addslashes($error) . "'}";
 
?>