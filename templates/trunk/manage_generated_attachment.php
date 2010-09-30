<?php
/**
* File : manage_generated_attachment.php
*
* Result of the generate answer form
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 10/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_resource.php");

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
$func = new functions();
if(empty($_REQUEST['mode']) || !isset($_REQUEST['mode']))
{
	$_SESSION['error'] .= _NO_MODE_DEFINED.'.<br/>';
	header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&module=templates&page=generate_attachment&template=".$_REQUEST['template_id']);
	exit;
}
else
{
	$conn = new dbquery();
	$conn->connect();

	if(empty($_REQUEST['template_content']) || !isset($_REQUEST['template_content']))
	{
		$_SESSION['error'] .= _NO_CONTENT.'.<br/>';
		if($_REQUEST['mode'] == 'add')
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&module=templates&page=generate_attachment&template=".$_REQUEST['template_id']."&mode=".$_REQUEST['mode']);
		}
		else
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&module=templates&page=generate_attachment&id=".$_REQUEST['id']."&mode=".$_REQUEST['mode']);
		}
		exit;

	}
	else
	{

		if($_REQUEST['mode'] == "add" )
		{
			if(empty($_REQUEST['answer_title']) || !isset($_REQUEST['answer_title']))
			{
				$_REQUEST['answer_title'] = $_SESSION['courrier']['res_id']."_".$_REQUEST['template_label'].date("dmY");
			}

			$path_tmp = $_SESSION['config']['tmppath'].DIRECTORY_SEPARATOR.$_REQUEST['answer_title'].".maarch";

			//echo $path.'<br/>';
			$myfile = fopen($path_tmp, "w");

			if(!$myfile)
			{
				$_SESSION['error'] .= _FILE_OPEN_ERROR.'.<br/>';
				header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&module=templates&page=generate_attachment&template=".$_REQUEST['template_id']);
				exit;
			}

			fwrite($myfile, $_REQUEST['template_content']);
			fclose($myfile);

			require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."docservers_controler.php");
			if(!isset($_SESSION['collection_id_choice']) || empty($_SESSION['collection_id_choice'])) {
				$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
			}
			$docserverControler = new docservers_controler();
			$docserver = $docserverControler->getDocserverToInsert($_SESSION['collection_id_choice']);
			if(empty($docserver)) {
					$_SESSION['error'] = _DOCSERVER_ERROR.' : '._NO_AVAILABLE_DOCSERVER.". "._MORE_INFOS.".";
					$location = "";
				} else {
				// some checking on docserver size limit
				$new_size = $docserverControler->checkSize($docserver, $_SESSION['file']['size']);
				if($new_size == 0) {
					$_SESSION['error'] = _DOCSERVER_ERROR.' : '._NOT_ENOUGH_DISK_SPACE.". "._MORE_INFOS.".";
					header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&module=templates&page=generate_attachment&template=".$_REQUEST['template_id']."&mode=".$_REQUEST['mode']);
					exit();
				} else {
					$docinfo = $docserverControler->filename($docserver);
					$destination_rept = $docinfo['destination_rept'];
					$file_destination_name = $docinfo['file_destination_name'];
					$file_path = $destination_rept.$file_destination_name.".".$_SESSION['upfile']['format'];
					if(file_exists( $destination_rept.$file_destination_name.".maarch")) {
						 $_SESSION['error'] .= _FILE_ALREADY_EXISTS.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>.";
						 header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&module=templates&page=generate_attachment&template=".$_REQUEST['template_id']."&mode=".$_REQUEST['mode']);
						exit();
					} else {
						if(!copy($path_tmp,$destination_rept.$file_destination_name.".maarch")) {
							$_SESSION['error'] = _FILE_SEND_ERROR.". "._TRY_AGAIN.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>.<br/>";
							header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&module=templates&page=generate_attachment&template=".$_REQUEST['template_id']."&mode=".$_REQUEST['mode']);
							exit();
						} else {
							if(!empty($path_tmp)) {
								//unlink($path_tmp);
							}
							$path_template= $docserver->path_template;
							$destination_rept = substr($destination_rept,strlen($path_template),4);
							$destination_rept = str_replace(DIRECTORY_SEPARATOR,'#',$destination_rept);
							$docserverControler->setSize($docserver, $new_size);
							$res_attach = new resource();
							$_SESSION['data'] = array();
							array_push($_SESSION['data'], array('column' => "typist", 'value' => $_SESSION['user']['UserId'], 'type' => "string"));
							array_push($_SESSION['data'], array('column' => "format", 'value' => 'maarch', 'type' => "string"));
							array_push($_SESSION['data'], array('column' => "docserver_id", 'value' => $docserver->docserver_id, 'type' => "string"));
							array_push($_SESSION['data'], array('column' => "status", 'value' => 'NEW', 'type' => "string"));
							array_push($_SESSION['data'], array('column' => "offset_doc", 'value' => ' ', 'type' => "string"));
							array_push($_SESSION['data'], array('column' => "logical_adr", 'value' => ' ', 'type' => "string"));
							array_push($_SESSION['data'], array('column' => "title", 'value' => $_REQUEST['answer_title'], 'type' => "string"));
							array_push($_SESSION['data'], array('column' => "coll_id", 'value' => $_SESSION['collection_id_choice'], 'type' => "string"));
							array_push($_SESSION['data'], array('column' => "res_id_master", 'value' => $_SESSION['doc_id'], 'type' => "integer"));
							array_push($_SESSION['data'], array('column' => "type_id", 'value' => 0, 'type' => "int"));
							$id = $res_attach->load_into_db($_SESSION['tablename']['attach_res_attachments'],$destination_rept,$file_destination_name.".maarch", $docserver->path_template, $docserver->docserver_id,  $_SESSION['data'], $_SESSION['config']['databasetype']);
							if($id == false)
							{
								$_SESSION['error'] = $res_attach->get_error();
								header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&module=templates&page=generate_attachment&template=".$_REQUEST['template_id']."&mode=".$_REQUEST['mode']);
								exit();
							}
							else
							{

								if($_SESSION['history']['attachadd'] == "true")
								{
									require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
									$users = new history();
									//$_SESSION['error'] = _NEW_ATTACH_ADDED;
									$users->add($_SESSION['tablename']['attach_res_attachments'], $id, "ADD", _NEW_ATTACH_ADDED." (".$title.") ", $_SESSION['config']['databasetype'],'attachments');
								}

							}

						}
					}
				}
			}

			if(empty($_SESSION['error']) || $_SESSION['error'] == _NEW_ATTACH_ADDED)
			{
				?>
				<script type="text/javascript">
					var eleframe1 =  window.opener.top.document.getElementById('list_attach');
					eleframe1.src = '<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=attachments&page=frame_list_attachments';
					window.top.close();
						</script>
					<?php
						exit();
			}
	}
	else //mode = up
	{

		if(empty($_REQUEST['id']) || !isset($_REQUEST['id']))
		{
			$_SESSION['error'] .= _ANSWER_OPEN_ERROR.'.<br/>';
			header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&module=templates&page=generate_attachment&template=".$_REQUEST['template_id']);
			exit;
		}
		else
		{

			$conn->query("select docserver_id, path, filename from ".$_SESSION['tablename']['attach_res_attachments']." where res_id = ".$_REQUEST['id']);

			if($conn->nb_result() == 0)
			{
				$_SESSION['error'] = _NO_DOC_OR_NO_RIGHTS."...";
				?>
				<script  type="text/javascript">
					var eleframe1 =  window.opener.top.frames['process_frame'].document.getElementById('list_attach');
					eleframe1.src = '<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=attachments&page=frame_list_attachments';
					window.top.close();
						</script>
					<?php

				exit;
			}
			else
			{
				$line = $conn->fetch_object();

				$docserver = $line->docserver_id;
				$path = $line->path;
				$filename = $line->filename;

				$conn->query("select path_template from ".$_SESSION['tablename']['docservers']." where docserver_id = '".$docserver."'");

				$line_doc = $conn->fetch_object();

				$docserver = $line_doc->path_template;
				$file = $docserver.$path.strtolower($filename);

				$file = str_replace("#",DIRECTORY_SEPARATOR,$file);
				$myfile = fopen($file, "w");

				if(!$myfile)
				{
					$_SESSION['error'] .= _FILE_OPEN_ERROR.'.<br/>';
					header("location: ".$_SESSION['config']['businessappurl']."index.php?display=true&module=templates&page=generate_attachment&id=".$_REQUEST['id']);
					exit;
				}

				fwrite($myfile, $_REQUEST['template_content']);
				fclose($myfile);

				$conn->query("update ".$_SESSION['tablename']['attach_res_attachments']." set title = '".$func->protect_string_db($_REQUEST['answer_title'])."' where res_id = ".$_REQUEST['id']);

				if($_SESSION['history']['attachup'] == "true")
				{
					require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
					$hist = new history();
					$hist->add($_SESSION['tablename']['attach_res_attachments'], $_SESSION['courrier']['res_id'],"UP", _ANSWER_UPDATED."  (".$_SESSION['courrier']['res_id'].")", $_SESSION['config']['databasetype'],'attachments');

				}
				?>
				<script  type="text/javascript">
					var eleframe1 =  window.opener.top.document.getElementById('list_attach');
					eleframe1.src = '<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=attachments&page=frame_list_attachments';
					window.top.close();
						</script>
					<?php
						exit();
			}
		}
	}
	}
}
?>
