<?php

$_ENV['DEBUG'] = false;
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

}
?>
