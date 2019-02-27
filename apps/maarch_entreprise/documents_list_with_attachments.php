<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

*
* @brief documents_list_width_attachments
*
* @author dev <dev@maarch.org>
* @ingroup apps
*/
require_once 'apps/'.$_SESSION['config']['app_id'].'/class/class_lists.php';


$list = new lists();

$tab = [
"column" => "res_id",
"value" => "0"
];

//Cle de la liste
$listKey = 'res_id';

//Initialiser le tableau de parametres
$paramsTab = array();
$paramsTab['pageTitle'] = ''; //Titre de la page
$paramsTab['listCss'] = 'listing largerList spec'; //css
$paramsTab['bool_sortColumn'] = false; //Affichage Tri
$paramsTab['bool_bigPageTitle'] = false; //Affichage du titre en grand
$paramsTab['bool_showIconDocument'] = false; //Affichage de l'icone du document
$paramsTab['bool_showIconDetails'] = false; //Affichage de l'icone de la page de details
$paramsTab['urlParameters'] = 'baskets='.$_SESSION['current_basket']['id'] //Parametres d'url supplementaires
.$urlParameters;
$paramsTab['start'] = 0;

$paramsTab['bool_showTemplateDefaultList'] = false; //Default list (no template)
$paramsTab['defaultTemplate'] = ''; //Default template
$paramsTab['tools'] = array(); //Icones dans la barre d'outils


//Afficher la liste
$status = 0;
$content = $list->showList($tab, $paramsTab, $listKey, $_SESSION['current_basket']);

$content .= '<script>$j(\'#inner_content\').hide();</script>';

echo "{'status' : ".$status.", 'content' : '".addslashes($debug.$content)."', 'error' : '".addslashes(functions::xssafe($error))."'}";

