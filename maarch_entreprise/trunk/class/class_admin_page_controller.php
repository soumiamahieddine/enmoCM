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
* @brief Contains functions of the admin controller page
*
*
* @file
* @author Laurent Giovannoni
* @date $date$
* @version $Revision$
* @ingroup admin
*/

include_once 'core/class/class_core_tools.php';

class adminPageController
{
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
        
        return $paramsReturn = array(
            'status' => $status,
            'pageName' => $_REQUEST['page'],
            'pageNb' => $pageNb,
            'mode' => $mode,
            'object' => $object,
            'objectId' => $objectId,
            'isApps' => $isApps,
            'viewLocation' => $viewLocation,
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
            $this->initSession();
        }
    }
    
    /**
     * Initialize session parameters for update display
     * @param $objectId
     */
    function displayUpdate($objectName, $object)
    {
        $this->putInSession($objectName, $object);
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
    
    function displayList($object, $actions, $pagePath, $showCols, $pageNb)
    {
        $listShow = new list_show;
        $listContent = $listShow->adminListShow($object, $actions, $pagePath, $showCols, $pageNb);
        return $listContent;
    }
}
