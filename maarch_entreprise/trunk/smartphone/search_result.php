<?php
if (file_exists('../../../core/init.php')) {
    include_once '../../../core/init.php';
}
if (!isset($_SESSION['config']['coreurl'])) {
    header('location: ../../../');
}
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
require_once('core/class/class_db.php');
require_once('core/class/class_manage_status.php');
require_once('core/class/class_security.php');
$core = new core_tools();
$core->load_lang();
$func = new functions();
$sec = new security();
$cpRes = 0;
$_SESSION['collection_id_choice'] = $_REQUEST['collection'];
$view = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);
?>
<div id="about" title="<?php echo _SEARCH_RESULTS;?>" class="panel">
    <p id="logo" align="center">
    <?php
    set_include_path('apps' . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
        . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . PATH_SEPARATOR 
        . get_include_path()
    );
    require_once('Zend/Search/Lucene.php');
    Zend_Search_Lucene_Analysis_Analyzer::setDefault(
        new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive() // we need utf8 for accents
    );
    Zend_Search_Lucene_Search_QueryParser::setDefaultOperator(Zend_Search_Lucene_Search_QueryParser::B_AND);
    Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
    $path_to_lucene_index = $_SESSION['collections'][0]['path_to_lucene_index'];
    if (isset($_REQUEST['fulltext']) && !empty($_REQUEST['fulltext'])) {
        if (is_dir($path_to_lucene_index)) {
           if (!$func->isDirEmpty($path_to_lucene_index)) {
                $index = Zend_Search_Lucene::open($path_to_lucene_index);
                $hits = $index->find(urldecode($_REQUEST['fulltext']));
                $Liste_Ids = "0";
                foreach ($hits as $hit) {
                    if ($cptIds < 500) {
                        $Liste_Ids .= ", '". $hit->Id ."'";
                    } else {
                        break;
                    }
                    $cptIds ++;
                }
                $whereRequest .= ' res_id IN (' . $Liste_Ids . ') and ';
            }
        }else {
            $whereRequest .= " 1=-1 and ";
        }
    }
    if(isset($_REQUEST['subject']) && !empty($_REQUEST['subject'])) {
        $whereRequest .= " translate(
        LOWER(subject),
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        ) like translate(
            '%".strtolower($_REQUEST['subject'])."%',
            'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
            'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        ) and ";
        } 
    if(isset($_REQUEST['contact']) && !empty($_REQUEST['contact'])) {
        $whereRequest .= " exp_contact_id IN 
    (SELECT contact_id
    FROM contacts
    WHERE translate(
        LOWER(society),
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        ) like translate(
        '%".strtolower($_REQUEST['contact'])."%',
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        )
    OR translate(
        LOWER(lastname),
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        ) like translate(
        '%".strtolower($_REQUEST['contact'])."%',
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        )
    OR translate(
        LOWER(firstname),
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        ) like translate(
        '%".strtolower($_REQUEST['contact'])."%',
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        )
    )
OR 
dest_contact_id IN 
    (SELECT contact_id
    FROM contacts
    WHERE translate(
        LOWER(society),
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        ) like translate(
        '%".strtolower($_REQUEST['contact'])."%',
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        )
    OR translate(
        LOWER(lastname),
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        ) like translate(
        '%".strtolower($_REQUEST['contact'])."%',
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        )
    OR translate(
        LOWER(firstname),
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        ) like translate(
        '%".strtolower($_REQUEST['contact'])."%',
        'âãäåÁÂÃÄÅèééêëÈÉÉÊËìíîïìÌÍÎÏÌóôõöÒÓÔÕÖùúûüÙÚÛÜ',
        'aaaaAAAAAeeeeeEEEEEiiiiiIIIIIooooOOOOOuuuuUUUU'
        )
    ) and ";
    } 
    $statusObj = new manage_status();
    $status = $statusObj->get_not_searchable_status();
    $status_str = '';
    for ($i=0; $i<count($status);$i++) {
        $status_str .=  "'" . $status[$i]['ID'] . "',";
    }
    $status_str = preg_replace('/,$/', '', $status_str);
    $whereRequest.= ' status not in (' . $status_str . ') ';
    $whereSecurity = $sec->get_where_clause_from_coll_id(
        $_SESSION['collection_id_choice']
    );
    if (trim($whereRequest) <> '') {
        $whereRequest = '(' . $whereRequest . ') and (' . $whereSecurity . ')';
    }
    $whereRequest = str_replace(" ()", "(1=-1)", $whereRequest);
    $whereRequest = str_replace("and ()", "", $whereRequest);
    //echo 'where ' . $whereRequest;
    include_once('apps/' . $_SESSION['config']['app_id'] 
        . '/smartphone/list_result.php'
    );
    ?>
</div>
