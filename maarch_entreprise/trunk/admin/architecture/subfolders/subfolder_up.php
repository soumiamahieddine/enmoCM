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
* @brief Modify a subfolder
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

//include('core/init.php');

//require_once("core/class/class_functions.php");
//require_once("core/class/class_db.php");
//require("core/class/class_core_tools.php");

$core_tools = new core_tools();
$core_tools->test_admin('admin_architecture', 'apps');
$core_tools->load_lang();

$db = new dbquery();
$db->connect();
$desc =  "";
$id = "";
$id_structure = "";

if(isset($_GET['id']) && !empty($_GET['id']))
{
	$id = $_GET['id'];
	$db->query("select doctypes_second_level_label, doctypes_first_level_id from ".$_SESSION['tablename']['doctypes_second_level']." where doctypes_second_level_id = ".$id);

	$res = $db->fetch_object();
	$desc = $db->show_string($res->doctypes_second_level_label);
	$id_structure = $res->doctypes_first_level_id;
}

$mode = "";
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
}
$erreur = "";
if( isset($_REQUEST['valid']))
{

	if(isset($_REQUEST['desc_sd']) && !empty($_REQUEST['desc_sd']))
	{

		$desc = $db->protect_string_db($_REQUEST['desc_sd']);

		$db->query("select * from ".$_SESSION['tablename']['doctypes_second_level']." where doctypes_second_level_label = '".$desc."' and enabled = 'Y'");
		//$db->show();
		if($db->nb_result() > 1)
		{
			$erreur .= _THE_SUBFOLDER." "._ALREADY_EXISTS.".";
		}
		else
		{
			if(isset($_REQUEST['structure']) && !empty($_REQUEST['structure']))
			{
				$structure = $_REQUEST['structure'];
				if($mode == "up")
				{

					if( isset($_REQUEST['ID_sd']) && !empty($_REQUEST['ID_sd']))
					{
						$id = $db->protect_string_db($_REQUEST['ID_sd']);
						$db->query("UPDATE ".$_SESSION['tablename']['doctypes_second_level']." set doctypes_second_level_label = '".$desc."', doctypes_first_level_id = ".$structure." where doctypes_second_level_id = ".$id."");
						if($_SESSION['history']['subfolderup'] == "true")
						{

							require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
							$hist = new history();
							$hist->add($_SESSION['tablename']['doctypes_second_level'], $id,"UP",_SUBFOLDER_MODIF." ".strtolower(_NUM).$id." (".$info.")", $_SESSION['config']['databasetype']);
						}
						$_SESSION['error'] .= _SUBFOLDER_MODIF." : ".$id."<br/>";
					}
					else
					{
						$erreur .= _SUBFOLDER_ID_PB.".";
					}
				}

				else
				{
					$desc = $db->protect_string_db($_REQUEST['desc_sd']);
					$db->query("INSERT INTO ".$_SESSION['tablename']['doctypes_second_level']." ( doctypes_second_level_label, doctypes_first_level_id) VALUES ( '".$desc."', ".$structure.")");
					$db->query("select doctypes_first_level_id from ".$_SESSION['tablename']['doctypes_second_level']." where doctypes_second_level_label =  '".$desc."' and doctypes_first_level_id= ".$structure);
					$res = $db->fetch_object();
					if($_SESSION['history']['subfolderadd'] == "true")
					{
						require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
						$hist = new history();
						$hist->add($_SESSION['tablename']['doctypes_second_level'], $res->doctypes_first_level_id,"ADD",_SUBFOLDER_ADDED." (".$desc.")", $_SESSION['config']['databasetype']);
					}

					$_SESSION['error'] .= _NEW_SUBFOLDER." : ".$desc."<br/>";
				}

				if(empty($erreur))
				{
					unset($_SESSION['m_admin']);
					?>
						<script language="javascript">window.opener.location.reload();self.close();</script>
					<?php
				}
			}
			else
			{
				$erreur .= _STRUCTURE_MANDATORY.'.<br/>';
			}
		}
	}
	else
	{
		$erreur .= _SUBFOLDER_DESC_MISSING.".<br/>";
	}
}

$core_tools->load_html();

if($mode == "up")
{
	$title = _SUBFOLDER_MODIF;
}
 else
{
	$title = _SUBFOLDER_CREATION;
}
$core_tools->load_header($title);
$time = $core_tools->get_session_time_expire();
?>
<body onLoad="setTimeout(window.close, <?php  echo $time;?>*60*1000);">

<div class="error">
<?php  echo $erreur;
	$erreur = "";
?>
</div>
<h2 class="tit"> &nbsp;<img src="<?php  echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/manage_structures_b.gif" alt="" valign="center"/> <?php  if($mode == "up"){ echo _SUBFOLDER_MODIF;} else{ echo _SUBFOLDER_CREATION;}?></h2>
<div class="block">
<br/>
<form method="post" name="modif" id="modif" class="forms" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=subfolder_up">
	<input type="hidden" name="display" value="true" />
    <input type="hidden" name="page" value="subfolder_up" />
	<?php  if ($mode == "up")
	{ ?>
	<p>
    	<label><?php  echo _ID.' '._SUBFOLDER;?>	</label>
		<input type="text" name="ID_sd"  value="<?php  echo $id; ?>" readonly="readonly" class="readonly" />
	</p>
    <p>&nbsp;</p>
	<?php  } ?>
	<p>
    	<label><?php  echo _DESC.' '._SUBFOLDER;?></label>
		<input type="text" name="desc_sd" value="<?php  echo $desc; ?>"   /> </td>
	</p>
    <p>&nbsp;</p>
	<p>
		<label><?php  echo _ATTACH_STRUCTURE;?> :</label>

			<select name="structure" >
				<option value=""><?php  echo _CHOOSE_STRUCTURE;?></option>
				<?php 	for($i=0; $i < count($_SESSION['m_admin']['structures']); $i++)
					{
						?>
							<option value="<?php  echo $_SESSION['m_admin']['structures'][$i]['ID'];?>" <?php  if ($id_structure == $_SESSION['m_admin']['structures'][$i]['ID']){ echo 'selected="selected"'; }?>><?php  echo $_SESSION['m_admin']['structures'][$i]['LABEL'];?></option>
						<?php
					}
				?>
			</select>
	</p>
	<p class="buttons">
    	<input type="submit" class="button" name="valid" value="<?php  echo _VALIDATE;?>" />
        <input type="button" class="button" name="cancel" value="<?php  echo _CANCEL;?>" onClick="self.close();" />
    </p>
<input type="hidden" name="mode" value="<?php  echo $mode;?>"/>
</form>
</div>
<div class="block_end">&nbsp;</div>
</body>
</html>
