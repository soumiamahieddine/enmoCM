<?php
$core = new core_tools();
//here we loading the lang vars
$core->load_lang();
$core->test_service('manage_attachments', 'attachments');

$func = new functions();

if (empty($_SESSION['collection_id_choice'])) {
    $_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
}

if (isset($_REQUEST['resId']) && $_REQUEST['resId'] <> '') {
   $resId = $_REQUEST['resId'];
   $_SESSION['doc_id'] = $resId;
} else {
    $resId = $_SESSION['doc_id'];
}

$viewOnly = false;
if (isset($_REQUEST['view_only'])) {
    $viewOnly = true;
}
require_once 'core/class/class_request.php';
require_once 'apps/' . $_SESSION['config']['app_id']
    . '/class/class_list_show.php';
require_once 'modules/attachments/attachments_tables.php';
$func = new functions();

$select[RES_ATTACHMENTS_TABLE] = array();
array_push(
    $select[RES_ATTACHMENTS_TABLE], 'res_id', 'typist', 'creation_date', 'title', 'format', 'identifier'
);

$where = " res_id_master = " . $resId  . " and coll_id ='"
       . $_SESSION['collection_id_choice'] . "' and status <> 'DEL'";
$request = new request;
$attachArr = $request->select(
    $select, $where, 'order by res_id desc', $_SESSION['config']['databasetype'], '500'
);
//$request->show_array($attachArr);

for ($i = 0; $i < count($attachArr); $i ++) {
    //$modifyValue = false;
    for ($j = 0; $j < count($attachArr[$i]); $j ++) {
        foreach (array_keys($attachArr[$i][$j]) as $value) {
            if (isset($attachArr[$i][$j][$value]) && $attachArr[$i][$j][$value] == 'res_id') {
                $attachArr[$i][$j]['res_id'] = $attachArr[$i][$j]['value'];
                $attachArr[$i][$j]['label'] = _ID;
                $attachArr[$i][$j]['size'] = '18';
                $attachArr[$i][$j]['label_align'] = 'left';
                $attachArr[$i][$j]['align'] = 'left';
                $attachArr[$i][$j]['valign'] = 'bottom';
                $attachArr[$i][$j]['show'] = false;
            }
            if (isset($attachArr[$i][$j][$value]) && $attachArr[$i][$j][$value] == 'typist') {
                $attachArr[$i][$j]['typist'] = $attachArr[$i][$j]['value'];
                $attachArr[$i][$j]['label'] = _TYPIST;
                $attachArr[$i][$j]['size'] = '30';
                $attachArr[$i][$j]['label_align'] = 'left';
                $attachArr[$i][$j]['align'] = 'left';
                $attachArr[$i][$j]['valign'] = 'bottom';
                $attachArr[$i][$j]['show'] = true;
            }
            if (isset($attachArr[$i][$j][$value]) && $attachArr[$i][$j][$value] == 'title') {
                $attachArr[$i][$j]['title'] = $attachArr[$i][$j]['value'];
                $attachArr[$i][$j]['label'] = _TITLE;
                $attachArr[$i][$j]['size'] = '30';
                $attachArr[$i][$j]['label_align'] = 'left';
                $attachArr[$i][$j]['align'] = 'left';
                $attachArr[$i][$j]['valign'] = 'bottom';
                $attachArr[$i][$j]['show'] = true;
            }
            if (isset($attachArr[$i][$j][$value]) && $attachArr[$i][$j][$value] == 'creation_date') {
                $attachArr[$i][$j]['value'] = $request->format_date_db(
                    $attachArr[$i][$j]['value'], true
                );
                $attachArr[$i][$j]['creation_date'] = $attachArr[$i][$j]['value'];
                $attachArr[$i][$j]['label'] = _DATE;
                $attachArr[$i][$j]['size'] = '30';
                $attachArr[$i][$j]['label_align'] = 'left';
                $attachArr[$i][$j]['align'] = 'left';
                $attachArr[$i][$j]['valign'] = 'bottom';
                $attachArr[$i][$j]['show'] = true;
            }
            if (isset($attachArr[$i][$j][$value]) && $attachArr[$i][$j][$value] == 'format') {
                $attachArr[$i][$j]['value'] = $request->show_string(
                    $attachArr[$i][$j]['value']
                );
                $attachArr[$i][$j]['format'] = $attachArr[$i][$j]['value'];
                $attachArr[$i][$j]['label'] = _FORMAT;
                $attachArr[$i][$j]['size'] = '5';
                $attachArr[$i][$j]['label_align'] = 'left';
                $attachArr[$i][$j]['align'] = 'left';
                $attachArr[$i][$j]['valign'] = 'bottom';
                $attachArr[$i][$j]['show'] = true;

                if (isset($attachArr[$i][$j][$value]) && $attachArr[$i][$j]['value'] == 'maarch') {
                    //$modifyValue = true;
                }
            }
        }
    }
    if (! $viewOnly || (!isset($_SESSION['current_basket']['id']) && $core->test_service('edit_attachments_from_detail', 'attachments', false)) || isset($_SESSION['current_basket']['id'])) {
        $tmp = array(
            'column' => 'modify_item',
            'value' => true,
            'label' => _MODIFY,
            'size' => '22',
            'label_align' => 'right',
            'align' => 'center',
            'valign' => 'bottom',
            'show' => false,
        );
        array_push($attachArr[$i], $tmp);

        $tmp2 = array(
            'column' => 'delete_item',
            'value' => true,
            'label' => _DELETE,
            'size' => '22',
            'label_align' => 'right',
            'align' => 'center',
            'valign' => 'bottom',
            'show' => false,
        );
        array_push($attachArr[$i], $tmp2);
    }
}

//$request->show_array($attachArr);
//here we loading the html
$core->load_html();
//here we building the header
$core->load_header('', true, false);
$mode = 'small';
if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'normal') {
    $mode = 'normal';
}

?>
<body <?php
if ($mode == 'small') {
    echo 'id="iframe"';
}
?>>
 <?php
$listAttach = new list_show();

$usedCss = 'listingsmall';
if ($mode == 'normal') {
    $usedCss = 'listing spec detailtabricatordebug';
}
$listAttach->list_simple(
    $attachArr, count($attachArr), '', 'res_id', 'res_id', true,
    $_SESSION['config']['businessappurl'] . 'index.php?display=true'
    . '&module=attachments&page=view_attachment', $usedCss,
    $_SESSION['config']['businessappurl'] . 'index.php?display=true'
    . '&module=attachments&page=update_attachments&mode=up&collId=' 
        . $_SESSION['collection_id_choice'], 1024, 768,
    $_SESSION['config']['businessappurl'] . 'index.php?display=true'
    . '&module=attachments&page=del_attachment'
);
$core->load_js();
?></body>
</html>
