<?php
//CONTROLLER
    //INIT
        $noModeUri = getDependantUri(
            'objectId',
            getDependantUri(
                'mode',
                $_SERVER['REQUEST_URI']
            )
        );
    
        $modeList   = false;
        $modeCreate = false;
        $modeRead   = false;
        $modeUpdate = false;
        
        $formFields = array(
            'entity_id' => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
                'tagStart' => true,
                'tagAlreadyOpen' => true,
            ),
            'entity_label' => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
            ),
            'short_label'  => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
            ),
            'enabled'      => array(
                'show'     => false,
                'tag'      => _STANDARD,
            ),
            'adrs_1'       => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
            ),
            'adrs_2'       => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
            ),
            'adrs_3'       => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
            ),
            'zipcode'      => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
            ),
            'city'         => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
            ),
            'country'      => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
            ),
            'email'        => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
            ),
            'business_id'  => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
            ),
            'parent_entity_id' => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _STANDARD,
            ),
            'entity_type'  => array(
                'show'     => true,
                    'input'          => 'select',
                    'selectValues'   => array(
                        'Bureau' => 'Bureau',
                        'Service' => 'Service',
                        'Direction' => 'Direction',
                    ),
                'tag'      => _STANDARD,
            ),
            'is_archival'  => array(
                'show'     => true,
                'input'    => 'radio',
                'tag'      => _5_ARCHIVAL,
                'tagAlreadyOpen' => true,
                'tagStart' => true,
                'radioValues'   => array(
                        _YES => 'Y',
                        _NO => 'N',
                    ),
            ),
            'is_originating' => array(
                'show'     => true,
                'input'    => 'radio',
                'tag'      => _5_ARCHIVAL,
                'radioValues'   => array(
                        _YES => 'Y',
                        _NO => 'N',
                    ),
            ),
            'is_transferring' => array(
                'show'     => true,
                'input'    => 'radio',
                'tag'      => _5_ARCHIVAL,
                'radioValues'   => array(
                        _YES => 'Y',
                        _NO => 'N',
                    ),
            ),
            'is_controlling'   => array(
                'show'     => true,
                'input'    => 'radio',
                'tag'      => _5_ARCHIVAL,
                'radioValues'   => array(
                        _YES => 'Y',
                        _NO => 'N',
                    ),
            ),
            'entity_typeBis'   => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _51_IDENTIFICATION,
                'readonly'      => true,
            ),
            'entity_labelBis'    => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _51_IDENTIFICATION,
                'readonly' => true,
            ),
            'parallel_forms_of_names' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _51_IDENTIFICATION,
                'tagStart' => true,
            ),
            'other_normalized_names' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _51_IDENTIFICATION,
            ),
            'other_names' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _51_IDENTIFICATION,
            ),
            'entity_IdBis' => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _51_IDENTIFICATION,
                'readonly' => true,
            ),
            'oldest_date' => array(
                'show'     => true,
                'input'    => 'date',
                'tag'      => _52_DESCRIPTION,
            ),
            'latest_date' => array(
                'show'     => true,
                'input'    => 'date',
                'tag'      => _52_DESCRIPTION,
            ),
            'history' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _52_DESCRIPTION,
                'tagStart' => true,
            ),
            'places' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _52_DESCRIPTION,
            ),
            'legal_status' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _52_DESCRIPTION,
            ),
            'activities' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _52_DESCRIPTION,
            ),
            'mandates' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _52_DESCRIPTION,
            ),
            'structure' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _52_DESCRIPTION,
            ),
            'context' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _52_DESCRIPTION,
            ),
            'record_id' => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _54_CONTROL,
            ),
            'institution_id' => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _54_CONTROL,
                'tagStart' => true,
            ),
            'rules' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _54_CONTROL,
            ),
            'status' => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _54_CONTROL,
            ),
            'detail_level' => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _54_CONTROL,
            ),
            'maintenance_dates' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _54_CONTROL,
            ),
            'language' => array(
                'show'     => true,
                'input'    => 'text',
                'tag'      => _54_CONTROL,
            ),
            'sources' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _54_CONTROL,
            ),
            'maintenance_notes' => array(
                'show'     => true,
                'input'    => 'textarea',
                'tag'      => _54_CONTROL,
            ),
        );
        
        $formButtons = array(
            'save'     => array(
                'show' => false,
                'jsEvent' => 'saveWithXSD',
                
            ),'add'     => array(
                'show' => false,
                'jsEvent' => 'saveWithXSD',
                
            ),
            'cancel'   => array(
                'show' => false,
                'jsEvent' => 'onClick="window.location.href=\''.$noModeUri.'\'"; ',
                
            ),
            'back'       => array(
                'show'   => false,
                'jsEvent' => 'onClick="window.location.href=\''.$noModeUri.'\'"; ',
                
            ),
            
        );
    
        //Titre de la page
        $titleImageSource = $_SESSION['config']['businessappurl'].'static.php?filename=favicon.png';
        $messageController = new MessageController();
        $messageController->loadMessageFile($params['viewLocation'] . '/xml/' . $params['objectName'] . '_Messages.xml');
        
        if ($params['mode'] == 'list') {
            $modeList = true;
            $titleText = $messageController->getMessageText('entities_list', false, array(count($dataObjectList->$params['objectName'])));
        } elseif ($params['mode'] == 'create') {
            $modeCreate = true;
            $titleText = $messageController->getMessageText('entities_create');
                
        } elseif ($params['mode'] == 'read') {
            $modeRead = true;
            //$titleText = getLabel(_READ).' '.getLabel($objectLabel);
            $titleText = $messageController->getMessageText('entities_read');
        } elseif ($params['mode'] == 'update') {
            $modeUpdate = true;
            $titleText = $messageController->getMessageText('entities_update');
        }
        
        //make list or form
        $columnsLabels = $messageController->getTexts(
            $params['objectName'] . '.'
        );
                
        if ($modeList) {
            /* just show the list */
            $str_returnShow = $listContent;
            
        } elseif ($modeCreate) {
            $formButtons['add']['show'] = true;
            $formButtons['cancel']['show'] = true;            
            $str_returnShow = makeForm($formFields, $formButtons, $dataObject, $schemaPath, $params, $noModeUri, $columnsLabels);
            
        } elseif ($modeRead) {
            foreach($formFields as $key => $value) {
                $formFields[$key]['readonly'] = true;
            }
            
            $formButtons['back']['show'] = true;
            
            $str_returnShow = makeAdvForm($formFields, $formButtons, $dataObject, $schemaPath, $params, $noModeUri, $columnsLabels);
            
        } elseif ($modeUpdate) {
            $formFields['entity_id']['readonly'] = true;
            $formButtons['save']['show'] = true;
            $formButtons['cancel']['show'] = true;
            
            $str_returnShow = makeForm($formFields, $formButtons, $dataObject, $schemaPath, $params, $noModeUri, $columnsLabels);
        }

    //default JS
        /*$str_defaultJs .= '<script>';
            if ($modeCreate || $modeRead || $modeUpdate) {
                $str_defaultJs .= 'convertSizeMoGoTo(\'Mo\');';
                $str_defaultJs .= 'showPercent();';
            }
        $str_defaultJs .= '</script>';*/
?>

<!--VIEW-->
<script>
    
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
                        goTo('<?php echo $noModeUri; ?>');
                    } else {
                        //alert(response.messages);
                        $('returnAjax').update(response.messages);
                        $('returnAjax').innerHtml;
                        for(var i=0; i < response.failFields.length; i++) {
                            $(response.failFields[i]).style.backgroundColor = '#f6bf36';
                            $(response.failFields[i]).style.color = '#459ed1';
                        }
                        if (response.alert.length > 0) {

                            alert(response.alert);
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
        
        var listHeight = $('<?php echo $params['objectName'] ?>_list').getHeight();
        
        var innerHeight = window.innerHeight;
        var innerWidth  = window.innerWidth;
        var half_innerWidth  = (innerWidth / 2);
        
        var goToTopHeight = $('goToTop').getHeight();
        var goToTopWidth  = $('goToTop').getWidth();
        
        var top  = (innerHeight - (goToTopHeight + 68));
        var left = (half_innerWidth + 500 + 10);
        
        var opacity = (scrollHeight / (listHeight - innerHeight));
    
        if (opacity < 0.01) {
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
