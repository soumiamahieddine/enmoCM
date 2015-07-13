<?php 

/*
 * Copyright (C) 2008-2015 Maarch
 *
 * This file is part of Maarch.
 *
 * Maarch is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Maarch is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Maarch.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");

$core_tools = new core_tools();
$core_tools->load_lang();

$db = new Database();
$types = array();

$stmt = $db->query("SELECT foldertype_id, foldertype_label FROM ".$_SESSION['tablename']['fold_foldertypes']);

while($res = $stmt->fetchObject())
{
	array_push($types, array('id' => $res->foldertype_id, 'label' => $res->foldertype_label));
}
$core_tools->load_html();
$core_tools->load_header();
?>
<body>
<?php 
if(isset($_REQUEST['foldertype']) && !empty($_REQUEST['foldertype']))
{
	$_SESSION['current_foldertype'] = $_REQUEST['foldertype'];

	?>
    <script type="text/javascript">window.top.frames['search_folder'].location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=search_folder';</script>
    <?php 

}
?>
<form name="choose_type" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=choose_foldertype2" method="get" <?php  if($_SESSION['origin'] == 'show_folder'){?>class="forms addforms"<?php  }?>>
	<input type="hidden" name="display"  value="true" />
	<input type="hidden" name="module"  value="folder" />
	<input type="hidden" name="page"  value="choose_foldertype2" />
	<p>
    	<label><?php echo _FOLDERTYPE;?> : </label>
        <select name="foldertype" id="foldertype" onchange="this.form.submit();">
        	<option value=""><?php echo _CHOOSE_FOLDERTYPE;?></option>
        	<?php  for($i=0; $i<count($types);$i++)
			{
				?><option value="<?php functions::xecho($types[$i]['id']);?>" <?php  if(count($types) == 1 || $types[$i]['id'] == $_SESSION['current_foldertype']) { echo 'selected="selected"';}?>><?php functions::xecho($types[$i]['label']);?></option><?php 
			}
        ?>
        </select>
    </p>
</form>
</body>
</html>
