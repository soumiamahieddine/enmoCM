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

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
include('apps/maarch_entreprise/security_bitmask.php');
include('core/manage_bitmask.php');
$core_tools = new core_tools();
$core_tools->load_lang();
$core_tools->test_admin('admin_groups', 'apps');
$sec = new security();
$func = new functions();
$clause = '';
$comment = '';
$start_date = '';
$stop_date = '';
$target = 'ALL';
$rights_bitmask = 0;
$coll_id = $_SESSION['collections'][0]['id'];
$ind = 0;

$mode = "add" ;
$access_ind = -1;
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
	$mode = trim($_REQUEST['mode']);
}

if(isset($_REQUEST['val']) && $_REQUEST['val'] >= 0)
{
	$access_ind = $_REQUEST['val'];
}

if($mode == "up" && $access_ind >= 0)
{
	$security_id = $_SESSION['m_admin']['groups']['security'][$access_ind]['SECURITY_ID'];
	$coll_id = $_SESSION['m_admin']['groups']['security'][$access_ind]['COLL_ID'];
	$ind = $sec->get_ind_collection($coll_id);
	$coll_label = $_SESSION['collections'][$ind]['label'];
	$target = $_SESSION['m_admin']['groups']['security'][$access_ind]['WHERE_TARGET'];
	$clause = $func->show_string($_SESSION['m_admin']['groups']['security'][$access_ind]['WHERE_CLAUSE']);
	$comment = $func->show_string($_SESSION['m_admin']['groups']['security'][$access_ind]['COMMENT']);
	$start_date = $func->format_date_db($_SESSION['m_admin']['groups']['security'][$access_ind]['START_DATE'], false);
	$stop_date = $func->format_date_db($_SESSION['m_admin']['groups']['security'][$access_ind]['STOP_DATE'], false);
	$rights_bitmask = $_SESSION['m_admin']['groups']['security'][$access_ind]['RIGHTS_BITMASK'];	
}
?>

<h2 class="tit"><?php  echo _ADD_GRANT;?></h2>
<div id="frm_error" class="error"></div>
<table  width="100%">
<tr>
<td>
<div class="popup_content">
<form name="addGrantForm" id="addGrantForm" method="post" action="#" class="forms">
	<input type="hidden"  id="mode" value="<?php  echo $mode;?>" />
	<p>
		<label><?php  echo _COLLECTION;?> :</label>
		<select name="coll_id" id="coll_id" >
			<option value=""><?php  echo _CHOOSE_COLLECTION;?></option>
			<?php
				for($i=0; $i < count($_SESSION['collections']); $i++)
				{
					?>
					<option value="<?php  echo $_SESSION['collections'][$i]['id']; ?>" <?php  if ($coll_id == $_SESSION['collections'][$i]['id']) {echo 'selected="selected"'; }?>><?php  echo $_SESSION['collections'][$i]['label']; ?></option>
					<?php
				}
				?>
		</select>
		<span class="red_asterisk" >*</span>
	</p>
	<br/>
	<p>
		<label><?php  echo _DESC;?>: </label>
		<input type="text" name="comment" id="comment" value="<?php  echo $comment;?>" />
		<span class="red_asterisk" >*</span>
	</p>
	<br/>
	<p>
		<label><?php echo _WHERE_CLAUSE_TARGET;?> : </label>
		<input type="radio"  class="check" name="target"  value="ALL" id="target_all" <?php if($target == 'ALL'){ echo 'checked="checked"';}?>  /><?php echo _ALL;?> 
		<input type="radio"  class="check" name="target"  value="DOC" id="target_doc"  <?php if($target == 'DOC'){ echo 'checked="checked"';}?>  /><?php echo _DOCS;?> 
		<input type="radio"  class="check" name="target"  value="CLASS" id="target_class" <?php if($target == 'CLASS'){ echo 'checked="checked"';}?>  /><?php echo _CLASS_SCHEME;?><span class="red_asterisk" >*</span>
	</p>
	<br/>
	<p>
		<label><?php  echo _WHERE_CLAUSE;?> : </label>
		<textarea rows="6" cols="100" name="where" id="where" /><?php  echo stripslashes($_SESSION['choosen_where_clause']);?></textarea>
		<span class="red_asterisk" >*</span>
	</p>
	<br/>
	<p >
		<label><?php echo _TASKS;?> : </label><br/>
		<div style="margin-left:40%;">
		<?php  for($k=0;$k<count($_ENV['security_bitmask']); $k++)
		{
			?>
			<input type="checkbox"  class="check" name="rights_bitmask[]" id="<?php echo $_ENV['security_bitmask'][$k]['ID'];?>" value="true" <?php  if(check_right($rights_bitmask , $_ENV['security_bitmask'][$k]['ID'])){ echo 'checked="checked"'; } ?>  /> 
		<?php echo constant($_ENV['security_bitmask'][$k]['LABEL']).'<br/>';
		}?>
		
		</div>
	</p>
	<br/>
	<p>
		<label><?php echo _PERIOD;?> : </label>
		<p>
			<label><?php echo _SINCE;?></label>
			<input type="text" id="start_date" name="start_date" value="" onclick="showCalender(this);"/>
		</p>
		<br/>
		<p>
			<label><?php echo _FOR;?></label>
			<input type="text" id="stop_date" name="stop_date" value="" onclick="showCalender(this);"/>
		</p>
	</p>
	<br/>
	<p class="buttons">
		<input type="button" name="Submit" value="<?php  echo _VALIDATE;?>" class="button" onclick="checkAccess('addGrantForm', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&admin=groups&page=check_access';?>', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&admin=groups&page=manage_access';?>', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&admin=groups&page=groups_form';?>');"  />
		<input type="button" name="cancel" value="<?php  echo _CANCEL;?>" class="button"  onclick="destroyModal('add_grant');"/>
	</p>

</form>
</div>
</td>

<td width='400px'>
	<?php
	include("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."keywords_help.php"); ?>
</td>
</tr>
</table>
