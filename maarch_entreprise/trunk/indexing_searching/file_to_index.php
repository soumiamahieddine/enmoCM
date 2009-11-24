<?php 
if(!empty($_FILES['file']['tmp_name']) )
{
	$extension = explode(".",$_FILES['file']['name']);
	$count_level = count($extension)-1;
	$the_ext = $extension[$count_level];
	if(!is_uploaded_file($_FILES['file']['tmp_name']))
	{
		echo "<br/>No Upload<br/>";
		exit;
	}
	if(!move_uploaded_file($_FILES['file']['tmp_name'], 'tmp'.DIRECTORY_SEPARATOR.'tmp_file_'.$_GET['md5'].'.'.$the_ext))
	{
		echo "<br/>No Copy to ftp/tmp_file<br/>";
		exit;
	}
	$_SESSION['upfile']['size'] = $_FILES['file']['size'];
	$_SESSION['upfile']['mime'] = $_FILES['file']['type'];
	$_SESSION['upfile']['local_path'] = 'tmp'.DIRECTORY_SEPARATOR.'tmp_file_'.$_GET['md5'].'.'.$the_ext;
	$_SESSION['upfile']['name'] = $_FILES['file']['name'];
}
?>
