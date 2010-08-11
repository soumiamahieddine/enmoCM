<?php 
/**
*  Login Class
*
* Contains all pre-login function  
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Loïc Vinet  <dev@maarch.org>
* 
*/


class login extends functions
{
	
	function build_login_method()
	{
		
		$pathtoxmllogin = '';
		
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'login_method.xml'))
		{
			$pathtoxmllogin = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'login_method.xml';
		}
		else
		{
			$pathtoxmllogin = 'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'login_method.xml';
		}
		
		$login_method = array();
		
		$xmlconfig = simplexml_load_file($pathtoxmllogin);
		if (!$xmlconfig)
			exit();
			
			
		foreach($xmlconfig->METHOD as $METHOD)
		{
			$id = (string) $METHOD->ID;	
			$brut_label = (string) $METHOD->NAME;
			$scriptname = (string) $METHOD->SCRIPT;
			$activated = (string) $METHOD->ENABLED; 
			
			
			array_push($login_method, array("ID"=>$id, "BRUT_LABEL"=>$brut_label, "SCRIPTNAME"=>$scriptname, "ACTIVATED"=>$activated));
		}
		
		$_SESSION['login_method_memory'] = $login_method;
		return $login_method;
		
	}
	
	public function execute_login_script($array_method)
	{
		
		$displayed_title = false;
		$tmp_engine = 0;
		$_SESSION['login_method_bool'] = false;
		
		foreach($array_method as $only)
		{
			if ($only['ACTIVATED'] == 'true')
				$tmp_engine = $tmp_engine +1;
		}
		
		if($tmp_engine > 1)
		{
			$displayed_title = true;
			$_SESSION['login_method_bool'] = true;
		}	
		foreach ($array_method as $KEY)
		{
		
			if ($KEY['ACTIVATED'] == 'true')
			{
				if ($displayed_title == true)
					echo "<h3 align='right'>".constant($KEY['BRUT_LABEL'])."</h3>";
					 
				self::launch_login_method($KEY['SCRIPTNAME']);
				
				if ($displayed_title == true)
					echo "<hr/>";
				
			}
			
		}
		
	}
	
	
	private function launch_login_method($script_method)
	{
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.$script_method))
		{
			include($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.$script_method);
		}
		else
		{
			include('apps'.DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR.$script_method);
		}
		
	}
	
}
?>
