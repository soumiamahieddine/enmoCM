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
		$xmlconfig = simplexml_load_file($_SESSION['pathtomodules']."notes/xml/config.xml");
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