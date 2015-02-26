<?php
/*
*
*    Copyright 2008-2013 Maarch
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
* @brief   Displays the logs list in the following baskets 
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
require_once('core/class/class_request.php');
require_once('core/class/class_security.php');
require_once('core/class/class_manage_status.php');
require_once('apps/maarch_entreprise/class/class_list_show.php');
require_once('modules/basket/class/class_modules_tools.php');

$security = new security();
$core_tools = new core_tools();
$request = new request();
$template_to_use = '';

$bask = new basket();
if (!empty($_REQUEST['id'])) {
    $bask->load_current_basket(trim($_REQUEST['id']), 'frame');
}
if (!empty($_SESSION['current_basket']['view'])) {
    $table = $_SESSION['current_basket']['view'];
} else {
    $table = $_SESSION['current_basket']['table'];
}
$_SESSION['collection_id_choice'] = $_SESSION['current_basket']['coll_id'];
$select[$table]= array();
$where = $_SESSION['current_basket']['clause'];
array_push($select[$table], 'res_id',  'creation_date');
$order = '';
if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
    $order = trim($_REQUEST['order']);
} else {
    $order = 'asc';
}
$order_field = '';
if (isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])) {
    $order_field = trim($_REQUEST['order_field']);
} else {
    $order_field = 'creation_date';
}
$list=new list_show();
$orderstr = $list->define_order($order, $order_field);
$bask->connect();
$do_actions_arr = array();

if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == 'searching') {
    $where = $_SESSION['searching']['where_request'] . ' '. $where;
}

$tab = $request->select(
    $select,
    $where,
    $orderstr,
    $_SESSION['config']['databasetype'], 
    '1000', 
    false, 
    '', 
    '', 
    '', 
    false
);
//$request->show();
//###################
for ($i=0;$i<count($tab);$i++) {
    for ($j=0;$j<count($tab[$i]);$j++) {
        foreach (array_keys($tab[$i][$j]) as $value) {
            if ($tab[$i][$j][$value]=='res_id') {
                $tab[$i][$j]['res_id']=$tab[$i][$j]['value'];
                $tab[$i][$j]['label']=_GED_NUM;
                $tab[$i][$j]['size']='4';
                $tab[$i][$j]['label_align']='left';
                $tab[$i][$j]['align']='left';
                $tab[$i][$j]['valign']='bottom';
                $tab[$i][$j]['show']=true;
                $tab[$i][$j]['order']='res_id';
            }
            if ($tab[$i][$j][$value]=='creation_date') {
                $tab[$i][$j]['value']=$core_tools->format_date_db(
                    $tab[$i][$j]['value'], false
                );
                $tab[$i][$j]['label']=_CREATION_DATE;
                $tab[$i][$j]['size']='10';
                $tab[$i][$j]['label_align']='left';
                $tab[$i][$j]['align']='left';
                $tab[$i][$j]['valign']='bottom';
                $tab[$i][$j]['show']=true;
                $tab[$i][$j]['order']='creation_date';
            }
        }
    }
}

$i = count($tab);
$title = _RESULTS.' : '.$i.' '._FOUND_LOGS;
//$request->show_array($tab);
$_SESSION['origin'] = 'basket';
$_SESSION['collection_id_choice'] = $_SESSION['current_basket']['coll_id'];

//Clé de la liste
$listKey = 'res_id';

//Initialiser le tableau de paramètres
$paramsTab = array();
$paramsTab['pageTitle'] =  _RESULTS." : ".count($tab).' '._FOUND_LOGS;              //Titre de la page
$paramsTab['bool_sortColumn'] = true;                                               //Affichage Tri
$paramsTab['bool_bigPageTitle'] = false;                                            //Affichage du titre en grand
$paramsTab['bool_showIconDocument'] = true;                                         //Affichage de l'icone du document
$paramsTab['bool_showIconDetails'] = false;                                          //Affichage de l'icone de la page de details
$paramsTab['urlParameters'] = 'baskets='.$_SESSION['current_basket']['id']
            .$urlParameters;                                                        //Parametres d'url supplementaires
if (count($template_list) > 0 ) {                                                   //Templates
    $paramsTab['templates'] = array();
    $paramsTab['templates'] = $template_list;
}
$paramsTab['bool_showTemplateDefaultList'] = true;                                  //Default list (no template)
$paramsTab['defaultTemplate'] = $defaultTemplate;                                   //Default template
$paramsTab['tools'] = array();                                                      //Icones dans la barre d'outils

if (isset($_REQUEST['origin']) && $_REQUEST['origin'] == 'searching')  {
    $save = array(
            "script"        =>  "createModal(form_txt, 'save_search', '100px', '500px');window.location.href='#top';",
            "icon"          =>  'save',
            "tooltip"       =>  _SAVE_QUERY,
            "disabledRules" =>  count($tab)." == 0"
            );      
    array_push($paramsTab['tools'],$save); 
}
$export = array(
        "script"        =>  "window.open('".$_SESSION['config']['businessappurl']."index.php?display=true&page=export', '_blank');",
        "icon"          =>  'file-excel-o',
        "tooltip"       =>  _EXPORT_LIST,
        "disabledRules" =>  count($tab)." == 0"
        );
array_push($paramsTab['tools'],$export);

//Afficher la liste
$status = 0;
require_once 'apps/' . $_SESSION['config']['app_id'] . '/class/class_lists.php';
$list       = new lists();
$content = $list->showList($tab, $paramsTab, $listKey, $_SESSION['current_basket']);
// $debug = $list->debug(false);
echo "{'status' : " . $status . ", 'content' : '" . addslashes($debug.$content) . "', 'error' : '" . addslashes($error) . "'}";
