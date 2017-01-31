<?php

/**
* @brief   Action : Viser le courrier
*
* Ouverture, dans une fenêtre séparée en deux, d'un document entrant (+ ses informations) d'une part
* et de ses projets de réponses d'autre part. Possibilité de modifier les réponses, écrire des notes 
* et envoyer des mails rapidement
*
* @file visa_mail
* @author Nicolas Couture <couture@docimsol.com>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

/**
* $confirm  bool false
*/
$confirm = false;
/**
* $etapes  array Contains only one etap : form
*/
$etapes = array('form');
/**
* $frm_width  Width of the modal (empty)
*/
$frm_width='';
/**
* $frm_height  Height of the modal (empty)
*/
$frm_height = '';
/**
* $mode_form  Mode of the modal : fullscreen
*/
$mode_form = 'fullscreen';

$_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";

function writeLogIndex($EventInfo)
{
    $logFileOpened = fopen($_SESSION['config']['logdir']."visa_mail.log", 'a');
    fwrite($logFileOpened, '[' . date('d') . '/' . date('m') . '/' . date('Y')
        . ' ' . date('H') . ':' . date('i') . ':' . date('s') . '] ' . $EventInfo
        . "\r\n"
    );
    fclose($logFileOpened);
}

function get_form_txt($values, $path_manage_action,  $id_action, $table, $module, $coll_id, $mode )
{
    //DECLARATIONS
    include('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'definition_mail_categories.php');
    require_once "modules" . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_modules_tools.php";
    require_once("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_security.php");
    require_once("apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_business_app_tools.php");
    require_once("modules" . DIRECTORY_SEPARATOR . "basket" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_modules_tools.php");
    require_once("apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_types.php");
    require_once("core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_request.php");
    
    //INSTANTIATE
    $sec = new security();
    $core_tools = new core_tools();
    $b = new basket();
    $type = new types();
    $business = new business_app_tools();
    $visa = new visa();
    $db = new Database();

    //INITIALIZE
    $frm_str = '';
    $_SESSION['stockCheckbox']= '';
    unset($_SESSION['m_admin']['contact']);
    $_SESSION['req'] = "action";
    $res_id = $values[0];
    $_SESSION['doc_id'] = $res_id;
    $_SESSION['current_basket']['lastBasketFromAction'] = $_SESSION['current_basket']['id'];
    $view = $sec->retrieve_view_from_coll_id($coll_id);
    $stmt = $db->query("select alt_identifier,category_id, status from " . $view . " where res_id = ?", array($res_id));
    $resChrono = $stmt->fetchObject();
    $chrono_number = $resChrono->alt_identifier;
    $cat_id = $resChrono->category_id;
    //LAUNCH DOCLOCKER
    $docLockerCustomPath = 'apps/maarch_entreprise/actions/docLocker.php';
    $docLockerPath = $_SESSION['config']['businessappurl'] . '/actions/docLocker.php';
    
    if (is_file($docLockerCustomPath)){
        require_once $docLockerCustomPath;
    }else if (is_file($docLockerPath)){
        require_once $docLockerPath;
    }else{
        exit("can't find docLocker.php");
    }
    
    $docLocker = new docLocker($res_id);
    if (!$docLocker->canOpen()) {
        $docLockerscriptError = '<script>';
        $docLockerscriptError .= 'destroyModal("modal_' . $id_action . '");';
        $docLockerscriptError .= 'alert("' . _DOC_LOCKER_RES_ID . '' . $res_id . '' . _DOC_LOCKER_USER . ' ' . $_SESSION['userLock'] . '");';
        $docLockerscriptError .= '</script>';
        return $docLockerscriptError;
    }

    // DocLocker constantly 
    $frm_str .= '<script>';
    $frm_str .= 'docLockInterval = setInterval("new Ajax.Request(\'' . $_SESSION['config']['businessappurl'] . 'index.php?display=true&dir=actions&page=docLocker\',{ method:\'post\', parameters: {\'AJAX_CALL\': true, \'lock\': true, \'res_id\': ' . $res_id . '} });", 5000);';
    $frm_str .= '</script>';

    $docLocker->lock();

    //MODAL CONTENT
    $frm_str .= '<h2 class="tit" id="action_title">' . _VISA_MAIL . ' ' . _NUM . '<span id="numIdDocPage">';

    if(_ID_TO_DISPLAY == 'res_id'){
        $frm_str .= $res_id;
    } else if (_ID_TO_DISPLAY == 'chrono_number'){
        $frm_str .= $chrono_number;
    }

    $frm_str .='</span>';

    $frm_str .= '</h2>';
    $frm_str .='<i onmouseover="this.style.cursor=\'pointer\';" ' . 'onclick="new Ajax.Request(\'' . $_SESSION['config']['businessappurl'] . 'index.php?display=true&dir=actions&page=docLocker\',{ method:\'post\', parameters: {\'AJAX_CALL\': true, \'unlock\': true, \'res_id\': ' . $res_id . '}, onSuccess: function(answer){var cur_url=window.location.href; if (cur_url.indexOf(\'&directLinkToAction\') != -1) cur_url=cur_url.replace(\'&directLinkToAction\',\'\');window.location.href=cur_url;} });javascript:$(\'baskets\').style.visibility=\'visible\';destroyModal(\'modal_'.$id_action.'\');reinit();" class="fa fa-times-circle fa-2x closeModale" title="'._CLOSE.'"/>';
    $frm_str .= '</i>';
    $frm_str .= '<div>';
    $pathScriptTab = $_SESSION['config']['businessappurl'].'index.php?display=true&page=show_visaListDocBasket_tab&module=visa&resId='. $res_id.'&collId='.$coll_id.'&view='.$view;
    $frm_str .= '<i id="firstFrame" class="fa fa-arrow-circle-o-right fa-2x" style="margin-left: 13.8%;cursor: pointer" onclick="loadSpecificTab(\'show_visaListDocBasket_tab\',\''.$pathScriptTab.'\');manageFrame(this)"></i>';
    $frm_str .= '<i id="secondFrame" class="fa fa-arrow-circle-o-left fa-2x" style="margin-left: 40.9%;cursor: pointer" onclick="manageFrame(this)"></i>';
    $frm_str .= '<i id="thirdFrame" class="fa fa-arrow-circle-o-right fa-2x" style="margin-left: 0.6%;cursor: pointer" onclick="manageFrame(this)"></i>';
    $frm_str .= '</div>';
        
    //List of documents
    $frm_str .= '<div id="visa_listDoc" style="display:none;">';
        $frm_str .= '<iframe src="" name="show_visaListDocBasket_tab" id="show_visaListDocBasket_tab"  scrolling="auto" frameborder="0"  style="width:100%;height:100%;" ></iframe></dd>';

    $frm_str .= '</div>';

    $frm_str .= '<div id="visa_left" style="display:none;">';

    //TODO BEGIN OF CLEAN
    $frm_str .= '<dl id="tabricatorLeft" >';
	
    //Onglet document
    if ($cat_id != 'outgoing'){
        $pathScriptTab = $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id='. $res_id.'&collid='.$coll_id;
        $frm_str .= '<dt id="onglet_entrant" style="padding-top: 6px;" onclick="loadSpecificTab(\'viewframevalidDoc\',\''.$pathScriptTab.'\');return false;">'._INCOMING.' <sup><span id="" style="'.$style2.'" class="'.$class.'"></span></sup></dt><dd style="overflow-y: hidden;">';
        $frm_str .= '<iframe src="" name="viewframevalidDoc" id="viewframevalidDoc"  scrolling="auto" frameborder="0"  style="width:100%;height:100%;" ></iframe></dd>';

        $frm_str .= '</dd>';
        $frm_str .= '<script>$$(\'#onglet_entrant\')[0].click();</script>';
    }
	
	
    //Onglet Circuit 
    $pathScriptTab = $_SESSION['config']['businessappurl'] . 'index.php?display=true&page=show_visa_tab&module=visa&resId='.$res_id.'&collId='.$coll_id.'&destination='.$data['destination']['value'];
    $frm_str .= '<dt id="visa_tab" style="padding-top: 6px;" onclick="loadSpecificTab(\'workflow_visa\',\''.$pathScriptTab.'\');return false;">'._VISA_WORKFLOW.'<span id="visa_tab_img"></span><span id="visa_tab_badge"></span></dt><dd id="page_circuit" style="overflow-x: hidden;">';
    $frm_str .= '<h2>'._VISA_WORKFLOW.'</h2>';
    $frm_str .= '<iframe src="" name="workflow_visa" width="100%" height="620px" align="left" scrolling="yes" frameborder="0" id="workflow_visa"></iframe>';
    $frm_str .= '</dd>';
    //LOAD TOOLBAR BADGE
    $toolbarBagde_script = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=visa&page=load_toolbar_visa&resId='.$res_id.'&collId='.$coll_id;
    $frm_str .='<script>loadToolbarBadge(\'visa_tab\',\''.$toolbarBagde_script.'\');</script>';


    //Onglet Avancement 
    $pathScriptTab = $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=document_workflow_history&id='. $res_id .'&coll_id='. $coll_id.'&load&size=full';
    $frm_str .= '<dt id="onglet_avancement" style="padding-top: 6px;" onclick="loadSpecificTab(\'workflow_history_document\',\''.$pathScriptTab.'\');return false;">Avancement <sup><span id="" style="'.$style2.'" class="'.$class.'"></span></sup></dt><dd id="page_avancement" style="overflow-x: hidden;">';
    $frm_str .= '<h2>'. _WF .'</h2>';
    $frm_str .= '<iframe src="" name="workflow_history_document" width="100%" height="620px" align="left" scrolling="yes" frameborder="0" id="workflow_history_document"></iframe>';
    $frm_str .= '<br/>';
    $frm_str .= '<br/>';

    $pathScriptTab = $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=document_history&id='. $res_id .'&coll_id='. $coll_id.'&load&size=full';
    $frm_str .= '<span style="cursor: pointer;" onmouseover="this.style.cursor=\'pointer\';" onclick="new Effect.toggle(\'history_document\', \'blind\', {delay:0.2});whatIsTheDivStatus(\'history_document\', \'divStatus_all_history_div\');loadSpecificTab(\'history_document\',\''.$pathScriptTab.'\');return false;">';
    $frm_str .= '<span id="divStatus_all_history_div" style="color:#1C99C5;"><i class="fa fa-plus-square-o"></i></span>';
    $frm_str .= '<b>&nbsp;'. _ALL_HISTORY .'</b>';
    $frm_str .= '</span>';
    $frm_str .= '<iframe src="" name="history_document" width="100%" height="620px" align="left" scrolling="yes" frameborder="0" id="history_document" style="display:none;"></iframe>';

    $frm_str .= '</dd>';
	
    //Onglet notes
    if ($core->is_module_loaded('notes')) {

        $pathScriptTab = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=notes&page=notes&identifier=' . $res_id . '&origin=document&coll_id=' . $coll_id . '&load&size=full';
        $frm_str .= '<dt id="notes_tab" style="padding-top: 6px;" onclick="loadSpecificTab(\'list_notes_doc\',\'' . $pathScriptTab . '\');return false;">' . _NOTES . '<span id="notes_tab_img"></span><span id="notes_tab_badge"></span></dt><dd id="page_notes" style="overflow-x: hidden;"><h2>' . _NOTES . '</h2><iframe name="list_notes_doc" id="list_notes_doc" src="" frameborder="0" scrolling="yes" width="99%" height="570px"></iframe></dd> ';
        
        //LOAD TOOLBAR BADGE
        $toolbarBagde_script = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=notes&page=load_toolbar_notes&resId='.$res_id.'&collId='.$coll_id;
        $frm_str .='<script>loadToolbarBadge(\'notes_tab\',\''.$toolbarBagde_script.'\');</script>';
    }
    $frm_str .= '</dl>';
    $frm_str .= '</div>';
	
    $frm_str .= '<div id="visa_right" style="display:none;">';
    $frm_str .= '<div style="height:100%;">';
    $frm_str .= '<dl id="tabricatorRight" >';
    $tab_path_rep_file = $visa->get_rep_path($res_id, $coll_id);
    $cptAttach = count($tab_path_rep_file);
    if ($cptAttach < 6) {
            $viewMode = 'extended';
    } elseif ($cptAttach < 10) {
            $viewMode = 'small';
    } else {
            $viewMode = 'verysmall';
    }
    for ($i = 0; $i < $cptAttach; $i++) {
        $num_rep = $i + 1;
        if ($viewMode == 'verysmall') {
            $titleRep = $i + 1;
        } elseif ($viewMode == 'small') {
            $titleRep = substr($_SESSION['attachment_types'][$tab_path_rep_file[$i]['attachment_type']], 0, 10);
        } else {
            if (strlen($tab_path_rep_file[$i]['title']) > 15){
                $titleRep = substr($_SESSION['attachment_types'][$tab_path_rep_file[$i]['attachment_type']], 0, 15) . '...';
            }else{
                $titleRep = $_SESSION['attachment_types'][$tab_path_rep_file[$i]['attachment_type']];
            }
        }
        if ($tab_path_rep_file[$i]['attachment_type'] == 'signed_response') {
            $titleRep = '<sup><i style="color:#fdd16c" class="fa fa-certificate fa-lg fa-fw"></i></sup>' . $titleRep;
        }
        $pathScriptTab = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master=' . $res_id . '&id=' . $tab_path_rep_file[$i]['res_id'];
        $frm_str .= '<dt style="padding-top: 6px;" title="'
                . $tab_path_rep_file[$i]['title'] . '" id="ans_' . $num_rep . '_' . $tab_path_rep_file[$i]['res_id'] . '" onclick="loadSpecificTab(\'viewframevalidRep' . $num_rep . '_' . $tab_path_rep_file[$i]['res_id'] . '\',\'' . $pathScriptTab . '\');updateFunctionModifRep(\''
                . $tab_path_rep_file[$i]['res_id'] . '\', ' . $num_rep . ', ' . $tab_path_rep_file[$i]['is_version'] . ');">' . $titleRep
                . ' <sup><span class="" style="" id=""></span></sup></dt><dd id="content_' . $num_rep . '_' . $tab_path_rep_file[$i]['res_id'] . '">';
        $frm_str .= '<iframe src="" name="viewframevalidRep' . $num_rep . '" id="viewframevalidRep' . $num_rep . '_' . $tab_path_rep_file[$i]['res_id'] . '"  scrolling="auto" frameborder="0" style="width:100%;height:100%;" ></iframe>';
        $frm_str .= '</dd>';
        //$frm_str .= '<script>console.log('.$cptAttach.');</script>';
        if($i==0){
            $frm_str .= '<script>$$(\'#ans_' . $num_rep . '_' . $tab_path_rep_file[$i]['res_id'] . '\')[0].click();</script>';
        }

    }
            
    $pathScriptTab = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=attachments&page=frame_list_attachments&template_selected=documents_list_attachments_simple&load&attach_type_exclude=converted_pdf,print_folder';

    $frm_str .= '<dt title="' . _ATTACHED_DOC .'" id="attachments_tab" style="padding-top: 6px;" onclick="loadSpecificTab(\'list_attach\',\''.$pathScriptTab.'\');$(\'cur_idAffich\').value=0;updateFunctionModifRep(0,0,0);">PJ<span id="attachments_tab_img"></span><span id="attachments_tab_badge"></span></dt><dd id="page_pj">';

    //LOAD TOOLBAR BADGE
    $toolbarBagde_script = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=attachments&page=load_toolbar_attachments&resId='.$res_id.'&collId='.$coll_id;
    $frm_str .='<script>loadToolbarBadge(\'attachments_tab\',\''.$toolbarBagde_script.'\');</script>';
        
    if ($core_tools->is_module_loaded('attachments')) {
        require 'modules/templates/class/templates_controler.php';
        $templatesControler = new templates_controler();
        $templates = array();
        $templates = $templatesControler->getAllTemplatesForProcess($curdest);
        $_SESSION['destination_entity'] = $curdest;
        //var_dump($templates);
        $frm_str .= '<div id="list_answers_div" onmouseover="this.style.cursor=\'pointer\';" style="width:100%;height:100%;">';
            $frm_str .= '<div class="block" style="margin-top:-2px;height:95%;">';
                $frm_str .= '<div id="processframe" name="processframe" style="height:100%;">';
                    $frm_str .= '<center><h2>' . _PJ . ', ' . _ATTACHEMENTS . '</h2></center>';

                    $frm_str .= '<div class="ref-unit">';

                    $frm_str .= '<center>';
                    if ($core_tools->is_module_loaded('templates')) {
                        $frm_str .= '<input type="button" name="attach" id="attach" class="button" value="'
                            . _CREATE_PJ
                            .'" onclick="showAttachmentsForm(\'' . $_SESSION['config']['businessappurl']
                            . 'index.php?display=true&module=attachments&page=attachments_content\')" />';
                    }
                    $frm_str .= '</center><iframe name="list_attach" id="list_attach" src="" '
                    . 'frameborder="0" width="100%" scrolling="yes" height="600px" scrolling="yes" ></iframe>';
                    $frm_str .= '</div>';
                $frm_str .= '</div>';
            $frm_str .= '</div>';
            //$frm_str .= '<hr />';
        $frm_str .= '</div>';
    }
	
	
    $frm_str .= '</dd>';


    $frm_str .= '</dl>';
    $frm_str .= '<div class="toolbar">';
    $frm_str .= '<table style="width:100%;">';	

    $frm_str .= '<tr>';
    $frm_str .= '<td>';	
    $frm_str .= '<form name="index_file" method="post" id="index_file" action="#" class="forms " style="text-align:left;">';
    $frm_str .= 'Consigne &nbsp;<input type="text" value="'.$visa->getConsigne($res_id, $coll_id, $_SESSION['user']['UserId']).'" style="width:50%;" readonly class="readonly"/><br/>';
    
    //GET ACTION LIST BY AJAX REQUEST
    $frm_str .= '<span id="actionSpan"></span>';
    $frm_str .= '<script>';
        $frm_str .= 'change_category_actions(\'' 
            . $_SESSION['config']['businessappurl'] 
            . 'index.php?display=true&dir=indexing_searching&page=change_category_actions'
            . '&resId=' . $res_id . '&collId=' . $coll_id . '\',\'' . $res_id . '\',\'' . $coll_id . '\',\''.$cat_id.'\');';
    $frm_str .= '</script>';
    $frm_str .= '<input type="button" name="send" id="send_action" value="'._VALIDATE.'" class="button" onclick="new Ajax.Request(\'' 
            . $_SESSION['config']['businessappurl'] . 'index.php?display=true&dir=actions&page=docLocker\',{ method:\'post\', parameters: {\'AJAX_CALL\': true, \'unlock\': true, \'res_id\': ' . $res_id . '} });valid_action_form( \'index_file\', \''.$path_manage_action.'\', \''. $id_action.'\', \''.$res_id.'\', \''.$table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');"/> ';
    //
    
    /*$frm_str .= '<b>'._ACTIONS.' : </b>';
    $actions  = $b->get_actions_from_current_basket($res_id, $coll_id, 'PAGE_USE');
    if(count($actions) > 0)
    {
        $frm_str .='<select name="chosen_action" id="chosen_action">';
        $frm_str .='<option value="">'._CHOOSE_ACTION.'</option>';
        for($ind_act = 0; $ind_act < count($actions);$ind_act++)
        {
            if (!($actions[$ind_act]['VALUE'] == "end_action" && $visa->getCurrentStep($res_id, $coll_id, 'VISA_CIRCUIT') == $visa->nbVisa($res_id, $coll_id))){
                $frm_str .='<option value="'.$actions[$ind_act]['VALUE'].'"';
                if($ind_act==0)
                {
                    $frm_str .= 'selected="selected"';
                }
                $frm_str .= '>'.$actions[$ind_act]['LABEL'].'</option>';
            }
        }
        $frm_str .='</select> ';
        $table = $sec->retrieve_table_from_coll($coll_id);
        $frm_str .= '<input type="button" name="send" id="send_action" value="'._VALIDATE.'" class="button" onclick="new Ajax.Request(\'' 
            . $_SESSION['config']['businessappurl'] . 'index.php?display=true&dir=actions&page=docLocker\',{ method:\'post\', parameters: {\'AJAX_CALL\': true, \'unlock\': true, \'res_id\': ' . $res_id . '} });valid_action_form( \'index_file\', \''.$path_manage_action.'\', \''. $id_action.'\', \''.$res_id.'\', \''.$table.'\', \''.$module.'\', \''.$coll_id.'\', \''.$mode.'\');"/> ';
    }*/


    $frm_str .= '<input type="hidden" name="cur_rep" id="cur_rep" value="'.$tab_path_rep_file[0]['res_id'].'" >';
    $frm_str .= '<input type="hidden" name="cur_idAffich" id="cur_idAffich" value="1" >';
    $frm_str .= '<input type="hidden" name="cur_resId" id="cur_resId" value="'.$res_id.'" >';
    $frm_str .= '<input type="hidden" name="list_docs" id="list_docs" value="'.$list_docs.'" >';

    $frm_str .= '<input type="hidden" name="values" id="values" value="'.$res_id.'" />';
    $frm_str .= '<input type="hidden" name="action_id" id="action_id" value="'.$id_action.'" />';
    $frm_str .= '<input type="hidden" name="mode" id="mode" value="'.$mode.'" />';
    $frm_str .= '<input type="hidden" name="table" id="table" value="'.$table.'" />';
    $frm_str .= '<input type="hidden" name="coll_id" id="coll_id" value="'.$coll_id.'" />';
    $frm_str .= '<input type="hidden" name="module" id="module" value="'.$module.'" />';
    $frm_str .= '<input type="hidden" name="category_id" id="category_id" value="'.$cat_id.'" />';
    $frm_str .= '<input type="hidden" name="req" id="req" value="second_request" />';


    //$frm_str .= '<input type="hidden" name="next_resId" id="next_resId" value="'.$nextId.'" >';
    $frm_str .= '</form>';
    $frm_str .= '</td>';
    $frm_str .= '<td style="width:25%;">';	
    //if ($core->test_service('sign_document', 'visa', false) && $currentStatus == 'ESIG') {
    if ($core->test_service('sign_document', 'visa', false) ) {
            $color = ' style="float:left;color:#666;" ';
            $img = '<img id="sign_link_img" src="'.$_SESSION['config']['businessappurl'].'static.php?filename=sign.png" title="Signer ces projets de réponse (sans certificat)" />';
            if ($tab_path_rep_file[0]['attachment_type'] == 'signed_response'){
                    $color = ' style="float:left;color:green;cursor:not-allowed;" ';
                    $img = '<img id="sign_link_img" src="'.$_SESSION['config']['businessappurl'].'static.php?filename=sign_valid.png" title="Enlever la signature" />';
            } 

            if ($_SESSION['modules_loaded']['visa']['showAppletSign'] == "true"){
                    $frm_str .= '<a href="javascript://" id="sign_link_certif" '.$color.' onclick="';
                    if ($tab_path_rep_file[0]['attachment_type'] != 'signed_response') $frm_str .= 'signFile('.$tab_path_rep_file[0]['res_id'].','.$tab_path_rep_file[0]['is_version'].',0);';
                    $frm_str .= '"><i class="fm fm-file-fingerprint fm-3x" title="Signer ces projets de réponse (avec certificat)"></i></a>';
            }
            if ($tab_path_rep_file[0]['attachment_type'] != 'signed_response'){
                    $frm_str .= ' <a href="javascript://" id="sign_link" '.$color.' onclick="';
                    $frm_str .= 'signFile('.$tab_path_rep_file[0]['res_id'].','.$tab_path_rep_file[0]['is_version'].',2);';
            }else{
                    $frm_str .= ' <a target="list_attach" href="';
                    $frm_str .= 'index.php?display=true&module=attachments&page=del_attachment&relation=1&id='.$tab_path_rep_file[0]['res_id'];
            } 
            $frm_str .= '" id="sign_link" '.$color.'>'.$img.'</a>';
    }

    $displayModif = ' style="float:right;" ';
    if ($tab_path_rep_file[0]['attachment_type'] == 'signed_response')
            $displayModif = ' style="float:right;display:none;" ';

    $frm_str .= ' <a href="javascript://" id="update_rep_link" '.$displayModif.'onclick="';

    /*if ($tab_path_rep_file[0]['attachment_type'] == 'outgoing_mail'){
            $frm_str .= 'window.open(\''
    . $_SESSION['config']['businessappurl'] . 'index.php?display=true'
    . '&module=content_management&page=applet_popup_launcher&objectType=resourceEdit'
    . '&objectId='
    . $tab_path_rep_file[0]['res_id']
    . '&objectTable='
    . $table
    . '\', \'\', \'height=200, width=250,scrollbars=no,resizable=no,directories=no,toolbar=no\');';
    }
    else*/ 
    if ($tab_path_rep_file[0]['is_version'] == 0 || $tab_path_rep_file[0]['is_version'] == 2) {
        $frm_str .= 'modifyAttachmentsForm(\''.$_SESSION['config']['businessappurl'] 
            . 'index.php?display=true&module=attachments&page=attachments_content&id='
            . $tab_path_rep_file[0]['res_id'] . '&relation=1&fromDetail=\',\'98%\',\'auto\');';
    } else {
       $frm_str .= 'modifyAttachmentsForm(\''.$_SESSION['config']['businessappurl'] 
            . 'index.php?display=true&module=attachments&page=attachments_content&id='
            . $tab_path_rep_file[0]['res_id'] . '&relation=2&fromDetail=\',\'98%\',\'auto\');';
    }
    $frm_str .= '"><i class="fa fa-pencil-square-o fa-3x" title="Modifier la réponse"></i></a>';

    $frm_str .= '</td>';
    $frm_str .= '</tr>';	
    $frm_str .= '</table>';	

    $frm_str .= '</div>';
    $frm_str .= '</div>';

    $frm_str .= '<div id="modalPIN">';
    $frm_str .= '<p id="badPin" style="display:none;color:red;font-weight:bold;text-align:center;margin-top: -15px">'. _BAD_PIN .'</p>';
    $frm_str .= '<label for="valuePIN">Saisissez votre code PIN</label>';
    $frm_str .= '<input type="password" name="valuePIN" id="valuePIN" onKeyPress="if (event.keyCode == 13) signFile('.$tab_path_rep_file[0]['res_id'].','.$tab_path_rep_file[0]['is_version'].',\'\', $(\'valuePIN\').value);"/><br/>';
    $frm_str .= '<input type="button" name="sendPIN" id="sendPIN" value="'._VALIDATE.'" class="button" onclick="signFile('.$tab_path_rep_file[0]['res_id'].','.$tab_path_rep_file[0]['is_version'].',\'\', $(\'valuePIN\').value);" />';
    $frm_str .= '&nbsp;<input type="button" name="cancelPIN" id="cancelPIN" value="'._CANCEL.'" class="button" onclick="$(\'badPin\').style.display = \'none\';$(\'modalPIN\').style.display = \'none\';" />';
    $frm_str .= '</div>';

    /*** Extra javascript ***/
    $frm_str .= '<script type="text/javascript">launchTabri();window.scrollTo(0,0);$(\'divList\').style.display = \'none\';';
    $frm_str .='var height = (parseInt($(\'visa_left\').parentElement.style.height.replace(/px/,""))-65)+"px";$(\'visa_listDoc\').style.height=height;$(\'visa_left\').style.height=height;$(\'visa_right\').style.height=height;$(\'tabricatorRight\').style.height=(parseInt($(\'tabricatorRight\').offsetHeight)-20)+"px";height = (parseInt($(\'tabricatorRight\').offsetHeight)-150)+"px";$(\'list_attach\').style.height=height;';
    $frm_str .= '$(\'visa_left\').style.display=\'block\';$(\'visa_right\').style.display=\'block\';manageFrame(\'firstFrame\');';
    $frm_str .='</script>';
    return addslashes($frm_str);
}

/**
 * Checks the action form
 *
 * @param $form_id String Identifier of the form to check
 * @param $values Array Values of the form
 * @return Bool true if no error, false otherwise
 **/
function check_form($form_id,$values)
{
    //writeLogIndex("GO check_form !!");
    $_SESSION['action_error'] = '';
    if(count($values) < 1 || empty($form_id))
    {
        $_SESSION['action_error'] =  _FORM_ERROR;
        return false;
    }
    else
    {
        return true;
    }
}

/**
 * Get the value of a given field in the values returned by the form
 *
 * @param $values Array Values of the form to check
 * @param $field String the field
 * @return String the value, false if the field is not found
 **/
function get_value_fields($values, $field)
{
    for($i=0; $i<count($values);$i++)
    {
        if($values[$i]['ID'] == $field)
        {
            return  $values[$i]['VALUE'];
        }
    }
    return false;
}


/**
 * Action of the form : update the database
 *
 * @param $arr_id Array Contains the res_id of the document to validate
 * @param $history String Log the action in history table or not
 * @param $id_action String Action identifier
 * @param $label_action String Action label
 * @param $status String  Not used here
 * @param $coll_id String Collection identifier
 * @param $table String Table
 * @param $values_form String Values of the form to load
 **/
function manage_form($arr_id, $history, $id_action, $label_action, $status,  $coll_id, $table, $values_form )
{
	$res_id = $arr_id[0];
	
	$act_chosen = get_value_fields($values_form, 'chosen_action');
	
	/*if ($act_chosen == "end_action"){
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
		require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
		$sec = new security();
		
		
		$replaceValues = array();
		array_push(
			$replaceValues,
			array(
				'column' => 'process_date',
				'value' => 'CURRENT_TIMESTAMP',
				'type' => 'date',
			)
		);
		$where = 'res_id = ? and item_id = ? and difflist_type = ?';
		$array_what[] = $res_id;
		$array_what[] = $_SESSION['user']['UserId'];
		$array_what[] = 'VISA_CIRCUIT';
		
		$request = new request();
		$table = 'listinstance';
		$request->PDOupdate($table, $replaceValues, $where, $array_what, $_SESSION['config']['databasetype']);
		
		$circuit_visa = new visa();
		if ($circuit_visa->allUserVised($res_id, $coll_id, 'VISA_CIRCUIT')){
			$up_request = "UPDATE res_letterbox SET status='ESIG' WHERE res_id = $res_id";
			$db = new Database();
			$db->query("UPDATE res_letterbox SET status='ESIG' WHERE res_id = ?", array($res_id));
		}
	}*/
    return array('result' => $res_id.'#', 'history_msg' => '');
}
