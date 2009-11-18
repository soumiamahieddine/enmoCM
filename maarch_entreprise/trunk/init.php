<?php
/**
* core tools Class
*
*  Contains all the functions to load core and others
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author Loïc Vinet  <dev@maarch.org>
*
*/

session_name('PeopleBox');
session_start();

if (isset($_SESSION['high_layer_id']) && !empty($_SESSION['high_layer_id']) )
{
	$path = $_SESSION['config']['corepath']."clients".DIRECTORY_SEPARATOR.$_SESSION['high_layer_id'].DIRECTORY_SEPARATOR;
	//echo $path;
	chdir($_SESSION['config']['corepath']);
	set_include_path( $path.PATH_SEPARATOR.$_SESSION['config']['corepath']);
}


