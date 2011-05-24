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

require_once "core/class/class_security.php";
require_once "core/class/class_request.php";
require_once "core/class/class_resource.php";
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    . "class_indexing_searching_app.php";
require_once "core/class/docservers_controler.php";
require_once 'modules/attachments/attachments_tables.php';
require_once "core/class/class_history.php";

$core = new core_tools();
$core->load_lang();
$sec = new security();
$func = new functions();
$req = new request();
$_SESSION['error'] = "";
if (isset($_POST['valid']) && $_POST['valid']) {
	$_SESSION['upfile'] = array();
	if (empty($_FILES['file']['tmp_name'])) {
		$_SESSION['error'] .= _FILE_MISSING . ".<br/>";
	} else {
		$_SESSION['upfile']['tmp_name'] = $_FILES['file']['tmp_name'];
	}

	if ($_FILES['file']['size'] == 0) {
		$_SESSION['error'] .= _FILE_EMPTY . ".<br />";
	} else {
		$_SESSION['upfile']['size'] = $_FILES['file']['size'];
	}
	if ($_FILES['file']['error'] == 1) {
		$filesize = $func->return_bytes(ini_get("upload_max_filesize"));
		$_SESSION['error'] = _ERROR_FILE_UPLOAD_MAX . "(" . round(
		    $filesize / 1024, 2
	    ) . "Ko Max).<br />";
	}
	$title = '';
	if (! isset($_REQUEST['title']) || empty($_REQUEST['title'])) {
		$_SESSION['error'] .= _TITLE . ' ' . _MANDATORY;
	} else {
		$title = $func->protect_string_db($_REQUEST['title']);
	}
	if (empty($_SESSION['error'])) {
		$_SESSION['upfile']['name'] = $_FILES['file']['name'];
		$extension = explode(".", $_SESSION['upfile']['name']);
		$countLevel = count($extension) - 1;
		$theExt = $extension[$countLevel];
        $tmpFileName = 'tmp_file_' . $_SESSION['user']['UserId']
            . '_' . rand() . '.' . strtolower($theExt);
        $filePathOnTmp = $_SESSION['config']['tmppath'] . $tmpFileName;
		if (! is_uploaded_file($_FILES['file']['tmp_name'])) {

			$_SESSION['error'] .= _FILE_NOT_SEND . ". " . _TRY_AGAIN . "."
			    . _MORE_INFOS . " : <a href=\"mailto:"
			    . $_SESSION['config']['adminmail'] . "\">"
			    . $_SESSION['config']['adminname'] . "</a>.<br/>";
		} else if (! @move_uploaded_file(
		    $_FILES['file']['tmp_name'], $filePathOnTmp
		)
		) {
            $_SESSION['error'] = _FILE_NOT_SEND . ". " . _TRY_AGAIN . ". "
                . _MORE_INFOS . " (<a href=\"mailto:"
                . $_SESSION['config']['adminmail'] . "\">"
                . $_SESSION['config']['adminname'] . "</a>)";
        } else {

			$is = new indexing_searching_app();
			$extOk = $is->is_filetype_allowed($theExt);
			$_SESSION['upfile']['format'] = $theExt;
			if ($extOk == false) {
				$_SESSION['error'] = _WRONG_FILE_TYPE . ".";
				$_SESSION['upfile'] = array();
			} else {
				if (! isset($_SESSION['collection_id_choice'])
				    || empty($_SESSION['collection_id_choice'])
				) {
					$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
				}
				$docserverControler = new docservers_controler();
				$docserver = $docserverControler->getDocserverToInsert(
				    $_SESSION['collection_id_choice']
				);
				if (empty($docserver)) {
					$_SESSION['error'] = _DOCSERVER_ERROR . ' : '
					    . _NO_AVAILABLE_DOCSERVER . ". " . _MORE_INFOS . ".";
					$location = "";
				} else {
					// some checking on docserver size limit
					$newSize = $docserverControler->checkSize(
					    $docserver, $_SESSION['upfile']['size']
					);
					if ($newSize == 0) {
						$_SESSION['error'] = _DOCSERVER_ERROR . ' : '
						    . _NOT_ENOUGH_DISK_SPACE . ". " . _MORE_INFOS . ".";
						?>
						<script type="text/javascript">
							var eleframe1 =  window.opener.top.frames['process_frame'].document.getElementById('list_attach');
							eleframe1.location.href = '<?php
					    echo $_SESSION['config']['businessappurl'];
					    ?>index.php?display=true&module=attachments&page=frame_list_attachments';
						</script>
						<?php
						exit();
					} else {
					    $fileInfos = array(
                    		"tmpDir"      => $_SESSION['config']['tmppath'],
                    		"size"        => $_SESSION['upfile']['size'],
                    		"format"      => $_SESSION['upfile']['format'],
                    		"tmpFileName" => $tmpFileName,
                        );

                    	$storeResult = array();
                    	$storeResult = $docserverControler->storeResourceOnDocserver(
                    	    $_SESSION['collection_id_choice'], $fileInfos
                    	);

                    	if (isset($storeResult['error']) && $storeResult['error'] <> '') {
                    	    $_SESSION['error'] = $storeResult['error'];
                    	} else {
                            $resAttach = new resource();
							$_SESSION['data'] = array();
							array_push(
						        $_SESSION['data'],
								array(
									'column' => "typist",
								    'value' => $_SESSION['user']['UserId'],
								    'type' => "string",
								)
							);
							array_push(
								$_SESSION['data'],
								array(
								    'column' => "format",
								    'value' => $theExt,
								    'type' => "string",
								)
							);
							array_push(
								$_SESSION['data'],
								array(
								    'column' => "docserver_id",
								    'value' => $storeResult['docserver_id'],
								    'type' => "string",
								)
							);
							array_push(
								$_SESSION['data'],
								array(
								    'column' => "status",
								    'value' => 'NEW',
								    'type' => "string",
								)
							);
							array_push(
								$_SESSION['data'],
								array(
								    'column' => "offset_doc",
								    'value' => ' ',
								    'type' => "string",
								)
							);
							array_push(
								$_SESSION['data'],
								array(
								    'column' => "logical_adr",
								    'value' => ' ',
								    'type' => "string",
								)
							);
							array_push(
								$_SESSION['data'],
								array(
								    'column' => "title",
								    'value' => $title,
								    'type' => "string",
								)
							);
							array_push(
								$_SESSION['data'],
								array(
								    'column' => "coll_id",
								    'value' => $_SESSION['collection_id_choice'],
								    'type' => "string",
								)
							);
							array_push(
								$_SESSION['data'],
								array(
								    'column' => "res_id_master",
								    'value' => $_SESSION['doc_id'],
								    'type' => "integer",
								)
							);
							if ($_SESSION['origin'] == "scan") {
								array_push(
								    $_SESSION['data'],
									array(
									    'column' => "scan_user",
									    'value' => $_SESSION['user']['UserId'],
									    'type' => "string",
									)
								);
								array_push(
									$_SESSION['data'],
									array(
									    'column' => "scan_date",
									    'value' => $req->current_datetime(),
									    'type' => "function",
									)
								);
							}
							array_push(
								$_SESSION['data'],
								array(
								    'column' => "type_id",
								    'value' => 0,
								    'type' => "int",
								)
							);

							$id = $resAttach->load_into_db(
							    RES_ATTACHMENTS_TABLE,
							    $storeResult['destination_dir'],
								$storeResult['file_destination_name'] ,
								$storeResult['path_template'],
								$storeResult['docserver_id'], $_SESSION['data'],
								$_SESSION['config']['databasetype']
							);
							if ($id == false) {
								$_SESSION['error'] = $resAttach->get_error();
								//echo $resource->get_error();
								//$resource->show();
								//exit();
							} else {
								if ($_SESSION['history']['attachadd'] == "true") {
									$users = new history();
									$view = $sec->retrieve_view_from_coll_id(
									    $_SESSION['collection_id_choice']
									);
									$users->add(
										$view, $_SESSION['doc_id'], "ADD",
										ucfirst(_DOC_NUM) . $id . ' '
										. _NEW_ATTACH_ADDED . ' ' . _TO_MASTER_DOCUMENT
                                        . $_SESSION['doc_id'],
										$_SESSION['config']['databasetype'],
										'apps'
									);
									$_SESSION['error'] = _NEW_ATTACH_ADDED;
									$users->add(
										RES_ATTACHMENTS_TABLE, $id, "ADD",
										$_SESSION['error'] . " (" . $title
										. ") ",
										$_SESSION['config']['databasetype'],
										'attachments'
									);
								}
							}
						}
					}
				}
			}
			if (empty($_SESSION['error'])
			    || $_SESSION['error'] == _NEW_ATTACH_ADDED
			) {
			    ?>
				<script type="text/javascript">
					var eleframe1 =  window.opener.top.document.getElementById('list_attach');
					eleframe1.src = '<?php
				echo $_SESSION['config']['businessappurl'];
				?>index.php?display=true&module=attachments&page=frame_list_attachments';
					window.top.close();
				</script>
				<?php
				exit();
			}
		}
	}
}

//here we loading the html
$core->load_html();
//here we building the header
$core->load_header(_ATTACH_ANSWER, true, false);
$time = $core->get_session_time_expire();
?>
<body id="pop_up" onload="setTimeout(window.close, <?php
echo $time;
?>*60*1000);" >
<div class="error"><?php
echo $_SESSION['error'];
$_SESSION['error'] = "";
?></div>
<h2 class="tit"><?php  echo _ATTACH_ANSWER;?> </h2>

	<form enctype="multipart/form-data" method="post" name="attachement" class="forms">
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
       <input type="submit" value="<?php
echo _VALIDATE;
?>" name="valid" id="valid" class="button" />
	<input type="button" value="<?php
echo _CANCEL;
?>" name="cancel" class="button"  onclick="self.close();"/>
  </p>
 </form>
<?php $core->load_js();?>
</body>
</html>
