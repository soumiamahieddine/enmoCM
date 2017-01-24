<?php
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_request.php';
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_security.php';
require_once "modules" . DIRECTORY_SEPARATOR . "visa" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_modules_tools.php";

$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header('', true, false);
$core_tools->load_js();

$res_id = $_REQUEST["resId"];
$coll_id = $_REQUEST["collId"];
$view = $_REQUEST["view"];

$security = new security();
$right = $security->test_right_doc($coll_id, $res_id);

if(!$right){
    exit(_NO_RIGHT_TXT);
}

$visa = new visa();
$db = new Database();

$frm_str .= '<div class="listDocsBasket">';
$tab_docs = $visa->getDocsBasket();
//$frm_str .= '<pre>'.print_r($tab_docs,true).'</pre>';
//$selectedCat = '';
$list_docs = '';
$data = array();
foreach ($tab_docs as $num => $res_id_doc) {

    $stmt = $db->query(
            "select alt_identifier, status, category_id, priority, destination, "
            . " dest_contact_id, exp_contact_id, dest_user_id, exp_user_id, address_id, "
            . " subject, admission_date, process_limit_date"
            . " from " . $view
            . " where res_id = ?", array($res_id_doc)
    );
    $resChrono_doc = $stmt->fetchObject();
    $chrono_number_doc = $resChrono_doc->alt_identifier;
    $cat_id = $resChrono_doc->category_id;
    $doc_status = $resChrono_doc->status;
    $doc_priority = $resChrono_doc->priority;
    $doc_destination = $resChrono_doc->destination;
    $doc_dest_contact_id = $resChrono_doc->dest_contact_id;
    $doc_exp_contact_id = $resChrono_doc->exp_contact_id;
    $doc_dest_user_id = $resChrono_doc->dest_user_id;
    $doc_exp_user_id = $resChrono_doc->exp_user_id;
    $doc_address_id = $resChrono_doc->address_id;
    $doc_subject = $resChrono_doc->subject;
    $doc_admission_date = functions::format_date_db($resChrono_doc->admission_date);
    $doc_process_limit_date = functions::format_date_db($resChrono_doc->process_limit_date);

    $allAnsSigned = true;
    $stmt2 = $db->query("SELECT status from res_view_attachments where (attachment_type='response_project' OR attachment_type='outgoing_mail') and res_id_master = ?", array($res_id_doc));
    while ($line = $stmt2->fetchObject()) {
        if ($line->status == 'TRA' || $line->status == 'A_TRA') {
            $allAnsSigned = false;
        }
    }

    if ($allAnsSigned)
        $classSign = "visibility:visible;";
    else
        $classSign = "visibility:hidden;";

    $list_docs .= $res_id_doc . "#";

    if ($res_id_doc == $res_id) {
        $classLine = ' class="selectedId " ';
    } else
        $classLine = ' class="unselectedId " ';

    $id_to_display = _ID_TO_DISPLAY;

    $frm_str .= '<div ' . $classLine . ' onmouseover="this.style.cursor=\'pointer\';" onclick="loadNewId(\'index.php?display=true&module=visa&page=update_visaPage\',' . $res_id_doc . ',\'' . $coll_id . '\',\'' . $id_to_display . '\');" id="list_doc_' . $res_id_doc . '">';
    //check_category($coll_id, $res_id_doc);
    //$data = get_general_data($coll_id, $res_id_doc, 'minimal', array(), $cat_id);

    if ($res_id_doc == $res_id) {
        $selectedCat = $cat_id;
        $curNumDoc = $num;
        $curdest = $doc_destination;
    }

    $frm_str .= '<ul>';
    $frm_str .= '<li><b style="float:left;">';
    $frm_str .= '<span id = "chrn_id_' . $res_id_doc . '">' . $chrono_number_doc . '</span> <i class="fa fa-certificate" id="signedDoc_' . $res_id_doc . '" style="' . $classSign . '" ></i> '/* . ' - ' .$res_id_doc */;

    //priority
    $color = '';
    $color = 'color:' . $_SESSION['mail_priorities_color'][$doc_priority] . ';';

    $frm_str .= '</b><i class="fa fa-circle" aria-hidden="true" style="float:right;' . $color . '" title="' . $_SESSION['mail_priorities'][$doc_priority] . '"></i></li>';

    $frm_str .= '<li style="clear:both;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">';
    $frm_str .= '<i class="fa fa-user" title="Contact"></i> ';

    //BEGIN CASE OF CONTACT
    $data = array();

    if ($doc_dest_user_id <> '' || $doc_exp_user_id <> '') {
        if ($doc_dest_user_id <> '') {
            $contactIdentifier = $doc_dest_user_id;
        } elseif ($doc_exp_user_id <> '') {
            $contactIdentifier = $doc_exp_user_id;
        }
        $data['type_contact'] = 'internal';
        $stmt2 = $db->query(
                'SELECT lastname, firstname FROM '
                . $_SESSION['tablename']['users']
                . " WHERE user_id = ?", array($contactIdentifier)
        );
        $res = $stmt2->fetchObject();
        $data['contact'] = $res->lastname . ' ' . $res->firstname;
        $data['contactId'] = $line->{$contactIdentifier};
    } elseif ($doc_dest_contact_id <> '' || $doc_exp_contact_id <> '') {

        if ($doc_dest_contact_id <> '') {
            $contactIdentifier = $doc_dest_contact_id;
        } elseif ($doc_exp_contact_id <> '') {
            $contactIdentifier = $doc_exp_contact_id;
        }

        $data['type_contact'] = 'external';

        // $stmt2 = $db->query("SELECT address_id FROM mlb_coll_ext WHERE res_id = ?", array($res_id));
        // $resAddress = $stmt2->fetchObject();
        $addressId = $doc_address_id;

        $stmt2 = $db->query('SELECT is_corporate_person, is_private, contact_lastname, contact_firstname, society, society_short, contact_purpose_id, address_num, address_street, address_postal_code, address_town, lastname, firstname FROM view_contacts WHERE contact_id = ? and ca_id = ?', array($contactIdentifier, $addressId));
        $res = $stmt2->fetchObject();

        if ($res->is_corporate_person == 'Y') {
            $data['contact'] = $res->society . ' ';
            if (!empty($res->society_short)) {
                $data['contact'] .= '(' . $res->society_short . ') ';
            }
        } else {
            $data['contact'] = $res->contact_lastname . ' ' . $res->contact_firstname . ' ';
            if (!empty($res->society)) {
                $data['contact'] .= '(' . $res->society . ') ';
            }
        }

        if ($res->is_private == 'Y') {
            $data['contact'] .= '(' . _CONFIDENTIAL_ADDRESS . ')';
        } else {
            require_once("apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id'] . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR . "class_contacts_v2.php");
            $contact = new contacts_v2();
            $data['contact'] .= '- ' . $contact->get_label_contact($res->contact_purpose_id, $_SESSION['tablename']['contact_purposes']) . ' : ';
            if (!empty($res->lastname) || !empty($res->firstname)) {
                $data['contact'] .= $res->lastname . ' ' . $res->firstname . ' ';
            }
            if (!empty($res->address_num) || !empty($res->address_street) || !empty($res->address_town) || !empty($res->address_postal_code)) {
                $data['contact'] .= ', ' . $res->address_num . ' ' . $res->address_street . ' ' . $res->address_postal_code . ' ' . strtoupper($res->address_town);
            }
        }
        $data['contactId'] = $contactIdentifier;
        $data['addressId'] = $addressId;
    }
    //TODO CASE OF MULTICONTACTS
    // else if ($arr[$i] == 'is_multicontacts') {
    //     if (!empty ($line->{$arr[$i]})) {
    //         $data['type_contact'] = 'multi_external';
    //     }
    // }
    //echo $data['contact'];exit;
    //END CASE OF CONTACT

    if (isset($data['contact']) && !empty($data['contact'])) {
        $frm_str .= '<span title="'.$data['contact'].'">'.$data['contact'].'</span>';
    } else {
        $frm_str .= _MULTI . '-' . _DEST;
    }
    $frm_str .= '</li>';

    $frm_str .= '<li>';
    $frm_str .= '<i class="fa fa-file" title="Objet"></i> ';
    if (isset($doc_subject) && !empty($doc_subject)) {
        $frm_str .= '<span title="'.$doc_subject.'">'.$doc_subject.'</span>';
    }
    $frm_str .= '</li>';

    $frm_str .= '<li>';
    $frm_str .= '<i class="fa fa-calendar " title="Date d\'arrivée"></i> ';
    $frm_str .= $doc_admission_date;
    $frm_str .= ' <i class="fa fa-bell" title="Date limite"></i> ';
    $frm_str .= $doc_process_limit_date;
    $frm_str .= '</li>';

    $frm_str .= '</ul>';

    $frm_str .= '</div>';
}
$frm_str .= '</div>';

$frm_str .= '<div class="toolbar" style="text-align:center;">';
$frm_str .= '<table style="width:100%;">';
$frm_str .= '<tr>';
$frm_str .= '<td style="width:50%";">';
$frm_str .= '<a href="javascript://" id="previous_doc" onclick="previousDoc(\'index.php?display=true&module=visa&page=update_visaPage\', \'' . $coll_id . '\');"><i class="fa fa-chevron-up fa-2x" title="Précédent"></i></a>';

$frm_str .= '</td>';

$frm_str .= '<td style="width:50%";">';
$frm_str .= '<a href="javascript://" id="next_doc" onclick="nextDoc(\'index.php?display=true&module=visa&page=update_visaPage\', \'' . $coll_id . '\');"><i class="fa fa-chevron-down fa-2x" title="Suivant"></i></a>';

$frm_str .= '</td>';

//$frm_str .= '<td style="width:33%";">';	
//$frm_str .= '<a href="javascript://" id="cancel" onclick="javascript:$(\'baskets\').style.visibility=\'visible\';destroyModal(\'modal_'.$id_action.'\');reinit();"><i class="fa fa-backward fa-2x" title="Annuler"></i></a>';
//$frm_str .= '</td>';
$frm_str .= '</tr>';
$frm_str .= '</table>';
$frm_str .= '</div>';

echo $frm_str;
