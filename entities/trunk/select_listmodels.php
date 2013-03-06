<?php

// AJAX 
// Loads a list of listmodels onto a html structure
// >>> listmodel_type : type of list (listmodels.object_type)
// >>> return type : type of list to return [select | ul]
require_once 'modules/entities/class/class_manage_listdiff.php';
$difflist = new diffusion_list();

$objectType = $_REQUEST['objectType'];
$collId = $_REQUEST['collId'];
$returnElementType = $_REQUEST['returnElementType'];

$listmodels = $difflist->select_listmodels($objectType, $collId);
$l = count($listmodels);

$return = "";

switch($returnElementType) {
case 'select':
    for($i=0; $i<$l; $i++) {
        $listmodel = $listmodels[$i];
        $return .= "<option value='".$listmodel['object_id']."' >".$listmodel['description']."</option>";
    }
    break;
    
case 'list':
    for($i=0; $i<$l; $i++) {
        $listmodel = $listmodels[$i];
        $return .= "<li id='".$listmodel['object_id']."'>".$listmodel['description']."</li>";
    }
    break;
}

echo $return;
