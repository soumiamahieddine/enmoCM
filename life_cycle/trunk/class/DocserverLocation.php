<?php
try {
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."BaseObject.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}
class DocserverLocation extends BaseObject
{
	function __toString()
	{
		return $this->ipv4_filter; 
	}
}
?>
