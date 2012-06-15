<?php

/*
*   Copyright 2008-2012 Maarch
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
* @brief Contains the admin standard controller page
*
* @file
* @author Arnaud Veber
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup core
*/

require_once 'core/class/class_core_tools.php';

/**
 * Management of the location bar
 * @param string $pageName
 * @param string $mode
 * @param string $object
 * @param string $path
 * @return string $pagePath the current page path
 */
function locationBarManagement($pageName, $mode, $object, $isApps)
{
    $pageLabels = array(
        'add'   => _ADDITION,
        'up'    => _MODIFICATION,
        'list'  => _LIST,
    );
    $pageIds = array(
        'add'   => $object . '_add',
        'up'    => $object . '_up',
        'list'  => $object . '_list',
    );
    $init = false;
    if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true') {
        $init = true;
    }
    $level = '';
    $allowedLevels = array(1, 2, 3, 4);
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], $allowedLevels)) {
        $level = $_REQUEST['level'];
    }
    if($isApps) {
        $pagePath = $_SESSION['config']['businessappurl'] . 'index.php?'
            . 'page='   . $pageName 
            . '&admin=' . $object 
            . '&object=' . $object 
            . '&mode='  . $mode;
    } else {
        $pagePath = $_SESSION['config']['businessappurl'] . 'index.php?'
            . 'page='    . $pageName 
            . '&module=' . $object 
            . '&object=' . $object 
            . '&mode='   . $mode;
    }
    $pageLabel = $pageLabels[$mode];
    $pageId = $pageIds[$mode];
    $coreTools = new core_tools();
    $coreTools->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
    return $pagePath;
}

/**
 * tests and retrieve params of the controller page
 * @param array $pageParams wich contains requests of the page
 * @return array(
        'status' => '',
        'pageName' => '',
        'pageNb' => '',
        'mode' => '',
        'object' => '',
        'objectId' => '',
        'isApps' => '',
        'viewLocation' => '',
        'error' => '',
    );
 */
function testParams($pageParams)
{
    $status = 'OK';
    $mode = 'list';
    $object = '';
    $objectId = '';
    $pageNb = 1;
    $isApps = false;
    $viewLocation = '';
    $order = '';
    $orderField = '';
    $what = '';
    $error = '';
    $paramsReturn = array();
    if (isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])) {
        $mode = $_REQUEST['mode'];
    }
    if (isset($_REQUEST['object']) && !empty($_REQUEST['object'])) {
        $object = $_REQUEST['object'];
    } else {
        $status = 'KO';
        $error .= _OBJECT_MANDATORY;
    }
    if (isset($_REQUEST['objectId']) && !empty($_REQUEST['objectId'])) {
        $objectId = $_REQUEST['objectId'];
    }
    if (isset($_REQUEST['pageNb']) && !empty($_REQUEST['pageNb'])) {
        $pageNb = $_REQUEST['pageNb'];
    }
    if (isset($_REQUEST['admin']) && !empty($_REQUEST['admin'])) {
        $isApps = true;
        //if empty this is an object in the apps
        $viewLocation = 'apps/' . $_SESSION['config']['app_id'] 
            . '/admin/' . $_REQUEST['admin'];
    } elseif (isset($_REQUEST['module']) && !empty($_REQUEST['module'])) {
        //the module parameter gives the module name
        $viewLocation = 'modules/' . $_REQUEST['module'];
        //test if the user is allowed to acces the admin service
        //$coreTools->test_admin('admin_' . $object, $object);
    } else {
        $status = 'KO';
        $error .= _VIEW_LOCATION_MANDATORY . ' ' . _IN_CONTROLLER_PAGE;
    }
    if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
        $order = $_REQUEST['order'];
    }
    if (isset($_REQUEST['orderField']) && !empty($_REQUEST['orderField'])) {
        $orderField = $_REQUEST['orderField'];
    }
    if (isset($_REQUEST['what']) && !empty($_REQUEST['what'])) {
        $what = $_REQUEST['what'];
    }
    return $paramsReturn = array(
        'status' => $status,
        'pageName' => $_REQUEST['page'],
        'pageNb' => $pageNb,
        'mode' => $mode,
        'object' => $object,
        'objectId' => $objectId,
        'isApps' => $isApps,
        'viewLocation' => $viewLocation,
        'order' => $order,
        'orderField' => $orderField,
        'what' => $what,
        'error' => $error,
    );
}

/**
 * Initialize session variables
 * @param string $object
 */
function initSession($object)
{
    $_SESSION['m_admin'][$object] = array();
}

/**
 * Initialize session parameters for add display with given object
 * @param string $object
 */
function displayAdd($object)
{
    if (!isset($_SESSION['m_admin'][$object])) {
        initSession();
    }
}

/**
 * Destroy session parameters for create display
 * @param $objectName
 */
function displayCreate($objectName)
{
    clearSession($objectName);
}

/**
 * Initialize session parameters for read display
 * @param $objectId
 */
function displayRead($objectName, $object)
{
    putInSession($objectName, $object);
}

/**
 * Initialize session parameters for update display
 * @param $objectId
 */
function displayUpdate($objectName, $object)
{
    putInSession($objectName, $object);
    //TODO: SPECIFIC SAMPLE !!!
/*
    if ($docserversControler->resxLinkExists(
        $docservers->docserver_id,
        $docservers->coll_id
    )
    ) {
        $_SESSION['m_admin']['docservers']['link_exists'] = true;
    }
    if ($docserversControler->adrxLinkExists(
        $docservers->docserver_id,
        $docservers->coll_id
    )
    ) {
        $_SESSION['m_admin']['docservers']['link_exists'] = true;
    }
*/
}

/**
 * Put given object in session, according with given object
 * NOTE: given object needs to be at least hashable
 * @param string $objectName
 * @param object $object
 */
function putInSession($objectName, $object)
{
    $_SESSION['m_admin'][$objectName] = $object;
}

/**
 * Clear the object in session
 * @param string $objectName
 */
function clearSession($objectName)
{
    $_SESSION['m_admin'][$objectName] = array();
}

function displayList($objectList, $actions, $showCols, $pageNb, $keyName)
{
    $pagePath = $_SERVER['REQUEST_URI'];
    //tri alphabetique
    $noWhatUrl = str_replace('&what='.$_REQUEST['what'], '', $pagePath);
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str_search = '<table width="100%">';
        $str_search .= '<tr>';
            $str_search .= '<td style="text-align: left;">';
                $str_search .= 'Liste alphabétique : ';
                for ($i=0; $i<strlen($alphabet); $i++) {
                    $str_search .= '<span>';
                        $str_search .= '<a href="'.$noWhatUrl.'&what='.substr($alphabet, $i, 1).'|">';
                            $str_search .= substr($alphabet, $i, 1);
                        $str_search .= '</a>';
                    $str_search .= '</span>';
                    $str_search .= '&nbsp;';
                }
                $str_search .= '&nbsp;-&nbsp;';
                $str_search .= '<span>';
                    $str_search .= '<a href="'.$noWhatUrl.'">';
                        $str_search .= 'Tout afficher';
                    $str_search .= '</a>';
                $str_search .= '</span>';
            $str_search .= '</td>';
            //tri recherche
            $noWhatUrl = str_replace('&what='.$_REQUEST['what'], '', $pagePath);
            $str_search  .= '<td style="text-align: right;">';
                    $str_search .= '<input name="what" id="what" type="text" size="15" autocomplete="off"/>';
                    $str_search .= '<div id="whatList" class="autocomplete" style="display: none;"></div>';
                    $str_search .= '<script type="text/javascript">';
                        $str_search .= 'initList(\'what\', \'whatList\', \''.$_SESSION['config']['businessappurl'].'index.php?display=true&admin=docservers&page=docservers_list_by_id\', \'what\', \'1\');';
                    $str_search .= '</script>';
                    $str_search .= '<input class="button" type="button" value="Rechercher" onClick="window.location.href=\''.$noWhatUrl.'&what=|\'+$(\'what\').value+\'|\'"/>';
            $str_search .= '</td>';
        $str_search .= '</tr>';
    $str_search .= '</table>';
    
    //pagination
    $nbLine  = $_SESSION['config']['nblinetoshow'];
    $nbEnd   = $pageNb * $nbLine - 1;
    $nbStart = $nbEnd - $nbLine + 1;
    $nbMax = count($objectList);
    $nbPageMax = ceil($nbMax/$nbLine);
    $actualURL = str_replace('&pageNb='.$pageNb, '', $pagePath);
    $nextLink = $actualURL . '&pageNb=' . ($pageNb + 1);
    $previousLink = $actualURL . '&pageNb=' . ($pageNb - 1);
    //$str_pagination
    $str_pagination  = '';
    if ($nbPageMax > 1) {
        $str_pagination .= '<table width="100%">';
            $str_pagination .= '<tr>';
                $str_pagination .= '<td width="80px">';
                    if ($pageNb > 1) {
                        $str_pagination .= '<a href="' . $previousLink . '">';
                            $str_pagination .= ' < Précédente';
                        $str_pagination .= '</a>';
                    }
                $str_pagination .= '</td>';
                $str_pagination .= '<td style="text-align: center;">';
                    $str_pagination .= 'Aller à la page : ';
                    $str_pagination .= '<select onchange="window.location.href=this.value;">';
                        for($k=1; $k<=$nbPageMax; $k++) {
                            $selected = '';
                            if ($k == $pageNb) {
                                $selected = 'selected ';
                            }
                            $str_pagination .= '<option value="' . $actualURL . '&pageNb=' . $k . '" ' . $selected . '>' . $k . '</option>';
                        }
                    $str_pagination .= '</select>';
                $str_pagination .= '</td>';
                $str_pagination .= '<td width="80px">';
                    if ($pageNb < $nbPageMax) {
                        $str_pagination .= '<a href="' . $nextLink . '">';
                            $str_pagination .= ' Suivante > ';
                        $str_pagination .= '</a>';
                    }
                $str_pagination .= '</td>';
            $str_pagination .= '</tr>';
        $str_pagination .= '</table>';
    }
    //actionsUrl
    $actionsURL = array();
    if (is_array($actions)) {
        for ($i=0; $i<count($actions); $i++) {
            $actionsURL[$actions[$i]] = str_replace('&mode=list', '', $pagePath) . '&mode=' . $actions[$i];
        }
    }
    //HTML list
    $str_tableStart .= '<br />';
    $str_tableStart .= '<table width="100%" cellpadding="7" cellspacing="0">';
        $str_tableStart .= '<tbody>';
            $i=0;
            foreach ($objectList as $object) {
                if (!($i < $nbStart || $i > $nbEnd)) {
                    $cssClass_tr = 'style="background-color: #93D1E4;" ';
                    if ($i%2 == 0) {
                        $cssClass_tr = 'style="background-color: #DEEDF3;" ';
                    }
                    $str_adminList .= '<tr ' . $cssClass_tr . '>';
                    $j=0;
                    foreach($object as $key => $value) {
                        if ((!is_scalar($value) && $value) || (!array_key_exists($key, $showCols))) {
                            continue;
                        }
                        
                        $header[$j] = $key;
                        
                        if (!isset($showCols[$key]['cssStyle'])) {
                            $showCols[$key]['cssStyle'] = '';
                        }
                        if (isset($showCols[$key]['functionFormat']) && !empty($showCols[$key]['functionFormat'])) {
                            $explode_functionFormat = explode('#', $showCols[$key]['functionFormat']);
                            if ($explode_functionFormat[0] == 'standard') {
                                $formatFunctionName =  $explode_functionFormat[1];
                            } elseif ($explode_functionFormat[0] == 'custom') {
                                $className = '';
                                $formatFunctionName = $explode_functionFormat[1];
                            }
                            
                            if (isset($formatFunctionName)) {
                                $value = call_user_func($formatFunctionName, $value);
                            }
                        } elseif (substr($_REQUEST['what'], 0, 1) == '|') {
                            $surligneWhat = str_replace('|', '', $_REQUEST['what']);
                            $surligneWhat = str_replace('|', '', $_REQUEST['what']);
                            $value = str_replace($surligneWhat, '<span style="background-color: #f6bf36; font-weight: 900;">'.$surligneWhat.'</span>', $value);
                        }
                        
                        $str_adminList .= '<td class="' . $key . '" style="' . $showCols[$key]['cssStyle'] . '">';
                            $str_adminList .= $value;
                        $str_adminList .= '</td>';
                        
                        $j++;
                    }
                    if (in_array('read', $actions)) {
                        if (!in_array(' ', $header)) {
                            array_push($header, ' ');
                        }
                        $str_adminList .= '<td width="1%">';
                            $str_adminList .= '<a href="' . $actionsURL['read'] . '&objectId=' . $object->$keyName . '">';
                                $str_adminList .= _READ;
                            $str_adminList .= '</a>';
                        $str_adminList .= '</td>';
                    }
                    if (in_array('update', $actions)) {
                        if (!in_array('  ', $header)) {
                            array_push($header, '  ');
                        }
                        $str_adminList .= '<td width="8%">';
                            $str_adminList .= '<a href="' . $actionsURL['update'] . '&objectId=' . $object->$keyName . '">';
                                $str_adminList .= _UPDATE;
                            $str_adminList .= '</a>';
                        $str_adminList .= '</td>';
                    }
                    if (in_array('delete', $actions)) {
                        if (!in_array('   ', $header)) {
                            array_push($header, '   ');
                        }
                        $str_adminList .= '<td width="1%">';
                            $str_adminList .= '<a href="' . $actionsURL['delete'] . '">';
                                $str_adminList .= '<img src="static.php?filename=picto_delete.gif" />';
                            $str_adminList .= '</a>';
                        $str_adminList .= '</td>';
                    }
                    $str_adminList .= '</tr>';
                }
                $i++;
            }
        $str_tableEnd .= '</tbody>';
    $str_tableEnd .= '</table>';
    if (in_array('create', $actions)) {
        $str_create .= '<br />';
        $str_create .= '<div style="text-align: right;">';
            $str_create .= '<a href="' . $actionsURL['create'] . '">';
                $str_create .= 'créer';
            $str_create .= '</a>';
        $str_create .= '</div>';
    }
    //header
    $urlNoTri = str_replace('&order='.$_REQUEST['order'], '', $pagePath);
    $urlNoTri = str_replace('&orderField='.$_REQUEST['orderField'], '', $urlNoTri);
    
    $str_header .= '<tr style="background-color: #f6bf36; color: rgba(255, 255, 255, 1);">';
        for($j=0; $j<count($header); $j++) {
            $str_header .= '<td style="' . $showCols[$header[$j]]['cssStyle'] . 'color: #459ed1;">';
                $str_header .= '<b>';
                    $trimHeader = trim($header[$j]);
                    $str_header .= $header[$j];
                $str_header .= '</b>';
                $str_header .= '&nbsp;&nbsp;';
                if (!empty($trimHeader)) {
                    //tri ASC
                    $str_header .= '<a href="' . $urlNoTri . '&orderField=' . $trimHeader . '&order=asc">';
                        $order_asc = '<img src="static.php?filename=order_asc.png" />';
                        if ($_REQUEST['order'] == 'asc' && $_REQUEST['orderField'] == $trimHeader) {
                            $order_asc = '<img src="static.php?filename=order_asc_select.png" />';
                        }
                        $str_header .= $order_asc;
                    $str_header .= '</a>';
                    $str_header .= '&nbsp;';
                    //tri DESC
                    $str_header .= '<a href="' . $urlNoTri . '&orderField=' . $trimHeader . '&order=desc">';
                        $order_desc = '<img src="static.php?filename=order_desc.png" />';
                        if ($_REQUEST['order'] == 'desc' && $_REQUEST['orderField'] == $trimHeader) {
                            $order_desc = '<img src="static.php?filename=order_desc_select.png" />';
                        }
                        $str_header .= $order_desc;
                    $str_header .= '</a>';
                    $str_header .= '&nbsp;&nbsp;';
                }
            $str_header .= '</td>';
        }
    $str_header .= '</tr>';
    //retour html
    $listContent = '<br /><br />';
    $listContent .= $str_search;
    $listContent .= '<br />';
    $listContent .= $str_pagination;
    $listContent .= $str_tableStart;
    $listContent .= $str_header;
    $listContent .= $str_adminList;
    $listContent .= $str_tableEnd;
    $listContent .= $str_create;
    return $listContent;
}

/**
 * Load hidden fields in the CRUD form
 * @param string $objectName
 * @param string $hiddenFields
 */
function loadHiddenFields($params)
{
    $hiddenFields = '<input type="hidden" name="display" value="value" />';
    $hiddenFields .= '<input type="hidden" name="admin" value="' 
        . $params['object'] . '" />';
    $hiddenFields .= '<input type="hidden" name="page" value="' 
        . $params['page'] . '" />';
    $hiddenFields .= '<input type="hidden" name="mode" value="' 
        . $params['mode'] . '" />';
    if (isset($params['order'])) {
        $hiddenFields .= '<input type="hidden" name="order" value="'
        . $params['order'] . '" />';
    }
    if (isset($params['orderField'])) {
        $hiddenFields .= '<input type="hidden" name="orderField" value="'
        . $params['orderField'] . '" />';
    }
    if (isset($params['what'])) {
        $hiddenFields .= '<input type="hidden" name="what" value="'
        . $params['what'] . '" />';
    }
    return $hiddenFields;
}

function isBoolean($string)
{
    if ($string == 'Y') {
        $return = '<img src="static.php?filename=picto_stat_enabled.gif" />';
    } elseif($string == 'N') {
        $return = '<img src="static.php?filename=picto_stat_disabled.gif" />';
    }
    return $return;
}


$coreTools = new core_tools();
$coreTools->load_lang();

//tests and retrieve params of the controller page
$params = testParams($_REQUEST);
/*
echo '<pre>';
print_r($params);
echo '</pre>';
*/

if ($params['status'] == 'KO') {
    echo $params['error'];
    exit; 
}

//test if the user is allowed to acces the admin service
$coreTools->test_admin('admin_' . $params['object'], 'apps');

$pagePath = locationBarManagement(
    $params['pageName'], 
    $params['mode'], 
    $params['object'], 
    $params['isApps']
);
//load the object
$schemaPath = $params['viewLocation'] . '/xml/' . $params['object'] . '.xsd';

require_once('core/tests/class/DataObjectController.php');
$DataObjectController = new DataObjectController();
$DataObjectController->loadSchema($schemaPath);

//CRUDL CASES
switch ($params['mode']) {
    case 'create' :
        displayCreate($params['object']);
        break;
    case 'read' :
        $DataObjectController->setKey($params['object'], $params['objectId']);
        $RootDataObject = $DataObjectController->loadRootDataObject(
            $params['object']
        );
        $state = displayRead(
            $params['object'], 
            $RootDataObject
        );
        break;
    case 'update' :
        //test if objectId
        $DataObjectController->setKey($params['object'], $params['objectId']);
        $RootDataObject = $DataObjectController->loadRootDataObject(
            $params['object']
        );
        $state = displayUpdate(
            $params['object'], 
            $RootDataObject
        );
        break;
    case 'delete' :
        doDelete($params['objectId']);
        break;
    case 'list' :
        if (isset($params['orderField']) && !empty($params['orderField'])) {
            $DataObjectController->setOrder(
                $params['object'], 
                $params['orderField'],
                $params['order']
            );
        }
        $RootDataObject = $DataObjectController->loadRootDataObject(
            $params['object'] . '_root'
        );
        $keyName = $DataObjectController->getKey($params['object']);
        
        $listContent = displayList(
            $RootDataObject->$params['object'], 
            $actions,
            $showCols,
            $params['pageNb'],
            $keyName
        );
        break;
    //TODO: PROCESS IT LIKE PARTICULAR CASES OF UPDATE
    case 'allow' :
        doEnable($docserverId);
    case 'ban' :
        doDisable($docserverId);
}
