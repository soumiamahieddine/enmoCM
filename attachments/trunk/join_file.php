<?php
/**
* File : join_file.php
*
* Add an answer in the process
*
* @package Maarch LetterBox 2.3
* @version 2.5
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_resource.php");
$core_tools = new core_tools();
$core_tools->load_lang();

$func = new functions();
$req = new request();
$_SESSION['error'] = "";
if($_POST['valid'])
{

	$_SESSION['upfile'] = array();
	if(empty($_FILES['file']['tmp_name']))
	{
		$_SESSION['error'] .= _FILE_MISSING.".<br/>";
	}
	else
	{
		$_SESSION['upfile']['tmp_name'] = $_FILES['file']['tmp_name'];
	}

	if($_FILES['file']['size'] == 0)
	{
		$_SESSION['error'] .= _FILE_EMPTY.".<br />";
	}
	else
	{
		$_SESSION['upfile']['size'] = $_FILES['file']['size'];
	}
	if($_FILES['file']['error'] == 1)
	{
		$filesize = $func->return_bytes(ini_get("upload_max_filesize"));
		$_SESSION['error'] = _ERROR_FILE_UPLOAD_MAX."(".round($filesize/1024,2)."Ko Max).<br />";
	}
	$title = '';
	if(!isset($_REQUEST['title']) || empty($_REQUEST['title']))
	{
		$_SESSION['error'] .= _TITLE.' '._MANDATORY;
	}
	else
	{
		$title = $func->protect_string_db($_REQUEST['title']);
	}
	if(empty($_SESSION['error']))
	{
		$_SESSION['upfile']['name'] = $_FILES['file']['name'];

		if(!is_uploaded_file($_FILES['file']['tmp_name']))
		{

			$_SESSION['error'] .= _FILE_NOT_SEND.". "._TRY_AGAIN."."._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>.<br/>";
		}
		elseif(isset($_SESSION['upfile']) && !empty($_SESSION['upfile']))
		{

			$extension = explode(".",$_SESSION['upfile']['name']);
			$count_level = count($extension)-1;
			$the_ext = $extension[$count_level];

			require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_indexing_searching_app.php");
			$is = new indexing_searching_app();
			$ext_ok = $is->is_filetype_allowed($the_ext);
			$_SESSION['upfile']['format'] = $the_ext;
			if($ext_ok == false)
			{
				$_SESSION['error'] = _WRONG_FILE_TYPE.".";
				$_SESSION['upfile'] = array();
			}
			else
			{

				require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_docserver.php");
				if(!isset($_SESSION['collection_id_choice']) || empty($_SESSION['collection_id_choice']))
				{
					$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
				}
				$docserver = new docserver($_SESSION['tablename']['docservers'], $_SESSION['collection_id_choice']);

				if(!empty($error))
				{
					$_SESSION['error'] = $error;
					$location = "";
				}
				else
				{
				// some checking on docserver size limit
					$new_size = $docserver->check_size($_FILES['file']['size'], $_SESSION["config"]["lang"]);
					if($new_size == 0)
					{
						$_SESSION['error'] = $docserver->get_error();
						?>
						<script language="javascript" type="text/javascript">
							var eleframe1 =  window.opener.top.frames['process_frame'].document.getElementById('list_attach');
							eleframe1.location.href = '<?php  echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=attachments&page=frame_list_attachments';
						</script>
						<?php
						exit();
					}
					else
					{
						$docinfo = $docserver->filename();
						if($docserver->get_error() == "txt_error_when_sending_file")
						{
							$_SESSION['error'] = _FILE_SEND_ERROR;
						}
						else
						{

							$destination_rept = $docinfo['destination_rept'];
							$file_destination_name = $docinfo['file_destination_name'];
							$docserver_id = $docserver->get_id();
							$file_path = $destination_rept.$file_destination_name.".".$_SESSION['upfile']['format'];

							if(file_exists( $destination_rept.$file_destination_name.".".$_SESSION['upfile']['format']))
							{
								 $_SESSION['error'] .= _FILE_ALREADY_EXISTS.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>.";
							}
							else
							{

								$file_name = $entry;
								if (!move_uploaded_file($_FILES['file']['tmp_name'],$destination_rept.$file_destination_name.".".$the_ext))
								{
									$_SESSION['error'] .= "<li> "._DOCSERVER_COPY_ERROR.".</li>";

								}
								else
								{
									$path_template= $docserver->get_path();
									$destination_rept = substr($destination_rept,strlen($path_template),4);
										//Linux / Windows
									$destination_rept = str_replace(DIRECTORY_SEPARATOR,'#',$destination_rept);
									$docserver->set_size($new_size, $_SESSION['tablename']['docservers']);
									$res_attach = new resource();

										$_SESSION['data'] = array();

										array_push($_SESSION['data'], array('column' => "typist", 'value' => $_SESSION['user']['UserId'], 'type' => "string"));
										array_push($_SESSION['data'], array('column' => "format", 'value' => $the_ext, 'type' => "string"));
										array_push($_SESSION['data'], array('column' => "docserver_id", 'value' => $docserver_id, 'type' => "string"));
										array_push($_SESSION['data'], array('column' => "status", 'value' => 'NEW', 'type' => "string"));
										array_push($_SESSION['data'], array('column' => "offset_doc", 'value' => ' ', 'type' => "string"));
										array_push($_SESSION['data'], array('column' => "logical_adr", 'value' => ' ', 'type' => "string"));
										array_push($_SESSION['data'], array('column' => "title", 'value' => $title, 'type' => "string"));
										array_push($_SESSION['data'], array('column' => "coll_id", 'value' => $_SESSION['collection_id_choice'], 'type' => "string"));
										array_push($_SESSION['data'], array('column' => "res_id_master", 'value' => $_SESSION['doc_id'], 'type' => "integer"));
										if($_SESSION['origin'] == "scan")
										{
											array_push($_SESSION['data'], array('column' => "scan_user", 'value' => $_SESSION['user']['UserId'], 'type' => "string"));
											array_push($_SESSION['data'], array('column' => "scan_date", 'value' => $req->current_datetime(), 'type' => "function"));

										}
										array_push($_SESSION['data'], array('column' => "type_id", 'value' => 0, 'type' => "int"));

									$id = $res_attach->load_into_db($_SESSION['tablename']['attach_res_attachments'],$destination_rept,$file_destination_name.".".$_SESSION['upfile']['format'], $docserver->get_path(), $docserver_id,  $_SESSION['data'], $_SESSION['config']['databasetype']);


									if($id == false)
									{
										$_SESSION['error'] = $res_attach->get_error();
										//echo $resource->get_error();
										//$resource->show();
										//exit();
									}
									else
									{

										if($_SESSION['history']['attachadd'] == "true")
										{
											require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
											$users = new history();
											$_SESSION['error'] = _NEW_ATTACH_ADDED;
											$users->add($_SESSION['tablename']['attach_res_attachments'], $id, "ADD", $_SESSION['error']." (".$title.") ", $_SESSION['config']['databasetype'],'attachments');
										}
									//}
									}
								}
							}
						}
					}
				}
			}
			if(empty($_SESSION['error']) || $_SESSION['error'] == _NEW_ATTACH_ADDED)
			{
				?>
				<script language="javascript" type="text/javascript">
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

//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_ATTACH_ANSWER);
?>
<body id="pop_up"  >
<div class="error"><?php  echo $_SESSION['error']; $_SESSION['error']=""; ?></div>
<h2 class="tit"><?php  echo _ATTACH_ANSWER;?> </h2>

	<form enctype="multipart/form-data" method="post" name="attachement" class="forms"  >
	<p>
    	<label><?php  echo _TITLE;?> : </label>
        <input type="text" name="title" id="title" />
    </p>
	<br/>
	<p>
    	<label><em><?php  echo _PLEASE_SELECT_FILE;?> :</em></label>
       	<input type="file" name="file" id="file" />
  	</p>
    <br/>

	<p class="buttons">
       <input type="submit" value="<?php  echo _VALIDATE;?>" name="valid" id="valid" class="button" />
	<input type="button" value="<?php  echo _CANCEL;?>" name="cancel" class="button"  onclick="self.close();"/>
  </p>
 </form>
</body>
</html>
