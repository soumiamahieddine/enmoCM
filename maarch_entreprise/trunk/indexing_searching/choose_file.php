<?php
/*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  Frame to choose a file to index
*
* @file choose_file.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/
include('core/init.php');

require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$func = new functions();
$db = new dbquery();
$db->connect();
$core_tools->load_html();
$core_tools->load_header();
?>
<body >
	<?php
	$_SESSION['upfile']['error'] = 0;
	if($_FILES['file']['error'] == 1)
	{
		$_SESSION['upfile']['error'] = $_FILES['file']['error'];
		if($_SESSION['upfile']['error'] == 1)
		{
			?>
	        <script language="javascript" type="text/javascript">
				var test = window.top.document.getElementById('file_iframe');
				if (test != null)
				{
					test.src = '<?php echo $_SESSION['config']['businessappurl'];?>indexing_searching/file_iframe.php';
				}
			</script>
	        <?php
		}
	}
	elseif(!empty($_FILES['file']['tmp_name']) && $_FILES['file']['error'] <> 1)
	{
		$extension = explode(".",$_FILES['file']['name']);
		$count_level = count($extension)-1;
		$the_ext = $extension[$count_level];
		//$md5 = md5_file($_FILES['file']['tmp_name']);
		if(!is_uploaded_file($_FILES['file']['tmp_name']))
		{
				$_SESSION['error'] = _FILE_NOT_SEND.". "._TRY_AGAIN.". "._MORE_INFOS." (<a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>)";
		}
		elseif(!@move_uploaded_file($_FILES['file']['tmp_name'], $_SESSION['config']['tmppath'].'tmp_file_'.$_SESSION['user']['UserId'].'.'.strtolower($the_ext)))
		{
			$_SESSION['error'] = _FILE_NOT_SEND.". "._TRY_AGAIN.". "._MORE_INFOS." (<a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>)";
		}
		else
		{
			$_SESSION['upfile']['size'] = $_FILES['file']['size'];
			$_SESSION['upfile']['mime'] = $_FILES['file']['type'];
			$_SESSION['upfile']['local_path'] = $_SESSION['config']['tmppath'].'tmp_file_'.$_SESSION['user']['UserId'].'.'.strtolower($the_ext);
			$_SESSION['upfile']['name'] = $_FILES['file']['name'];
			$_SESSION['upfile']['format'] = $the_ext;
			require_once("apps/".$_SESSION['businessapps'][0]['appid']."/class".DIRECTORY_SEPARATOR."class_indexing_searching_app.php");
			$is = new indexing_searching_app();
			$ext_ok = $is->is_filetype_allowed($the_ext);
			if($ext_ok == false)
			{
				$_SESSION['error'] = _WRONG_FILE_TYPE.".";
				$_SESSION['upfile'] = array();
			}
		}
		?>
		<script language="javascript" type="text/javascript">
		var test = window.top.document.getElementById('file_iframe');
			if (test != null)
			{
				test.src = '<?php echo $_SESSION['config']['businessappurl'];?>indexing_searching/file_iframe.php';
			}
		</script>
		<?php
	}
	?>
    <form name="select_file_form" id="select_file_form" method="get" enctype="multipart/form-data" action="<?php  echo $_SESSION['config']['businessappurl'];?>indexing_searching/choose_file.php" class="forms">
        <p>
               <label for="file" ><?php  echo _CHOOSE_FILE; ?> </label>
               <input type="file" name="file" id="file"  onchange="this.form.method = 'post';this.form.submit();" value="<?php  echo $_SESSION['file_path']; ?>" style="width:200px;margin-left:33px;" /><?php
		if(!empty($_SESSION['upfile']['local_path']) && empty($_SESSION['error'] ) )
			{
				?><img src="<?php  echo $_SESSION['config']['businessappurl'];?>img/picto_stat_enabled.gif" alt="" class="img_upload_doc" /><?php
				echo "<br/><center><small>"._DOWNLOADED_FILE." : ".$_SESSION['upfile']['name'];"</small></center><br/>";
			}
			else
			{
				?><img src="<?php  echo $_SESSION['config']['businessappurl'];?>img/picto_stat_disabled.gif" class="img_upload_doc" alt=""/><?php
			}
            ?>
        </p>
    </form>
</body>
</html>
