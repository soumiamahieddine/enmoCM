<?php
try {
	require_once("core/class/BaseObject.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}
class EntityObj extends BaseObject
 {	
	function __toString(){
		return $this->entity_label ; 
	}	
}
?>
