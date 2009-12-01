<?php 
/**
* modules tools Class for notes
*
*  Contains all the functions to load modules tables for notes
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
* 
*/

class notes
{
	
	/**
	* Build Maarch module tables into sessions vars with a xml configuration file
	*/
	public function build_modules_tables()
	{
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."notes".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."notes".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		else
		{
			$path = "modules".DIRECTORY_SEPARATOR."notes".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		$xmlconfig = simplexml_load_file($path);
		foreach($xmlconfig->TABLENAME as $TABLENAME)
		{
			$_SESSION['tablename']['not_notes'] = (string) $TABLENAME->not_notes;
		}
		$HISTORY = $xmlconfig->HISTORY;
		$_SESSION['history']['noteadd'] = (string) $HISTORY->noteadd;
		$_SESSION['history']['noteup'] = (string) $HISTORY->noteup;
		$_SESSION['history']['notedel'] = (string) $HISTORY->notedel;
	}
	
	public function load_module_var_session()
	{
	
	}
}
?>
