<?php
try {
	require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."BaseObject.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}
class Cycle extends BaseObject
{
	function __toString()
	{
		return $this->cycle_mode; 
	}
}
?>
