<?php
/**
* File : notes_details.php
*
* Popup to show the notes
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
require_once "core/class/class_security.php";
require_once "core/class/class_request.php";
require_once "core/class/class_history.php";
require_once 'core/core_tables.php';
require_once 'modules/notes/notes_tables.php';
$core = new core_tools();
$core->load_lang();
$sec = new security();
$req = new request();
$func = new functions();
$db = new dbquery();
$db->connect();
$table = '';
$collId = "";
$user = '';
$text = "";
$userId = '';
$date = "";
$identifier = '';
if (empty($_SESSION['collection_id_choice'])) {
	$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
	$collId = $_SESSION['collection_id_choice'] ;
} else if (isset($_REQUEST['coll_id'])&& empty($collId)) {
	$collId = $_REQUEST['coll_id'];
}
$view = $sec->retrieve_view_from_coll_id($collId);
$table = $sec->retrieve_table_from_coll($collId);

$error = '';
if (isset($_REQUEST['modify'])) {
	$id = $_REQUEST['id'];
	$identifier = $_REQUEST['identifier'];
	$table = $_REQUEST['table'];
	$collId = $_REQUEST['coll_id'];

	if (empty($_REQUEST['notes'])) {
		$error = _NOTES . ' ' . _EMPTY;
	} else if (empty($error)) {
		$text = $func->protect_string_db($_REQUEST['notes']);
		$db->query(
			"UPDATE ".NOTES_TABLE." SET note_text = '". $text
		    . "', date_note = " . $req->current_datetime() . " WHERE id = "
		    . $id
		);
		//$db->show();exit();
		if ($_SESSION['history']['noteup']) {
			$hist = new history();
			$hist->add(
			    NOTES_TABLE, $id , "UP", _NOTE_UPDATED . ' (' . $id . ')',
			    $_SESSION['config']['databasetype'], 'notes'
			);
			if ($_SESSION['origin'] == "show_folder" ) {
				$hist->add(
				    $table, $identifier, "UP", _NOTE_UPDATED . _ON_FOLDER_NUM
				    . $identifier . ' (' . $id . ')',
				    $_SESSION['config']['databasetype'], 'notes'
			    );
			} else {
				$hist->add(
				    $view, $identifier, "UP", _NOTE_UPDATED . _ON_DOC_NUM
				    . $identifier . ' (' . $id . ')',
				    $_SESSION['config']['databasetype'], 'notes'
				);
			}
		}
		//$_SESSION['error'] = _NOTES_MODIFIED;
		?>
        <script type="text/javascript">window.opener.location.reload();self.close();</script>
        <?php
		exit();
	}

}
if (isset($_REQUEST['delete'])) {
	$id = $_REQUEST['id'];
	$identifier = $_REQUEST['identifier'];

	$db->query("delete from " . NOTES_TABLE . " where id = " . $id);

	if ($_SESSION['history']['notedel']) {
		$hist = new history();
		$hist->add(
		    NOTES_TABLE, $id, "DEL", _NOTES_DELETED . ' (' . $id . ')',
		    $_SESSION['config']['databasetype'], 'notes'
		);
		if ($_SESSION['origin'] == "show_folder" ) {
		    $hist->add(
		        $table, $identifier, "DEL", _NOTES_DELETED . _ON_FOLDER_NUM
		        . $identifier . ' (' . $id . ')',
		        $_SESSION['config']['databasetype'], 'notes'
		    );
		} else {
			$hist->add(
			    $view, $identifier, "DEL", _NOTES_DELETED . _ON_DOC_NUM
			    . $identifier . ' (' . $id . ')',
			    $_SESSION['config']['databasetype'], 'notes'
			);
		}
	}
	//$_SESSION['error'] = _NOTES_DELETED;
	?>
    <script type="text/javascript">window.opener.location.reload();self.close();</script>
    <?php
	exit();
}

if (isset($_REQUEST['id'])) {
	$sId = $_REQUEST['id'];
} else {
	$sId = "";
}
if (isset($_REQUEST['identifier'])) {
	$identifier = $_REQUEST['identifier'];
}
if (isset($_REQUEST['table']) && empty($table)) {
	$table = $_REQUEST['table'];
}
if (isset($_REQUEST['coll_id']) && empty($collId)) {
	$collId = $_REQUEST['coll_id'];
}

$core->load_html();
//here we building the header
$core->load_header(_NOTES);
$time = $core->get_session_time_expire();
?>
<body id="pop_up" onload="resizeTo(410, 380);setTimeout(window.close, <?php
echo $time;
?>*60*1000);">
<?php
if (empty($table) && empty($collId)) {
	$error = _PB_TABLE_COLL;
} else {

	if (! empty($collId)) {
		$where = " and coll_id = '" . $collId . "'";
	} else {
		$where = " and tablename = '" . $table . "'";
	}
	$db->query(
		"select n.identifier, n.date_note, n.user_id, n.note_text, u.lastname, "
	    . "u.firstname from " . NOTES_TABLE . " n inner join ". USERS_TABLE
	    . " u on n.user_id  = u.user_id where n.id = " . $sId . " " . $where
	);
	//$db->show();
	$line = $db->fetch_object();
	$user = $func->show_string($line->lastname . " " . $line->firstname);
	$text = $func->show_string($line->note_text);
	$userId = $line->user_id;
	$date = $line->date_note;
	$identifier = $line->identifier;
}

$canModify = false;
if (trim($userId) == $_SESSION['user']['UserId']) {
	$canModify = true;
}
?>
<div class="error"><?php
echo $error;
$error = '';
?></div>
<h2 class="tit" style="padding:10px;"><?php  echo _NOTES;?> </h2>
	<h2 class="sstit" style="padding:10px;"><?php
echo _NOTES . " " . _OF . " " . $user . " (" . $date . ") ";
?></h2>

	<div class="block" style="padding:10px">
      <form name="form1" method="post" class="forms" action="<?php
echo $_SESSION['config']['businessappurl'] . "index.php?display=true"
    . "&module=notes&page=note_details";
?>">
		<input type="hidden" name="display" value="true" />
		<input type="hidden" name="modules" value="notes" />
		<input type="hidden" name="page" value="note_details" />
		<textarea  <?php
if (! $canModify) {
    ?>readonly="readonly" class="readonly" <?php
}
?>style="width:380px" cols="70" rows="10"  name="notes"  id="notes"><?php
echo $text;
?></textarea>

      	<input type="hidden" name="id" id="id" value="<?php  echo $sId; ?>"/>
      	<input type="hidden" name="identifier" id="identifier" value="<?php
echo $identifier;
?>"/>
        <input type="hidden" name="table" id="table" value="<?php
echo $table;
?>"/>
        <input type="hidden" name="coll_id" id="coll_id" value="<?php
echo $collId;
?>"/>
	   <br/>
       <p class="buttons">
    <?php
if ($canModify) {
    ?>
    <input type="submit" name="modify" id="modify" value="<?php
    echo _MODIFY;
    ?>"  class="button"/>
    <input type="submit" name="delete" id="delete" value="<?php
    echo _DELETE;
    ?>"  class="button"/>
    <?php
}
?>
    <input type="button" name="close_button" value="<?php
echo _CLOSE_WINDOW;
?>" onclick="javascript:self.close();" class="button"/>
    </p>

      </form>
	</div>
	<div class="block_end">&nbsp;</div>
</body>
</html>
