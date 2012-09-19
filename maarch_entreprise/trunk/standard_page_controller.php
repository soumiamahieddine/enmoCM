<?php
/* -----------------------------------------------------------------------------
- standard page controller
----------------------------------------------------------------------------- */
//require
    //require the class core_tools
        require_once(
            'core/class/class_core_tools.php'
        );
    //require the class ViewController
        require_once(
            'core/class/class_history.php'
        );
    //require the class MessageController
        require_once(
            'core/tests/class/MessageController.php'
        );
    //require the class ViewController
        require_once(
            'core/tests/class/ViewController.php'
        );
    //require the DataObjectController
        require_once(
            'core/tests/class/DataObjectController.php'
        );

//object instanciation
    //object core_tools
        $core_tools = new core_tools();
    //object MessageController
        $messageController = new MessageController();
    //object ViewController
        $viewController = new ViewController();
    //object DataObjectController
        $dataObjectController = new DataObjectController();

//functions
    //testParams
        function testParams()
        {
            $error = false;
            
            $params = array();
            $params['status'] = 'OK';
            $params['mode']   = 'list';
            $params['pageNb'] = 1;
            $params['isApps'] = false;
            
            if (!empty($_REQUEST['mode']))
                $params['mode'] = $_REQUEST['mode'];
            
            if (!empty($_REQUEST['objectName']))
                $params['objectName'] = $_REQUEST['objectName'];
            else
                $error .= _OBJECT_NAME_MANDATORY . '<br />';
            
            if (!empty($_REQUEST['objectId']))
                $params['objectId'] = $_REQUEST['objectId'];
            
            if (!empty($_REQUEST['pageNb']))
                $params['pageNb'] = $_REQUEST['pageNb'];
            
            if (!empty($_REQUEST['admin'])) {
                $params['isApps'] = true;
                
                $params['viewLocation'] = 'apps/' 
                    . $_SESSION['config']['app_id'] . '/' 
                    . 'admin/' 
                    . $_REQUEST['admin'];
                    
                $params['schemaPath'] = $params['viewLocation'] . '/'
                    . 'xml/'
                    . $_REQUEST['admin'] . '.xsd';
                    
            } elseif (!empty($_REQUEST['module'])) {
                $params['viewLocation'] = 'modules/'
                    . $_REQUEST['module'];
                    
                $params['schemaPath'] = $params['viewLocation'] . '/'
                    . 'xml/'
                    . $_REQUEST['module'] . '.xsd';
                    
            }
            
            if (!empty($_REQUEST['order']))
                $params['order'] = $_REQUEST['order'];
            
            if (!empty($_REQUEST['orderField']))
                $params['orderField'] = $_REQUEST['orderField'];
            
            if (!empty($_REQUEST['what']))
                $params['what'] = $_REQUEST['what'];
            
            return $params;
        }
    
    //locationBarManagement
        function locationBarManagement($pageName, $mode, $objectName, $isApps)
        {
            $pageLabels = array();
            $pageLabels['add']  = _ADDITION;
            $pageLabels['up']   = _MODIFICATION;
            $pageLabels['list'] = _LIST;
            
            $pageIds = array();
            $pageIds['add']  = $objectName . '_add';
            $pageIds['up']   = $objectName . '_up';
            $pageIds['list'] = $objectNAme . '_list';
            
            $init = false;
            if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true')
                $init = true;
            
            $level = '';
            if (isset($_REQUEST['level']))
                $level = $_REQUEST['level'];
            
            if ($isApps) {
                $pagePath = $_SESSION['config']['businessappurl'] . 'index.php'
                          . '?page='       . $pageName
                          . '&admin='      . $objectName
                          . '&objectName=' . $objectName
                          . '&mode='       . $mode;
            } else {
                $pagePath = $_SESSION['config']['businessappurl'] . 'index.php'
                          . '?page='       . $pageName
                          . '&module='     . $objectName
                          . '&objectName=' . $objectName
                          . '&mode='       . $mode;
            }
            
            $pageLabel = $pageLabels[$mode];
            $pageId    = $pageIds[$mode];
            
            $coreTools = new core_tools();
            $coreTools->manage_location_bar(
                $pagePath, 
                $pageLabel, 
                $pageId, 
                $init, 
                $level
            );
            
            return $pagePath;
        }
        
    //initSession
        function initSession($objectName)
        {
            $_SESSION['m_admin'][$objectName] = false;
        }
        
    //updateObject
        function updateObject($request, $object)
        {
            foreach ($object as $key => $value) {
                $object->$key = $request[$key];
            }
        }
    
    //displayAdd
        function displayAdd($objectName)
        {
            if (!isset($_SESSION['m_admin'][$objectName]))
                initSession();
        }
    
    //displayCreate
        function displayCreate($objectName)
        {
            clearSession($objectName);
        }
    
    //displayRead
        function displayRead($objectName, $object)
        {
            putInSession($objectName, $object);
        }
    
    //displayUpdate
        function displayUpdate($objectName, $object)
        {
            putInSession($objectName, $object);
        }
    
    //putInSession
        function putInSession($objectName, $object)
        {
            $_SESSION['m_admin'][$objectName] = $object->asXml();
        }
    
    //clearSession
        function clearSession($objectName)
        {
            $_SESSION['m_admin'][$objectName] = false;
        }
    
    //loadHiddenFields
        function loadHiddenFields($params)
        {
            $hiddenFields  = '';
            $hiddenFields .= '<input ';
             $hiddenFields .= 'type="hidden" ';
             $hiddenFields .= 'name="display" ';
             $hiddenFields .= 'type="value" ';
            $hiddenFields .= '/>';
            
            $hiddenFields  = '';
            $hiddenFields .= '<input ';
             $hiddenFields .= 'type="hidden" ';
             $hiddenFields .= 'name="admin" ';
             $hiddenFields .= 'type="' . $params['objectName'] . '" ';
            $hiddenFields .= '/>';
            
            $hiddenFields  = '';
            $hiddenFields .= '<input ';
             $hiddenFields .= 'type="hidden" ';
             $hiddenFields .= 'name="page" ';
             $hiddenFields .= 'type="' . $params['page'] . '" ';
            $hiddenFields .= '/>';
            
            if (isset($params['order'])) {
                $hiddenFields  = '';
                $hiddenFields .= '<input ';
                 $hiddenFields .= 'type="hidden" ';
                 $hiddenFields .= 'name="order" ';
                 $hiddenFields .= 'type="' . $params['order'] . '" ';
                $hiddenFields .= '/>';
            }
            
            if (isset($params['orderField'])) {
                $hiddenFields  = '';
                $hiddenFields .= '<input ';
                 $hiddenFields .= 'type="hidden" ';
                 $hiddenFields .= 'name="orderField" ';
                 $hiddenFields .= 'type="' . $params['orderField'] . '" ';
                $hiddenFields .= '/>';
            }
            
            if (isset($params['what'])) {
                $hiddenFields  = '';
                $hiddenFields .= '<input ';
                 $hiddenFields .= 'type="hidden" ';
                 $hiddenFields .= 'name="what" ';
                 $hiddenFields .= 'type="' . $params['what'] . '" ';
                $hiddenFields .= '/>';
            }
            
            return $hiddenFields;
        }
    
    //isBoolean
        function isBoolean($string)
        {
            $return = '';
            
            if ($string == 'Y') {
                $return .= '<img ';
                 $return .= 'src="static.php?filename=picto_stat_enabled.gif" ';
                $return .= '/>';
            } else {
                $return .= '<img ';
                 $return .= 'src="static.php?filename=picto_stat_disabled.gif" ';
                $return .= '/>';
            }
            
            return $return;
        }
    
    //getLabel
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
            
            $source = array();
            $source[] = '?' . $toSearch . '&';
            $source[] = '&' . $toSearch;
            
            $target = array();
            $target[] = '?';
            $target[] = '';
            
            return str_replace($source, $target, $uri);
        }

//process
    //load_lang
        $core_tools->load_lang();
    
    //retrieve parameters
        $params = testParams();
    
    //test access
        if ($isApps)
            $core_tools->test_admin('admin_' . $params['objectName'], 'apps');
        else
            $core_tools->test_admin('admin_' . $params['objectName'], 'entities');
    
    //pagePath
        $pagePath = locationBarManagement(
            $params['pageName'],
            $params['mode'],
            $params['objectName'],
            $params['isApps']
        );
    
    //load message file
        $messagePath = $params['viewLocation'] . '/'
            . 'xml/' 
            . $params['objectName'] . '_Messages.xml';
        $messageController->loadMessageFile($messagePath);
    
    //load xsd file
        $dataObjectController->loadXSD($params['schemaPath']);
    
    //CRUDL cases
        switch($params['mode'])
        {
            case 'create' :
                $dataObject = $dataObjectController->create(
                    $params['objectName']
                );
                displayCreate($params['objectName']);
                break;
            
            case 'details' :
                $dataObject = $dataObjectController->read(
                    $params['objectName'], 
                    $params['objectId']
                );
                break;
            
            case 'read' :
                $dataObject = $dataObjectController->read(
                    $params['objectName'], 
                    $params['objectId']
                );
                break;
            
            case 'update' :
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
                break;
            
            case 'allow' :
                break;
            
            case 'ban' :
                break;
            
            case 'list' :
                clearSession($params['objectName']);
                
                $requestUri = $_SERVER['REQUEST_URI'];
                
                if (!empty($params['what']))
                    $filter = str_replace('.', '%', $params['what']);
                
                $dataObjectList = $dataObjectController->enumerate(
                    $params['objectName'],
                    $filter,
                    $sortFields = $params['orderField'],
                    $order = $params['order']
                );
                $objectList = $dataObjectList->$params['objectName'];
                
                $keyProperties = $dataObjectController->getKeyProperties(
                    $params['objectName']
                );
                
                $viewController->loadHTMLFile(
                    'modules/'
                    . 'records_management/' 
                    . $params['objectName'] . '_list.html'
                );
                $view = $viewController->view;
                
                $dataTranslates = $viewController->getDataTranslate();
                $i_max = $dataTranslates->length;
                for($i=0; $i<$i_max; $i++) {
                    $dataTranslate = $dataTranslates->item($i);
                    $translate = $dataTranslate->getAttribute('data-translate');
                    $dataTranslate->nodeValue = $messageController->getMessageText($translate);
                }
                /* ------
                - filters
                ------ */
                $noWhatUri = getDependantUri('what', getDependantUri('pageNb', $requestUri));
                $alphabetFilter = $view->getElementById('filter.alphabetique');
                $alphabetFilter->setDataAttribute('baseurl', $noWhatUri);
                $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $i_max = strlen($alphabet);
                for ($i=0; $i<$i_max; $i++) {
                    $letter = substr($alphabet, $i, 1);
                    $filter = $view->createElement('span');
                    $filter->setDataAttribute('filter', $letter . '.');
                    $filter->setAttribute('class', 'action');
                    $filter->setAttribute('onClick', 'filter(this)');
                    $filter->nodeValue = ' ' . $letter;
                    $alphabetFilter->appendChild($filter);
                }
                
                /* ---------
                - pagination
                --------- */
                $noPageNbUri = getDependantUri('pageNb', $requestUri);
                $noNbLineUrl = getDependantUri('nbLine', getDependantUri('pageNb', $requestUri));
                
                $nbLine = $_SESSION['config']['nblinetoshow'];
                if (!empty($_REQUEST['nbLine'])) 
                    $nbLine = $_REQUEST['nbLine'];
                
                $nbMax = count($objectList);
                $nbPageMax = ceil($nbMax/$nbLine);
                
                $nbStart = ($params['pageNb'] - 1) * $nbLine;
                if ($nbStart < 0)
                    $nbStart = 0;
                
                $nbEnd = $nbStart + ($nbLine - 1);
                if ($nbEnd > ($nbMax-1))
                    $nbEnd = $nbMax -1;
                
                $previousLink = $noPageNbUri . '&pageNb=' . ($params['pageNb'] - 1);
                $nextLink = $noPageNbUri . '&pageNb=' . ($params['pageNb'] + 1);
                
                $nbLineSelect = array(10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000, 25000, 50000);
                if (!in_array($_SESSION['config']['nblinetoshow'], $nbLineSelect))
                    $nbLineSelect[] = $_SESSION['config']['nblinetoshow'];
                sort($nbLineSelect);
                
                $paginationShow = $view->getElementById('pagination.show');
                $paginationShow->setDataAttribute('baseurl', $noNbLineUrl);
                $i_max = count($nbLineSelect);
                for ($i=0; $i<$i_max; $i++) {
                    if ($nbLineSelect[$i]>=count($objectList)) {
                        $option = $paginationShow->addOption(count($objectList), 'tous (' . count($objectList) . ')');
                        if (count($objectList) == $nbLine)
                            $option->setAttribute('selected', 'selected');
                        break;
                    }
                    $option = $paginationShow->addOption($nbLineSelect[$i], $nbLineSelect[$i]);
                }
                
                $paginationGoToPage = $view->getElementById('pagination.goToPage');
                $paginationGoToPage->setDataAttribute('baseurl', $noPageNbUri);
                for ($i=1; $i<=$nbPageMax; $i++) {
                    $option = $paginationGoToPage->addOption($i, $i);
                    if ($i == $params['pageNb'])
                        $option->setAttribute('selected', 'selected');
                }
                
                $paginationPrevious = $view->getElementById('pagination.previous');
                if ($params['pageNb'] > 1) {
                    $paginationPrevious->removeAttribute('style');
                    $paginationPrevious->setAttribute('href', $previousLink);
                }
                
                $paginationNext = $view->getElementById('pagination.next');
                if ($params['pageNb'] < $nbPageMax) {
                    $paginationNext->removeAttribute('style');
                    $paginationNext->setAttribute('href', $nextLink);
                }
                /* ----
                - order
                ---- */
                $noOrderUri = getDependantUri('orderField', getDependantUri('order', $requestUri));
                $listHeader = $view->getElementById('listHeader');
                $listHeader->setDataAttribute('baseurl', $noOrderUri);
                
                /* ----
                - liste
                ---- */
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
                
                $whatFilter = false;
                if (!empty($_REQUEST['what']))
                    $whatFilter = str_replace('.', '', $_REQUEST['what']);
                
                $liste = $view->getElementById('list');
                $rowTemplate = $view->getElementById('rowTemplate');
                $tableRow = $rowTemplate->cloneNode(true);
                $tableRow->removeAttribute('id');
                $tableRow->removeAttribute('style');
                for ($i=$nbStart; $i<=$nbEnd; $i++) {
                    $object = $objectList[$i];
                    
                    /* ---
                    - $key
                    --- */
                    $keyValues = array(); 
                    for($j=0; $j<count($keyProperties); $j++) {
                        $keyName = $keyProperties[$j];
                        $keyValues[] = $object->$keyName;
                    }
                    $key = implode(' ', $keyValues);
                    
                    /* ----
                    - lines
                    ---- */
                    $row = $tableRow->cloneNode(true);
                    
                    /* ----
                    - class
                    ---- */
                    $row->setAttribute('id', 'row_' . $i);
                    if (($i-$nbStart)%2==0)
                        $row->setAttribute('class', 'rowOdd');
                    else
                        $row->setAttribute('class', 'rowEven');
                    
                    /* ----
                    - cells
                    ---- */
                    $tds = $row->getElementsByTagName('td');
                    
                    $j_max = $tds->length;
                    for ($j=0; $j<$j_max; $j++) {
                        $td = $tds->item($j);
                        $name = $td->getAttribute('name');
                        if ($name) {
                            $propertyName = end(explode('.', $name));
                            if ($object->$propertyName) {
                                if ($whatFilter && strlen($whatFilter) > 2)
                                    $td->nodeValue = str_ireplace(
                                        $whatFilter, 
                                        '[[TODO[' . strtoupper($whatFilter) . ']TODO]]', 
                                        $object->$propertyName
                                    );
                                else
                                    $td->nodeValue = $object->$propertyName;
                            } else {
                                $td->setDataAttribute('key', $key);
                            }
                            
                            if ($propertyName == $params['orderField'])
                                $td->setAttribute('style', 'background-image: url(static.php?filename=black_0.1.png);');
                        }
                    }
                    $liste->appendChild($row);
                }
                
                /* ------
                - actions
                ------ */
                $actions = $viewController->query("//*[@data-action]");
                $i_max = $actions->length;
                for ($i=0; $i<$i_max; $i++) {
                    $action = $actions->item($i);
                    $type = $action->getAttribute('data-action');
                    switch($type) {
                        case 'create' :
                            $action->setAttribute(
                                'onclick',
                                'goTo(\'' . $actionsURL['create'] . '\');'
                            );
                            break;
                        case 'previsualize' :
                            $action->setAttribute(
                                'onclick',
                                'alert(\'en dev\');'
                            );
                            break;
                        case 'delete' :
                            $key = $action->getAttribute('data-key');
                            $action->setAttribute(
                                'onclick',
                                'alert(\'en dev\');'
                            );
                            break;
                        default :
                            $key = $action->getAttribute('data-key');
                            $action->setAttribute(
                                'onclick',
                                'goTo(\'' . $actionsURL[$type] . '&objectId=' . $key . '\');'
                            );
                    }
                }
                
                $viewController->showView();
                break;
        }