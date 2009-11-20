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
* @brief  Form to add a grant to a group, pop up page
*
* @file view.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

include('core/init.php');

require_once("core/class/class_functions.php");
require("core/class/class_core_tools.php");
require_once("core/class/class_db.php");
require_once("core/class/class_security.php");
$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_admin('admin_groups', 'apps');
$func = new functions();
$coll = '';
$coll_label = '';
$clause = '';
$comment = '';
$insert = '';
$update = '';
$delete = '';
$tabdiff=array();
$mode = "add";
$sec = new security();
$show_checkbox = true;

if(isset($_REQUEST['collection']) && !empty($_REQUEST['collection']))
{
	$mode = "up";
	for($i=0;$i< count($_SESSION['m_admin']['groups']['security']);$i++)
	{
		if($_SESSION['m_admin']['groups']['security'][$i]['COLL_ID'] == trim($_REQUEST['collection']))
		{
			$_SESSION['m_admin']['group']['coll_id'] = trim($_REQUEST['collection']);
			$ind = $sec->get_ind_collection($_SESSION['m_admin']['group']['coll_id']);
			$coll_label = $_SESSION['collections'][$ind]['label'];
			$clause = $func->show_string($_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE']);
			$comment = $_SESSION['m_admin']['groups']['security'][$i]['COMMENT'];
			if(!isset($_SESSION['collections'][$ind]['table']) || empty($_SESSION['collections'][$ind]['table']))
			{
				$show_checkbox = false;
			}
			else
			{
				$insert = $_SESSION['m_admin']['groups']['security'][$i]['CAN_INSERT'];
				$update = $_SESSION['m_admin']['groups']['security'][$i]['CAN_UPDATE'];
				$delete = $_SESSION['m_admin']['groups']['security'][$i]['CAN_DELETE'];
			}
		}
	}
	if($coll_label == "")
	{
		$ind = $sec->get_ind_collection($_SESSION['m_admin']['group']['coll_id']);
		$coll_label = $_SESSION['collections'][$ind]['label'];
		$mode = "add";
	}
}
else
{
	$show_checkbox = $_SESSION['m_admin']['group']['show_check'];
}
if($core_tools->is_module_loaded("basket"))
{
	$_SESSION['entities_choosen'] = array();
}
$_SESSION['doctypes_choosen'] = array();
if($_SESSION['m_admin']['mode'] == "up" && $_SESSION['m_admin']['init'] == true)
{
	$where = "";
	for($i=0;$i<count($_SESSION['m_admin']['groups']['security']);$i++)
	{
		if($_SESSION['m_admin']['groups']['security'][$i]['COLL_ID'] == $_SESSION['m_admin']['group']['coll_id'])
		{
			$_SESSION['m_admin']['groups']['coll_id'] = $_SESSION['m_admin']['groups']['security'][$i]['COLL_ID'];
			$_SESSION['m_admin']['groups']['where_clause'] = $func->show_string($_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE']);
		}
	}
	$where = $func->show_string(trim($_SESSION['m_admin']['groups']['where_clause']));
	if($core_tools->is_module_loaded("basket"))
	{
		$where = str_replace("DESTINATION IN (", "", $where);
	}
	$where = str_replace("TYPE_ID IN (", "", $where);
	$where = str_replace(")", "", $where);
	$where = str_replace("'", "", $where);
	if(preg_match("/,/", $where))
	{
		if($core_tools->is_module_loaded("basket"))
		{
			$where_init = explode(" AND ", $func->show_string($where));
			$_SESSION['entities_choosen'] = explode(",", $where_init[0]);
			$_SESSION['doctypes_choosen'] = explode(",", $where_init[1]);
			for($i=0; $i<count($_SESSION['entities_choosen']);$i++)
			{
				$_SESSION['entities_choosen'][$i] = trim($_SESSION['entities_choosen'][$i]);
			}
			for($j=0; $j<count($_SESSION['doctypes_choosen']);$j++)
			{
				$_SESSION['doctypes_choosen'][$j] = trim($_SESSION['doctypes_choosen'][$j]);
			}
		}
		else
		{
			$_SESSION['doctypes_choosen'] = explode(",", $where);
			for($j=0; $j<count($_SESSION['doctypes_choosen']);$j++)
			{
				$_SESSION['doctypes_choosen'][$j] = trim($_SESSION['doctypes_choosen'][$j]);
			}
		}
	}
	else
	{
		if($core_tools->is_module_loaded("basket"))
		{
			array_push($_SESSION['entities_choosen'], trim($where));
		}
	}

	$_SESSION['m_admin']['init'] = false;
}
if($core_tools->is_module_loaded("basket"))
{
	if($_SESSION['entities_choosen_where_clause'] == " DESTINATION IN ('')")
	{
		$_SESSION['entities_choosen_where_clause'] = "";
	}
}
if($_SESSION['doctypes_choosen_where_clause'] == " TYPE_ID IN ('')")
{
	$_SESSION['doctypes_choosen_where_clause'] = "";
}
if($_REQUEST['expertmode'] <> "true")
{
	$_SESSION['choosen_where_clause'] = $clause;
}
else
{
	if($core_tools->is_module_loaded("basket"))
	{
		if(trim($_SESSION['entities_choosen_where_clause']) <> "" and trim($_SESSION['doctypes_choosen_where_clause']) <> "")
		{
			$_SESSION['choosen_where_clause'] = stripslashes($_SESSION['entities_choosen_where_clause'])." AND ".stripslashes($_SESSION['doctypes_choosen_where_clause']);
		}
		elseif(trim($_SESSION['entities_choosen_where_clause']) <> "" and trim($_SESSION['doctypes_choosen_where_clause']) == "")
		{
			$_SESSION['choosen_where_clause'] = stripslashes($_SESSION['entities_choosen_where_clause']);
		}
		elseif(trim($_SESSION['entities_choosen_where_clause']) == "" and trim($_SESSION['doctypes_choosen_where_clause']) <> "")
		{
			$_SESSION['choosen_where_clause'] = stripslashes($_SESSION['doctypes_choosen_where_clause']);
		}
		elseif(trim($_SESSION['entities_choosen_where_clause']) == "" and trim($_SESSION['doctypes_choosen_where_clause']) == "")
		{
			$_SESSION['choosen_where_clause'] = "";
		}
	}
	else
	{
		if(trim($_SESSION['doctypes_choosen_where_clause']) <> "")
		{
			$_SESSION['choosen_where_clause'] = stripslashes($_SESSION['doctypes_choosen_where_clause']);
		}
		elseif(trim($_SESSION['doctypes_choosen_where_clause']) == "")
		{
			$_SESSION['choosen_where_clause'] = "";
		}
	}
}

//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header();
$time = $core_tools->get_session_time_expire();
?>
<body onload="setTimeout(window.close, <?php  echo $time;?>*60*1000);">

<h2 class="tit"><?php  echo _ADD_GRANT;?></h2>
<table  width="100%">
<tr>
<td>
<div class="popup_content">
<form name="addGrant" method="post" action="<?php  echo $_SESSION['config']['businessappurl']."/admin/groups/add_grant_table.php";?>" class="forms">
	<input type="hidden" name="mode" value="<?php  echo $mode;?>" />
		<?php
		if(isset($_REQUEST['collection']) && !empty($_REQUEST['collection']))
		{
			?>
			<p>
			<label><?php  echo _COLLECTION;?> : </label>
		    <input type="text" readonly="readonly" name="coll" class="readonly" value="<?php  echo $coll_label;?>" />
		    <input type="hidden" readonly="readonly" name="collselect" class="readonly" value="<?php  echo $_SESSION['m_admin']['group']['coll_id'];?>" />
		    </p>
		    <?php
		}
		else
		{
			?>
	    	<div align="center">
			<iframe name="choose_coll" id="choose_coll" scrolling="auto" width="100%" height="25" frameborder="0" src="<?php echo $_SESSION['config']['businessappurl'];?>admin/groups/choose_coll.php"></iframe></div>
			<?php
		}
		?>
	<br/>
	<p>
		<label><?php  echo _WHERE_CLAUSE;?> : </label>
	<!--	<div id="label_expert_hide">
			<h5><a href="#" onclick="javascript:expertmodehide();"><i><?php  echo _EDIT_WITH_ASSISTANT;?></i></a></h5>
		</div>-->
	</p>
	<p>
		<label>&nbsp;</label>
		<textarea rows="6" cols="100" name="where" id="where" /><?php  echo stripslashes($_SESSION['choosen_where_clause']);?></textarea>
	</p>
	<p>
		<iframe name="frm_expert_mode" id="frm_expert_mode" src="<?php  echo $_SESSION['config']['businessappurl'].'admin/groups/frame_expert_mode.php';?>" width="1" height="1" frameborder="0" scrolling="auto"></iframe>
		<div id="label_expert_show" class="input_expert_hide">
			<a href="#" onclick="javascript:expertmodeview('<?php  echo $_SESSION['m_admin']['group']['coll_id'];?>');"><h5><i><b><?php  echo _VALID_THE_WHERE_CLAUSE;?>!!!</b></i></h5></a>
		</div>
	</p>
	<br>
	<p>
		<label><?php  echo _COMMENTS;?>: </label>
		<input type="text" name="comment" value="<?php  echo $comment;?>" />
	</p>
	<br/>
	<p>
		<label><?php  echo _INSERT;?> :</label>
		<input type="checkbox"  class="check" name="insert[]" class="check" value="Y" <?php  if($insert == "Y"){ echo 'checked="checked"'; } ?> <?php  if(!$show_checkbox){ echo 'disabled="disabled"';}?>  />
	</p>
	<p>
		<label><?php  echo _UPDATE;?>  :</label>
		<input type="checkbox"  class="check" name="update[]" class="check" value="Y" <?php  if($update == "Y"){ echo 'checked="checked"'; }?> <?php  if(!$show_checkbox){ echo 'disabled="disabled"';}?>/>
	</p>
	<p>
		<label><?php  echo _DELETE_SHORT;?>  :</label>
		<input type="checkbox"  class="check" name="delete[]" class="check"  value="Y" <?php  if($delete == "Y"){ echo 'checked="checked"'; }?> <?php  if(!$show_checkbox){ echo 'disabled="disabled"';}?>/>
	</p>
	<br/>

	<br/>
	<p class="buttons">
		<input type="submit" name="Submit" value="<?php  echo _VALIDATE;?>" class="button"  />
		<input type="button" name="cancel" value="<?php  echo _CANCEL;?>" class="button"  onclick="window.close()"/>
	</p>

</form>
</div>
</td>

<td width='400px'>
	<?php
	$incl = "apps/".$_SESSION['businessapps'][0]['appid']."/keywords_help.php";
	include ($incl); ?>
</td>
</tr>

</body>
</html>
