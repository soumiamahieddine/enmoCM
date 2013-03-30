<?php
/*
*   Copyright 2008, 2013 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Advanced search form management
*
* @file search_adv_result_business.php
* @author Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching
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
$coll_id = 'business_coll';
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
    $core_tools->test_service('adv_search_business', 'apps');
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
    //$func->show_array($_REQUEST['meta']);exit;
    for ($i=0;$i<count($_REQUEST['meta']);$i++) {
        //echo $_REQUEST['meta'][$i]."<br>";
        $tab = explode('#', $_REQUEST['meta'][$i]);
        if ($tab[0] == 'welcome') {
            $tab[0] = 'multifield';
            $tab[2] = 'input_text';
        }
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
                $where_request .= "(lower(subject) LIKE lower('%".$func->protect_string_db($_REQUEST['multifield'])."%') "
                    ."or lower(identifier) LIKE lower('%".$func->protect_string_db($_REQUEST['multifield'])."%') "
                    ."or lower(doc_custom_t1) LIKE lower('%".$func->protect_string_db($_REQUEST['multifield'])."%')) ";                
                $where_request .=" and  ";
            }
            // NOTES
            elseif ($tab_id_fields[$j] == 'doc_notes' && !empty($_REQUEST['doc_notes']))
            {
                $json_txt .= " 'doc_notes' : ['".addslashes(trim($_REQUEST['doc_notes']))."'],";
                $s_doc_notes = $func->wash($_REQUEST['doc_notes'], "no", _NOTES,"no");
                $where_request .= " res_id in(select identifier from ".$_SESSION['tablename']['not_notes']
                    . " where lower(note_text) LIKE lower('%".$func->protect_string_db($s_doc_notes)."%')) and ";
            }
            // FOLDER : MARKET
            elseif ($tab_id_fields[$j] == 'market' && !empty($_REQUEST['market']))
            {
                $json_txt .= " 'market' : ['".addslashes(trim($_REQUEST['market']))."'],";
                $market = $func->wash($_REQUEST['market'], "no", _MARKET,"no");
                $where_request .= " (lower(folder_name) like lower('%".$func->protect_string_db($market)."%') or folder_id like '%"
                    . $func->protect_string_db($market)."%' ) and ";
            }
            // GED NUM
            elseif ($tab_id_fields[$j] == 'numged' && !empty($_REQUEST['numged']))
            {
                $json_txt .= " 'numged' : ['".addslashes(trim($_REQUEST['numged']))."'],";
                require_once('core/class/class_security.php');
                $sec = new security();
                $view = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
                if ($view <> '') {
                    $view .= '.';
                }
                $where_request .= $view . "res_id = ".$func->wash($_REQUEST['numged'], "num", _N_GED,"no")." and ";
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

                $where_request .= " (dest_user IN  ".$destinataire_chosen_tmp." or res_id in (select res_id from "
                    . $_SESSION['tablename']['ent_listinstance']." where item_id in ".$destinataire_chosen_tmp." and item_mode = 'dest')) ";
                $where_request .=" and  ";
                //echo $where_request;exit;
                $json_txt .= '],';
            }
            // SUBJECT
            elseif ($tab_id_fields[$j] == 'subject' && !empty($_REQUEST['subject']))
            {
                $json_txt .= " 'subject' : ['".addslashes(trim($_REQUEST['subject']))."'],";
                $where_request .= " lower(subject) like lower('%".$func->protect_string_db($_REQUEST['subject'])."%') and ";
            } 
            elseif ($tab_id_fields[$j] == 'fulltext' && !empty($_REQUEST['fulltext'])
            ) {
                // FULLTEXT
                $fulltext_request = $_REQUEST['fulltext'];
                $json_txt .= " 'fulltext' : ['" 
                    . addslashes(trim($_REQUEST['fulltext'])) . "'],";
                set_include_path('apps' . DIRECTORY_SEPARATOR 
                    . $_SESSION['config']['app_id'] 
                    . DIRECTORY_SEPARATOR . 'tools' 
                    . DIRECTORY_SEPARATOR . PATH_SEPARATOR . get_include_path()
                );
                require_once('Zend/Search/Lucene.php');
                Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                    new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive() // we need utf8 for accents
                );
                Zend_Search_Lucene_Search_QueryParser::setDefaultOperator(Zend_Search_Lucene_Search_QueryParser::B_AND);
                Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
                
                $_SESSION['search']['plain_text'] = $_REQUEST['fulltext'];

                $path_to_lucene_index = $_SESSION['collections'][1]['path_to_lucene_index'];
                if (is_dir($path_to_lucene_index))
                {
                    if (!$func->isDirEmpty($path_to_lucene_index)) {
                        $index = Zend_Search_Lucene::open($path_to_lucene_index);
                        $hits = $index->find(urldecode($fulltext_request));
                        $Liste_Ids = "0";
                        $cptIds = 0;
                        foreach ($hits as $hit) {
                            if ($cptIds < 500) {
                                $Liste_Ids .= ", '". $hit->Id ."'";
                            } else {
                                break;
                            }
                            $cptIds ++;
                        }
                        $where_request .= " res_id IN ($Liste_Ids) and ";
                    }
                } else {
                    $where_request .= " 1=-1 and ";
                }
            }
            // TAGS
            elseif ($tab_id_fields[$j] == 'tags_chosen' && !empty($_REQUEST['tags_chosen']))
            {
                include_once('modules/tags/tags_search.php');              
            }
            //WELCOME PAGE
            elseif ($tab_id_fields[$j] == 'welcome'  && (!empty($_REQUEST['welcome'])))
            {
                $welcome = $func->store_html($_REQUEST['welcome']);
                $json_txt .= "'multifield' : ['".addslashes(trim($welcome))."'],";
                if (is_numeric($_REQUEST['welcome']))
                {
                    $where_multifield_request .= "(res_id = ".$func->protect_string_db($_REQUEST['welcome'].") or ");
                }
                $where_multifield_request .= "(lower(subject) LIKE lower('%".$func->protect_string_db($_REQUEST['welcome'])."%') "
                    ."or lower(identifier) LIKE lower('%".$func->protect_string_db($_REQUEST['welcome'])."%') "
                    ."or lower(title) LIKE lower('%".$func->protect_string_db($_REQUEST['welcome'])."%')) ";

                $welcome = $func->store_html($_REQUEST['welcome']);
                set_include_path('apps' . DIRECTORY_SEPARATOR 
                    . $_SESSION['config']['app_id'] 
                    . DIRECTORY_SEPARATOR . 'tools' 
                    . DIRECTORY_SEPARATOR . PATH_SEPARATOR . get_include_path()
                );
                require_once('Zend/Search/Lucene.php');
                Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                    new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive() // we need utf8 for accents
                );
                $path_to_lucene_index = $_SESSION['collections'][1]['path_to_lucene_index'];
                if (is_dir($path_to_lucene_index))
                {
                    if (!$func->isDirEmpty($path_to_lucene_index)) {
                        $index = Zend_Search_Lucene::open($path_to_lucene_index);
                        $hits = $index->find($welcome);
                        $Liste_Ids = "0";
                        $cptIds = 0;
                        foreach ($hits as $hit) {
                            if ($cptIds < 500) {
                                $Liste_Ids .= ", '". $hit->Id ."'";
                            } else {
                                break;
                            }
                            $cptIds ++;
                        }
                        $where_request_welcome .= " res_id IN ($Liste_Ids) or ".$where_multifield_request. " and ";
                    }
                } else {
                    $where_request_welcome .= " ".$where_multifield_request." and ";
                }
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
            // CREATION DATE : FROM
            elseif ($tab_id_fields[$j] == 'creation_date_from' && !empty($_REQUEST['creation_date_from']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['creation_date_from'])==false )
                {
                    $_SESSION['error'] .= _WRONG_DATE_FORMAT.' : '.$_REQUEST['creation_date_from'];
                }
                else
                {
                    $where_request .= " (".$req->extract_date("creation_date")." >= '".$func->format_date_db($_REQUEST['creation_date_from'])."') and ";
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
            // CLOSING DATE : FROM (closing_date)
            elseif ($tab_id_fields[$j] == 'closing_date_from' && !empty($_REQUEST['closing_date_from']))
            {
                if ( preg_match($_ENV['date_pattern'],$_REQUEST['closing_date_from'])==false )
                {
                    $_SESSION['error'] .=  _WRONG_DATE_FORMAT.' : '.$_REQUEST['closing_date_from'];
                }
                else
                {
                    $where_request .= " (".$req->extract_date("closing_date")." >= '".$func->format_date_db($_REQUEST['closing_date_from'])."') and ";
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
                    $where_request .= " (".$req->extract_date("closing_date")." <= '".$func->format_date_db($_REQUEST['closing_date_to'])."') and ";
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
                    $where_request .= " (".$req->extract_date("process_limit_date")." >= '".$func->format_date_db($_REQUEST['process_limit_date_from'])."') and ";
                    $json_txt .= "'process_limit_date_from' : ['".trim($_REQUEST['process_limit_date_from'])."'],";
                }
            }
            // PROCESS LIMIT DATE : TO
            elseif ($tab_id_fields[$j] == 'process_limit_date_to' && !empty($_REQUEST['process_limit_date_to']))
            {
                if (preg_match($_ENV['date_pattern'],$_REQUEST['process_limit_date_to'])==false)
                {
                    $_SESSION['error'] = _WRONG_DATE_FORMAT.' : '.$_REQUEST['process_limit_date_to'];
                }
                else
                {
                    $where_request .= " (".$req->extract_date("process_limit_date")." <= '".$func->format_date_db($_REQUEST['process_limit_date_to'])."') and ";
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
                        $where_request .="( "
                            . $req->extract_date('alarm1_date')." <= "
                            . $req->current_datetime()." and "
                            . $req->extract_date('alarm2_date')." > ".$req->current_datetime()." and status <> 'END') or ";
                    }
                    else
                    {
                        if ($_REQUEST['status_chosen'][$get_i]=="REL2")
                        {
                            $where_request .="( ".$req->current_datetime()." >= ".$req->extract_date('alarm2_date')."  and status <> 'END') or ";
                        }
                        elseif ($_REQUEST['status_chosen'][$get_i]=="LATE")
                        {
                            $where_request .="(process_limit_date is not null and "
                                . $req->current_datetime()." > ".$req->extract_date('process_limit_date')."  and status <> 'END') or ";
                        }
                        else
                        {
                            $where_request .= " (status = '".$func->protect_string_db($_REQUEST['status_chosen'][$get_i])."') or ";
                        }
                    }
                }
                $where_request = preg_replace("/or $/", "", $where_request);
                $json_txt = substr($json_txt, 0, -1);
                $where_request .=") and ";
                $json_txt .= '],';
            }
            // CATEGORY
            elseif ($tab_id_fields[$j] == 'category' && !empty($_REQUEST['category']))
            {
                $where_request .= " category_id = '".$func->protect_string_db($_REQUEST['category'])."' AND ";
                $json_txt .= "'category' : ['".addslashes($_REQUEST['category'])."'],";
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
                    $where_request .= " (".$req->extract_date("doc_date")." >= '".$func->format_date_db($_REQUEST['doc_date_from'])."') and ";
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
                    $where_request .= " (".$req->extract_date("doc_date")." <= '".$func->format_date_db($_REQUEST['doc_date_to'])."') and ";
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
                $where_request .= " contact_id = '".$contact_id."' and ";
            }
            // TOTAL SUM : MIN
            elseif ($tab_id_fields[$j] == 'total_sum_min' && !empty($_REQUEST['total_sum_min'])) {
                if (!is_numeric($_REQUEST['total_sum_min'])) {
                    $_SESSION['error'] .= _WRONG_FORMAT .  ' ' . _TOTAL_SUM_MIN .' : ' . $_REQUEST['total_sum_min'];
                } else {
                    $where_request .= " (total_sum >= " . $_REQUEST['total_sum_min'] . ") and ";
                    $json_txt .= " 'total_sum_min' : ['".trim($_REQUEST['total_sum_min'])."'],";
                }
            }
            // TOTAL SUM : MAX
            elseif ($tab_id_fields[$j] == 'total_sum_max' && !empty($_REQUEST['total_sum_max'])) {
                if (!is_numeric($_REQUEST['total_sum_max'])) {
                    $_SESSION['error'] .= _WRONG_FORMAT .  ' ' . _TOTAL_SUM_MAX .' : ' . $_REQUEST['total_sum_max'];
                } else {
                    $where_request .= " (total_sum <= " . $_REQUEST['total_sum_max'] . ") and ";
                    $json_txt .= " 'total_sum_max' : ['".trim($_REQUEST['total_sum_max'])."'],";
                }
            }
            // SEARCH IN BASKETS
            else if ($tab_id_fields[$j] == 'baskets_clause' && !empty($_REQUEST['baskets_clause'])) {
                //$func->show_array($_REQUEST);exit;
                switch($_REQUEST['baskets_clause']) {
                case 'false':
                    $baskets_clause = "false";
                    $json_txt .= "'baskets_clause' : ['false'],";
                    break;
                case 'true':
                    for($ind_bask = 0; $ind_bask < count($_SESSION['user']['baskets']); $ind_bask++) {
                       if ($_SESSION['user']['baskets'][$ind_bask]['coll_id'] == $coll_id 
                        && $_SESSION['user']['baskets'][$ind_bask]['is_folder_basket'] == 'N') {
                            if(isset($_SESSION['user']['baskets'][$ind_bask]['clause']) && trim($_SESSION['user']['baskets'][$ind_bask]['clause']) <> '') {
                                $_SESSION['searching']['comp_query'] .= ' or ('.$_SESSION['user']['baskets'][$ind_bask]['clause'].')';
                            }
                         }
                    }
                    $_SESSION['searching']['comp_query'] = preg_replace('/^ or/', '', $_SESSION['searching']['comp_query']);
                    $baskets_clause = ($_REQUEST['baskets_clause']);
                    $json_txt .= " 'baskets_clause' : ['true'],";
                    break;
                default:
                    $json_txt .= " 'baskets_clause' : ['".addslashes(trim($_REQUEST['baskets_clause']))."'],";
                    for($ind_bask = 0; $ind_bask < count($_SESSION['user']['baskets']); $ind_bask++) {
                        if($_SESSION['user']['baskets'][$ind_bask]['id'] == $_REQUEST['baskets_clause']
                            && $_SESSION['user']['baskets'][$ind_bask]['is_folder_basket'] == 'N') {
                            if(isset($_SESSION['user']['baskets'][$ind_bask]['clause']) && trim($_SESSION['user']['baskets'][$ind_bask]['clause']) <> '') {
                                $where_request .= ' ' . $_SESSION['user']['baskets'][$ind_bask]['clause'] . ' and ' ;
                            } 
                        }
                    }
                }
            }
            else  // opt indexes check
            {
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
        $_SESSION['error_search'] = '<br /><div class="error">'
            . _MUST_CORRECT_ERRORS.' : <br /><br /><strong>' 
            . $_SESSION['error_search'].'<br /><a href="'
            . $_SESSION['config']['businessappurl'].'index.php?page=search_adv_business&dir=indexing_searching">'
            . _CLICK_HERE_TO_CORRECT.'</a></strong></div>';
        ?>
        <script  type="text/javascript">window.top.location.href='<?php  
            echo $_SESSION['config']['businessappurl']
                . 'index.php?page=search_adv_error_business&dir=indexing_searching';?>';</script>
        <?php
    } else {
        $_SESSION['error_search'] = '<br /><div class="error">'
            . _MUST_CORRECT_ERRORS.' : <br /><br /><strong>'
            . $_SESSION['error_search'].'<br /><a href="'
            . $_SESSION['config']['businessappurl']
            . 'index.php?display=true&dir=indexing_searching&page=search_adv_business&mode='
            . $mode.'">'._CLICK_HERE_TO_CORRECT.'</a></strong></div>';
        ?>
        <script type="text/javascript">window.top.location.href='<?php 
        echo $_SESSION['config']['businessappurl']
            . 'index.php?display=true&dir=indexing_searching&page=search_adv_error_business&mode='
            . $mode;?>';</script>
        <?php
    }
    exit();
} else {
    if ($where_request_welcome <> '') {
        $where_request_welcome = substr($where_request_welcome, 0, -4);
        $where_request .= '(' . $where_request_welcome . ') and ';
    }
    $where_request = trim($where_request);
    $_SESSION['searching']['where_request'] = $where_request;
}
if(!empty($_REQUEST['baskets_clause']) && $_REQUEST['baskets_clause'] != 'false' && $_REQUEST['baskets_clause'] != 'true') {
    ?>
    <script  type="text/javascript">window.top.location.href='<?php 
        echo $_SESSION['config']['businessappurl'] 
        . "index.php?page=view_baskets&module=basket&baskets="
        . $_REQUEST['baskets_clause']."&origin=searching";?>';</script>
    <?php
    exit();
}
if (empty($_SESSION['error_search'])) {
    //specific string for search_adv cases
    $extend_link_case = "";
    //##################
    $page = 'list_results_business';
    ?>
    <script type="text/javascript">window.top.location.href='<?php 
        if ($mode == 'normal'){ 
            echo $_SESSION['config']['businessappurl']
                . 'index.php?page='.$page.'&dir=indexing_searching&load'
                . $extend_link_case;
        } elseif ($mode=='frame' || $mode == 'popup'){
            echo $_SESSION['config']['businessappurl']
                . 'index.php?display=true&dir=indexing_searching&page='
                . $page.'&mode='.$mode.'&action_form='
                . $_REQUEST['action_form'].'&modulename='
                . $_REQUEST['modulename'];
        }
        if (isset($_REQUEST['nodetails'])){
            echo '&nodetails';
        }?>';</script>
    <?php
    exit();
}
