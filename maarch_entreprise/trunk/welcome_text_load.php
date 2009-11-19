<?php 
/**
* Type : Include
* 
* Activated function to show welcome message file in Maarch LetterBox 3.0
* Compatible > MLB 2.6 
* Compatible > MLB 3.0 
* 
* @file
* @author  Loïc Vinet  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

/*

include('core/init.php');

*/

require_once("core/class/class_functions.php");
require_once("core/class/class_request.php");
require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
$core_tools->test_user();
//$core_tools->load_lang();
$core_tools->test_service('welcome_text_load', "apps");

function get_file_template($this_file)
{			
		//Ouverture du fichier
		$mail_trait = file_get_contents ($this_file);
		//Suppression des commantaires dans la page
		$mail_trait = preg_replace("/(<!--.*?-->)/s","", $mail_trait);	
		
		return $mail_trait; 

}
	
?>
<div id="welcome_box_left_text" >

	<?php echo get_file_template($_SESSION['config']['businessapppath']."welcome_file.html");?>
	<div class="blank_space">&nbsp;</div>
	
	
				
	
</div>
			
