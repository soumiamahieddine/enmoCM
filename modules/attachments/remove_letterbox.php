<?php
/**
 *​ ​Copyright​ ​Maarch​ since ​2008​ under licence ​GPLv3.
 *​ ​See​ LICENCE​.​txt file at the root folder ​for​ more details.
 *​ ​This​ file ​is​ part of ​Maarch​ software.
 *
 */


use \Attachments\Models\ReconciliationModel;

$core = new core_tools();
$core->test_user();
$db = new Database();

// Variable declaration
$res_id = $_SESSION['doc_id'];
$res_id_master = $_SESSION['stockCheckbox'];
$letterboxTable = $_SESSION['tablename']['reconciliation']['letterbox'];
$attachmentTable = $_SESSION['tablename']['reconciliation']['attachment'];
$delete_response_project = $_SESSION['modules_loaded']['attachments']['reconciliation']['delete_response_project'];
$close_incoming = $_SESSION['modules_loaded']['attachments']['reconciliation']['close_incoming'];

// Modification of the incoming document, as deleted
$delMail = ReconciliationModel::updateReconciliation([
    'set'       => ['status' => 'DEL'],
    'where'     => ['res_id = (?)'],
    'data'      => [$res_id],
    'table'     => $letterboxTable
]);

$tabFormValues = $_SESSION['modules_loaded']['attachments']['reconciliation']['tabFormValues'];

// Deletion of the response project, with his chrono number and the res_id_master
if($delete_response_project == 'true'){
    $delProject = ReconciliationModel::updateReconciliation([
        'set'       => ['status' => 'DEL'],
        'where'     => ["res_id_master = (?) AND identifier = (?) AND status NOT IN ('DEL','TMP') AND attachment_type = 'response_project'"],
        'data'      => [$res_id_master[0], $tabFormValues['chrono_number']],
        'table'     => $attachmentTable
    ]);
}

// End the incoming mail after the reconciliation of the attachment
if($close_incoming == 'true' && $tabFormValues['close_incoming_mail'] == 'true'){
	for($i = 0; $i < count($res_id_master); $i++){
	    $queryClose = ReconciliationModel::updateReconciliation([
            'set'       => ['status' => 'END'],
            'where'     => ['res_id = (?)'],
            'data'      => [$res_id_master[$i]],
            'table'     => $letterboxTable
        ]);
	}
}