<?php 
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
$core_tools = new core_tools();
$core_tools->load_lang();

$db = new dbquery();
$db->connect();
$types = array();

$db->query("select foldertype_id, foldertype_label from ".$_SESSION['tablename']['fold_foldertypes']);

while($res = $db->fetch_object())
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
    <script language="javascript" type="text/javascript">window.top.frames['search_folder'].location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=search_folder';</script>
    <?php 

}
?>
<form name="choose_type" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=choose_foldertype2" method="get" <?php  if($_SESSION['origin'] == 'show_folder'){?>class="forms addforms"<?php  }?>>
	<input type="hidden" name="display"  value="true" />
	<input type="hidden" name="module"  value="folder" />
	<input type="hidden" name="page"  value="choose_foldertype2" />
	<p>
    	<label><?php  echo _FOLDERTYPE;?> : </label>
        <select name="foldertype" id="foldertype" onChange="this.form.submit();">
        	<option value=""><?php  echo _CHOOSE_FOLDERTYPE;?></option>
        	<?php  for($i=0; $i<count($types);$i++)
			{
				?><option value="<?php  echo $types[$i]['id'];?>" <?php  if(count($types) == 1 || $types[$i]['id'] == $_SESSION['current_foldertype']) { echo 'selected="selected"';}?>><?php  echo $types[$i]['label'];?></option><?php 
			}
        ?>
        </select>
    </p>
</form>
</body>
</html>
