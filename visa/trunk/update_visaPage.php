<?php

$db = new Database();
$core_tools =new core_tools();
        
$res_id = $_REQUEST['res_id'];
$_SESSION['doc_id'] = $res_id;
$coll_id = $_REQUEST['coll_id'];
// Ouverture de la modal
	$frm_str = '';
	$docLockerCustomPath = 'apps/maarch_entreprise/actions/docLocker.php';
    $docLockerPath = $_SESSION['config']['businessappurl'] . '/actions/docLocker.php';
    if (is_file($docLockerCustomPath))
        require_once $docLockerCustomPath;
    else if (is_file($docLockerPath))
        require_once $docLockerPath;
    else
        exit("can't find docLocker.php");

    $docLocker = new docLocker($res_id);
    if (!$docLocker->canOpen()) {
        echo "{status : 0,error:'"._DOC_LOCKER_RES_ID."".$res_id.""._DOC_LOCKER_USER." ".$_SESSION['userLock']."'}";
        exit();
    }

require_once "modules" . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR
			. "class" . DIRECTORY_SEPARATOR
			. "class_modules_tools.php";
include('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'definition_mail_categories.php');
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
$sec =new security();
$core =new core_tools();

$data = get_general_data($coll_id, $res_id, 'minimal');
			
/* Partie centrale*/
$left_html = '';


//Onglet document
if ($selectedCat != 'outgoing'){
    $pathScriptTab = $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=view_resource_controler&visu&id='. $res_id.'&collid='.$coll_id;
    $left_html .= '<dt id="onglet_entrant" style="padding-top: 6px;" onclick="loadSpecificTab(\'viewframevalidDoc\',\''.$pathScriptTab.'\');return false;">'._INCOMING.' <sup><span id="nb_note" style="'.$style2.'" class="'.$class.'">'.$nbr_notes.'</span></sup></dt><dd style="overflow-y: hidden;">';
    $left_html .= '<iframe src="'.$pathScriptTab.'" name="viewframevalidDoc" id="viewframevalidDoc"  scrolling="auto" frameborder="0"  style="width:100%;height:100%;" ></iframe></dd>';

    $left_html .= '</dd>';
}
	
//Onglet Circuit 
$pathScriptTab = $_SESSION['config']['businessappurl'] . 'index.php?display=true&page=show_visa_tab&module=visa&resId='.$res_id.'&collId='.$coll_id.'&destination=';
$left_html .= '<dt id="visa_tab" style="padding-top: 6px;" onclick="loadSpecificTab(\'workflow_visa\',\''.$pathScriptTab.'\');return false;">'._VISA_WORKFLOW.'<span id="visa_tab_img"></span><span id="visa_tab_badge"></span></dt><dd id="page_circuit" style="overflow-x: hidden;">';
$left_html .= '<h2>'._VISA_WORKFLOW.'</h2>';
$left_html .= '<iframe src="" name="workflow_visa" width="100%" height="620px" align="left" scrolling="yes" frameborder="0" id="workflow_visa"></iframe>';
$left_html .= '</dd>';

//LOAD TOOLBAR BADGE
$toolbarBagde_script = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=visa&page=load_toolbar_visa&origin=parent&resId='.$res_id.'&collId='.$coll_id;
$js .='loadToolbarBadge(\'visa_tab\',\''.$toolbarBagde_script.'\');';

//Onglet Avancement 
$pathScriptTab = $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=document_workflow_history&id='. $res_id .'&coll_id='. $coll_id.'&load&size=full';
$left_html .= '<dt id="onglet_avancement" style="padding-top: 6px;" onclick="loadSpecificTab(\'workflow_history_document\',\''.$pathScriptTab.'\');return false;">Avancement <sup><span id="nb_note" style="'.$style2.'" class="'.$class.'">'.$nbr_notes.'</span></sup></dt><dd id="page_avancement" style="overflow-x: hidden;">';
$left_html .= '<h2>'. _WF .'</h2>';
$left_html .= '<iframe src="" name="workflow_history_document" width="100%" height="620px" align="left" scrolling="yes" frameborder="0" id="workflow_history_document"></iframe>';
$left_html .= '<br/>';
$left_html .= '<br/>';

$pathScriptTab = $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=document_history&id='. $res_id .'&coll_id='. $coll_id.'&load&size=full';
$left_html .= '<span style="cursor: pointer;" onmouseover="this.style.cursor=\'pointer\';" onclick="new Effect.toggle(\'history_document\', \'blind\', {delay:0.2});whatIsTheDivStatus(\'history_document\', \'divStatus_all_history_div\');loadSpecificTab(\'history_document\',\''.$pathScriptTab.'\');return false;">';
$left_html .= '<span id="divStatus_all_history_div" style="color:#1C99C5;"><i class="fa fa-plus-square-o"></i></span>';
$left_html .= '<b>&nbsp;'. _ALL_HISTORY .'</b>';
$left_html .= '</span>';
$left_html .= '<iframe src="" name="history_document" width="100%" height="620px" align="left" scrolling="yes" frameborder="0" id="history_document" style="display:none;"></iframe>';

$left_html .= '</dd>';

//Onglet notes
if ($core->is_module_loaded('notes')) {

    $pathScriptTab = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=notes&page=notes&identifier=' . $res_id . '&origin=document&coll_id=' . $coll_id . '&load&size=full';
    $left_html .= '<dt id="notes_tab" style="padding-top: 6px;" onclick="loadSpecificTab(\'list_notes_doc\',\'' . $pathScriptTab . '\');return false;">' . _NOTES . '<span id="notes_tab_img"></span><span id="notes_tab_badge"></span></dt><dd id="page_notes" style="overflow-x: hidden;"><h2>' . _NOTES . '</h2><iframe name="list_notes_doc" id="list_notes_doc" src="" frameborder="0" scrolling="yes" width="99%" height="570px"></iframe></dd> ';

    //LOAD TOOLBAR BADGE
    $toolbarBagde_script = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=notes&page=load_toolbar_notes&origin=parent&resId='.$res_id.'&collId='.$coll_id;
    $js .='loadToolbarBadge(\'notes_tab\',\''.$toolbarBagde_script.'\');';
}

/* Partie droite */
$right_html = '';
$visa = new visa();
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
                if (strlen($tab_path_rep_file[$i]['title']) > 15)
                    $titleRep = substr($_SESSION['attachment_types'][$tab_path_rep_file[$i]['attachment_type']], 0, 15) . '...';
                else
                    $titleRep = $_SESSION['attachment_types'][$tab_path_rep_file[$i]['attachment_type']];
            }
            if ($tab_path_rep_file[$i]['attachment_type'] == 'signed_response') {
                $titleRep = '<i style="color:#fdd16c" class="fa fa-certificate fa-lg fa-fw"></i>' . $titleRep;
            }
            $pathScriptTab = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=visa&page=view_pdf_attachement&res_id_master=' . $res_id . '&id=' . $tab_path_rep_file[$i]['res_id'];
            $right_html .= '<dt style="padding-top: 6px;" title="'
                    . $tab_path_rep_file[$i]['title'] . '" id="ans_' . $num_rep . '_' . $tab_path_rep_file[$i]['res_id'] . '" onclick="loadSpecificTab(\'viewframevalidRep' . $num_rep . '_' . $tab_path_rep_file[$i]['res_id'] . '\',\'' . $pathScriptTab . '\');updateFunctionModifRep(\''
                    . $tab_path_rep_file[$i]['res_id'] . '\', ' . $num_rep . ', ' . $tab_path_rep_file[$i]['is_version'] . ');">' . $titleRep
                    . ' <sup><span class="" style="" id=""></span></sup></dt><dd id="content_' . $num_rep . '_' . $tab_path_rep_file[$i]['res_id'] . '">';
            if($i==0){
                $right_html .= '<iframe src="'.$pathScriptTab.'" name="viewframevalidRep' . $num_rep . '" id="viewframevalidRep' . $num_rep . '_' . $tab_path_rep_file[$i]['res_id'] . '"  scrolling="auto" frameborder="0" style="width:100%;height:100%;" ></iframe>';

            }  else {
                $right_html .= '<iframe src="" name="viewframevalidRep' . $num_rep . '" id="viewframevalidRep' . $num_rep . '_' . $tab_path_rep_file[$i]['res_id'] . '"  scrolling="auto" frameborder="0" style="width:100%;height:100%;" ></iframe>';
  
            }
            $right_html .= '</dd>';
            //$right_html .= '<script>console.log('.$cptAttach.');</script>';
           
        }
            
        $pathScriptTab = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=attachments&page=frame_list_attachments&template_selected=documents_list_attachments_simple&load&attach_type_exclude=converted_pdf,print_folder';
            
        $right_html .= '<dt title="' . _ATTACHED_DOC .'" id="attachments_tab" style="padding-top: 6px;" onclick="loadSpecificTab(\'list_attach\',\''.$pathScriptTab.'\');$(\'cur_idAffich\').value=0;updateFunctionModifRep(0,0,0);">PJ<span id="attachments_tab_img"></span><span id="attachments_tab_badge"></span></dt><dd id="page_pj">';

        //LOAD TOOLBAR BADGE
        $toolbarBagde_script = $_SESSION['config']['businessappurl'] . 'index.php?display=true&module=attachments&page=load_toolbar_attachments&origin=parent&resId='.$res_id.'&collId='.$coll_id;
        $js .='loadToolbarBadge(\'attachments_tab\',\''.$toolbarBagde_script.'\');';
        
        if ($core_tools->is_module_loaded('attachments')) {
        require 'modules/templates/class/templates_controler.php';
        $templatesControler = new templates_controler();
        $templates = array();
        $templates = $templatesControler->getAllTemplatesForProcess($curdest);
        $_SESSION['destination_entity'] = $curdest;
        //var_dump($templates);
        $right_html .= '<div id="list_answers_div" onmouseover="this.style.cursor=\'pointer\';" style="width:100%;height:100%;">';
            $right_html .= '<div class="block" style="margin-top:-2px;height:95%;">';
                $right_html .= '<div id="processframe" name="processframe" style="height:100%;">';
                    $right_html .= '<center><h2>' . _PJ . ', ' . _ATTACHEMENTS . '</h2></center>';
                   
                    $right_html .= '<div class="ref-unit">';
                    
                    $right_html .= '<center>';
                    if ($core_tools->is_module_loaded('templates')) {
                        $right_html .= '<input type="button" name="attach" id="attach" class="button" value="'
                            . _CREATE_PJ
                            .'" onclick="showAttachmentsForm(\'' . $_SESSION['config']['businessappurl']
                            . 'index.php?display=true&module=attachments&page=attachments_content\')" />';
                    }
                    $right_html .= '</center><iframe name="list_attach" id="list_attach" src="" '
                    . 'frameborder="0" width="100%" scrolling="yes" height="600px" scrolling="yes" ></iframe>';
                    $right_html .= '</div>';
                $right_html .= '</div>';
            $right_html .= '</div>';
            //$right_html .= '<hr />';
        $right_html .= '</div>';
    }
	
	
        $right_html .= '</dd>';
						

	$valid_but = 'valid_action_form( \'index_file\', \'index.php?display=true&page=manage_action&module=core\', \''.$_REQUEST['action'].'\', \''.$res_id.'\', \'res_letterbox\', \'null\', \''.$coll_id.'\', \'page\');';

//echo "{status : 1,avancement:'".$avancement_html."',circuit:'".$circuit_html."',notes_dt:'".$notes_html_dt."',notes_dd:'".$notes_html_dd."'}";
echo "{status : 1,left_html:'".addslashes($left_html)."',right_html:'".addslashes($right_html)."',valid_button:'".addslashes($valid_but)."',id_rep:'".$tab_path_rep_file[0]['res_id']."',is_vers_rep:'".$tab_path_rep_file[0]['is_version']."',exec_js:'".addslashes($js)."'}";
exit();
