<?php 
/**
* File : delete_popup.php
*
* Delete a folder when allowed
*
* @package  Maarch PeopleBox 1.0
* @version 1.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox'); 
session_start();

require($_SESSION['pathtocoreclass']."class_functions.php");
require($_SESSION['pathtocoreclass']."class_db.php");
require($_SESSION['pathtocoreclass']."class_core_tools.php");

if(isset($_REQUEST['id_value']) && !empty($_REQUEST['id_value']))
{
	$folders_table = $_SESSION['tablename']['fold_folders'];
	//$res_table = $_SESSION['ressources'][0]['tablename'];
	$db = new dbquery();
	$db->connect();
	
	$folder_sys_id = $_REQUEST['id_value'];
	
	$db->query("UPDATE ".$folders_table." SET status='DEL' WHERE folders_system_id = ".$folder_sys_id."");
	if($_SESSION['history']['folderdel'] == "true")
	{
		$db->query('select folder_id from '.$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$folder_sys_id."");
		$res = $db->fetch_object();
		require_once($_SESSION['pathtocoreclass']."class_history.php");
		$users = new history();
		$users->add($_SESSION['tablename']['fold_folders'], $res->folder_id ,"DEL", _DEL_FOLDER_NUM.$res->folder_id, $_SESSION['config']['databasetype'],'folder');
	}
	for($i=0; $i< count($_SESSION['collections']);$i++)
	{
		if(isset($_SESSION['collections'][$i]['table']) && !empty($_SESSION['collections'][$i]['table']))
		{
			$db->query("update ".$_SESSION['collections'][$i]['table']." set status = 'DEL' where folders_system_id = ".$folder_sys_id);
		}
	}
	
	if($folder_sys_id == $_SESSION['current_folder_id'])
	{
		 $_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = '';
		 $_SESSION['current_folder_id'] = '';
		 $db->query("UPDATE ".$_SESSION['tablename']['users']." set custom_t1='' where custom_t1 = ".$folder_sys_id);
	}
	else
	{
		 $_SESSION['FOLDER']['SEARCH']['FOLDER_ID'] = '';
	}
		?>
        <script language="javascript" type="text/javascript">window.opener.top.location.reload();window.top.close();</script>
        <?php  			
		exit(); 	
}		
$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_service('delete_folder', 'folder');
//Verification des droits de suppression des documents:
$time = $core_tools->get_session_time_expire();
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_DELETE_FOLDER);	
	?>
		<body onLoad="setTimeout(window.close, <?php  echo $time;?>*60*1000);">
		<br/>
        <h2><?php  echo _DELETE_FOLDER;?></h2>
		 <br/>
		 <p><?php  echo _DELETE_FOLDER_NOTES1;?></p><br/>
		   <p><?php  echo _REALLY_DELETE_FOLDER;?>  </p>
		 <form name="del_folder1" method="post" action="delete_popup.php">
		      <div align="center">
				<input name="id_value" type="hidden" value="<?php  echo $_SESSION['current_folder_id']; ?>" />
		        <input type="submit" name="valid" value="<?php  echo _DELETE;?>" class="button" />
		      	 <input type="button" name="cancel" value="<?php  echo _CANCEL;?>" onClick="javascript:self.close();" class="button"  />
              </div>
		    </form>

		</body>
	</html>
