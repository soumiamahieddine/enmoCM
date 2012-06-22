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
            'cancel'   => array(
                'show' => false,
                'jsEvent' => 'onClick="window.location.href=\''.str_replace(array('&objectId='.$_REQUEST['objectId'], '&mode='.$_REQUEST['mode']), array('', ''), $_SERVER['REQUEST_URI']).'\'"; ',
                
            ),
            'save'     => array(
                'show' => false,
                'jsEvent' => 'onClick="saveWithXSD();"',
                
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
            $str_returnShow = makeForm($formFields, $formButtons);
            
        } elseif ($modeRead) {
            foreach($formFields as $key => $value) {
                $formFields[$key]['readonly'] = true;
            }
            
            $formButtons['back']['show'] = true;
            
            $str_returnShow = makeForm($formFields, $formButtons);
            
        } elseif ($modeUpdate) {
            $formButtons['save']['show'] = true;
            $formButtons['cancel']['show'] = true;
            
            $str_returnShow = makeForm($formFields, $formButtons);
        }
        
    //function to create the form
        function makeForm($formFields, $formButtons) {
            $str_return .= '<table width="80%" align="center">';
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
                            $objectFieldValue = '';
                            $objectFieldValue = $_SESSION['m_admin']['docservers']->$key;
                            if ($formFields[$key]['input'] == 'text') {
                                $str_return .= '<input ';
                                  $str_return .= 'id="'.$key.'" ';
                                  $str_return .= 'name="'.$key.'" ';
                                  $str_return .= 'type="text" ';
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
    
    function saveWithXSD() {
        var returnConfirm = false;
        returnConfirm = confirm('Êtes-vous sûr ?');
        
        if (returnConfirm) {
            alert('super');
        } else {
            alert('tanpis');
        }
    }
</script>
<h1>
    <img 
      src="<?php echo $titleImageSource; ?>" 
      alt="" 
    />
    <?php echo $titleText; ?>
</h1>
<?php echo $str_returnShow; ?>
<?php echo $str_defaultJs; ?>
