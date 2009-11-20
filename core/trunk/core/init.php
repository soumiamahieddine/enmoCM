<?php
session_name('maarch_entreprise');
session_start();

if(isset($_SESSION['config']['corepath']) && !empty($_SESSION['config']['corepath']))
{
	chdir($_SESSION['config']['corepath']);
}
//ini_set('error_reporting', E_ALL);
if (isset($_SESSION['custom_override_id']) && !empty($_SESSION['custom_override_id']) && isset($_SESSION['config']['corepath']) && !empty($_SESSION['config']['corepath']))
{
	$path = $_SESSION['config']['corepath']."custom".DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR;
	//echo $path;
	set_include_path( $path.PATH_SEPARATOR.$_SESSION['config']['corepath']);
}
elseif(isset($_SESSION['config']['corepath']) && !empty($_SESSION['config']['corepath']))
{
	set_include_path($_SESSION['config']['corepath']);	
}
