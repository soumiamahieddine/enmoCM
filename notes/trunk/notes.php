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
* @brief    Notes
*
* @file     notes.php
* @author   Yves Christian Kpakpo <dev@maarch.org>
* @date     $date$
* @version  $Revision$
* @ingroup  notes
*/

require_once "core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php";
require_once "apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
            ."class".DIRECTORY_SEPARATOR."class_lists.php";
require_once "modules".DIRECTORY_SEPARATOR."notes".DIRECTORY_SEPARATOR."notes_tables.php";
require_once "modules" . DIRECTORY_SEPARATOR . "notes" . DIRECTORY_SEPARATOR
    . "class" . DIRECTORY_SEPARATOR
    . "class_modules_tools.php";

$core_tools = new core_tools();
$request    = new request();
$list       = new lists();   
$notes_tools = new notes();

$identifier = '';
$origin = '';
$parameters = '';

//Collection ID
if(isset($_REQUEST['coll_id']) && !empty($_REQUEST['coll_id'])) $parameters = "&coll_id=".$_REQUEST['coll_id'];

//Identifier
if (isset($_REQUEST['identifier']) && !empty($_REQUEST['identifier'])) {
    $identifier = $_REQUEST['identifier'];
} else {
    echo '<span class="error">'._IDENTIFIER.' '._IS_EMPTY.'</span>';
    exit();
}

//Origin
if (isset($_REQUEST['origin']) && !empty($_REQUEST['origin'])) $origin = $_REQUEST['origin'];
 
//Extra parameters
if (isset($_REQUEST['size']) && !empty($_REQUEST['size'])) $parameters .= '&size='.$_REQUEST['size'];
if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) $parameters .= '&order='.$_REQUEST['order'];
if (isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])) $parameters .= '&order_field='.$_REQUEST['order_field'];
if (isset($_REQUEST['what']) && !empty($_REQUEST['what'])) $parameters .= '&what='.$_REQUEST['what'];
if (isset($_REQUEST['start']) && !empty($_REQUEST['start'])) $parameters .= '&start='.$_REQUEST['start'];

 if (isset($_REQUEST['load'])) {
    $core_tools->load_lang();
    $core_tools->load_html();
    $core_tools->load_header('', true, false);
    
    ?><body><?php
    $core_tools->load_js();

    //Load list
    if (!empty($identifier) && !empty($origin)) {
    
        $target = $_SESSION['config']['businessappurl']
            .'index.php?module=notes&page=notes&identifier='
            .$identifier.'&origin='.$origin.$parameters;
        
        $listContent = $list->loadList($target);
        echo $listContent;
    } else {
        echo '<span class="error">'._ERROR_IN_PARAMETERS.'</span>';
    }
    ?><div id="container" style="width:100%;min-height:0px;height:0px;"></div></body></html><?php
} else {
    //If size is full change some parameters
    if (isset($_REQUEST['size']) 
        && ($_REQUEST['size'] == "full")
    ) {
        $sizeUser = "10";
        $sizeText = "40";
        $css = "listing spec";
        $cutString = 150;
    } else if (isset($_REQUEST['size']) 
        && ($_REQUEST['size'] == "medium")
    ) {
        $sizeUser = "15";
        $sizeText = "30";
        $css = "listingsmall";
        $cutString = 100;
    } else {
        $sizeUser = "10";
        $sizeText = "10";
        $css = "listingsmall";
        $cutString = 20;
    }
    
    //Table or view
        $select[NOTES_TABLE] = array(); //Notes
        $select[USERS_TABLE] = array(); //Users
        
    //Fields
        array_push($select[NOTES_TABLE], "id", "identifier", "date_note", "user_id", "note_text", "note_text as note_short", "coll_id");    //Notes
        array_push($select[USERS_TABLE], "user_id", "firstname", "lastname");           //Users
        
    //Where clause
        $where_tab = array();
        //
        $where_tab[] = " identifier = " . $identifier . " ";
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
        /*$tab=$request->select(
            $select, $where, $orderstr,
            $_SESSION['config']['databasetype'], "500", true, NOTES_TABLE, USERS_TABLE,
            "user_id"
        );*/
		$tabNotes=$request->select(
            $select, $where, $orderstr,
            $_SESSION['config']['databasetype'], "500", true, NOTES_TABLE, USERS_TABLE,
            "user_id"
        );
        // $request->show();
        
    //Result Array
        /*for ($i=0;$i<count($tab);$i++)
        {
            for ($j=0;$j<count($tab[$i]);$j++)
            {
                foreach(array_keys($tab[$i][$j]) as $value)
                {
                    if($tab[$i][$j][$value]=="id")
                    {
                        $tab[$i][$j]["id"]=$tab[$i][$j]['value'];
                        $tab[$i][$j]["label"]='ID';
                        $tab[$i][$j]["size"]="1";
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
                        $tab[$i][$j]["size"]="5";
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["order"]='date_note';
                    }
                    if($tab[$i][$j][$value]=="user_id")
                    {
                        $tab[$i][$j]["label"]=_USER_ID;
                        $tab[$i][$j]["size"]="5";
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=false;
                        $tab[$i][$j]["order"]='user_id';
                    }
                    if($tab[$i][$j][$value]=="firstname")
                    {
                        $firstname =  $request->show_string($tab[$i][$j]["value"]);
                    }
                    if($tab[$i][$j][$value]=="lastname")
                    {
                        $tab[$i][$j]["value"] = $request->show_string($tab[$i][$j]["value"]). ' ' .$firstname ;
                        $tab[$i][$j]["label"]=_USER;
                        $tab[$i][$j]["size"]=$sizeUser;
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["order"]='lastname';
                    }
                    if($tab[$i][$j][$value]=="note_short")
                    {
                        $tab[$i][$j]["value"] = $request->cut_string( $request->show_string($tab[$i][$j]["value"]), $cutString);
                        $tab[$i][$j]["label"]=_NOTES;
                        $tab[$i][$j]["size"]=$sizeText;
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=true;
                        $tab[$i][$j]["order"]='note_short';
                    }
                    if($tab[$i][$j][$value]=="note_text")
                    {
                        $tab[$i][$j]["value"] = addslashes($tab[$i][$j]["value"]);
                        $tab[$i][$j]["label"]=_NOTES;
                        $tab[$i][$j]["size"]=$sizeText;
                        $tab[$i][$j]["label_align"]="left";
                        $tab[$i][$j]["align"]="left";
                        $tab[$i][$j]["valign"]="bottom";
                        $tab[$i][$j]["show"]=false;
                        $tab[$i][$j]["order"]='note_text';
                    }
                }
            }
        }*/
        
		//LGI UPDATE
		$arrayToUnset = array();

		for ($indNotes1 = 0; $indNotes1 < count($tabNotes); $indNotes1 ++ ) {
			for ($indNotes2 = 0; $indNotes2 < count($tabNotes[$indNotes1]); $indNotes2 ++) {
				foreach (array_keys($tabNotes[$indNotes1][$indNotes2]) as $value) {
					if ($tabNotes[$indNotes1][$indNotes2][$value] == "id") {
						$tabNotes[$indNotes1][$indNotes2]["id"] = $tabNotes[$indNotes1][$indNotes2]['value'];
						$tabNotes[$indNotes1][$indNotes2]["label"] = 'ID';
						$tabNotes[$indNotes1][$indNotes2]["size"] = $sizeSmall;
						$tabNotes[$indNotes1][$indNotes2]["label_align"] = "left";
						$tabNotes[$indNotes1][$indNotes2]["align"] = "left";
						$tabNotes[$indNotes1][$indNotes2]["valign"] = "bottom";
						$tabNotes[$indNotes1][$indNotes2]["show"] = true;
						$indNotes1d = $tabNotes[$indNotes1][$indNotes2]['value'];
						//echo $tabNotes[$indNotes1][$indNotes2]['value'] . '<br>';
						if (!$notes_tools->isUserNote(
							$tabNotes[$indNotes1][$indNotes2]['value'], 
							$_SESSION['user']['UserId'], 
							$_SESSION['user']['primaryentity']['id']
							)
						) {
							//unset($tabNotes[$indNotes1]);
							//echo 'sort ' . $indNotes1 . '<br>';
							array_push($arrayToUnset, $indNotes1);
						} else {
							//echo 'garde ' . $indNotes1 . '<br>';
						}
					}
				}
			}
		}

		for ($cptUnset=0;$cptUnset<count($arrayToUnset);$cptUnset++ ) {
			unset($tabNotes[$arrayToUnset[$cptUnset]]);
		}
		array_multisort($tabNotes, SORT_DESC);
		
		//$request->show_array($tabNotes);
		for ($indNotes1 = 0; $indNotes1 < count($tabNotes); $indNotes1 ++ ) {
			for ($indNotes2 = 0; $indNotes2 < count($tabNotes[$indNotes1]); $indNotes2 ++) {
				foreach (array_keys($tabNotes[$indNotes1][$indNotes2]) as $value) {
					if ($tabNotes[$indNotes1][$indNotes2][$value] == "id") {
						$tabNotes[$indNotes1][$indNotes2]["id"] = $tabNotes[$indNotes1][$indNotes2]['value'];
						$tabNotes[$indNotes1][$indNotes2]["label"] = 'ID';
						$tabNotes[$indNotes1][$indNotes2]["size"] = $sizeSmall;
						$tabNotes[$indNotes1][$indNotes2]["label_align"] = "left";
						$tabNotes[$indNotes1][$indNotes2]["align"] = "left";
						$tabNotes[$indNotes1][$indNotes2]["valign"] = "bottom";
						$tabNotes[$indNotes1][$indNotes2]["show"] = false;
						$indNotes1d = $tabNotes[$indNotes1][$indNotes2]['value'];
					}
					if ($tabNotes[$indNotes1][$indNotes2][$value] == "user_id") {
						$tabNotes[$indNotes1][$indNotes2]["user_id"] = $tabNotes[$indNotes1][$indNotes2]['value'];
						$tabNotes[$indNotes1][$indNotes2]["label"] = _ID;
						$tabNotes[$indNotes1][$indNotes2]["size"] = $sizeSmall;
						$tabNotes[$indNotes1][$indNotes2]["label_align"] = "left";
						$tabNotes[$indNotes1][$indNotes2]["align"] = "left";
						$tabNotes[$indNotes1][$indNotes2]["valign"] = "bottom";
						$tabNotes[$indNotes1][$indNotes2]["show"] = false;
					}
					if ($tabNotes[$indNotes1][$indNotes2][$value] == "lastname") {
						$tabNotes[$indNotes1][$indNotes2]['value'] = $request->show_string(
							$tabNotes[$indNotes1][$indNotes2]['value']
						);
						$tabNotes[$indNotes1][$indNotes2]["lastname"] = $tabNotes[$indNotes1][$indNotes2]['value'];
						$tabNotes[$indNotes1][$indNotes2]["label"] = _LASTNAME;
						$tabNotes[$indNotes1][$indNotes2]["size"] = $sizeSmall ;
						$tabNotes[$indNotes1][$indNotes2]["label_align"] = "left";
						$tabNotes[$indNotes1][$indNotes2]["align"] = "left";
						$tabNotes[$indNotes1][$indNotes2]["valign"] = "bottom";
						$tabNotes[$indNotes1][$indNotes2]["show"] = true;
					}
					if ($tabNotes[$indNotes1][$indNotes2][$value] == "date_note") {
						$tabNotes[$indNotes1][$indNotes2]["date_note"] = $tabNotes[$indNotes1][$indNotes2]['value'];
						$tabNotes[$indNotes1][$indNotes2]["label"] = _DATE;
						$tabNotes[$indNotes1][$indNotes2]["size"] = $sizeMedium;
						$tabNotes[$indNotes1][$indNotes2]["label_align"] = "left";
						$tabNotes[$indNotes1][$indNotes2]["align"] = "left";
						$tabNotes[$indNotes1][$indNotes2]["valign"] = "bottom";
						$tabNotes[$indNotes1][$indNotes2]["show"] = true;
					}
					if ($tabNotes[$indNotes1][$indNotes2][$value] == "firstname") {
						$tabNotes[$indNotes1][$indNotes2]["firstname"] = $tabNotes[$indNotes1][$indNotes2]['value'];
						$tabNotes[$indNotes1][$indNotes2]["label"] = _FIRSTNAME;
						$tabNotes[$indNotes1][$indNotes2]["size"] = $sizeSmall;
						$tabNotes[$indNotes1][$indNotes2]["label_align"] = "center";
						$tabNotes[$indNotes1][$indNotes2]["align"] = "center";
						$tabNotes[$indNotes1][$indNotes2]["valign"] = "bottom";
						$tabNotes[$indNotes1][$indNotes2]["show"] = true;
					}
					if ($tabNotes[$indNotes1][$indNotes2][$value] == "note_text") {
						$tabNotes[$indNotes1][$indNotes2]['value'] = '<a href="javascript://"'
							. ' onclick="ouvreFenetre(\''
							. $_SESSION['config']['businessappurl']
							. 'index.php?display=true&module=notes&page=note_details&id='
							. $indNotes1d . '&amp;resid=' . $_SESSION['doc_id']
							. '&amp;coll_id=' . $_SESSION['collection_id_choice']
							. $extendUrl . '\', 1024, 650)">'
							. $request->cut_string(
								$request->show_string(
									$tabNotes[$indNotes1][$indNotes2]['value']
								), $cutString
							) . '<span class="sstit"> > ' . _READ . '</span>';
						$tabNotes[$indNotes1][$indNotes2]["note_text"] = $tabNotes[$indNotes1][$indNotes2]['value'];
						$tabNotes[$indNotes1][$indNotes2]["label"] = _NOTES;
						$tabNotes[$indNotes1][$indNotes2]["size"] = $sizeFull;
						$tabNotes[$indNotes1][$indNotes2]["label_align"] = "center";
						$tabNotes[$indNotes1][$indNotes2]["align"] = "center";
						$tabNotes[$indNotes1][$indNotes2]["valign"] = "bottom";
						$tabNotes[$indNotes1][$indNotes2]["show"] = true;
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
        $paramsTab['urlParameters'] = 'identifier='.$identifier
                ."&origin=".$origin.'&display=true'.$parameters;                            //Parametres d'url supplementaires
        $paramsTab['filters'] = array('user');                                              //Filtres    
        $paramsTab['listHeight'] = '100%';                                                 //Hauteur de la liste
        // $paramsTab['bool_showSmallToolbar'] = true;                                         //Mini barre d'outils
        // $paramsTab['linesToShow'] = 15;                                                     //Nombre de ligne a afficher
        $paramsTab['listCss'] = $css;                                                       //CSS
        $paramsTab['tools'] = array();                                                      //Icones dans la barre d'outils
          
        $add = array(
                "script"        =>  "showNotesForm('".$_SESSION['config']['businessappurl']  
                                        . "index.php?display=true&module=notes&page=notes_ajax_content"
                                        . "&mode=add&identifier=".$identifier."&origin=".$origin
                                        . $parameters."')",
                "icon"          =>  $_SESSION['config']['businessappurl']."static.php?filename=tool_note.gif&module=notes",
                "tooltip"       =>  _ADD_NOTE,
                "alwaysVisible" =>  true
                );
        array_push($paramsTab['tools'],$add);   
        
        //Action icons array
        $paramsTab['actionIcons'] = array();
        $preview = array(
                    "type"      =>  "preview",
                    "class"     =>  "preview",
                    "icon"      =>  $_SESSION['config']['businessappurl']."static.php?filename=showFrameAdminList.png",
                    "tooltip"   =>  _NOTES,
                    "content"   =>  "{'identifierDetailFrame' : '@@id@@', '"._DATE." ' : '@@date_note@@', '"._USER." ' : '@@lastname@@', '"._NOTES." ' : '@@note_text@@'}"
                );
        array_push($paramsTab['actionIcons'], $preview);        
        
        $read = array(
            "script"        => "showNotesForm('".$_SESSION['config']['businessappurl']
                                    ."index.php?display=true&module=notes&page=notes_ajax_content"
                                    ."&mode=up&id=@@id@@&identifier=".$identifier."&origin=".$origin
                                    . $parameters."');",
            "class"         =>  "read",
            "icon"          =>  $_SESSION['config']['businessappurl']."static.php?module=notes&filename=modif_note_small.gif",
            // "label"         =>  _UPDATE.'/'._DELETE,
            "tooltip"       =>  _UPDATE.'/'._DELETION,
            "disabledRules" => "@@user_id@@ != '".$_SESSION['user']['UserId']."'"
            );
        array_push($paramsTab['actionIcons'], $read);     
         
        //Output
        $status = 0;
        //$content = $list->showList($tab, $paramsTab, $listKey);
        $content = $list->showList($tabNotes, $paramsTab, $listKey);
        // $debug = $list->debug();

    echo "{status : " . $status . ", content : '" . addslashes($debug.$content) . "', error : '" . addslashes($error) . "'}";
}

