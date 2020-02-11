<?php
/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   sendmail.php
* @author  dev <dev@maarch.org>
* @ingroup sendmail
*/

require_once 'core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_request.php';
require_once 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR
            .'class'.DIRECTORY_SEPARATOR.'class_lists.php';
require_once 'modules'.DIRECTORY_SEPARATOR.'sendmail'.DIRECTORY_SEPARATOR
    .'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php';

$core_tools     = new core_tools();
$request        = new request();
$list           = new lists();
$sendmail_tools = new sendmail();

$identifier = '';
$origin     = '';
$parameters = '';

//Collection ID
if (isset($_REQUEST['coll_id']) && !empty($_REQUEST['coll_id'])) {
    $parameters = '&coll_id='.$_REQUEST['coll_id'];
} elseif ((isset($_SESSION['collection_id_choice']) && !empty($_SESSION['collection_id_choice']))) {
    $parameters = '&coll_id='.$_SESSION['collection_id_choice'];
}

//Identifier
if (isset($_REQUEST['identifier']) && !empty($_REQUEST['identifier'])) {
    $identifier = $_REQUEST['identifier'];
} elseif (isset($_SESSION['doc_id']) && !empty($_SESSION['doc_id'])) {
    $identifier = $_SESSION['doc_id'];
} else {
    echo '<span class="error">'._IDENTIFIER.' '._IS_EMPTY.'</span>';
    exit();
}

$security = new security();
$right = $security->test_right_doc('letterbox_coll', $identifier);
if (!$right) {
    exit(_NO_RIGHT_TXT);
}

//Origin
if (isset($_REQUEST['origin']) && !empty($_REQUEST['origin'])) {
    $origin = $_REQUEST['origin'];
} else {
    $origin = 'document';
}

//Extra parameters
if (isset($_REQUEST['size']) && !empty($_REQUEST['size'])) {
    $parameters .= '&size='.$_REQUEST['size'];
} else {
    $parameters .= '&size=full';
}
if (isset($_REQUEST['order']) && !empty($_REQUEST['order'])) {
    $parameters .= '&order='.$_REQUEST['order'];
}
if (isset($_REQUEST['order_field']) && !empty($_REQUEST['order_field'])) {
    $parameters .= '&order_field='.$_REQUEST['order_field'];
}
if (isset($_REQUEST['what']) && !empty($_REQUEST['what'])) {
    $parameters .= '&what='.$_REQUEST['what'];
}
if (isset($_REQUEST['start']) && !empty($_REQUEST['start'])) {
    $parameters .= '&start='.$_REQUEST['start'];
} else {
    $_REQUEST['start'] = 0;
}

if (isset($_REQUEST['load'])) {
    $core_tools->load_lang();
    $core_tools->load_html();
    $core_tools->load_header('', true, false);

    echo '<body>';

    $core_tools->load_js();

    //Load list
    if (!empty($identifier)) {
        $target = $_SESSION['config']['businessappurl']
        .'index.php?module=sendmail&page=sendmail&identifier='
        .$identifier.'&origin='.$origin.$parameters;

        $listContent = $list->loadList($target);
        echo $listContent;
    } else {
        echo '<span class="error">'._ERROR_IN_PARAMETERS.'</span>';
    }
    echo '<div id="container" style="width:100%;min-height:0px;height:0px;"></div>';

    echo '</body>';
    echo '</html>';
} else {
    //If size is full change some parameters
    if (isset($_REQUEST['size'])
        && ($_REQUEST['size'] == 'full')
    ) {
        $sizeUser = '10';
        $sizeObject = '30';
        $css = 'listing spec';
        $cutString = 150;
    } elseif (isset($_REQUEST['size'])
        && ($_REQUEST['size'] == 'medium')
    ) {
        $sizeUser = '15';
        $sizeObject = '30';
        $css = 'listingsmall';
        $cutString = 100;
    } else {
        $sizeUser = '10';
        $sizeObject = '10';
        $css = 'listingsmall';
        $cutString = 20;
    }

    //Table or view
        $select['emails'] = array(); //Emails
        $select[USERS_TABLE] = array(); //Users

    //Fields
    array_push(
        $select['emails'],
        'id as email_id',
        'document->>\'id\' as res_id',
        'creation_date',
        'user_id',
        'object as email_object_short',
        'sender as sender_email',
        'recipients as email_destinataire',
        'id',
        'status',
        'status as status_label'
    );

    array_push($select[USERS_TABLE], 'user_id', 'firstname', 'lastname', 'mail');  //Users

    //Where clause
    $where_tab = array();

    $where_tab[] = ' document->>\'id\' = \''.$identifier.'\' ';
    $where_tab[] = 'emails.user_id = users.id';

    //Build where
    $where = implode(' and ', $where_tab);

    //Order
    $order = $order_field = '';
    $order = $list->getOrder();
    $order_field = $list->getOrderField();
    if (!empty($order_field) && !empty($order)) {
        $orderstr = 'order by '.$order_field.' '.$order;
    } else {
        $list->setOrder();
        $list->setOrderField('creation_date');
        $orderstr = 'order by creation_date desc';
    }

    if (isset($_REQUEST['lines'])) {
        $limit = $_REQUEST['lines'];
    } else {
        $limit = 'default';
    }

    //Request
    $tab = $request->PDOselect(
        $select,
        $where,
        array(),
        $orderstr,
        $_SESSION['config']['databasetype'],
        $limit,
        false,
        '',
        '',
        '',
        true,
        false,
        false,
        $_REQUEST['start']
    );

    //Result Array
    if (!empty($tab)) {
        for ($i = 0; $i < count($tab); ++$i) {
            for ($j = 0; $j < count($tab[$i]); ++$j) {
                foreach (array_keys($tab[$i][$j]) as $value) {
                    if ($tab[$i][$j][$value] == 'email_id') {
                        $tab[$i][$j]['email_id'] = $tab[$i][$j]['value'];
                        $tab[$i][$j]['label'] = 'ID';
                        $tab[$i][$j]['size'] = '1';
                        $tab[$i][$j]['label_align'] = 'left';
                        $tab[$i][$j]['align'] = 'left';
                        $tab[$i][$j]['valign'] = 'bottom';
                        $tab[$i][$j]['show'] = false;
                        $tab[$i][$j]['order'] = 'id';
                    }
                    if ($tab[$i][$j][$value] == 'creation_date') {
                        $tab[$i][$j]['value'] = $request->dateformat($tab[$i][$j]['value']);
                        $tab[$i][$j]['label'] = _CREATION_DATE;
                        $tab[$i][$j]['size'] = '11';
                        $tab[$i][$j]['label_align'] = 'left';
                        $tab[$i][$j]['align'] = 'left';
                        $tab[$i][$j]['valign'] = 'bottom';
                        $tab[$i][$j]['show'] = true;
                        $tab[$i][$j]['order'] = 'creation_date';
                    }
                    if ($tab[$i][$j][$value] == 'user_id') {
                        $tab[$i][$j]['label'] = _USER_ID;
                        $tab[$i][$j]['size'] = '5';
                        $tab[$i][$j]['label_align'] = 'left';
                        $tab[$i][$j]['align'] = 'left';
                        $tab[$i][$j]['valign'] = 'bottom';
                        $tab[$i][$j]['show'] = false;
                        $tab[$i][$j]['order'] = 'user_id';
                    }
                    if ($tab[$i][$j][$value] == 'email_destinataire') {
                        $tab_dest = (array)json_decode(htmlspecialchars_decode($tab[$i][$j]['value'], ENT_QUOTES | ENT_HTML401));
                        $tab[$i][$j]['value'] = implode(', ', $tab_dest);
                        $tab[$i][$j]['label'] = _RECIPIENT;
                        $tab[$i][$j]['size'] = $sizeObject;
                        $tab[$i][$j]['label_align'] = 'left';
                        $tab[$i][$j]['align'] = 'left';
                        $tab[$i][$j]['valign'] = 'bottom';
                        $tab[$i][$j]['show'] = true;
                        $tab[$i][$j]['order'] = 'email_destinataire';
                    }
                    if ($tab[$i][$j][$value] == 'email_object_short') {
                        $tab[$i][$j]['value'] = $request->cut_string($request->show_string($tab[$i][$j]['value']), $cutString);
                        $tab[$i][$j]['label'] = _EMAIL_OBJECT;
                        $tab[$i][$j]['size'] = $sizeObject;
                        $tab[$i][$j]['label_align'] = 'left';
                        $tab[$i][$j]['align'] = 'left';
                        $tab[$i][$j]['valign'] = 'bottom';
                        $tab[$i][$j]['show'] = true;
                        $tab[$i][$j]['order'] = 'email_object_short';
                    }
                    if ($tab[$i][$j][$value] == 'status_label') {
                        $tab[$i][$j]['value'] = $sendmail_tools->emailStatus(['status' => $tab[$i][$j]['value']]);
                        $tab[$i][$j]['label'] = _STATUS;
                        $tab[$i][$j]['size'] = '10';
                        $tab[$i][$j]['label_align'] = 'left';
                        $tab[$i][$j]['align'] = 'left';
                        $tab[$i][$j]['valign'] = 'bottom';
                        $tab[$i][$j]['show'] = true;
                        $tab[$i][$j]['order'] = 'status_label';
                    }
                    if ($tab[$i][$j][$value] == 'sender_email') {
                        $senderInfo = (array)json_decode(htmlspecialchars_decode($tab[$i][$j]['value'], ENT_QUOTES | ENT_HTML401));
                        $tab[$i][$j]['value'] = $senderInfo['email'];

                        $tab[$i][$j]['label'] = _SENDER;
                        $tab[$i][$j]['size'] = '20';
                        $tab[$i][$j]['label_align'] = 'left';
                        $tab[$i][$j]['align'] = 'left';
                        $tab[$i][$j]['valign'] = 'bottom';
                        $tab[$i][$j]['show'] = true;
                        $tab[$i][$j]['order'] = 'sender_email';
                    }
                    if ($tab[$i][$j][$value] == 'id') {
                        $tab[$i][$j]['value'] = (\Email\models\EmailModel::hasJoinFiles(['id' => $tab[$i][$j]['value']])) ?
                                '<i class="fa fa-paperclip fa-2x" title="'._JOINED_FILES.'"></i>' :
                                    '';
                        $tab[$i][$j]['label'] = false;
                        $tab[$i][$j]['size'] = '1';
                        $tab[$i][$j]['label_align'] = 'left';
                        $tab[$i][$j]['align'] = 'left';
                        $tab[$i][$j]['valign'] = 'bottom';
                        $tab[$i][$j]['show'] = true;
                        $tab[$i][$j]['order'] = false;
                    }
                }
            }
        }
    }

    //List
    $listKey = 'email_id';                                                              // Cle de la liste
    $paramsTab = array();                                                               // Initialiser le tableau de parametres
    $paramsTab['bool_sortColumn'] = true;                                               // Affichage Tri
    $paramsTab['pageTitle'] = '';                                                       // Titre de la page
    $paramsTab['bool_bigPageTitle'] = false;                                            // Affichage du titre en grand
    $paramsTab['urlParameters'] = 'identifier='.$identifier
            .'&origin='.$origin.'&display=true'.$parameters;                            // Parametres d'url supplementaires
    $paramsTab['filters'] = array();                                                    // Filtres
    $paramsTab['listHeight'] = '100%';                                                  // Hauteur de la liste
    $paramsTab['start'] = $_REQUEST['start'];
    $paramsTab['listCss'] = $css;                                                       // CSS
    $paramsTab['tools'] = array();                                                      // Icones dans la barre d'outils

    $addMail = array(
        'script' => "showEmailForm('".$_SESSION['config']['businessappurl']
                                .'index.php?display=true&module=sendmail&page=sendmail_ajax_content'
                                .'&mode=add&identifier='.$identifier.'&origin='.$origin.'&formContent=email'
                                .$parameters."')",
        'icon' => 'envelope',
        'tooltip' => _NEW_EMAIL,
        'alwaysVisible' => true,
    );

    $addExchangeMessage = array(
        'script' => "showEmailForm('".$_SESSION['config']['businessappurl']
                                .'index.php?display=true&module=sendmail&page=sendmail_ajax_content'
                                .'&mode=add&identifier='.$identifier.'&origin='.$origin.'&formContent=messageExchange'
                                .$parameters."')",
        'icon' => 'exchange-alt',
        'tooltip' => _NEW_NUMERIC_PACKAGE,
        'alwaysVisible' => true,
    );

    array_push($paramsTab['tools'], $addMail, $addExchangeMessage);

    //Action icons array
    $paramsTab['actionIcons'] = array();
    $read = array(
        'script' => "showEmailForm('".$_SESSION['config']['businessappurl']
                                    .'index.php?display=true&module=sendmail&page=sendmail_ajax_content'
                                    .'&mode=read&id=@@email_id@@&identifier='.$identifier.'&origin='.$origin
                                    .$parameters."');",
        'icon' => 'eye',
        'tooltip' => _READ,
    );
    array_push($paramsTab['actionIcons'], $read);
    $update = array(
        'script' => "showEmailForm('".$_SESSION['config']['businessappurl']
                                    .'index.php?display=true&module=sendmail&page=sendmail_ajax_content'
                                    .'&mode=up&id=@@email_id@@&identifier='.$identifier.'&origin='.$origin
                                    .$parameters."');",
        'class' => 'change',
        'tooltip' => _UPDATE,
        'disabledRules' => "@@user_id@@ != '".$_SESSION['user']['UserId']."'",
    );
    array_push($paramsTab['actionIcons'], $update);
    $transfer = array(
        'script' => "showEmailForm('".$_SESSION['config']['businessappurl']
                                .'index.php?display=true&module=sendmail&page=sendmail_ajax_content'
                                .'&mode=transfer&id=@@email_id@@&identifier='.$identifier.'&origin='.$origin
                                .$parameters."');",
        'icon' => 'share',
        'tooltip' => _TRANSFER_EMAIL,
        'disabledRules' => "@@user_id@@ != '".$_SESSION['user']['UserId']."' || @@status@@ != 'SENT'",
    );
    array_push($paramsTab['actionIcons'], $transfer);

    //Output
    $status = 0;
    $content = $list->showList($tab, $paramsTab, $listKey);

    $toolbarBagde_script = $_SESSION['config']['businessappurl'].'index.php?display=true&module=sendmail&page=load_toolbar_sendmail&origin=parent&resId='.$identifier.'&collId=letterbox_coll';

    $content .= '<script>loadToolbarBadge(\'sendmail_tab\',\''.$toolbarBagde_script.'\');</script>';

    // /********* MESSAGE EXCHANGE PART ***************/
    // include_once 'modules/sendmail/messageExchangeList.php';
    // include_once 'modules/sendmail/acknowledgementReceiptsList.php';

    echo '{status : '.$status.", content : '".addslashes($debug.$content.$contentMessageExchange.$contentAcknowledgementReceipts)."', error : '".addslashes($error)."'}";
}
