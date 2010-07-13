<?php

define ("_DEBUG", false);
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

try {
	require_once("core/class/Service.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

class ServiceControler
{

	public function get($service_id, $module_id)
	{
		if(empty($service_id))
		{
			// Nothing to get
			return null;
		} 
		
	}
	
}
?>
