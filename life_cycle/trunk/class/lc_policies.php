<?php

try {
	require_once("core/class/BaseObject.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}
define("_CODE_SEPARATOR","/");

class lc_policies extends BaseObject {
	/**
	 *Print a viewable string to render the object.
	 * @return string Rendering of the object
	 */
	public function __toString(){
		return $this->net_domain;
	}
} 
?>
