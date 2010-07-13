<?php
try {
	require_once("core/class/BaseObject.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}
class User extends BaseObject
 {	
	function __toString(){
		return $this->lastname.", ".$this->firstname." (".$this->user_id.")" ; 
	}	
}
?>
