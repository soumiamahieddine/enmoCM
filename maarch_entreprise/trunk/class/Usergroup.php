<?php
try {
	require_once("core/class/BaseObject.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}
class Usergroup  extends BaseObject
{
	function __toString(){
		return $this->group_desc; 
	}
	
}
?>
