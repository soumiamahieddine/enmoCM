<?php
/**
* File : select_folder.php
*
* Form to choose a folder (used in indexing process)
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$func = new functions();
$_SESSION['origin_folder'] = "select_folder";
$_SESSION['select_folder'] = true;
$_SESSION['res_folder'] = "";
$_SESSION['search_res_folder'] = "";
$_SESSION['folderSystemId'] = "";
$_SESSION['stringSearch'] = "";
//if(!empty($_REQUEST['project']) && empty($_REQUEST['market']))
if(!empty($_REQUEST['project']))
{
	$_SESSION['stringSearch'] = $_REQUEST['project'];
	/*if(substr($_REQUEST['project'], strlen($_REQUEST['project']) -1, strlen($_REQUEST['project'])) == ")")
	{
		$_SESSION['folderSystemId'] = str_replace(')', '', substr($_REQUEST['project'], strrpos($_REQUEST['project'],'(')+1));
	}*/
}
/*
if(!empty($_REQUEST['market']))
{
	if(substr($_REQUEST['market'], strlen($_REQUEST['market']) -1, strlen($_REQUEST['market'])) == ")")
	{
		$_SESSION['folderSystemId'] = str_replace(')', '', substr($_REQUEST['market'], strrpos($_REQUEST['market'],'(')+1));
	}
}
*/
if(isset($_REQUEST['matricule'])and !empty($_REQUEST['matricule']))
{
	$_SESSION['res_folder'] = "matricule";
	$_SESSION['search_res_folder'] =$_REQUEST['matricule'];
}
elseif( isset($_REQUEST['nom']) and !empty($_REQUEST['nom']))
{
	$_SESSION['res_folder'] = "nom";
	$_SESSION['search_res_folder'] = $_REQUEST['nom'];
}
//echo $_SESSION['stringSearch'];
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_SELECT_FOLDER_TITLE);
$time = $core_tools->get_session_time_expire();
if($_SESSION['origin'] == "qualify")
{
	//echo $_SESSION['res_id_to_qualify'];
	$tab = array();
	$select = array();
	$col ="";
	if(isset($_SESSION['collection_choice']) && !empty($_SESSION['collection_choice']))
	{
		$col = $_SESSION['collection_choice'];
	}
	else
	{
		$col = $_SESSION['collections'][0]['table'];
	}
	if($_SESSION['current_folder_id'] <> "")
	{
		$select[$col] = array();
		array_push($select[$col],"folders_system_id");
		$where = "res_id = ".$_SESSION['res_id_to_qualify'];
		$request = new request();
		$tab = $request->select($select, $where, "", $_SESSION['config']['databasetype']);
		//print_r($tab);
		for ($i=0;$i<count($tab);$i++)
		{
			for ($j=0;$j<count($tab[$i]);$j++)
			{
				foreach(array_keys($tab[$i][$j]) as $value)
				{
					if($tab[$i][$j][$value]=="folders_system_id")
					{
						$_SESSION['current_folder_id']= $tab[$i][$j]['value'];
					}
				}
			}
		}
	}
}
//echo "<br/>folder ".$_SESSION['current_folder_id'];
require_once($_SESSION['pathtomodules']."folder".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
$folder = new folder();
if($_SESSION['current_folder_id'] <> "" && $folder->is_folder_exists($_SESSION['current_folder_id']))
{
	$folder->load_folder($_SESSION['current_folder_id'], $_SESSION['tablename']['fold_folders']);
	$folder_data = $folder->get_folder_info();
	//$func->show_array($folder_data);
	if(file_exists($path_trombi."00".$folder_data['folder_id'].".jpg"))
	{
		$file_trombi = $path_trombi."00".$folder_data['folder_id'].".jpg";
	}
	else
	{
		$file_trombi = $path_trombi."manage_foldertypes.jpg";
	}
}
else
{
	$file_trombi = $path_trombi."manage_foldertypes.jpg";
}
?>
<body  onload="setTimeout(window.close, <?php  echo $time;?>*60*1000);">
<br/>
<br/>
<!--<img src="img/<?php  echo $file_trombi;?>" style="float:left; position:absolute; top:40px; left:10px" alt="" />-->
<div class="block">
	<form name="frm1" class="physicalform" action="<?php  echo $_SESSION['urltomodules'];?>indexing_searching/file_index.php">
		<b><?php  echo _SELECTED_FOLDER;?></b>
		<br/>
		<br/>
		<p>
			<label><?php  echo _FOLDERTYPE;?> :</label>
			<input type="text" value="<?php  echo $folder_data['foldertype_label'];?>" name="foldertype" readonly="readonly" class="readonly"/>
		</p>
		<p>
			<label><?php  echo _FOLDERID;?> :</label>
			<input type="text" value="<?php  echo $folder_data['folder_name'];?>" name="nom_view" readonly="readonly" class="readonly "/>
		</p>
		<p>
			<label><?php  echo _FOLDERNAME;?> :</label>
			<input type="text" value="<?php  echo $folder_data['subject'];?>" name="nom_view" readonly="readonly" class="readonly "/>
		</p>

	</form>
</div>
<div class="block_end">&nbsp;</div>
<div class="blank_space">&nbsp;</div>
<?php
if($_SESSION['origin'] <> "qualify")
{
?>
   <!-- <hr class="select_folder" />-->
   <div class="block">
    <b><?php  echo _SEARCH_FOLDER;?></b>
    <br/>
    <br/>
    <form name="select_folder" method="get" action="<?php  echo $_SESSION["urltomodules"];?>folder/select_folder.php" class="physicalform">
        <p>
            <label><?php  echo _PROJECT." / "._MARKET;?> :</label>
            <input type="text" name="project" id="project"/>
			<!--<div id="show_project" class="autocomplete"></div>-->
        </p>
        <!--<p>
            <label><?php  echo _MARKET;?> :</label>
			<input type="text" name="market" id="market"/>
			<div id="show_market" class="autocomplete"></div>
        </p>-->
		<p>
			<label>&nbsp;</label>
			<input type="submit" name="submit2" value="<?php  echo _SEARCH_FOLDER;?>" class="button"/>
		</p>
    </form>
	</div><div class="block_end">&nbsp;</div>
    <?php
    //if(isset($_SESSION['folderSystemId'])and !empty($_SESSION['folderSystemId']))
    if(isset($_SESSION['stringSearch'])and !empty($_SESSION['stringSearch']))
    {
		?>
		<div align="center">
			<iframe name="result_folder" src="<?php  echo $_SESSION["urltomodules"];?>folder/result_folder.php" frameborder="0" width="98%" height="1000" scrolling="no"></iframe>
		</div>
		<?php
    }
    else
    {
		?>
        <!--<div align="center"><input type="button" name="cancel" value="<?php  echo _CLOSE_WINDOW;?>" onclick="self.close();" class="button" /></div>   -->
		<?php
    }
    ?>  <?php
}
?>
</body>
</html>
