<?php

/**
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
require_once 'core/tests/class/MessageController.php';
require_once 'core/tests/class/ViewController.php';
require_once 'core/class/class_history.php';
require_once 'apps/' . $_SESSION['config']['app_id'] 
    . '/admin/admin_form_standard_tools.php';
$messageController = new MessageController();
$viewController = new ViewController();

/**
 * Management of the location bar
 * @param string $pageName
 * @param string $mode
 * @param string $objectName
 * @param string $path
 * @return string $pagePath the current page path
 */
function locationBarManagement($pageName, $mode, $objectName, $isApps)
{
    $pageLabels = array(
        'add'   => _ADDITION,
        'up'    => _MODIFICATION,
        'list'  => _LIST,
    );
    $pageIds = array(
        'add'   => $objectName . '_add',
        'up'    => $objectName . '_up',
        'list'  => $objectName . '_list',
    );
    
    $init = false;
    if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true')
        $init = true;
    
    $level = '';
    $allowedLevels = array(
        1, 
        2, 
        3, 
        4
    );
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], $allowedLevels))
        $level = $_REQUEST['level'];
    
    if($isApps) {
        $pagePath = $_SESSION['config']['businessappurl'] . 'index.php'
            . '?page=' . $pageName 
            . '&admin=' . $objectName 
            . '&objectName=' . $objectName 
            . '&mode='  . $mode;
    } else {
        $pagePath = $_SESSION['config']['businessappurl'] . 'index.php?'
            . 'page='    . $pageName 
            . '&module=' . $objectName 
            . '&objectName=' . $objectName 
            . '&mode='   . $mode;
    }
    
    $pageLabel = $pageLabels[$mode];
    
    $pageId = $pageIds[$mode];
    
    $coreTools = new core_tools();
    $coreTools->manage_location_bar($pagePath, $pageLabel, $pageId, $init, $level);
    
    return $pagePath;
}

/* -----------------------
- test and retrieve params
----------------------- */
function testParams()
{
    /* -----------------------------------
    - Initialise array with default values
    ----------------------------------- */
    $params = array(
        'status' => 'OK',
        'mode' => 'list',
        'pageNb' => 1,
        'isApps' => false,
    );
    
    $error = false;
    
    /* ------------------
    - Test some $_REQUEST
    ------------------ */
    if (isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
        $params['mode'] = $_REQUEST['mode'];
    
    if (isset($_REQUEST['objectName']) && !empty($_REQUEST['objectName']))
        $params['objectName'] = $_REQUEST['objectName'];
    else
        $error .= _OBJECT_NAME_MANDATORY . '<br />';
    
    if (isset($_REQUEST['objectId']) && !empty($_REQUEST['objectId']))
        $params['objectId'] = $_REQUEST['objectId'];
    
    if (isset($_REQUEST['pageNb']) && !empty($_REQUEST['pageNb']))
        $params['pageNb'] = $_REQUEST['pageNb'];
    
    if (isset($_REQUEST['admin']) && !empty($_REQUEST['admin'])) {
        $params['isApps'] = true;
        $params['viewLocation'] = 'apps' . DIRECTORY_SEPARATOR
            . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR
            . 'admin' . DIRECTORY_SEPARATOR
            . $_REQUEST['admin'];
		$params['schemaPath'] = $params['viewLocation'] . DIRECTORY_SEPARATOR
			. 'xml' . DIRECTORY_SEPARATOR
			. $_REQUEST['admin'] . '.xsd';
    } elseif (isset($_REQUEST['module']) && !empty($_REQUEST['module'])) {
        $params['viewLocation'] = 'modules' . DIRECTORY_SEPARATOR
            . $_REQUEST['module'];
		$params['schemaPath'] = $params['viewLocation'] . DIRECTORY_SEPARATOR
			. 'xml' . DIRECTORY_SEPARATOR
			. $_REQUEST['module'] . '.xsd';
    }
    
    if (isset($_REQUEST['order']) && !empty($_REQUEST['order']))
        $params['order'] = $_REQUEST['order'];
    
    if (isset($_REQUEST['orderField']) && !empty($_REQUEST['orderField']))
        $params['orderField'] = $_REQUEST['orderField'];
    
    if (isset($_REQUEST['what']) && !empty($_REQUEST['what']))
        $params['what'] = $_REQUEST['what'];
    
    /* -----
    - return
    ----- */
    if ($error)
        exit($error);
    else
        return $params;
}

/**
 * Initialize session variables
 * @param string $objectName
 */
function initSession($objectName)
{
    $_SESSION['m_admin'][$objectName] = false;
}

/**
 * Initialize session Object with form values
 * @param string $objectName
 */
function updateObject($request, $object)
{
    foreach($object as $key => $value) {
        $object->$key = $request[$key];
    }
}

/**
 * Initialize session parameters for add display with given objectName
 * @param string $objectName
 */
function displayAdd($objectName)
{
    if (!isset($_SESSION['m_admin'][$objectName]))
        initSession();
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
}

/**
 * Put given object in session, according with given object
 * NOTE: given object needs to be at least hashable
 * @param string $objectName
 * @param object $object
 */
function putInSession($objectName, $object)
{
    $_SESSION['m_admin'][$objectName] = $object->asXml();
}

/**
 * Clear the object in session
 * @param string $objectName
 */
function clearSession($objectName)
{
    $_SESSION['m_admin'][$objectName] = false;
}

function displayList($objectList, $actions, $showCols, $pageNb, $keyProperties)
{
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
        . $params['objectName'] . '" />';
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

function getLabel($constant) 
{
    if (!defined($constant))
        return $constant;
    else
        return constant($constant);
}

//getDependantUri
function getDependantUri($get, $uri)
{
    $getValue = str_replace(
        ' ',
        '%20',
        $_REQUEST[$get]
    );
    
    $toSearch = $get . '=' . $getValue;

    $sourceArray = array(
        '?'.$toSearch . '&',
        '&'.$toSearch,
    );
    $targetArray = array(
        '?',
        '',
    );
    
    $return = str_replace(
        $sourceArray,
        $targetArray,
        $uri
    );
    
    return $return;
}

$coreTools = new core_tools();
$coreTools->load_lang();

//tests and retrieve params of the controller page
$params = testParams();

//test if the user is allowed to acces the admin service
if ($isApps)
    $coreTools->test_admin(
        'admin_' . $params['objectName'], 
        'apps'
    );
else
    $coreTools->test_admin(
        'admin_' . $params['objectName'], 
        'entities'
    );

$pagePath = locationBarManagement(
    $params['pageName'], 
    $params['mode'], 
    $params['objectName'], 
    $params['isApps']
);

//load the message object
$messagePath = $params['viewLocation'] . '/xml/' . $params['objectName'] . '_Messages.xml';
$messageController->loadMessageFile(
    $messagePath
);

require_once(
    'core/tests/class/DataObjectController.php'
);
$dataObjectController = new DataObjectController();
$dataObjectController->loadXSD(
    $params['schemaPath']
);

if (isset($_REQUEST['submit'])) {
    $dataObject = $dataObjectController->load(
        $_SESSION['m_admin'][$params['objectName']]
    );
    
    //fill the object with the request
    updateObject(
        $_REQUEST, 
        $dataObject
    );
    
    //validate the object
    $validateObject = $dataObjectController->validate(
        $dataObject
    );
    
    if ($validateObject) {
        $dataObjectController->save(
            $dataObject
        );
    } else {
        foreach($dataObjectController->getValidationErrors() as $error) {
            $errors[] = $error->message;
        }
        $_SESSION['error'] = implode('<br />', $errors);
        
        $url = $_SERVER['REQUEST_URI'];
        $url = str_replace(
            array(
                '?display=true&', 
                '&display=true'
            ), 
            array(
                '?', 
                ''
            ), 
            $url
        );
        
        $_SESSION['m_admin'][$params['objectName']] = $dataObject->asXml();
        
        header("Location: ".$url);
    }
    exit;
} else {
    //CRUDL CASES
    switch ($params['mode']) {
        case 'create' :
            /* -----
            - CREATE
            ----- */
        	$dataObject = $dataObjectController->create($params['objectName']);
            displayCreate($params['objectName']);
            
            break;
            
        case 'details' :
            /* ------
            - DETAILS
            ------ */
            $dataObject = $dataObjectController->read(
                $params['objectName'], $params['objectId']
            );
            
            break;
            
        case 'read' :
            /* ---
            - READ
            --- */
            $dataObject = $dataObjectController->read(
                $params['objectName'], $params['objectId']
            );
            
            break;
            
        case 'update' :
            /* -----
            - UPDATE
            ----- */
            if (!$_SESSION['m_admin'][$params['objectName']]) {
                $dataObject = $dataObjectController->read(
                    $params['objectName'], 
                    $params['objectId']
                );
                $_SESSION['m_admin'][$params['objectName']] = $dataObject->asXml();
            } else {
                $dataObject = $dataObjectController->load(
                    $_SESSION['m_admin'][$params['objectName']]
                );
            }
            
            break;
            
        case 'delete' :
            /* -----
            - DELETE
            ----- */
            break;
            
        //TODO: PROCESS IT LIKE PARTICULAR CASES OF UPDATE
        case 'allow' :
            doEnable($docserverId);
            break;
        case 'ban' :
            doDisable($docserverId);
            break;
        case 'list' :
            /* ---
            - LIST
            --- */
            clearSession($params['objectName']);
            
            /* ---------
            - set filter
            --------- */
            if (isset($params['what']) && !empty($params['what']))
                $filter = str_replace(
                    '|', 
                    '%', 
                    $params['what']
                );
            
            /* --------------
            - load dataObject
            -------------- */
            $dataObjectList = $dataObjectController->enumerate(
                $params['objectName'],
                $filter,
                $sortFields = $params['orderField'],
                $order = $params['order']
            );
            
            /* ------
            - get key
            ------ */
            $keyProperties = $dataObjectController->getKeyProperties(
                $params['objectName']
            );
            
            /* ---------
            - objectList
            --------- */
            $objectList = $dataObjectList->$params['objectName'];
            
            /* -----------------
            - prevent PHP NOTICE
            ----------------- */
            $str_filter     = '';
            $str_pagination = '';
            $str_htmlList   = '';
            $str_goToTop    = '';
            
            /* ----------
            - request uri
            ---------- */
            $requestUri = $_SERVER['REQUEST_URI'];
            
            /* -----
            - filter
            ----- */
            $noWhatUri = getDependantUri(
                'what',
                getDependantUri(
                    'pageNb',
                    $requestUri
                )
            );
            
            /* ------
            - filters
            ------ */
            $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            
            $str_filter .= '<table ';
             $str_filter .= 'width="100%" ';
            $str_filter .= '>';
                $str_filter .= '<tr>';
                    $str_filter .= '<td ';
                     $str_filter .= 'style="';
                      $str_filter .= 'text-align: left; ';
                     $str_filter .= '" ';
                    $str_filter .= '>';
                        $str_filter .= 'Liste alphab&eacute;tique: ';
                        for($cpt_alphabet=0; $cpt_alphabet<strlen($alphabet); $cpt_alphabet++) {
                            $str_filter .= '<span>';
                                $str_filter .= '<a ';
                                 $str_filter .= 'href="';
                                  $str_filter .= $noWhatUri . '&what=' . substr($alphabet, $cpt_alphabet, 1) . '|';
                                 $str_filter .= '" ';
                                $str_filter .= '>';
                                    $str_filter .= substr($alphabet, $cpt_alphabet, 1);
                                $str_filter .= '</a>';
                            $str_filter .= '</span>';
                            $str_filter .= '&nbsp;';
                        }
                        $str_filter .= ' - ';
                        $str_filter .= '<a ';
                         $str_filter .= 'href="' . $noWhatUri . '" ';
                        $str_filter .= '>';
                            $str_filter .= 'Tout afficher';
                        $str_filter .= '</a>';
                    $str_filter .= '</td>';
                    
                    $str_filter .= '<td ';
                     $str_filter .= 'style="';
                      $str_filter .= 'text-align: right; ';
                     $str_filter .= '"';
                    $str_filter .= ' >';
                        $str_filter .= '<input ';
                         $str_filter .= 'name="what" ';
                         $str_filter .= 'id="what" ';
                         $str_filter .= 'type="text" ';
                         $str_filter .= 'size="15" ';
                         $str_filter .= 'autocomplete="off" ';
                         $str_filter .= 'onKeyPress="'; 
                          $str_filter .= 'if(window.event.keyCode == 13 && $(\'what\').value != \'\')';
                           $str_filter .= 'goTo(\''.$noWhatUri.'&what=|\'+$(\'what\').value+\'|\'); ';
                         $str_filter .= '" ';
                        $str_filter .= '/>';
                        $str_filter .= '<div ';
                         $str_filter .= 'id="whatList" ';
                         $str_filter .= 'class="autocomplete" ';
                         $str_filter .= 'style="';
                          $str_filter .= 'display: none; ';
                         $str_filter .= '" ';
                        $str_filter .= '>';
                        $str_filter .= '</div>';
                        $str_filter .= '<script ';
                         $str_filter .= 'type="text/javascript" ';
                        $str_filter .= '>';
                            $str_filter .= 'initList(';
                                $str_filter .= '\'what\', ';
                                $str_filter .= '\'whatList\', ';
                                $str_filter .= '\''.$_SESSION['config']['businessappurl']
                                    .'index.php?display=true&admin=docservers&page=docservers_list_by_id\', ';
                                $str_filter .= '\'what\', ';
                                $str_filter .= '\'1\'';
                            $str_filter .= '); ';
                        $str_filter .= '</script>';
                        $str_filter .= '<input ';
                         $str_filter .= 'class="button" ';
                         $str_filter .= 'type="button" ';
                         $str_filter .= 'value="Filtrer" ';
                         $str_filter .= 'onClick="';
                          $str_filter .= 'if ($(\'what\').value != \'\') ';
                           $str_filter .= '{';
                            $str_filter .= 'goTo(';
                             $str_filter .= '\''.$noWhatUri.'&what=|\'+$(\'what\').value+\'|\'';
                            $str_filter .= ')';
                           $str_filter .= '} ';
                          $str_filter .= 'else ';
                           $str_filter .= '{';
                            $str_filter .= 'goTo(';
                             $str_filter .= '\''.$noWhatUri.'\'';
                            $str_filter .= ')';
                           $str_filter .= '}';
                         $str_filter .= '" ';
                        $str_filter .= '/>';
                    $str_filter .= '</td>';
                $str_filter .= '</tr>';
            $str_filter .= '</table>';
            
            /* ---------
            - pagination
            --------- */
            $nbLine = $_SESSION['config']['nblinetoshow'];
            if (isset($_REQUEST['nbLine']) && !empty($_REQUEST['nbLine'])) {
                $nbLine = $_REQUEST['nbLine'];
            }
            $nbEnd = $params['pageNb'] * $nbLine - 1;
            $nbStart = $nbEnd - $nbLine + 1;
            $nbMax = count(
                $objectList
            );
            $nbPageMax = ceil(
                $nbMax/$nbLine
            );
            $noPageNbUri = getDependantUri(
                'pageNb',
                $requestUri
            );
            $previousLink = $noPageNbUri . '&pageNb=' . ($params['pageNb'] - 1);
            $nextLink = $noPageNbUri . '&pageNb=' . ($params['pageNb'] + 1);
            
            $noNbLineUrl = getDependantUri(
                'nbLine',
                getDependantUri(
                    'pageNb',
                    $requestUri
                )
            );
            $nbLineSelect = array(
                10,
                25,
                50,
                100,
                250,
                500
            );
            if (!in_array($_SESSION['config']['nblinetoshow'], $nbLineSelect)) {
                array_push($nbLineSelect, $_SESSION['config']['nblinetoshow']);
            }
            sort($nbLineSelect);
        
            $str_pagination .= '<table ';
             $str_pagination .= 'width="100%" ';
            $str_pagination .= '>';
                $str_pagination .= '<tr>';
                    $str_pagination .= '<td ';
                     $str_pagination .= 'width="100px" ';
                     $str_pagination .= 'style="';
                      $str_pagination .= 'text-align: left; ';
                     $str_pagination .= '" ';
                    $str_pagination .= '>';
                        if ($params['pageNb'] > 1) {
                            $str_pagination .= '<a ';
                             $str_pagination .= 'href="' . $previousLink . '" ';
                            $str_pagination .= '>';
                                $str_pagination .= '< Précédente';
                            $str_pagination .= '</a>';
                        }
                    $str_pagination .= '</td>';
                    $str_pagination .= '<td ';
                     $str_pagination .= 'style="';
                      $str_pagination .= 'text-align: center; ';
                     $str_pagination .= '" ';
                    $str_pagination .= '>';
                        $str_pagination .= '<table ';
                         $str_pagination .= 'width="100%" ';
                        $str_pagination .= '>';
                            $str_pagination .= '<tr>';
                                $str_pagination .= '<td ';
                                 $str_pagination .= 'width="50%" ';
                                 $str_pagination .= 'style="';
                                    $str_pagination .= 'text-align: center; ';
                                 $str_pagination .= '" ';
                                $str_pagination .= '>';
                                    //nombre d'éléménts par page
                                    $str_pagination .= 'Afficher ';
                                    $str_pagination .= '<select ';
                                     $str_pagination .= 'onChange="';
                                      $str_pagination .= 'goTo(';
                                        $str_pagination .= '\''.$noNbLineUrl.'&nbLine=\'+this.value';
                                      $str_pagination .= ');';
                                     $str_pagination .= '" ';
                                    $str_pagination .= '>';
                                        for ($cpt_nbElement=0; $cpt_nbElement<count($nbLineSelect); $cpt_nbElement++) {
                                            if ($nbLineSelect[$cpt_nbElement] >= $nbMax) {
                                                break;
                                            }
                                            $default_nbLineSelect = '';
                                            if ($nbLineSelect[$cpt_nbElement] == $nbLine) {
                                                $default_nbLineSelect = 'selected="selected" ';
                                            }
                                            $str_pagination .= '<option ';
                                             $str_pagination .= 'value="' . $nbLineSelect[$cpt_nbElement] . '" ';
                                             $str_pagination .= $default_nbLineSelect; 
                                            $str_pagination .= '>';
                                                $str_pagination .= $nbLineSelect[$cpt_nbElement]; 
                                            $str_pagination .= '</option>';
                                        }
                                        $default_nbLineSelect = '';
                                        if ($nbMax == $nbLine || $nbMax < $nbLine) {
                                            $default_nbLineSelect = 'selected="selected" ';
                                        }
                                        $str_pagination .= '<option ';
                                         $str_pagination .= 'value="' . $nbMax . '" ';
                                         $str_pagination .= $default_nbLineSelect;
                                        $str_pagination .= '>';
                                            $str_pagination .= 'tous ('.$nbMax.')';
                                        $str_pagination .= '</option>';
                                    $str_pagination .= '</select>';
                                    $str_pagination .= ' éléments';
                                $str_pagination .= '</td>';
                                $str_pagination .= '<td ';
                                 $str_pagination .= 'width="50%" ';
                                 $str_pagination .= 'style="';
                                    $str_pagination .= 'text-align: center; ';
                                 $str_pagination .= '" ';
                                $str_pagination .= '>';
                                    //aller a la page
                                    if ($nbPageMax > 1) {
                                        $str_pagination .= 'Aller &agrave; la page ';
                                        $str_pagination .= '<select ';
                                         $str_pagination .= 'onChange="';
                                          $str_pagination .= 'goTo(this.value);';
                                         $str_pagination .= '" ';
                                        $str_pagination .= '>';
                                            for($cpt_pageNb=1; $cpt_pageNb<=$nbPageMax; $cpt_pageNb++) {
                                                $selected = '';
                                                if ($cpt_pageNb == $params['pageNb']) {
                                                    $selected = 'selected="selected" ';
                                                }
                                                $str_pagination .= '<option ';
                                                 $str_pagination .= 'value="';
                                                  $str_pagination .= $noPageNbUri . '&pageNb=' . $cpt_pageNb . '';
                                                 $str_pagination .= '" ';
                                                 $str_pagination .= $selected ;
                                                $str_pagination .= '>';
                                                    $str_pagination .= $cpt_pageNb;
                                                $str_pagination .= '</option>';
                                            }
                                        $str_pagination .= '</select>';
                                        $str_pagination .= ' sur '.$nbPageMax;
                                    }
                                $str_pagination .= '</td>';
                            $str_pagination .= '</tr>';
                        $str_pagination .= '</table>';
                    $str_pagination .= '</td>';
                    $str_pagination .= '<td ';
                     $str_pagination .= 'width="100px" ';
                     $str_pagination .= 'style="';
                      $str_pagination .= 'text-align: right; ';
                     $str_pagination .= '" ';
                    $str_pagination .= '>';
                        if ($params['pageNb'] < $nbPageMax) {
                            $str_pagination .= '<a ';
                             $str_pagination .= 'href="' . $nextLink . '" ';
                            $str_pagination .= '>';
                                $str_pagination .= 'Suivante >';
                            $str_pagination .= '</a>';
                        }
                    $str_pagination .= '</td>';
                $str_pagination .= '</tr>';
            $str_pagination .= '</table>';
            
            /* ------
            - actions
            ------ */
            $actionsURL = array();
            if (is_array($actions)) {
                for ($cpt_actions=0; $cpt_actions<count($actions); $cpt_actions++) {
                    $actionsURL[$actions[$cpt_actions]] = getDependantUri(
                        'mode', 
                        $requestUri
                    );
                    $actionsURL[$actions[$cpt_actions]] .= '&mode=' . $actions[$cpt_actions];
                }
            }
            
            /* ---
            - list
            --- */
            foreach ($showCols as $propertyName => $colParams) {
                $columnsLabels[$propertyName] = $messageController->getMessageText(
                    $params['objectName'] . '.' . $propertyName
                );
            }
            
            $noOrderUri = getDependantUri(
                'orderField', 
                getDependantUri(
                    'order', 
                    $requestUri
                )
            );
            
            $noModeUri = getDependantUri(
                'mode', 
                $requestUri
            );
            
            $str_htmlList .= '<table ';
             $str_htmlList .= 'id="'.$params['objectName'].'_list" ';
             $str_htmlList .= 'width="100%" ';
             $str_htmlList .= 'cellpadding="4" ';
             $str_htmlList .= 'cellspacing="0" ';
            $str_htmlList .= '>';
                $cpt_line = 0;
                //header
                $str_htmlList .= '<tr ';
                 $str_htmlList .= 'style="';
                  $str_htmlList .= 'background-color: #f6bf36; ';
                  $str_htmlList .= 'color: #459ed1; ';
                 $str_htmlList .= '" ';
                $str_htmlList .= '>';
                    foreach($columnsLabels as $labelId => $labelColumn) {
                        $keyColumn = $labelId;
                        $cssHeaderColumn = '';
                        if (isset($showCols[$keyColumn]['cssStyle'])) {
                            $cssHeaderColumn = $showCols[$keyColumn]['cssStyle'];
                        }
                        $str_htmlList .= '<td ';
                         $str_htmlList .= 'style="';
                          $str_htmlList .= $cssHeaderColumn;
                         $str_htmlList .= '" ';
                        $str_htmlList .= '>';
                            $str_htmlList .= '<b>';
                                $str_htmlList .= $columnsLabels[$labelId];
                            $str_htmlList .= '</b>';
                            $str_htmlList .= '<div>';
                                $str_htmlList .= '<a ';
                                 $str_htmlList .= 'href="' . $noOrderUri . '&orderField=' . $keyColumn . '&order=ascending" ';
                                $str_htmlList .= '>';
                                    if ($params['orderField'] == $keyColumn && $params['order'] == 'ascending') {
                                        $str_htmlList .= '<img ';
                                         $str_htmlList .= 'src="static.php?filename=order_asc_select.png" ';
                                        $str_htmlList .= '/>';
                                    } else {
                                        $str_htmlList .= '<img ';
                                         $str_htmlList .= 'src="static.php?filename=order_asc.png" ';
                                        $str_htmlList .= '/>';
                                    }
                                $str_htmlList .= '</a>';
                                $str_htmlList .= '&nbsp;';
                                $str_htmlList .= '<a ';
                                 $str_htmlList .= 'href="' . $noOrderUri . '&orderField=' . $keyColumn . '&order=descending" ';
                                $str_htmlList .= '>';
                                    if ($params['orderField'] == $keyColumn && $params['order'] == 'descending') {
                                        $str_htmlList .= '<img ';
                                         $str_htmlList .= 'src="static.php?filename=order_desc_select.png" ';
                                        $str_htmlList .= '/>';
                                    } else {
                                        $str_htmlList .= '<img ';
                                         $str_htmlList .= 'src="static.php?filename=order_desc.png" ';
                                        $str_htmlList .= '/>';
                                    }
                                $str_htmlList .= '</a>';
                            $str_htmlList .= '</div>';
                        $str_htmlList .= '</td>';
                    }
                    //cell for previsualise
                    $colspanTd = 1;
                    
                    if (in_array('read', $actions)) {
                        //cell for action read
                        $colspanTd++;
                    }
                    if (in_array('update', $actions)) {
                        //cell for action read
                        $colspanTd++;
                    }
                    if (in_array('delete', $actions)) {
                        //cell for action read
                        $colspanTd++;
                    }
                    
                    $str_htmlList .= '<td ';
                    $str_htmlList .= 'colspan="'.$colspanTd.'" ';
                     $str_htmlList .= 'onMouseOver="';
                      $str_htmlList .= '$(\'identifierDetailFrame\').setValue(\'\'); ';
                      $str_htmlList .= '$(\'return_previsualise\').style.display=\'none\';';
                     $str_htmlList .= '" ';
                     $str_htmlList .= 'style="';
                      $str_htmlList .= 'text-align: center; ';
                     $str_htmlList .= '" ';
                    $str_htmlList .= '>';
                        if (in_array('create', $actions)) {
                            $str_htmlList .= '<b>';
                                $str_htmlList .= '<span ';
                                 $str_htmlList .= 'style="';
                                  $str_htmlList .= 'height: 32px; ';
                                  $str_htmlList .= 'width: 100%; ';
                                  $str_htmlList .= 'background-color: rgba(255, 255 ,255 ,0.4); ';
                                  $str_htmlList .= 'border-radius: 10px; ';
                                  $str_htmlList .= 'border: 3px solid; ';
                                  $str_htmlList .= 'border-color: #459ed1; ';
                                  $str_htmlList .= 'float: right; ';
                                  $str_htmlList .= 'cursor: pointer; ';
                                 $str_htmlList .= '" ';
                                 $str_htmlList .= 'onClick="';
                                  $str_htmlList .= 'goTo(\''.$actionsURL['create'].'\'); ';
                                 $str_htmlList .= '" ';
                                 $str_htmlList .= 'onMouseOver="';
                                  $str_htmlList .= 'this.style.backgroundColor=\'rgba(255, 255, 255, 0.8)\'';
                                 $str_htmlList .= '" ';
                                 $str_htmlList .= 'onMouseOut="';
                                  $str_htmlList .= 'this.style.backgroundColor=\'rgba(255, 255, 255, 0.4)\'';
                                 $str_htmlList .= '" ';
                                $str_htmlList .= '>';
                                    $str_htmlList .= '<span ';
                                     $str_htmlList .= 'style="';
                                      $str_htmlList .= 'position: relative; ';
                                      $str_htmlList .= 'top: 9px; ';
                                     $str_htmlList .= '" ';
                                    $str_htmlList .= '>';
                                        $str_htmlList .= 'Ajouter';
                                    $str_htmlList .= '</span>';
                                $str_htmlList .= '</span>';
                            $str_htmlList .= '</b>';
                        }
                    $str_htmlList .= '</td>';
                    
                $str_htmlList .= '</tr>';
                //liste
                for ($i=0; $i<count($objectList); $i++) {
                    $object = $objectList[$i];
                    if (!($cpt_line < $nbStart || $cpt_line > $nbEnd)) {
                        $cssClass_tr = 'style="';
                         $cssClass_tr .= 'background-color: #DEEDF3; ';
                        $cssClass_tr .= '" ';
                        if (($cpt_line-$nbStart)%2 == 0) {
                            $cssClass_tr = 'style="';
                             $cssClass_tr .= 'background-color: #93D1E4;';
                            $cssClass_tr .= '" ';
                        }
                        $str_htmlList .= '<tr ';
                         $str_htmlList .= $cssClass_tr;
                        $str_htmlList .= '>';
                        	foreach($object->getProperties() as $propertyName => $propertyValue) {
	                        	$json[$propertyName] = $propertyValue;
                        	}
                            
                            foreach ($showCols as $propertyName => $colParams) {
	                            $propertyValue = (string)$object->$propertyName;
	                            
	                            $cssColumn = '';
                                if (isset($colParams['cssStyle'])) {
                                    $cssColumn = $colParams['cssStyle'];
                                }
                                if (isset($colParams['functionFormat']) && !empty($colParams['functionFormat'])) {
                                    $functionFormat = $colParams['functionFormat'];
                                    $propertyValue = call_user_func($functionFormat, $propertyValue);
                                } elseif (substr($_REQUEST['what'], 0, 1) == '|') {
                                    $surligneWhat = strtoupper(
                                        str_replace(
                                            '|', 
                                            '', 
                                            $_REQUEST['what']
                                        )
                                    );
                                    $replaceWith = '<span ';
                                     $replaceWith .= 'title="Recherche: \''.$surligneWhat.'\'" ';
                                     $replaceWith .= 'style="';
                                      $replaceWith .= 'background-color: rgba(84, 131, 246, 0.7); ';
                                      $replaceWith .= 'font-weight: 900; ';
                                      $replaceWith .= 'border-radius: 5px; ';
                                      $replaceWith .= 'padding: 2px; ';
                                      $replaceWith .= 'color: white; ';
                                      $replaceWith .= 'box-shadow: inset 0px 0px 15px rgba(0, 0, 0, 0.6); ';
                                     $replaceWith .= '" ';
                                    $replaceWith .= '>';
                                        $replaceWith .= $surligneWhat;
                                    $replaceWith .= '</span>';
                                    
                                    $propertyValue = str_ireplace(
                                        $surligneWhat, 
                                        $replaceWith, 
                                        $propertyValue
                                    );
                                }
                                $str_htmlList .= '<td ';
                                 $str_htmlList .= 'class="'.$propertyName.'" ';
                                 $str_htmlList .= 'style="';
                                  $str_htmlList .= $cssColumn;
                                 $str_htmlList .= '"';
                                 $str_htmlList .= 'onMouseOver="';
                                  $str_htmlList .= '$(\'identifierDetailFrame\').setValue(\'\'); ';
                                  $str_htmlList .= '$(\'return_previsualise\').style.display=\'none\';';
                                 $str_htmlList .= '"';
                                $str_htmlList .= '>';
                                    $str_htmlList .= $propertyValue;
                                $str_htmlList .= '</td>';
                            }
                            
                            //previsualize area
                            $encodeJSON = '{';
                                $encodeJSON .= "'identifierDetailFrame'";
                                $encodeJSON .= ' : ';
                                $encodeJSON .= "'".$cpt_line."'";
                                $encodeJSON .= ', ';
                                if (count($json) > 0) {
                                    foreach($json as $keyJSON => $valueJSON) {
                                        $encodeJSON .= "'".str_replace("'", "\'", $keyJSON)." '";
                                        $encodeJSON .= ' : ';
                                        if (DIRECTORY_SEPARATOR != '/') {
                                            $valueJSON = str_replace(
                                                DIRECTORY_SEPARATOR, 
                                                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, 
                                                $valueJSON
                                            );
                                        }
                                        $encodeJSON .= "'".str_replace("'", "\'", $valueJSON)." '";
                                        $encodeJSON .= ', ';
                                    }
                                }
                                $encodeJSON = substr(
                                    $encodeJSON, 
                                    0, 
                                    -2
                                );
                            $encodeJSON .= '}';
                            
                            $str_htmlList .= '<td ';
                             $str_htmlList .= 'onMouseOver="';
                              $str_htmlList .= 'previsualiseAdminRead(event, '.$encodeJSON.');';
                             $str_htmlList .= '" ';
                             $str_htmlList .= 'style="';
                              $str_htmlList .= 'background-image: url(static.php?filename=showFrameAdminList.png); ';
                              $str_htmlList .= 'background-repeat: no-repeat; ';
                              $str_htmlList .= 'background-position: center; ';
                              $str_htmlList .= 'width: 35px; ';
                              $str_htmlList .= 'cursor: help; ';
                             $str_htmlList .= '" ';
                            $str_htmlList .= '>';
                                $str_htmlList .= '';
                            $str_htmlList .= '</td>';
                            
                            //fill key array
                            $keyValues = array(); 
                            for($j=0; $j<count($keyProperties); $j++) {
                                $keyName = $keyProperties[$j];
                                $keyValues[] = $object->$keyName;
                            }
                            $key = implode(' ', $keyValues);
                            
                            //action read
                            if (in_array('read', $actions)) {
                                $str_htmlList .= '<td ';
                                 $str_htmlList .= 'onMouseOver="';
                                  $str_htmlList .= '$(\'identifierDetailFrame\').setValue(\'\'); ';
                                  $str_htmlList .= '$(\'return_previsualise\').style.display=\'none\';';
                                 $str_htmlList .= '" ';
                                $str_htmlList .= '>';
                                    $str_htmlList .= '<a ';
                                     $str_htmlList .= 'href="' . $actionsURL['read'] . '&objectId=' . $key . '"';
                                    $str_htmlList .= '>';
                                        $str_htmlList .= '<img ';
                                         $str_htmlList .= 'src="static.php?filename=icon_read.png" ';
                                        $str_htmlList .= '/>';
                                    $str_htmlList .= '</a>';
                                $str_htmlList .= '</td>';
                            }
                            //action update
                            if (in_array('update', $actions)) {
                                $str_htmlList .= '<td ';
                                 $str_htmlList .= 'onMouseOver="';
                                  $str_htmlList .= '$(\'identifierDetailFrame\').setValue(\'\'); ';
                                  $str_htmlList .= '$(\'return_previsualise\').style.display=\'none\';';
                                 $str_htmlList .= '" ';
                                $str_htmlList .= '>';
                                    $str_htmlList .= '<a ';
                                     $str_htmlList .= 'href="' . $actionsURL['update'] . '&objectId=' . $key . '"';
                                    $str_htmlList .= '>';
                                        $str_htmlList .= '<img ';
                                         $str_htmlList .= 'src="static.php?filename=picto_change.gif" ';
                                        $str_htmlList .= '/>';
                                    $str_htmlList .= '</a>';
                                $str_htmlList .= '</td>';
                            }
                            //action delete
                            if (in_array('delete', $actions)) {
                                $str_htmlList .= '<td ';
                                 $str_htmlList .= 'onMouseOver="';
                                  $str_htmlList .= '$(\'identifierDetailFrame\').setValue(\'\'); ';
                                  $str_htmlList .= '$(\'return_previsualise\').style.display=\'none\';';
                                 $str_htmlList .= '" ';
                                $str_htmlList .= '>';
                                    $str_htmlList .= '<a ';
                                     $str_htmlList .= 'href="' . $actionsURL['delete'] . '&objectId=' . $key . '&display=true"';
                                    $str_htmlList .= '>';
                                        $str_htmlList .= '<img ';
                                         $str_htmlList .= 'src="static.php?filename=picto_delete.gif" ';
                                        $str_htmlList .= '/>';
                                    $str_htmlList .= '</a>';
                                $str_htmlList .= '</td>';
                            }
                        $str_htmlList .= '</tr>';
                    }
                    $cpt_line++;
                }
            $str_htmlList .= '</table>';
            $str_htmlList .= '<table ';
             $str_htmlList .= 'width="100%" ';
            $str_htmlList .= '>';
                $str_htmlList .= '<tr>';
                    $str_htmlList .= '<td ';
                     $str_htmlList .= 'onMouseOver="';
                      $str_htmlList .= '$(\'identifierDetailFrame\').setValue(\'\'); ';
                      $str_htmlList .= '$(\'return_previsualise\').style.display=\'none\';';
                     $str_htmlList .= '" ';
                     $str_htmlList .= 'style="';
                      $str_htmlList .= 'text-align: right; height: 20px;';
                     $str_htmlList .= '" ';
                    $str_htmlList .= '>';
                        if (in_array('create', $actions)) {
                            $str_htmlList .= '<span ';
                             $str_htmlList .= 'style="';
                              $str_htmlList .= 'height: 1px; ';
                              $str_htmlList .= 'width: 100%; ';
                              $str_htmlList .= 'background-color: rgba(255, 255 ,255 ,0.5); ';
                              $str_htmlList .= 'border-radius: 10px; ';
                              $str_htmlList .= 'float: right; ';
                             $str_htmlList .= '" ';
                            $str_htmlList .= '>';
                            $str_htmlList .= '</span>';
                        }
                    $str_htmlList .= '</td>';
                $str_htmlList .= '</tr>';
            $str_htmlList .= '</table>';
                
            //div previsualize
            $str_previsualise  .= '<div ';
             $str_previsualise .= 'id="return_previsualise" ';
             $str_previsualise .= 'style="';
              $str_previsualise .= 'display: none; ';
              $str_previsualise .= 'border-radius: 10px; ';
              $str_previsualise .= 'box-shadow: 10px 10px 15px rgba(0, 0, 0, 0.4); ';
              $str_previsualise .= 'padding: 10px; ';
              $str_previsualise .= 'width: auto; ';
              $str_previsualise .= 'height: auto; ';
              $str_previsualise .= 'position: absolute; ';
              $str_previsualise .= 'top: 0; ';
              $str_previsualise .= 'left: 0; ';
              $str_previsualise .= 'z-index: 999; ';
              $str_previsualise .= 'background-color: rgba(255, 255, 255, 0.9); ';
              $str_previsualise .= 'border: 3px solid #459ed1;';
             $str_previsualise .= '" ';
            $str_previsualise .= '>';
                $str_previsualise .= '<input type="hidden" id="identifierDetailFrame" value="" />';
            $str_previsualise .= '</div>';
            
        //div goToTop
            $str_goToTop .= '<div ';
             $str_goToTop .= 'id="goToTop" ';
             $str_goToTop .= 'style="';
              $str_goToTop .= 'display: none; ';
              $str_goToTop .= 'width: 48px; ';
              $str_goToTop .= 'height: 48px; ';
              $str_goToTop .= 'border-radius: 12px; ';
              $str_goToTop .= 'border: 3px solid; ';
              $str_goToTop .= 'border-color: #459ed1; ';
              $str_goToTop .= 'background-image: url(static.php?filename=goToTop.png); ';
              $str_goToTop .= 'background-repeat: no-repeat; ';
              $str_goToTop .= 'background-position: center; ';
              $str_goToTop .= 'position: fixed; ';
              $str_goToTop .= 'cursor: pointer; ';
             $str_goToTop .= '" ';
             $str_goToTop .= 'onClick="';
              $str_goToTop .= 'goTo(\'#\'); ';
             $str_goToTop .= '" ';
            $str_goToTop .= '>';
            $str_goToTop .= '</div>';
        
            echo $str_filter;
            echo $str_pagination;
            echo $str_htmlList;
            echo $str_previsualise;
            echo $str_goToTop;
            
            break;
    }
}
