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
$coreTools = new core_tools();
$coreTools->load_lang();

$adminPageController = new adminPageController();

//tests and retrieve params of the controller page
$params = $adminPageController->testParams($_REQUEST);
echo '<pre>';
print_r($params);
echo '</pre>';

if ($params['status'] == 'KO') {
    echo $params['error'];
    exit; 
}

//test if the user is allowed to acces the admin service
$coreTools->test_admin('admin_' . $params['object'], 'apps');

$pagePath = $adminPageController->locationBarManagement(
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
$RootDataObject = $DataObjectController->loadRootDataObject($params['object'] . '_root');

//$DataObjectController->validate();

//if mode = read, update, delete of the objectId

//echo '<pre>';
//var_dump($RootDataObject);
//echo '</pre>';

//CRUDL CASES
switch ($params['mode']) {
    case 'create' :
        $adminPageController->displayCreate();
        break;
    case 'read' :
        $state = $adminPageController->displayRead($params['objectId']);
        break;
    case 'update' :
        //test if objectId
        $myObject = $RootDataObject->{$params['object']}[0];
        $state = $adminPageController->displayUpdate(
            $params['object'], 
            $myObject
        );
        echo '<pre>';
        print_r($_SESSION['m_admin']);
        echo '</pre>';
        break;
    case 'delete' :
        $adminPageController->doDelete($params['objectId']);
        break;
    case 'list' :
        require_once('apps/' . $_SESSION['config']['app_id'] 
            . '/class/class_list_show.php');
            
        $listContent = $adminPageController->displayList(
            $RootDataObject->$params['object'], 
            $actions, 
            $showCols, 
            $params['pageNb']
        );
        break;
    //TODO: PROCESS IT LIKE PARTICULAR CASES OF UPDATE
    case 'allow' :
        doEnable($docserverId);
    case 'ban' :
        doDisable($docserverId);
}
