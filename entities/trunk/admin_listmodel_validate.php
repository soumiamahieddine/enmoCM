<?php
/*
$_SESSION[m_admin][entity][listmodel][objectType]
                                     [objectId]
                                     [dest]
                                     [*role*][users]
                                     [*role*][entities]
*/
require_once("modules/entities/class/class_manage_listdiff.php");
$difflist = new diffusion_list();

$difflist->save_listmodel(
    $_SESSION['m_admin']['entity']['listmodel'], 
    $collId = 'letterbox_coll',
    $listType = 'DOC', 
    $objectType = $_SESSION['m_admin']['entity']['listmodel_objectType'],
    $objectId = $_SESSION['m_admin']['entity']['listmodel_objectId']
);