<?php 
/**
* modules tools Class for attachments
*
*  Contains all the functions to load modules tables for attachments
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Claire Figueras  <dev@maarch.org>
* 
*/

class attachments
{
	
	/**
	* Build Maarch module tables into sessions vars with a xml configuration file
	*/
	public function build_modules_tables()
	{
		$xmlconfig = simplexml_load_file("modules/attachments/xml/config.xml");
		foreach($xmlconfig->TABLENAME as $TABLENAME)
		{
			$_SESSION['tablename']['attach_res_attachments'] = (string) $TABLENAME->attach_res_attachments;
		}
		$HISTORY = $xmlconfig->HISTORY;
		$_SESSION['history']['attachadd'] = (string) $HISTORY->attachadd;
		$_SESSION['history']['attachup'] = (string) $HISTORY->attachup;
		$_SESSION['history']['attachdel'] = (string) $HISTORY->attachdel;
		$_SESSION['history']['attachview'] = (string) $HISTORY->attachview;
	}
	
	public function load_module_var_session()
	{
	
	}
}
?>
