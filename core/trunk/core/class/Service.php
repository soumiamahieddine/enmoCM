<?php
try {
	require_once("core/class/BaseObject.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class Service extends BaseObject{

	public function __toString(){
		return $this->name ; 
	}
}
?>
