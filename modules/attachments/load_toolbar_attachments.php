<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   load_toolbar_attachments
* @author  dev <dev@maarch.org>
* @ingroup attachments
*/

$targetTab = $_REQUEST['targetTab'];
$res_id    = $_REQUEST['resId'];
$coll_id   = $_REQUEST['collId'];

require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR . 'class_request.php';

$db = new Database;
$core = new core_tools();

if ($_SESSION['req'] == 'details') {
    if (isset($_REQUEST['responses'])) {
        $stmt = $db->query("SELECT res_id, creation_date, title, format FROM "
            . $_SESSION['tablename']['attach_res_attachments']
            . " WHERE res_id_master = ? and coll_id = ? and status <> 'DEL' and (attachment_type IN ('response_project', 'outgoing_mail_signed', 'outgoing_mail', 'signed_response', 'aihp')) and (status <> 'TMP' or (typist = ? and status = 'TMP'))", array($res_id, $coll_id, $_SESSION['user']['UserId']));
    } else {
        $stmt = $db->query("SELECT res_id, creation_date, title, format FROM "
            . $_SESSION['tablename']['attach_res_attachments']
            . " WHERE res_id_master = ? and coll_id = ? and status <> 'DEL' and attachment_type NOT IN ('response_project','signed_response','outgoing_mail_signed','outgoing_mail','aihp'". $exclude . ") and (status <> 'TMP' or (typist = ? and status = 'TMP'))", array($res_id, $coll_id, $_SESSION['user']['UserId']));
    }
} else {
    $stmt = $db->query("SELECT res_id FROM "
        . $_SESSION['tablename']['attach_res_attachments']
        . " WHERE status <> 'DEL' and res_id_master = ? and coll_id = ? and (status <> 'TMP' or (typist = ? and status = 'TMP'))", array($res_id, $coll_id, $_SESSION['user']['UserId']));
}
$nbAttach = $stmt->rowCount();

if ($nbAttach == 0) {
    $class = 'nbResZero';
    $style2 = 'display:none;';
    $style = '0.5';
    $styleDetail = '#9AA7AB';
} else {
    $class = 'nbRes';
    $style = '';
    $style2 = 'display:inherit;';
    $styleDetail = '#666';
}
if ($_SESSION['save_list']['fromDetail'] == 'true') {
    if ($nbAttach == 0 && strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
        $nav = 'attachments_tab';
        if (isset($_REQUEST['responses'])) {
            $nav = 'responses_tab';
        }
        $style2 = 'visibility:hidden;';
    }

    if ($_REQUEST['origin'] == 'parent') {
        $js .= 'window.parent.top.$(\''.$targetTab.'\').style.color=\''.$styleDetail.'\';window.parent.top.$(\''.$targetTab.'_badge\').innerHTML = \'<span id="nb_'.$targetTab.'" style="'.$style2.'font-size: 10px;" class="'.$class.'">'.$nbAttach.'</span>\'';
    } elseif ($_REQUEST['origin'] == 'document') {
        $js .= '$(\''.$targetTab.'\').style.color=\''.$styleDetail.'\';$(\''.$targetTab.'_badge\').innerHTML = \'<span id="nb_'.$targetTab.'" style="'.$style2.'font-size: 10px;" class="'.$class.'">'.$nbAttach.'</span>\'';
    } else {
        $js .= 'parent.$(\''.$targetTab.'\').style.color=\''.$styleDetail.'\';parent.$(\''.$targetTab.'_badge\').innerHTML = \'<span id="nb_'.$targetTab.'" style="'.$style2.'font-size: 10px;" class="'.$class.'">'.$nbAttach.'</span>\'';
    }
} else {
    if ($_REQUEST['origin'] == 'parent') {
        $js .= 'window.parent.top.$(\''.$targetTab.'_img\').style.opacity=\''.$style.'\';window.parent.top.$(\''.$targetTab.'_badge\').innerHTML = \'&nbsp;<sup><span id="nb_'.$targetTab.'" style="'.$style2.'" class="'.$class.'">'.$nbAttach.'</span></sup>\'';
    } elseif ($_REQUEST['origin'] == 'document') {
        $js .= '$(\''.$targetTab.'_img\').style.opacity=\''.$style.'\';$(\''.$targetTab.'_badge\').innerHTML = \'&nbsp;<sup><span id="nb_'.$targetTab.'" style="'.$style2.'" class="'.$class.'">'.$nbAttach.'</span></sup>\'';
    } else {
        $js .= 'parent.$(\''.$targetTab.'_img\').style.opacity=\''.$style.'\';parent.$(\''.$targetTab.'_badge\').innerHTML = \'&nbsp;<sup><span id="nb_'.$targetTab.'" style="'.$style2.'" class="'.$class.'">'.$nbAttach.'</span></sup>\'';
    }
}
      
echo "{status : 0, nav : '".$nav."',content : '', error : '', exec_js : '".addslashes($js)."'}";
exit();
