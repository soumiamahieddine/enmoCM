<?php
/**
* File : choose_coll.php
*
* Form to choose a collection (used in doctypes administration)
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
$sec = new security();
$array_coll = $sec->retrieve_insert_collections();

if(isset($_REQUEST['collection']) && !empty($_REQUEST['collection']) )
{
	$_SESSION['m_admin']['doctypes']['COLL_ID'] = $_REQUEST['collection'];
}
?>
<body id="iframe">
<form name="choose_coll" method="get" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=folder&page=choose_coll" class="forms" >
	<input type="hidden" name="display"  value="true" />
	<input type="hidden" name="module"  value="folder" />
	<input type="hidden" name="page"  value="choose_coll" />
	<p>
		<label for="coll_id"><?php  echo _COLLECTION;?> : </label>
		<select name="collection" onchange="this.form.submit();">
			<option value="" ><?php  echo _CHOOSE_COLLECTION;?></option>
			<?php
			for($i=0; $i<count($array_coll);$i++)
			{
				?>
					<option value="<?php  echo $array_coll[$i]['id'];?>" <?php  if($_SESSION['m_admin']['doctypes']['COLL_ID'] == $array_coll[$i]['id']){ echo 'selected="selected"';}?> ><?php  echo $array_coll[$i]['label'];?></option>
				<?php
			}
			?>
		</select>
	</p>
</form>
</body>
</html>
