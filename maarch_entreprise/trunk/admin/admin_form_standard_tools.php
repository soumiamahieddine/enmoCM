<?php

//function to create the form
function makeForm($formFields, $formButtons, $dataObject, $schemaPath, $params, $noModeUri, $columnsLabels) {
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
                            if (!empty($columnsLabels[$params['objectName'] . '.' . $key])) {
                                $str_return .= $columnsLabels[$params['objectName'] . '.' . $key];
                            } else {
                                $str_return .= '<span ';
                                 $str_return .= 'style="';
                                  $str_return .= 'color: red; ';
                                 $str_return .= '">';
                                    $str_return .= '##_'.$key.'_##';
                                $str_return .= '</span>';
                            }
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
                          $str_return .= $jsEvent;
                          $str_return .= 'value="'.$objectFieldValue.'" ';
                          $str_return .= $readonlyInput;
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
                              $str_return .= $jsEvent;
                              $str_return .= $selected;
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
                                        $json .= '\''.$keyField.'\' : getCheckedValue(document.getElementsByName(\''.$keyField.'\')), ';
                                    } else {
                                        $json .= '\''.$keyField.'\' : $(\''.$keyField.'\').value, ';
                                    }
                                }
                            }
                            
                            $json .= "'modeAjax':'".$_REQUEST['mode']."', ";
                            $json .= "'schemaPathAjax':'".$schemaPath."', ";
                            $json .= "'viewLocationAjax':'".$params['viewLocation']."', ";
                            $json .= "'noModeUriAjax':'".$noModeUri."', ";
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

//function to create the form
function makeAdvForm($formFields, $formButtons, $dataObject, $schemaPath, $params, $noModeUri, $columnsLabels) {
    $str_return .= '<form name="formAdvAdmin" method="post" class="forms">';
    $newTag = '';
    $cptTag = 0;
    $cptTotalElements = count($formFields);
    $cptCurrentElement = 0;
        foreach($formFields as $key => $value) {
            $cptCurrentElement++;
            if ($formFields[$key]['tag']) {
                if ($newTag <> $formFields[$key]['tag']) {
                    $cptTag++;
                    if ($newTag <> '') {
                            $str_return .= '</div>';
                        $str_return .= '</div>';
                        $str_return .= '<br/>';
                    }
                    $newTag = $formFields[$key]['tag'];
                    $str_return .= '<h3 style="color:#FFC200;" onclick="new Effect.toggle(\'div' . $cptTag . '\', \'blind\', {delay:0.2});">'
                        . $formFields[$key]['tag'] . ' <span style="color:#1C99C5;">>></span></h3>';
                    $str_return .= '<hr class="hr_admin"/>';
                    $str_return .= '<br/>';
                    if ($formFields[$key]['tagAlreadyOpen']) {
                        $displayDiv = 'style="width:100%"';
                    } else {
                        $displayDiv = 'style="display:none;width:100%"';
                    }
                }
                if ($formFields[$key]['tagStart']) {
                    $str_return .= '<div class="desc" id="div' . $cptTag . '" ' . $displayDiv . '>';
                    $str_return .= '<div class="ref-unit">';
                }
            }
            if ($formFields[$key]['show']) {
                $readonlyInput = '';
                if ($formFields[$key]['readonly']) {
                    $readonlyInput = 'readonly="readonly"';
                }
                $jsEvent = '';
                if (isset($formFields[$key]['jsEvent'])) {
                    $jsEvent = $formFields[$key]['jsEvent'];
                }
                $str_return .= '<p>';
                    if ($formFields[$key]['input'] != 'hidden') {
                        $str_return .= '<label for="' . $key . '">';
                            if (!empty($columnsLabels[$params['objectName'] . '.' . $key])) {
                                $str_return .= $columnsLabels[$params['objectName'] . '.' . $key] . ' : ';
                            } else {
                                $str_return .= '<span ';
                                 $str_return .= 'style="';
                                  $str_return .= 'color: red; ';
                                 $str_return .= '">';
                                    $str_return .= '##_'.$key.'_##';
                                $str_return .= '</span>';
                            }
                        $str_return .= '</label>';
                    }
                    $objectFieldValue = $dataObject->$key;
                    if ($formFields[$key]['input'] == 'text') {
                        $str_return .= '<input ';
                          $str_return .= 'id="'.$key.'" ';
                          $str_return .= 'name="'.$key.'" ';
                          $str_return .= 'type="text" ';
                          $str_return .= 'size="35" ';
                          $str_return .= $jsEvent;
                          $str_return .= 'value="'.$objectFieldValue.'" ';
                          $str_return .= $readonlyInput;
                        $str_return .= '/>';
                    } elseif ($formFields[$key]['input'] == 'textarea') {
                        $str_return .= '<textarea ';
                          $str_return .= 'id="'.$key.'" ';
                          $str_return .= 'name="'.$key.'" ';
                          $str_return .= $jsEvent;
                          $str_return .= $readonlyInput;
                          $str_return .= '>';
                          $str_return .= $objectFieldValue;
                          $str_return .= '</textarea>';
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
                              $str_return .= $jsEvent;
                              $str_return .= $selected;
                              $str_return .= '>';
                            $str_return .= $keyRadio;
                            $str_return .= '  ';
                        }
                    }
                $str_return .= '</p>';
            }
            if ($cptTotalElements == $cptCurrentElement && $cptTag <> 0) {
                    $str_return .= '</div>';
                $str_return .= '</div>';
            }
        }
        $str_return .= '<table width="70%" align="center" >';
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
                                        $json .= '\''.$keyField.'\' : getCheckedValue(document.getElementsByName(\''.$keyField.'\')), ';
                                    } else {
                                        $json .= '\''.$keyField.'\' : $(\''.$keyField.'\').value, ';
                                    }
                                }
                            }
                            
                            $json .= "'modeAjax':'".$_REQUEST['mode']."', ";
                            $json .= "'schemaPathAjax':'".$schemaPath."', ";
                            $json .= "'viewLocationAjax':'".$params['viewLocation']."', ";
                            $json .= "'noModeUriAjax':'".$noModeUri."', ";
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
    $str_return .= '</form>';
    
    return $str_return;
}
