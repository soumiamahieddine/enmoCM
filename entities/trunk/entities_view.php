<?php
//CONTROLLER
    define('objectRead', 'Détail de: %1$s');
    
    //INIT
        $modeList   = false;
        $modeCreate = false;
        $modeRead   = false;
        $modeUpdate = false;
        
        $formFields = array(
            'docserver_id' => array(
                'show'     => true,
                'input'    => 'text',
                
            ),
            'docserver_type_id' => array(
                'show'          => true,
                'input'         => 'text',
                
            ),
            'device_label' => array(
                'show'     => true,
                'input'    => 'text',
                
            ),
            'is_readonly'     => array(
                'show'        => true,
                'input'       => 'radio',
                'radioValues' => array(
                    'Oui'     => 'Y',
                    'Non'     => 'N',
                    
                ),
                
            ),
            'size_format'        => array(
                'show'           => true,
                'jsEvent'        => 'onChange="convertSizeMoGoTo($(this).value);" ',
                'input'          => 'select',
                'selectValues'   => array(
                    'Megaoctets' => 'Mo',
                    'Gigaoctets' => 'Go',
                    'Teraoctets' => 'To',
                    
                ),
                
            ),
            'enabled'   => array(
                'show'  => false,
                
            ),
            'size_limit_number' => array(
                'show'          => true,
                'input'         => 'hidden',
                
            ),
            'size_limit_number_inForm' => array(
                'show'          => true,
                'input'         => 'text',
                
            ),
            'actual_size_number' => array(
                'show'                 => true,
                'input'                => 'hidden',
                
            ),
            'actual_size_number_inForm' => array(
                'show'                 => true,
                'input'                => 'text',
                
            ),
            'pourcentage_size' => array(
                'show'                 => true,
                'input'                => 'text',
                
            ),
            'path_template' => array(
                'show'      => true,
                'input'     => 'text',
                
            ),
            'ext_docservers_info' => array(
                'show'            => false,
                
            ),
            'chain_before' => array(
                'show'     => false,
                
            ),
            'chain_after' => array(
                'show'    => false,
                
            ),
            'creation_date' => array(
                'show'      => false,
                
            ),
            'closing_date' => array(
                'show'     => false,
                
            ),
            'coll_id'   => array(
                'show'  => true,
                'input' => 'text',
                
            ),
            'priority_number' => array(
                'show'        => true,
                'input'       => 'text',
                
            ),
            'docserver_location_id' => array(
                'show'              => true,
                'input'             => 'text',
                
            ),
            'adr_priority_number' => array(
                'show'            => true,
                'input'           => 'text',
                
            ),
            
        );
        
        $formButtons = array(
            'save'     => array(
                'show' => false,
                'jsEvent' => 'saveWithXSD',
                
            ),
            'cancel'   => array(
                'show' => false,
                'jsEvent' => 'onClick="window.location.href=\''.str_replace(array('&objectId='.$_REQUEST['objectId'], '&mode='.$_REQUEST['mode']), array('', ''), $_SERVER['REQUEST_URI']).'\'"; ',
                
            ),
            'back'       => array(
                'show'   => false,
                'jsEvent' => 'onClick="window.location.href=\''.str_replace(array('&objectId='.$_REQUEST['objectId'], '&mode='.$_REQUEST['mode']), array('', ''), $_SERVER['REQUEST_URI']).'\'"; ',
                
            ),
            
        );
    
    //Titre de la page
        $titleImageSource = $_SESSION['config']['businessappurl'].'static.php?filename=favicon.png';
        $objectLabel = $DataObjectController->getLabel($params['objectName']);

        
        if ($params['mode'] == 'list') {
            $modeList = true;
            $listLabel = $DataObjectController->getLabel($params['objectName'].'_list');
            $itemLabels = $DataObjectController->getContentLabels($params['objectName'].'_list');
            //echo '<pre>'.print_r($itemLabels, true).'</pre>';
            $titleText = getLabel($listLabel).' : '.count($dataObjectList->$params['objectName']).' '.getLabel($itemLabels[$params['objectName']]);
            
        } elseif ($params['mode'] == 'create') {
            $modeCreate = true;
            $titleText = getLabel($objectLabel).' '.getLabel(_ADDITION);
                
        } elseif ($params['mode'] == 'read') {
            $modeRead = true;
            //$titleText = getLabel(_READ).' '.getLabel($objectLabel);
            $titleText = sprintf(objectRead, getLabel($objectLabel));
                
        } elseif ($params['mode'] == 'update') {
            $modeUpdate = true;
            $titleText = _DOCSERVER_MODIFICATION;
            
        }
        
    //make list or form
        if ($modeList) {
            /* just show the list */
            $str_returnShow = $listContent;
            
        } elseif ($modeCreate) {
            $formButtons['save']['show'] = true;
            $formButtons['cancel']['show'] = true;            
            $str_returnShow = makeForm($formFields, $formButtons, $dataObject, $schemaPath, $params);
            
        } elseif ($modeRead) {
            foreach($formFields as $key => $value) {
                $formFields[$key]['readonly'] = true;
            }
            
            $formButtons['back']['show'] = true;
            
            $str_returnShow = makeForm($formFields, $formButtons, $dataObject, $schemaPath, $params);
            
        } elseif ($modeUpdate) {
            $formFields['docserver_id']['readonly'] = true;
            $formButtons['save']['show'] = true;
            $formButtons['cancel']['show'] = true;
            
            $str_returnShow = makeForm($formFields, $formButtons, $dataObject, $schemaPath, $params);
        }
        
    //function to create the form
        function makeForm($formFields, $formButtons, $dataObject, $schemaPath, $params) {
            $str_return .= '<table width="70%" align="center" >';
                foreach($formFields as $key => $value) {
                    if ($formFields[$key]['show']) {
                        $readonlyInput = '';
                        if ($formFields[$key]['readonly']) {
                            $readonlyInput = 'readonly="readonly"';
                        }
                        $jsEvent = '';
                        if (isset($formFields[$key]['jsEvent'])) {
                            $jsEvent = $formFields[$key]['jsEvent'];
                        }
                        $str_return .= '<tr>';
                            if ($formFields[$key]['input'] != 'hidden') {
                                $str_return .= '<td>';
                                    $str_return .= $key;
                                $str_return .= '</td>';
                                $str_return .= '<td style="width: 20px; text-align: center;" />';
                                $str_return .= '<td>';
                            }
                            $objectFieldValue = $dataObject->$key;
                            if ($formFields[$key]['input'] == 'text') {
                                $str_return .= '<input ';
                                  $str_return .= 'id="'.$key.'" ';
                                  $str_return .= 'name="'.$key.'" ';
                                  $str_return .= 'type="text" ';
                                  $str_return .= 'size="35" ';
                                  $str_return .= 'value="'.$objectFieldValue.'" ';
                                  $str_return .= $readonlyInput;
                                  $str_return .= $jsEvent;
                                $str_return .= '/>';
                            } elseif ($formFields[$key]['input'] == 'hidden') {
                                $str_return .= '<input ';
                                  $str_return .= 'id="'.$key.'" ';
                                  $str_return .= 'name="'.$key.'" ';
                                  $str_return .= 'value="'.$objectFieldValue.'" ';
                                  $str_return .= 'type="hidden" ';
                                $str_return .= '/>';
                            } elseif ($formFields[$key]['input'] == 'select') {
                                $str_return .= '<select ';
                                  $str_return .= 'id="'.$key.'" ';
                                  $str_return .= 'name="'.$key.'" ';
                                  $str_return .= $jsEvent;
                                $str_return .= '>';
                                    foreach ($formFields[$key]['selectValues'] as $keySelect => $valueSelect) {
                                        $selected = '';
                                        if ($valueSelect == $objectFieldValue) {
                                            $selected = 'selected="selected" ';
                                        }
                                        $str_return .= '<option ';
                                          $str_return .= 'value="'.$valueSelect.'" ';
                                        $str_return .= '>';
                                            $str_return .= $keySelect;
                                        $str_return .= '</option>';
                                    }
                                $str_return .= '</select>';
                            } elseif ($formFields[$key]['input'] == 'radio') {
                                foreach ($formFields[$key]['radioValues'] as $keyRadio => $valueRadio) {
                                    $selected = '';
                                    if ($valueRadio == $objectFieldValue) {
                                        $selected = 'checked ';
                                    }
                                    $str_return .= '<input ';
                                      $str_return .= 'type="radio" ';
                                      $str_return .= 'value="'.$valueRadio.'" ';
                                      $str_return .= 'name="'.$key.'" ';
                                      $str_return .= 'id="'.$key.'" ';
                                      $str_return .= $selected;
                                      $str_return .= $jsEvent;
                                      $str_return .= '>';
                                    $str_return .= $keyRadio;
                                    $str_return .= '  ';
                                }
                            }
                            if ($formFields[$key]['input'] != 'hidden') {
                                $str_return .= '</td>';
                            }
                        $str_return .= '</tr>';
                    }
                }
                $str_return .= '<tr>';
                    $str_return .= '<td>&nbsp;</td>';
                    $str_return .= '<td />';
                    $str_return .= '<td>&nbsp;</td>';
                $str_return .= '</tr>';
                $str_return .= '<tr>';
                    $str_return .= '<td/>';
                    $str_return .= '<td/>';
                    $str_return .= '<td>';
                        //echo '<pre>'.print_r($formButtons, true).'</pre>';exit;
                        foreach($formButtons as $keyButton => $valueButton) {
                            $jsEvent = '';
                            if (isset($formButtons[$keyButton]['jsEvent'])) {
                                $jsEvent = $formButtons[$keyButton]['jsEvent'];
                                if ($jsEvent == 'saveWithXSD') {
                                    $json = '{';
                                    foreach($formFields as $keyField => $valueField) {
                                        if ($formFields[$keyField]['show']) {
                                            if ($formFields[$keyField]['input'] == 'radio') {
                                                /*$json .= '\''.$keyField.'\' : function() {document.getElementsByName(\''.$keyField.'\')';
                                                $json .= 'for (var i=0; i < nodeList.length, i++) {';
                                                    $json .= 'if (nodeList[i].checked) { return nodeList[i].value} ';
                                                $json .= '}';*/
                                                
                                                $json .= '\''.$keyField.'\' : getCheckedValue(document.getElementsByName(\''.$keyField.'\')), ';
                                            } else {
                                                $json .= '\''.$keyField.'\' : $(\''.$keyField.'\').value, ';
                                            }
                                        }
                                    }
                                    
                                    $json .= "'schemaPathAjax':'".$schemaPath."', ";
                                    $json .= "'viewLocationAjax':'".$params['viewLocation']."', ";
                                    $json .= "'objectNameAjax':'".$_REQUEST['objectName']."'";
                                    
                                    $json .= '}';
                                    
                                    $jsEvent = 'onClick="';
                                     $jsEvent .= 'saveWithXSD(';
                                      $jsEvent .= $json;
                                     $jsEvent .= ');';
                                    $jsEvent .= '" ';
                                }
                            }
                            
                            if ($formButtons[$keyButton]['show']) {
                                $str_return .= '<input ';
                                  $str_return .= 'type="button" ';
                                  $str_return .= 'value="'.$keyButton.'" ';
                                  $str_return .= $jsEvent;
                                $str_return .= '/>';
                                $str_return .= '  ';
                            }
                        }
                    $str_return .= '</td>';
                $str_return .= '</tr>';
            $str_return .= '</table>';
            
            return $str_return;
        }
        
    //default JS
        $str_defaultJs .= '<script>';
            if ($modeCreate || $modeRead || $modeUpdate) {
                $str_defaultJs .= 'convertSizeMoGoTo(\'Mo\');';
                $str_defaultJs .= 'showPercent();';
            }
        $str_defaultJs .= '</script>';
?>

<!--VIEW-->
<script>
    function convertSizeMoGoTo(targetUnit) {
        var size_limit_number_inForm = false;
        var size_limit_numberinOctet = $('size_limit_number').value;
        if (targetUnit == 'Mo') {
            size_limit_number_inForm = (size_limit_numberinOctet / (1000 * 1000));
        } else if(targetUnit == 'Go') {
            size_limit_number_inForm = (size_limit_numberinOctet / (1000 * 1000 * 1000));
        } else if(targetUnit == 'To') {
            size_limit_number_inForm = (size_limit_numberinOctet / (1000 * 1000 * 1000 * 1000));
        }
        
        $('size_limit_number_inForm').setValue(size_limit_number_inForm + ' ' + targetUnit);
        
        
        var actual_size_number_inForm = false;
        var actual_size_numberinOctet = $('actual_size_number').value;
        if (targetUnit == 'Mo') {
            actual_size_number_inForm = (actual_size_numberinOctet / (1000 * 1000));
        } else if(targetUnit == 'Go') {
            actual_size_number_inForm = (actual_size_numberinOctet / (1000 * 1000 * 1000));
        } else if(targetUnit == 'To') {
            actual_size_number_inForm = (actual_size_numberinOctet / (1000 * 1000 * 1000 * 1000));
        }
        
        $('actual_size_number_inForm').setValue(actual_size_number_inForm + ' ' + targetUnit);
        
    }
    
    function showPercent() {
        var size_limit = $('size_limit_number').value;
        var actual_size = $('actual_size_number').value;
        
        var percent = false;
        percent = Math.round((actual_size / size_limit) * 100);
        
        $('pourcentage_size').setValue(percent + ' %');
    }
    
    function getCheckedValue(radioObj) {
        if(!radioObj)
            return "";
        var radioLength = radioObj.length;
        if(radioLength == undefined)
            if(radioObj.checked) {
                return radioObj.value;
            } else {
                return "";
            }
        for(var i = 0; i < radioLength; i++) {
            if(radioObj[i].checked) {
                return radioObj[i].value;
            }
        }
        return "";
    }
    
    function saveWithXSD(object) {
        var returnConfirm = false;
        returnConfirm = confirm('Êtes-vous sûr ?');
        
        if (returnConfirm) {
        
            for(i in object) {
                if ($(i)) {
                    $(i).style.backgroundColor = 'white';
                    $(i).style.color = 'black';
                    $(i).style.fontWeight = 'normal';
                }
            }
            
            var path_php = 'index.php?display=true&page=admin_standard_ajax&dir=admin';
            
            new Ajax.Request(path_php,
            {
                method:'post',
                parameters: object,
                onSuccess: function(answer){
                    eval("response = "+answer.responseText);
                    if (response.status == 1) {
                        alert('ok !');
                    } else {
                        //alert(response.messages);
                        $('returnAjax').update(response.messages);
                        $('returnAjax').innerHtml;
                        for(var i=0; i < response.failFields.length; i++) {
                            $(response.failFields[i]).style.backgroundColor = '#f6bf36';
                            $(response.failFields[i]).style.color = '#459ed1';
                            //$(response.failFields[i]).style.fontWeight = '900';
                        }
                    }
                }
            });
            
        }
        return;
    }
<?php if ($modeList) { ?>
    function show_goToTop() {
        var scrollHeight = f_filterResults (
            window.pageYOffset ? window.pageYOffset : 0,
            document.documentElement ? document.documentElement.scrollTop : 0,
            document.body ? document.body.scrollTop : 0
        );
        
        var innerHeight = window.innerHeight;
        var innerWidth  = window.innerWidth;
        var half_innerWidth  = (innerWidth / 2);
        
        var goToTopHeight = $('goToTop').getHeight();
        var goToTopWidth  = $('goToTop').getWidth();
        var half_goToTopWidth  = (goToTopWidth / 2);
        
        var top  = (innerHeight - (goToTopHeight + 15));
        var left = (half_innerWidth + 500 + 10);
        
        var opacity = (scrollHeight / innerHeight);
    
        if (opacity < 0.1) {
            $('goToTop').style.top     = '0px';
            $('goToTop').style.left    = '0px';
            $('goToTop').style.display = 'none';
            return ;
        } else if (opacity > 1) {
            opacity = 1;
        }
        
        $('goToTop').style.top     = top + 'px';
        $('goToTop').style.left    = left + 'px';
        $('goToTop').style.display = 'block';
        $('goToTop').style.opacity = opacity;
        return;
    }
    
    Event.observe(window, 'scroll', function() {
        show_goToTop();
    });
<?php } ?>
</script>
<h1>
    <img src="<?php echo $titleImageSource; ?>" />
    <?php echo $titleText; ?>
</h1>
<div class="<?php echo $params['objectName'] ?>">
    <div id="returnAjax"><br /><br /></div>
    <?php echo $str_returnShow; ?>
</div>
<?php echo $str_defaultJs; ?>
