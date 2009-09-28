<?php 
session_name('PeopleBox'); 
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php"); 
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
//here we building the header
$core_tools->load_header();
?>
<body>
<?php 
if(isset($_REQUEST['foldertype']) && !empty($_REQUEST['foldertype']))
{
	$_SESSION['current_foldertype'] = $_REQUEST['foldertype'];

	?>
    <script language="javascript" type="text/javascript">window.top.frames['search_folder'].location.href='<?php  echo $_SESSION['urltomodules']."folder/search_folder.php";?>';</script>
    <?php 

}
?>
<form name="choose_type" action="choose_foldertype2.php" method="get" <?php  if($_SESSION['origin'] == 'show_folder'){?>class="forms addforms"<?php  }?>>
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