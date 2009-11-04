<?php
/**
* File : view_attachement.php
*
* View a document
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");
$core_tools = new core_tools();
$core_tools->test_user();
//here we loading the lang vars
$core_tools->load_lang();
$function = new functions();
$sec = new security();
$_SESSION['error'] = "";
if(isset($_GET['id']))
{
	$s_id = $_GET['id'];
}
else
{
	$s_id = "";
}
$s_id = $function->wash($_GET['id'], "num", _THE_DOC);
if(!empty($_SESSION['error']))
{
	header("location: ".$_SESSION['config']['businessappurl']."index.php");
	exit();
}
else
{
	$db = new dbquery();
	$db->connect();

	$db->query("select coll_id, res_id_master from ".$_SESSION['tablename']['attach_res_attachments']." where res_id = ".$s_id);
	$res = $db->fetch_object();
	$coll_id = $res->coll_id;
	$res_id_master = $res->res_id_master;

	$where2 = "";
	for($i=0; $i < count($_SESSION['user']['security']); $i++)
	{
		if($coll_id == $_SESSION['user']['security'][$i]['coll_id'])
		{
			$where2 = " and ( ".$_SESSION['user']['security'][$i]['where']." ) ";
		}
	}

	$table = $sec->retrieve_table_from_coll($coll_id);
	//$db->query("select res_id from ".$table." where res_id = ".$res_id_master." ".$where2);
	$db->query("select res_id from ".$table." where res_id = ".$res_id_master);
	//$db->show();
	if($db->nb_result() == 0)
	{
		$_SESSION['error'] = _THE_DOC." "._EXISTS_OR_RIGHT."&hellip;";
		header("location: ".$_SESSION['config']['businessappurl']."index.php");
		exit();
	}
	else
	{
		$db->query("select docserver_id, path, filename, format from ".$_SESSION['tablename']['attach_res_attachments']." where res_id = ".$s_id);

		if($db->nb_result() == 0)
		{
			$_SESSION['error'] = _THE_DOC." "._EXISTS_OR_RIGHT."&hellip;";
			header("location: ".$_SESSION['config']['businessappurl']."index.php");
			exit();
		}
		else
		{
			$line = $db->fetch_object();
			$docserver = $line->docserver_id;
			$path = $line->path;
			$filename = $line->filename;
			$format = $line->format;
			$db->query("select path_template from ".$_SESSION['tablename']['docservers']." where docserver_id = '".$docserver."'");
			//$db->show();
			$line_doc = $db->fetch_object();
			$docserver = $line_doc->path_template;
			$file = $docserver.$path.$filename;
			$file = str_replace("#",DIRECTORY_SEPARATOR,$file);

			if(strtoupper($format) == "MAARCH")
			{
				if(file_exists($file))
				{
					$myfile = fopen($file, "r");

					$data = fread($myfile, filesize($file));
					fclose($myfile);
					$content = stripslashes($data);
					$core_tools->load_html();
					$core_tools->load_header();
					?>
                    <body id="validation_page" onLoad="javascript:moveTo(0,0);resizeTo(screen.width, screen.height);">
                     <div id="model_content" style="width:100%;"  >

                    <?php  echo $content;?>

                    </div>
                    </body>
                    </html> <?php
				}
				else
				{
					$_SESSION['error'] = _NO_DOC_OR_NO_RIGHTS."...";
					?><script>window.opener.top.location.href='index.php';self.close();</script><?php
				}
			}
			else
			{
				// TEMP : to modify when deleting indexing_searching module
				//if(!$core_tools->is_module_loaded('indexing_searching'))
				//{
					require_once($_SESSION['config']['businessapppath'].'class'.DIRECTORY_SEPARATOR.'class_indexing_searching_app.php');
					$is = new indexing_searching_app();
					$type_state = $is->is_filetype_allowed($format);
/*
				}
				else
				{
					require_once($_SESSION['pathtomodules']."indexing_searching".DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_modules_tools.php');
					$is = new indexing_searching();
					$type_state = $is->is_filetype_allowed($format);
				}
*/
				if($type_state <> false)
				{
					$mime_type = $is->get_mime_type($format);
					if($_SESSION['history']['attachview'] == "true")
					{
						require_once($_SESSION['pathtocoreclass']."class_history.php");
						$users = new history();
						$users->add($table, $s_id ,"VIEW", _VIEW_DOC_NUM."".$s_id, $_SESSION['config']['databasetype'],'apps');
					}
					header("Pragma: public");
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Cache-Control: public");
					header("Content-Description: File Transfer");
					header("Content-Type: ".$mime_type);
					header("Content-Disposition: inline; filename=".basename('maarch.'.$format).";");
					header("Content-Transfer-Encoding: binary");
					readfile($file);
					exit();
				}
				else
				{
					echo _FORMAT.' '._UNKNOWN;
					exit();
				}
			}
		}
	}
}
?>