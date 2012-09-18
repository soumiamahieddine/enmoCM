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
                    $filter = str_replace('|', '%', $params['what']);
                
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
                $filters = $view->getElementById('filters');
                
                
                /* ---------
                - pagination
                --------- */
                
                
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
                
                $liste = $view->getElementById('list');
                $rowTemplate = $view->getElementById('rowTemplate');
                $tableRow = $rowTemplate->cloneNode(true);
                $tableRow->removeAttribute('id');
                $tableRow->removeAttribute('style');
                $i_max = count($objectList);
                for ($i=0; $i<$i_max; $i++) {
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
                    if ($i%2==0)
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
                            $td->nodeValue = $object->$propertyName;
                        }
                    }
                    $liste->appendChild($row);
                }
                
                /* ------
                - actions
                ------ */
                $adds = $viewController->query("//*[@name='add']");
                $i_max = $adds->length;
                for ($i=0; $i<$i_max; $i++) {
                    $add = $adds->item($i);
                    $add->setAttribute(
                        'href',
                        $requestUri . '&mode=create'
                    );
                }
                $reads = $viewController->query("//*[@name='read']");
                $i_max = $reads->length;
                for ($i=0; $i<$i_max; $i++) {
                    $read = $reads->item($i);
                    $read->setAttribute(
                        'href',
                        $actionsURL['read'] . '&objectId=' . $key
                    );
                }
                $updates = $viewController->query("//*[@name='update']");
                $i_max = $updates->length;
                for ($i=0; $i<$i_max; $i++) {
                    $update = $updates->item($i);
                    $update->setAttribute(
                        'href',
                        $actionsURL['update'] . '&objectId=' . $key
                    );
                }
                
                $viewController->showView();
                break;
        }
    
/* -----------------------------------------------------------------------------
- old
----------------------------------------------------------------------------- */

    //CRUDL CASES
    switch ($params['mode']) {
        case 'list' :
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
                                 $str_htmlList .= 'onClick=" ';
                                  $str_htmlList .= 'if (confirm(\'Supprimer ?\')) goTo(\'' . $actionsURL['delete'] . '&objectId=' . $key . '&display=true\');';
                                 $str_htmlList .= '" ';
                                $str_htmlList .= '>';
                                    $str_htmlList .= '<img ';
                                     $str_htmlList .= 'src="static.php?filename=picto_delete.gif" ';
                                    $str_htmlList .= '/>';
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
            
            /*
            echo $str_filter;
            echo $str_pagination;
            echo $str_htmlList;
            echo $str_previsualise;
            echo $str_goToTop;
            */
            break;
    }
