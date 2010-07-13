<?php
try {
	require_once("core/class/BaseObject.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class Basket extends BaseObject {
	function __toString(){
		return $this->basket_name." (".$this->basket_id.")" ; 
	}
}
?>
