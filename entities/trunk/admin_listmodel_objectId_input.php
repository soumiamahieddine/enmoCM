<?php
# loadListmodelObjects
# AJAX script to list objects to be linked with list model

function asSelect(
    $items
) {
    $return = "<select id='objectId' style='width:300px;'>";
    
    foreach($items as $id => $label) {
        $return .= "<option value='".$id."'>".$id . ' - ' .$label."</option>";
    }
    $return .= "</select>";
    return $return;
}

require_once 'core/class/class_core_tools.php';
$core = new core_tools();
$core->load_lang();

require_once 'modules/entities/class/class_manage_listdiff.php';
$difflist = new diffusion_list();

switch($_REQUEST['objectType']) {
case 'entity_id':   
    require_once 'modules/entities/class/class_manage_entities.php';
    $ent = new entity();
    $entity_ids = $ent->get_all_entities_id_user();
    $entities = array();
    for($i=0, $l=count($entity_ids);
        $i<$l;
        $i++
    ) {
        $entity_id = substr($entity_ids[$i], 1, -1);
        $existinglist = 
            $difflist->get_listmodel(
                'entity_id',
                $entity_id
            );
        if(count($existinglist) == 0) {
            $entities[$entity_id] = $ent->getentitylabel($entity_id); 
        }
    }
    if(count($entities) > 0)
        echo asSelect($entities);
    else {   
        echo asSelect(array("" => _ALL_OBJECTS_ARE_LINKED));
    }    
    break;
    
case 'type_id':
    require_once 'core/class/class_db.php';
    require_once 'core/core_tables.php';
    $db = new dbquery();
    $db->connect();
    $db->query("SELECT type_id, description FROM  " . DOCTYPES_TABLE);
    while($doctype = $db->fetch_object()) {
        $type_id = $doctype->type_id;
        $existinglist = 
            $difflist->get_listmodel(
                'type_id',
                $type_id
            );
        if(count($existinglist) == 0) {
            $doctypes[$type_id] = $doctype->description; 
        }
    }
    if(count($doctypes) > 0)
        echo asSelect($doctypes);
    else    
        echo asSelect(array("" => _ALL_OBJECTS_ARE_LINKED));
    
    break;
    
case 'foldertype_id':
    require_once 'core/class/class_db.php';
    require_once 'modules/folder/folder_tables.php';
    $db = new dbquery();
    $db->connect();
    $db->query("SELECT foldertype_id, foldertype_label FROM  " . FOLD_FOLDERTYPES_TABLE);
    while($foldertype = $db->fetch_object()) {
        $foldertype_id = $foldertype->foldertype_id;
        $existinglist = 
            $difflist->get_listmodel(
                'foldertype_id',
                $foldertype_id
            );
        if(count($existinglist) == 0) {
            $foldertypes[$foldertype_id] = $foldertype->foldertype_label; 
        }
    }
    if(count($foldertypes) > 0)
        echo asSelect($foldertypes);
    else    
        echo asSelect(array("" => _ALL_OBJECTS_ARE_LINKED));
    break;
    
case 'user_defined_id':
default:
    echo "<input type='text' id='objectId' style='width:300px;' />";
    break;
}

