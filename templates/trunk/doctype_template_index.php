<?php
/**
* File : doctype_template_index.php
*
* Frame : used in the doctype administration, this doctype is generated (template) or loaded (file)
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 06/2008
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*
*/
$core_tools = new core_tools();
$core_tools->load_lang();
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");

$func = new functions();
$db = new dbquery();
$db->connect();
if($_REQUEST['e_file'])
{
	$_SESSION['temp_admin']['GENERATE'] = $_REQUEST['e_file'];
	$_SESSION['m_admin']['doctypes']['is_generated'] = $_REQUEST['e_file'];
}
else
{
	if  ($_SESSION['m_admin']['doctypes']['is_generated'] <> "Y")
	{
		$_SESSION['temp_admin']['GENERATE'] = 'N';
	}
	else
	{
		$_SESSION['temp_admin']['GENERATE'] = 'Y';
	}
}


if($_REQUEST['templates'])
{
	$_SESSION['temp_admin']['TEMPLATE_ID'] =  $_REQUEST['templates'];
	$_SESSION['m_admin']['doctypes']['template_id'] =  $_REQUEST['templates'];
}
else
{
	if(isset($_SESSION['m_admin']['doctypes']['template_id']) && !empty($_SESSION['m_admin']['doctypes']['template_id']))
	{
		$_SESSION['temp_admin']['TEMPLATE_ID'] = $_SESSION['m_admin']['doctypes']['template_id'];
	}
}
$templates = array();
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_MANAGE_RIGHTS);
$db->query("select id, label from ".$_SESSION['tablename']['temp_templates']." ");
while($res = $db->fetch_object())
{
	array_push($templates, array('id' => $res->id, 'label' => $res->label));
}


?>

<body>
<form name="e_type" action="<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=templates&page=doctype_template_index" method="post">
	<input type="hidden" name="display"  value="true" />
	<input type="hidden" name="module"  value="templates" />
	<input type="hidden" name="page"  value="doctype_template_index" />
  <p>
    <label>
      <input type="radio"  class="check" onClick="javascript:this.form.submit();" name="e_file" value="N"  <?php  if ($_SESSION['temp_admin']['GENERATE'] == 'N') { echo 'checked="checked"';} ?>/>
      <?php  echo _LOADED_FILE;?></label>

    <label>
      <input type="radio"  class="check" onClick="javascript:this.form.submit();" name="e_file" value="Y"  <?php  if ($_SESSION['temp_admin']['GENERATE'] == 'Y') { echo 'checked="checked"';} ?> />
      <?php  echo _GENERATED_FILE;?></label>

  <br/>
  <?php  if ($_SESSION['temp_admin']['GENERATE'] == 'Y')
  	   {
	   	?>
       	<?php  echo _CHOOSE_TEMPLATE;?> :
       		<select name="templates" onChange="javascript:this.form.submit();" >
				<?php

					//$connexion -> show();
					for($i=0; $i<count($templates); $i++)
					{
						echo '<option value="'.$templates[$i]['id'].'" ';
						if ($_SESSION['temp_admin']['TEMPLATE_ID'] == $templates[$i]['id'] || $i == 0)
						{
							echo 'selected="selected"';
						}
						echo '>'.$templates[$i]['label'].'</option>';
					}
				?>
            </select><span class="red_asterisk">*</span>
       </p>
       <?php
	   }
  ?>
</form>
</body>
</html>




