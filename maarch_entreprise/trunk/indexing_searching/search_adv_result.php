<?php

/*
*    Copyright 2008 - 2012 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Advanced search form management
*
* @file search_adv_result.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/

require_once('core/class/class_request.php');
require_once('core/class/class_security.php');
require_once('apps/' . $_SESSION['config']['app_id'] 
    . '/class/class_indexing_searching_app.php'
);
require_once('apps/' . $_SESSION['config']['app_id'] . '/class/class_types.php');
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$is = new indexing_searching_app();
$func = new functions();
$req = new request();
$type = new types();
$fields = "";
$orderby = "";

$baskets_clause = '';
$coll_id = 'letterbox_coll';
$indexes = $type->get_all_indexes($coll_id);
//$func->show_array($indexes);
$_SESSION['error_search'] = '';
$_SESSION['searching']['comp_query'] = '';
// define the row of the start
if (isset($_REQUEST['start'])) {
    $start = $_REQUEST['start'];
} else {
    $start = 0;
}

if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'frame') {
    $mode = 'frame';
} elseif (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'popup') {
    $mode = 'popup';
} else {
    $mode = 'normal';
    $core_tools->test_service('adv_search_mlb', 'apps');
}
$where_request = "";
$case_view = false;
 $_ENV['date_pattern'] = "/^[0-3][0-9]-[0-1][0-9]-[1-2][0-9][0-9][0-9]$/";
$json_txt = '{';

/**
 * Array $_REQUEST['meta'] exemple
(
    [0] => copies#copies_false,copies_true#radio
    [1] => objet#objet#input_text
    [2] => numged#numged#input_text
    [3] => multifield#multifield#input_text
    [4] => category#category#select_simple
    [5] => doc_date#doc_date_from,doc_date_to#date_range
)
**/
//$func->show_array($_REQUEST['meta']);exit;
if (count($_REQUEST['meta']) > 0) {
    //Verif for parms sended by url
    if ($_GET['meta']) {
        for ($m=0; $m<count($_REQUEST['meta']);$m++) {
            if (strstr($_REQUEST['meta'][$m], '||') == true) {
                $_REQUEST['meta'][$m] = str_replace('||', '#', $_REQUEST['meta'][$m]);
            }
        }
    }
    $opt_indexes = array();
    $_SESSION['meta_search'] = $_REQUEST['meta'];
    for ($i=0;$i<count($_REQUEST['meta']);$i++) {
        //echo $_REQUEST['meta'][$i]."<br>";
        $tab = explode('#', $_REQUEST['meta'][$i]);
        $id_val = $tab[0];
        $json_txt .= "'".$tab[0]."' : { 'type' : '".$tab[2]."', 'fields' : {";
        $tab_id_fields = explode(',', $tab[1]);
        //$func->show_array($tab_id_fields);
        for ($j=0; $j<count($tab_id_fields);$j++) {
            //echo $tab_id_fields[$j]."<br>";
            // ENTITIES
            if ($tab_id_fields[$j] == 'services_chosen' && isset($_REQUEST['services_chosen'])) {
                $json_txt .= " 'services_chosen' : [";
                $srv_chosen_tmp = " (";
                for ($get_i = 0; $get_i <count($_REQUEST['services_chosen']); $get_i++) {
                    $srv_chosen_tmp .= "'".$func->protect_string_db($_REQUEST['services_chosen'][$get_i])."',";
                    $json_txt .= "'".$_REQUEST['services_chosen'][$get_i]."',";
                }
                $srv_chosen_tmp = substr($srv_chosen_tmp, 0, -1);
                $json_txt = substr($json_txt, 0, -1);
                $srv_chosen_tmp .= ") ";

                $where_request .= " destination IN  ".$srv_chosen_tmp." ";
                $where_request .=" and  ";
                $json_txt .= '],';
            } elseif ($tab_id_fields[$j] == 'multifield' && !empty($_REQUEST['multifield'])) {
                // MULTIFIELD : subject, title, doc_custom_t1, process notes
                $json_txt .= "'multifield' : ['".addslashes(trim($_REQUEST['multifield']))."'],";
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL") {
                    $where_request .= "(subject ilike  '%".$func->protect_string_db($_REQUEST['multifield'])."%' or alt_identifier ilike  '%".$func->protect_string_db($_REQUEST['multifield'])."%' or title ilike  '%".$func->protect_string_db($_REQUEST['multifield'])."%' or doc_custom_t1 ilike  '%".$func->protect_string_db($_REQUEST['multifield'])."%' or process_notes ilike '%".$func->protect_string_db($_REQUEST['multifield'])."%') ";
                } else {
                    $where_request .= "(subject LIKE  '%".$func->protect_string_db($_REQUEST['multifield'])."%' or alt_identifier LIKE  '%".$func->protect_string_db($_REQUEST['multifield'])."%' or title LIKE  '%".$func->protect_string_db($_REQUEST['multifield'])."%' or doc_custom_t1 LIKE  '%".$func->protect_string_db($_REQUEST['multifield'])."%' or process_notes like '%".$func->protect_string_db($_REQUEST['multifield'])."%') ";
                }
                $where_request .=" and  ";
            } elseif ($tab_id_fields[$j] == 'numcase' && !empty($_REQUEST['numcase'])) {
                // CASE_NUMBER
                $json_txt .= "'numcase' : ['".addslashes(trim($_REQUEST['numcase']))."'],";
                //$where_request .= "res_view_letterbox.case_id = ".$func->wash($_REQUEST['numcase'], "num", _N_CASE,"no")." and ";
                $where_request .= " ".$_SESSION['collections'][0]['view'].".case_id = ".$func->wash($_REQUEST['numcase'], "num", _N_CASE,"no")." and ";
                $case_view=true;
            } elseif ($tab_id_fields[$j] == 'labelcase' && !empty($_REQUEST['labelcase'])) {
                // CASE_LABEL
                $json_txt .= "'labelcase' : ['".addslashes(trim($_REQUEST['labelcase']))."'],";
                //$where_request .= "res_view_letterbox.case_id = ".$func->wash($_REQUEST['numcase'], "num", _N_CASE,"no")." and ";
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                    $where_request .= " ".$_SESSION['collections'][0]['view'].".case_label ilike '%".$func->wash($_REQUEST['labelcase'], "no", _CASE_LABEL,"no")."%' and ";
                else
                    $where_request .= " ".$_SESSION['collections'][0]['view'].".case_label like '%".$func->wash($_REQUEST['labelcase'], "no", _CASE_LABEL,"no")."%' and ";
                $case_view=true;
            } elseif ($tab_id_fields[$j] == 'descriptioncase' && !empty($_REQUEST['descriptioncase'])) {
                // CASE_DESCRIPTION
                $json_txt .= "'descriptioncase' : ['".addslashes(trim($_REQUEST['descriptioncase']))."'],";
                //$where_request .= "res_view_letterbox.case_id = ".$func->wash($_REQUEST['numcase'], "num", _N_CASE,"no")." and ";
                $where_request .= " ".$_SESSION['collections'][0]['view'].".case_description ilike '%".$func->wash($_REQUEST['descriptioncase'], "no", _CASE_DESCRIPTION,"no")."%' and ";
                $case_view=true;
            } elseif ($tab_id_fields[$j] == 'chrono' && !empty($_REQUEST['chrono'])) {
                $json_txt .= " 'chrono' : ['".addslashes(trim($_REQUEST['chrono']))."'],";
                $chrono = $func->wash($_REQUEST['chrono'],"no",_CHRONO_NUMBER,"no");
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " alt_identifier ilike '%".$chrono."%' ";
                }
                else
                {
                    $where_request .= " alt_identifier like '%".$chrono."%' ";
                }
                $where_request .=" and  ";
            }
            // PRIORITY
            elseif ($tab_id_fields[$j] == 'priority' && !empty($_REQUEST['priority']))
            {
                $json_txt .= " 'priority' : ['".addslashes(trim($_REQUEST['priority']))."'],";
                $prio = $func->wash($_REQUEST['priority'],"num",_THE_PRIORITY,"no");
                $where_request .= " priority = ".$prio." ";
                $where_request .=" and  ";
            }
            // PROCESS NOTES
            elseif ($tab_id_fields[$j] == 'process_notes' && !empty($_REQUEST['process_notes']))
            {
                $json_txt .= " 'process_notes' : ['".addslashes(trim($_REQUEST['process_notes']))."'],";
                $s_process_notes = $func->wash($_REQUEST['process_notes'], "no", _PROCESS_NOTES,"no");
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " (process_notes ILIKE '%".$func->protect_string_db($s_process_notes)."%' ) and ";
                }
                else
                {
                    $where_request .= " (process_notes LIKE '%".$func->protect_string_db($s_process_notes)."%' ) and ";
                }
            }
            // NOTES
            elseif ($tab_id_fields[$j] == 'doc_notes' && !empty($_REQUEST['doc_notes']))
            {
                $json_txt .= " 'doc_notes' : ['".addslashes(trim($_REQUEST['doc_notes']))."'],";
                $s_doc_notes = $func->wash($_REQUEST['doc_notes'], "no", _NOTES,"no");
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " res_id in(select identifier from ".$_SESSION['tablename']['not_notes']." where note_text ILIKE '%".$func->protect_string_db($s_doc_notes)."%') and ";
                }
                else
                {
                    $where_request .= " res_id in(select identifier from ".$_SESSION['tablename']['not_notes']." where note_text LIKE '%".$func->protect_string_db($s_doc_notes)."%') and ";
                }
            }
            // FOLDER : MARKET
            elseif ($tab_id_fields[$j] == 'market' && !empty($_REQUEST['market']))
            {
                $json_txt .= " 'market' : ['".addslashes(trim($_REQUEST['market']))."'],";
                $market = $func->wash($_REQUEST['market'], "no", _MARKET,"no");
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " (folder_name ilike '%".$func->protect_string_db($market)."%' or folder_id ilike '%".$func->protect_string_db($market)."%' ) and ";
                }
                else
                {
                    $where_request .= " (folder_name like '%".$func->protect_string_db($market)."%' or folder_id like '%".$func->protect_string_db($market)."%' ) and ";
                }
            }
            // FOLDER : PROJECT
            elseif ($tab_id_fields[$j] == 'project' && !empty($_REQUEST['project']))
            {
                $json_txt .= " 'project' : ['".addslashes(trim($_REQUEST['project']))."'],";
                $project = $func->wash($_REQUEST['project'], "no", _MARKET,"no");
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " (folder_name ilike '%".$func->protect_string_db($project)."%' or folder_id ilike '%".$func->protect_string_db($project)."%' or folders_system_id in (select parent_id from ".$_SESSION['tablename']['fold_folders']." where folder_name ilike '%".$func->protect_string_db($project)."%' or folder_id like '%".$func->protect_string_db($project)."%')) and ";
                }
                else
                {
                    $where_request .= " (folder_name like '%".$func->protect_string_db($project)."%' or folder_id like '%".$func->protect_string_db($project)."%' or folders_system_id in (select parent_id from ".$_SESSION['tablename']['fold_folders']." where folder_name like '".$func->protect_string_db($project)."%' or folder_id like '%".$func->protect_string_db($project)."%')) and ";
                }
            }

            elseif ($tab_id_fields[$j] == 'folder_name' && !empty($_REQUEST['folder_name']))
            {
                $json_txt .= " 'folder_name' : ['".addslashes(trim($_REQUEST['folder_name']))."'],";
                $folder_name = $func->wash($_REQUEST['folder_name'], "no", _FOLDER_NAME,"no");
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " (folder_name ilike '%".$func->protect_string_db($folder_name)."%' and ";
                }
                else
                {
                    $where_request .= " (folder_name like '%".$func->protect_string_db($folder_name)."%' and ";
                }
            }
            // DEST
            elseif ($tab_id_fields[$j] == 'dest' && !empty($_REQUEST['dest']))
            {
                $json_txt .= " 'dest' : ['".addslashes(trim($_REQUEST['dest']))."'],";
                $dest = $func->wash($_REQUEST['dest'], "no", _DEST,"no");
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " (dest_contact_id in(select contact_id from ".$_SESSION['tablename']['contacts']." where lastname  ILIKE '".$func->protect_string_db($dest)."%' or  firstname ILIKE '".$func->protect_string_db($dest)."%' or  society ILIKE '".$func->protect_string_db($dest)."%' or  function ILIKE '".$func->protect_string_db($dest)."%') or dest_user_id in (select user_id from ".$_SESSION['tablename']['users']." where lastname  ILIKE '".$func->protect_string_db($dest)."%' or  firstname ILIKE '".$func->protect_string_db($dest)."%'  or  user_id ILIKE '".$func->protect_string_db($dest)."%')) and ";
                }
                else
                {
                    $where_request .= " (dest_contact_id in(select contact_id from ".$_SESSION['tablename']['contacts']." where lastname  LIKE '".$func->protect_string_db($dest)."%' or  firstname LIKE '".$func->protect_string_db($dest)."%' or  society LIKE '".$func->protect_string_db($dest)."%' or  function LIKE '".$func->protect_string_db($dest)."%') or dest_user_id in (select user_id from ".$_SESSION['tablename']['users']." where lastname  LIKE '".$func->protect_string_db($dest)."%' or  firstname LIKE '".$func->protect_string_db($dest)."%' or  user_id LIKE '".$func->protect_string_db($dest)."%')) and ";
                }
            }
            //SHIPPER
            elseif ($tab_id_fields[$j] == 'shipper' && !empty($_REQUEST['shipper']))
            {
                $json_txt .= " 'shipper' : ['".addslashes(trim($_REQUEST['shipper']))."'],";
                $shipper = $func->wash($_REQUEST['shipper'], "no", _SHIPPER,"no");
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " (exp_contact_id in(select contact_id from ".$_SESSION['tablename']['contacts']." where lastname  ILIKE '".$func->protect_string_db($shipper)."%' or  firstname ILIKE '".$func->protect_string_db($shipper)."%' or  society ILIKE '".$func->protect_string_db($shipper)."%' or  function ILIKE '".$func->protect_string_db($shipper)."%') or exp_user_id in (select user_id from ".$_SESSION['tablename']['users']." where lastname  ILIKE '".$func->protect_string_db($shipper)."%' or  firstname ILIKE '".$func->protect_string_db($shipper)."%'  or  user_id ILIKE '".$func->protect_string_db($shipper)."%')) and ";
                }
                else
                {
                    $where_request .= " (exp_contact_id in(select contact_id from ".$_SESSION['tablename']['contacts']." where lastname  LIKE '".$func->protect_string_db($shipper)."%' or  firstname LIKE '".$func->protect_string_db($shipper)."%' or  society LIKE '".$func->protect_string_db($shipper)."%' or  function LIKE '".$func->protect_string_db($shipper)."%') or exp_user_id in (select user_id from ".$_SESSION['tablename']['users']." where lastname  LIKE '".$func->protect_string_db($shipper)."%' or  firstname LIKE '".$func->protect_string_db($shipper)."%' or  user_id LIKE '".$func->protect_string_db($shipper)."%')) and ";
                }
            }
            // GED NUM
            elseif ($tab_id_fields[$j] == 'numged' && !empty($_REQUEST['numged']))
            {
                $json_txt .= " 'numged' : ['".addslashes(trim($_REQUEST['numged']))."'],";
                $where_request .= "res_id = ".$func->wash($_REQUEST['numged'], "num", _N_GED,"no")." and ";
            }
            // DEST_USER
            elseif ($tab_id_fields[$j] == 'destinataire_chosen' && !empty($_REQUEST['destinataire_chosen']))
            {
                $json_txt .= " 'destinataire_chosen' : [";
                $destinataire_chosen_tmp = " (";
                for ($get_i = 0; $get_i <count($_REQUEST['destinataire_chosen']); $get_i++)
                {
                    $destinataire_chosen_tmp .= "'".$func->protect_string_db($_REQUEST['destinataire_chosen'][$get_i])."',";
                    $json_txt .= "'".$_REQUEST['destinataire_chosen'][$get_i]."',";
                }
                $destinataire_chosen_tmp = substr($destinataire_chosen_tmp, 0, -1);
                $json_txt = substr($json_txt, 0, -1);
                $destinataire_chosen_tmp .= ") ";

                $where_request .= " (dest_user IN  ".$destinataire_chosen_tmp." or res_id in (select res_id from ".$_SESSION['tablename']['ent_listinstance']." where item_id in ".$destinataire_chosen_tmp." and item_mode = 'dest')) ";
                $where_request .=" and  ";
                //echo $where_request;exit;
                $json_txt .= '],';
            }
            // SUBJECT
            elseif ($tab_id_fields[$j] == 'subject' && !empty($_REQUEST['subject']))
            {
                $json_txt .= " 'subject' : ['".addslashes(trim($_REQUEST['subject']))."'],";
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " subject ilike '%".$func->protect_string_db($_REQUEST['subject'])."%' and ";
                }
                else
                {
                    $where_request .= " subject like '%".$func->protect_string_db($_REQUEST['subject'])."%' and ";
                }
            } elseif (
                $tab_id_fields[$j] == 'fulltext' 
                && !empty($_REQUEST['fulltext'])
            ) {
                // FULLTEXT
                $json_txt .= " 'fulltext' : ['" 
                    . addslashes(trim($_REQUEST['fulltext'])) . "'],";
                set_include_path('apps' . DIRECTORY_SEPARATOR 
                    . $_SESSION['config']['app_id'] 
                    . DIRECTORY_SEPARATOR . 'tools' 
                    . DIRECTORY_SEPARATOR . PATH_SEPARATOR . get_include_path()
                );
                require_once('Zend/Search/Lucene.php');
                Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                    new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum()
                );
                $_SESSION['search']['plain_text'] = $_REQUEST['fulltext'];
                //echo $_SESSION['search']['plain_text']; exit()
                $path_to_lucene_index = $_SESSION['collections'][0]['path_to_lucene_index'];
                //$lucene_all_words = explode(' ',$_REQUEST['fulltext']);
                if (is_dir($path_to_lucene_index))
                {
                    if (!$func->isDirEmpty($path_to_lucene_index)) {
                        $index = Zend_Search_Lucene::open($path_to_lucene_index);
//$hits = $index->find($_REQUEST['fulltext']."~");
//$query_res_ft = Zend_Search_Lucene_Search_QueryParser::parse($_REQUEST['fulltext'], 'iso-8859-5'));
						$hits = $index->find($_REQUEST['fulltext']);
                        /*
						avec accent
						$hits = $index->find(
                            strtolower(strtr(iconv("UTF-8","ISO-8859-1"
                                , $_REQUEST['fulltext']),
                                'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 
                                'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY')
                            )
                        );*/
                        $Liste_Ids = "0";
                        foreach ($hits as $hit) {
                            $Liste_Ids .= ", '". $hit->Id ."'";
                        }
                        $where_request .= " res_id IN ($Liste_Ids) and ";
                    }
                } else {
                    $where_request .= " 1=-1 and ";
                }
            }
            // PRIORITY
            elseif ($tab_id_fields[$j] == 'priority' && (!empty($_REQUEST['priority']) ||$_REQUEST['priority'] == 0) )
            {
                $json_txt .= " 'priority' : ['".addslashes(trim($_REQUEST['priority']))."'],";
                $where_request .= " priority  = ".$_REQUEST['priority']." and ";
            }
            // DOCTYPES
            elseif ($tab_id_fields[$j] == 'doctypes_chosen' && !empty($_REQUEST['doctypes_chosen']))
            {
                $json_txt .= " 'doctypes_chosen' : [";
                $doctypes_chosen_tmp = " (";
                for ($get_i = 0; $get_i <count($_REQUEST['doctypes_chosen']); $get_i++)
                {
                    $doctypes_chosen_tmp .= "'".$func->protect_string_db($_REQUEST['doctypes_chosen'][$get_i])."',";
                    $json_txt .= "'".$_REQUEST['doctypes_chosen'][$get_i]."',";
                }
                $doctypes_chosen_tmp = substr($doctypes_chosen_tmp, 0, -1);
                $json_txt = substr($json_txt, 0, -1);
                $doctypes_chosen_tmp .= ") ";

                $where_request .= " type_id IN  ".$doctypes_chosen_tmp." ";
                $where_request .=" and  ";
                $json_txt .= '],';
            }
            // ARBOXE
            //Physical Archive including => filter on boxes
            //elseif ($tab_id_fields[$j] == 'boxes_chosen' && !empty($_REQUEST['arbox_id_chosen']))
            elseif ($tab_id_fields[$j] == 'arboxes_chosen')
            {
                $json_txt .= " 'arbox_id_chosen' : [";
                $arbox_id_chosen_tmp = " (";
                if (count($_REQUEST['arboxes_chosen'])){
                    for ($get_i = 0; $get_i <count($_REQUEST['arboxes_chosen']); $get_i++)
                    {
                        $arbox_id_chosen_tmp .= "'".$func->protect_string_db($_REQUEST['arboxes_chosen'][$get_i])."',";
                        $json_txt .= "'".$_REQUEST['arboxes_chosen'][$get_i]."',";
                    }
                    $arbox_id_chosen_tmp = substr($arbox_id_chosen_tmp, 0, -1);
                    $json_txt = substr($json_txt, 0, -1);
                    $arbox_id_chosen_tmp .= ") ";

                    $where_request .= " arbox_id IN  ".$arbox_id_chosen_tmp." ";
                    $where_request .=" and  ";
                }
                $json_txt .= '],';
            }
            // ARBATCH
            //Gestion boite archive => Limitation au lot
            elseif ($tab_id_fields[$j] == 'arbatch_id' && !empty($_REQUEST['arbatch_id']))
            {
                $json_txt .= " 'arbatch_id' : ['".addslashes(trim($_REQUEST['arbatch_id']))."'],";
                $arbatch_id = $func->wash($_REQUEST['arbatch_id'], "no", _BATCH,"no");
                {
                    $where_request .= " arbatch_id = ".$arbatch_id." and ";
                }
            }

            // MAIL NATURE
            elseif ($tab_id_fields[$j] == 'mail_nature' && !empty($_REQUEST['mail_nature']))
            {
                $json_txt .= "'mail_nature' : ['".addslashes(trim($_REQUEST['mail_nature']))."'],";
                $where_request .= " nature_id = '".$func->protect_string_db($_REQUEST['mail_nature'])."' and ";
            }
            // CREATION DATE : FROM
            elseif ($tab_id_fields[$j] == 'creation_date_from' && !empty($_REQUEST['creation_date_from']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['creation_date_from'])==false )
                {
                    $_SESSION['error'] .= _WRONG_DATE_FORMAT.' : '.$_REQUEST['creation_date_from'];
                }
                else
                {
                    if ($_SESSION['config']['databasetype'] == 'ORACLE')
                    {
                        $where_request .= " (creation_date >= to_date('".$func->format_date_db($_REQUEST['creation_date_from'])."', 'DD/MM/YYYY') ) and ";
                    }
                    else
                    {
                        $where_request .= " (".$req->extract_date("creation_date")." >= '".$func->format_date_db($_REQUEST['creation_date_from'])."') and ";
                    }
                    $json_txt .= " 'creation_date_from' : ['".trim($_REQUEST['creation_date_from'])."'],";
                }
            }
            // CREATION DATE : TO
            elseif ($tab_id_fields[$j] == 'creation_date_to' && !empty($_REQUEST['creation_date_to']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['creation_date_to'])==false )
                {
                    $_SESSION['error'] .= _WRONG_DATE_FORMAT.' : '.$_REQUEST['creation_date_to'];
                }
                else
                {
                    $where_request .= " (".$req->extract_date("creation_date")." <= '".$func->format_date_db($_REQUEST['creation_date_to'])."') and ";
                    $json_txt .= " 'creation_date_to' : ['".trim($_REQUEST['creation_date_to'])."'],";
                }
            }
            // PROCESS DATE : FROM (closing_date)
            elseif ($tab_id_fields[$j] == 'closing_date_from' && !empty($_REQUEST['closing_date_from']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['closing_date_from'])==false )
                {
                    $_SESSION['error'] .=  _WRONG_DATE_FORMAT.' : '.$_REQUEST['closing_date_from'];
                }
                else
                {
                    if ($_SESSION['config']['databasetype'] == 'ORACLE')
                    {
                        $where_request .= " (closing_date >= to_date('".$func->format_date_db($_REQUEST['closing_date_from'])."', 'DD/MM/YYYY') ) and ";
                    }
                    else
                    {
                        $where_request .= " (".$req->extract_date("closing_date")." >= '".$func->format_date_db($_REQUEST['closing_date_from'])."') and ";
                    }
                    $json_txt .= "'closing_date_from' : ['".trim($_REQUEST['closing_date_from'])."'],";
                }
            }
            // CLOSING DATE : TO
            elseif ($tab_id_fields[$j] == 'closing_date_to' && !empty($_REQUEST['closing_date_to']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['closing_date_to'])==false )
                {
                    $_SESSION['error'] = _WRONG_DATE_FORMAT.' : '.$_REQUEST['closing_date_to'];
                }
                else
                {
                    if ($_SESSION['config']['databasetype'] == 'ORACLE')
                    {
                        $where_request .= " (closing_date <= to_date('".$func->format_date_db($_REQUEST['closing_date_to'])."', 'DD/MM/YYYY') ) and ";
                    }
                    else
                    {
                        $where_request .= " (".$req->extract_date("closing_date")." <= '".$func->format_date_db($_REQUEST['closing_date_to'])."') and ";
                    }
                    $json_txt .= "'closing_date_to' : ['".trim($_REQUEST['closing_date_to'])."'],";
                }
            }
            // PROCESS LIMIT DATE : FROM
            elseif ($tab_id_fields[$j] == 'process_limit_date_from' && !empty($_REQUEST['process_limit_date_from']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['process_limit_date_from'])==false )
                {
                    $_SESSION['error'] = _WRONG_DATE_FORMAT.' : '.$_REQUEST['process_limit_date_from'];
                }
                else
                {
                    if ($_SESSION['config']['databasetype'] == 'ORACLE')
                    {
                        $where_request .= " (process_limit_date >= to_date('".$func->format_date_db($_REQUEST['process_limit_date_from'])."', 'DD/MM/YYYY') ) and ";
                    }
                    else
                    {
                        $where_request .= " (".$req->extract_date("process_limit_date")." >= '".$func->format_date_db($_REQUEST['process_limit_date_from'])."') and ";
                    }
                    $json_txt .= "'process_limit_date_from' : ['".trim($_REQUEST['process_limit_date_from'])."'],";
                }
            }
            // PROCESS LIMIT DATE : TO
            elseif ($tab_id_fields[$j] == 'process_limit_date_to' && !empty($_REQUEST['process_limit_date_to']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['process_limit_date_to'])==false )
                {
                    $_SESSION['error'] = _WRONG_DATE_FORMAT.' : '.$_REQUEST['process_limit_date_to'];
                }
                else
                {
                    if ($_SESSION['config']['databasetype'] == 'ORACLE')
                    {
                        $where_request .= " (process_limit_date <= to_date('".$func->format_date_db($_REQUEST['process_limit_date_to'])."', 'DD/MM/YYYY') ) and ";
                    }
                    else
                    {
                        $where_request .= " (".$req->extract_date("process_limit_date")." <= '".$func->format_date_db($_REQUEST['process_limit_date_to'])."') and ";
                    }
                    $json_txt .= "'process_limit_date_to' : ['".trim($_REQUEST['process_limit_date_to'])."'],";
                }
            }
            // STATUS
            elseif ($tab_id_fields[$j] == 'status_chosen' && isset($_REQUEST['status_chosen']))
            {
                $json_txt .= " 'status_chosen' : [";
                $where_request .="( ";
                for ($get_i = 0; $get_i <count($_REQUEST['status_chosen']); $get_i++)
                {
                    $json_txt .= "'".$_REQUEST['status_chosen'][$get_i]."',";
                    if ($_REQUEST['status_chosen'][$get_i]=="REL1")
                    {
                        if ($_SESSION['config']['databasetype'] == 'ORACLE')
                        {
                            $where_request .= " (alarm1_date <= ".$req->current_datetime()." and alarm2_date > ".$req->current_datetime()." and status <> 'END') or ";
                        }
                        else
                        {
                            $where_request .="( ".$req->extract_date('alarm1_date')." <= ".$req->current_datetime()." and ".$req->extract_date('alarm2_date')." > ".$req->current_datetime()." and status <> 'END') or ";
                        }

                    }
                    else
                    {
                        if ($_REQUEST['status_chosen'][$get_i]=="REL2")
                        {
                            if ($_SESSION['config']['databasetype'] == 'ORACLE')
                            {
                                $where_request .="( ".$req->current_datetime()." >= alarm2_date and status <> 'END') or ";
                            }
                            else
                            {
                                $where_request .="( ".$req->current_datetime()." >= ".$req->extract_date('alarm2_date')."  and status <> 'END') or ";
                            }

                        }
                        elseif ($_REQUEST['status_chosen'][$get_i]=="LATE")
                        {
                            if ($_SESSION['config']['databasetype'] == 'ORACLE')
                            {
                                $where_request .="( process_limit_date is not null and ".$req->current_datetime()." > process_limit_date  and status <> 'END') or ";
                            }
                            else
                            {
                                $where_request .="( process_limit_date is not null and ".$req->current_datetime()." > ".$req->extract_date('process_limit_date')."  and status <> 'END') or ";
                            }

                        }
                        else
                        {
                            $where_request .= " ( status = '".$func->protect_string_db($_REQUEST['status_chosen'][$get_i])."') or ";
                        }
                    }
                }
                $where_request = preg_replace("/or $/", "", $where_request);
                $json_txt = substr($json_txt, 0, -1);
                $where_request .=") and ";
                $json_txt .= '],';
            }
            // ANSWER TYPE BITMASK
            /**
             * Answer type bitmask
             * 0 0 0 0 0 0
             * | | | | | |_ Simple Mail
             * | | | | |___ Registered mail
             * | | | |_____ Direct Contact
             * | | |_______ Email
             * | |_________ Fax
             * |___________ Other Answer
             **/
            elseif ($tab_id_fields[$j] == 'AR' && !empty($_REQUEST['AR']))
            {
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " answer_type_bitmask ilike '____1_' AND ";
                }
                else
                {
                    $where_request .= " answer_type_bitmask like '____1_' AND ";
                }
                $json_txt .= " 'AR' : ['".addslashes(trim($_REQUEST['AR']))."'],";
            }
            elseif ($tab_id_fields[$j] == 'fax' && !empty($_REQUEST['fax']))
            {
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " answer_type_bitmask ilike '_1____' AND ";
                }
                else
                {
                    $where_request .= " answer_type_bitmask like '_1____' AND ";
                }
                $json_txt .= " 'fax' : ['".addslashes(trim($_REQUEST['fax']))."'],";
            }
            elseif ($tab_id_fields[$j] == 'courriel' && !empty($_REQUEST['courriel']))
            {
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " answer_type_bitmask ilike '__1___' AND ";
                }
                else
                {
                    $where_request .= " answer_type_bitmask like '__1___' AND ";
                }
                $json_txt .= " 'courriel' : ['".addslashes(trim($_REQUEST['courriel']))."'],";
            }
            elseif ($tab_id_fields[$j] == 'autre' && !empty($_REQUEST['autre']))
            {
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " answer_type_bitmask ilike '1_____' AND ";
                }
                else
                {
                    $where_request .= " answer_type_bitmask like '1_____' AND ";
                }
                $json_txt .= " 'autre' : ['".addslashes(trim($_REQUEST['autre']))."'],";
            }
            elseif ($tab_id_fields[$j] == 'direct' && !empty($_REQUEST['direct']))
            {
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " answer_type_bitmask ilike '___1__'  AND ";
                }
                else
                {
                    $where_request .= " answer_type_bitmask like '___1__' AND ";
                }
                $json_txt .= " 'direct' : ['".addslashes(trim($_REQUEST['direct']))."'],";
            }
            elseif ($tab_id_fields[$j] == 'simple_mail' && !empty($_REQUEST['simple_mail']))
            {
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " answer_type_bitmask ilike '_____1' AND ";
                }
                else
                {
                    $where_request .= " answer_type_bitmask like '_____1' AND ";
                }
                $json_txt .= " 'simple_mail' : ['".addslashes(trim($_REQUEST['simple_mail']))."'],";
            }
            elseif ($tab_id_fields[$j] == 'norep' && !empty($_REQUEST['norep']))
            {
                if ($_SESSION['config']['databasetype'] == "POSTGRESQL")
                {
                    $where_request .= " answer_type_bitmask = '000000' AND ";
                }
                else
                {
                    $where_request .= " answer_type_bitmask = '000000' AND ";
                }
                $json_txt .= " 'norep' : ['".addslashes(trim($_REQUEST['norep']))."'],";
            }
            // MAIL CATEGORY
            elseif ($tab_id_fields[$j] == 'category' && !empty($_REQUEST['category']))
            {
                $where_request .= " category_id = '".$func->protect_string_db($_REQUEST['category'])."' AND ";
                $json_txt .= "'category' : ['".addslashes($_REQUEST['category'])."'],";
            } elseif (isset($_REQUEST['baskets_clause'])
                && $tab_id_fields[$j] == 'baskets_clause_true'
                && $_REQUEST['baskets_clause'] == "true"
            ) {
                for ($ind_bask = 0; $ind_bask < count($_SESSION['user']['baskets']); $ind_bask++)
                {
                    if (isset($_SESSION['user']['baskets'][$ind_bask]['clause']) && trim($_SESSION['user']['baskets'][$ind_bask]['clause']) <> '')
                    {
                        $_SESSION['searching']['comp_query'] .= ' or ('.$_SESSION['user']['baskets'][$ind_bask]['clause'].')';
                    }
                }
                $_SESSION['searching']['comp_query'] = preg_replace('/^ or/', '', $_SESSION['searching']['comp_query']);
                $baskets_clause = ($_REQUEST['baskets_clause']);
                $json_txt .= " 'baskets_clause_true' : ['true'],";
            }

            elseif ( $tab_id_fields[$j] == 'baskets_clause_false'  && $_REQUEST['baskets_clause'] == "false" )
            {
                $baskets_clause  = "false";
                $json_txt .= "'baskets_clause_false' : ['false'],";
            }
            // ADMISSION DATE : FROM
            elseif ($tab_id_fields[$j] == 'admission_date_from' && !empty($_REQUEST['admission_date_from']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['admission_date_from'])==false )
                {
                    $_SESSION['error'] .= _WRONG_DATE_FORMAT.' : '.$_REQUEST['admission_date_from'];
                }
                else
                {
                    if ($_SESSION['config']['databasetype'] == 'ORACLE')
                    {
                        $where_request .= " (admission_date >= to_date('".$func->format_date_db($_REQUEST['admission_date_from'])."', 'DD/MM/YYYY') ) and ";
                    }
                    else
                    {
                        $where_request .= " (".$req->extract_date("admission_date")." >= '".$func->format_date_db($_REQUEST['admission_date_from'])."') and ";
                    }
                    $json_txt .= " 'admission_date_from' : ['".trim($_REQUEST['admission_date_from'])."'],";
                }
            }
            // ADMISSION DATE : TO
            elseif ($tab_id_fields[$j] == 'admission_date_to' && !empty($_REQUEST['admission_date_to']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['admission_date_to'])==false )
                {
                    $_SESSION['error'] .= _WRONG_DATE_FORMAT.' : '.$_REQUEST['admission_date_to'];
                }
                else
                {
                    if ($_SESSION['config']['databasetype'] == 'ORACLE')
                    {
                        $where_request .= " (admission_date <= to_date('".$func->format_date_db($_REQUEST['admission_date_to'])."', 'DD/MM/YYYY') ) and ";
                    }
                    else
                    {
                        $where_request .= " (".$req->extract_date("admission_date")." <= '".$func->format_date_db($_REQUEST['admission_date_to'])."') and ";
                    }

                    $json_txt .= " 'admission_date_to' : ['".trim($_REQUEST['admission_date_to'])."'],";
                }
            }
            // DOC DATE : FROM
            elseif ($tab_id_fields[$j] == 'doc_date_from' && !empty($_REQUEST['doc_date_from']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['doc_date_from'])==false )
                {
                    $_SESSION['error'] .= _WRONG_DATE_FORMAT.' : '.$_REQUEST['doc_date_from'];
                }
                else
                {
                    if ($_SESSION['config']['databasetype'] == 'ORACLE')
                    {
                        $where_request .= " (doc_date >= to_date('".$func->format_date_db($_REQUEST['doc_date_from'])."', 'DD/MM/YYYY') ) and ";
                    }
                    else
                    {
                        $where_request .= " (".$req->extract_date("doc_date")." >= '".$func->format_date_db($_REQUEST['doc_date_from'])."') and ";
                    }
                    $json_txt .= " 'doc_date_from' : ['".trim($_REQUEST['doc_date_from'])."'],";
                }
            }
            // DOC DATE : TO
            elseif ($tab_id_fields[$j] == 'doc_date_to' && !empty($_REQUEST['doc_date_to']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['doc_date_to'])==false )
                {
                    $_SESSION['error'] .= _WRONG_DATE_FORMAT.' : '.$_REQUEST['doc_date_to'];
                }
                else
                {
                    if ($_SESSION['config']['databasetype'] == 'ORACLE')
                    {
                        $where_request .= " (doc_date <= to_date('".$func->format_date_db($_REQUEST['doc_date_to'])."', 'DD/MM/YYYY') ) and ";
                    }
                    else
                    {
                        $where_request .= " (".$req->extract_date("doc_date")." <= '".$func->format_date_db($_REQUEST['doc_date_to'])."') and ";
                    }

                    $json_txt .= " 'doc_date_to' : ['".trim($_REQUEST['doc_date_to'])."'],";
                }
            }
            // CONTACTS
            elseif ($tab_id_fields[$j] == 'contactid' && !empty($_REQUEST['contactid']))
            {
                $json_txt .= " 'contactid' : ['".addslashes(trim($_REQUEST['contactid']))."'],";
                //$where_request .= "res_id = ".$func->wash($_REQUEST['numged'], "num", _N_GED,"no")." and ";
                $contactTmp = str_replace(')', '', substr($_REQUEST['contactid'], strrpos($_REQUEST['contactid'],'(')+1));
                $find1 = strpos($contactTmp, ':');
                $find2 =  $find1 + 1;
                $contact_type = substr($contactTmp, 0, $find1);
                $contact_id = substr($contactTmp, $find2, strlen($contactTmp));
                if ($contact_type == "user")
                {
                    $where_request .= " (exp_user_id = '".$contact_id."' or dest_user_id = '".$contact_id."') and ";
                }
                elseif ($contact_type == "contact")
                {
                    $where_request .= " (exp_contact_id = '".$contact_id."' or dest_contact_id = '".$contact_id."') and ";
                }
            }
            else  // opt indexes check
            {
                //echo $tab_id_fields[$j].' : '.$_REQUEST[$tab_id_fields[$j]].'<br/>';
                $tmp = $type->search_checks($indexes, $tab_id_fields[$j], $_REQUEST[$tab_id_fields[$j]] );
                //$func->show_array($tmp);
                $json_txt .= $tmp['json_txt'];
                $where_request .= $tmp['where'];
            }
        }
        $json_txt = preg_replace('/,$/', '', $json_txt);
        $json_txt .= "}},";
    }
    $json_txt = preg_replace('/,$/', '', $json_txt);
}
//echo $where_request;exit;
$json_txt = preg_replace("/,$/", "", $json_txt);
$json_txt .= '}';
/*
echo $json_txt;
echo '<br/>'.$where_request;
exit();
*/

$_SESSION['current_search_query'] = $json_txt;
if (!empty($_SESSION['error'])) {
    if ($mode == 'normal') {
        $_SESSION['error_search'] = '<br /><div class="error">'._MUST_CORRECT_ERRORS.' : <br /><br /><strong>'.$_SESSION['error_search'].'<br /><a href="'.$_SESSION['config']['businessappurl'].'index.php?page=search_adv&dir=indexing_searching">'._CLICK_HERE_TO_CORRECT.'</a></strong></div>';
        ?>
        <script  type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?page=search_adv_error&dir=indexing_searching';?>';</script>
        <?php
    } else {
        $_SESSION['error_search'] = '<br /><div class="error">'._MUST_CORRECT_ERRORS.' : <br /><br /><strong>'.$_SESSION['error_search'].'<br /><a href="'.$_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=search_adv&mode='.$mode.'">'._CLICK_HERE_TO_CORRECT.'</a></strong></div>';
        ?>
        <script type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page=search_adv_error&mode='.$mode;?>';</script>
        <?php
    }
    exit();
} else {
    $where_request = trim($where_request);
    $_SESSION['searching']['where_request'] = $where_request;
}
if (isset($_REQUEST['specific_case'])
    && $_REQUEST['specific_case'] == "attach_to_case"
) {
    $page = 'list_results_mlb_frame';
    ?>
    <script type="text/javascript">window.top.location.href='<?php  echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=cases&page='.$page.'&searched_item='.$_REQUEST['searched_item'].'&searched_value='.$_REQUEST['searched_value'].'&template='.$_REQUEST['template'];?>';</script>
    <?php
    exit();
}

if (empty($_SESSION['error_search'])) {
    //specific string for search_adv cases
    $extend_link_case = "";
    if ($case_view == true) {
        $extend_link_case = "&template=group_case";
    }
    //##################
    $page = 'list_results_mlb';
    ?>
    <script type="text/javascript">window.top.location.href='<?php if ($mode == 'normal'){ echo $_SESSION['config']['businessappurl'].'index.php?page='.$page.'&dir=indexing_searching'.$extend_link_case;} elseif ($mode=='frame' || $mode == 'popup'){echo $_SESSION['config']['businessappurl'].'index.php?display=true&dir=indexing_searching&page='.$page.'&mode='.$mode.'&action_form='.$_REQUEST['action_form'].'&modulename='.$_REQUEST['modulename'];} if (isset($_REQUEST['nodetails'])){echo '&nodetails';}?>';</script>
    <?php
    exit();
}
