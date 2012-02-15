<?php
/**
* File : note_add.php
*
* Popup add a note
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
require_once "core" . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    . "class_security.php";
require_once "core/class/class_history.php";
require_once 'modules/notes/notes_tables.php';
$core = new core_tools();
$sec = new security();
//here we loading the lang vars
$core->load_lang();
$func = new functions();
$db = new dbquery();
$db->connect();
$core->load_html();
//here we building the header
$core->load_header(_ADD_NOTE, true, false);
$time = $core->get_session_time_expire();
$identifier = '';
$table = '';
$collId = '';
$extendUrl = '';
$extendUrlValue = '';
if (isset($_REQUEST['size']) && $_REQUEST['size'] == "full") {
	$extendUrl = "&size=full";
	$extendUrlValue = $_REQUEST['size'];
}

if (isset($_REQUEST['identifier']) && ! empty($_REQUEST['identifier'])) {
	$identifier = trim($_REQUEST['identifier']);
}
if (isset($_REQUEST['coll_id']) && ! empty($_REQUEST['coll_id'])) {
	$collId = trim($_REQUEST['coll_id']);
	$view = $sec->retrieve_view_from_coll_id($collId);
	$table = $sec->retrieve_table_from_coll($collId);
}

if (isset($_REQUEST['table']) && ! empty($_REQUEST['table'])) {
	$table = trim($_REQUEST['table']);
}
?>
<body id="pop_up" onload="resizeTo(450, 350);setTimeout(window.close, <?php
echo $time;
?>*60*1000);">
<?php

if (isset($_REQUEST['notes']) && ! empty($_REQUEST['notes'])) {
	$date = $db->current_datetime();

	$db->query(
		"INSERT INTO " . NOTES_TABLE . "(identifier, note_text, date_note, "
	    . "user_id, coll_id, tablename) VALUES"
		. " (".$identifier . ", '" . $db->protect_string_db($_REQUEST['notes'])
		. "', " . $date . ", '"
		. $db->protect_string_db($_SESSION['user']['UserId']) . "', '"
		. $db->protect_string_db($collId) . "', '"
		. $db->protect_string_db($table) . "')"
    );
	if ($_SESSION['history']['noteadd']) {
	    $hist = new history();
		$db->query(
			"SELECT id FROM " . NOTES_TABLE . " WHERE date_note = " . $date
		    . " and identifier = " . $identifier . " and user_id = '"
		    . $_SESSION['user']['UserId'] . "' and coll_id = '" . $collId . "' "
		);
		$res = $db->fetch_object();
		$id = $res->id;
		if (isset($_SESSION['origin']) && $_SESSION['origin'] == "show_folder") {
			$hist->add(
			    $table, $identifier, "ADD", 'noteadd', _ADDITION_NOTE . _ON_FOLDER_NUM
			    . $identifier . ' (' . $id . ')',
			    $_SESSION['config']['databasetype'], 'notes'
			);
		} else {
			$hist->add(
			    $view, $identifier, "ADD", 'noteadd',  _ADDITION_NOTE . _ON_DOC_NUM
			    . $identifier . ' (' . $id . ')',
			    $_SESSION['config']['databasetype'], 'notes'
			);
		}

		$hist->add(
		    NOTES_TABLE, $id, "ADD", 'noteadd', _NOTES_ADDED . ' (' . $id . ')',
		    $_SESSION['config']['databasetype'], 'notes'
		);
	}

	if (isset($_REQUEST['origin']) && $_SESSION['origin'] <> 'valid'
	    && $_SESSION['origin'] <> 'qualify'
	) {
		//$_SESSION['error'] = _ADDITION_NOTE;
	}
	?>
		<script type="text/javascript">
        <?php
    if (isset($_SESSION['origin']) && $_SESSION['origin'] == "process") {
        ?>
        var eleframe1 = window.opener.top.frames['process_frame'].document.getElementById('list_notes_doc');
        <?php
    } else if (isset($_SESSION['origin']) && ($_SESSION['origin'] == "valid"
        || $_SESSION['origin'] == 'qualify')
    ) {
        ?>
        var eleframe1 = window.opener.top.frames['index'].document.frames['myframe'].document.getElementById('list_notes_doc');
        <?php
    } else if (isset($_SESSION['origin'])
        && $_SESSION['origin'] == "show_folder"
    ) {
        ?>
        var eleframe1 = window.opener.top.document.getElementById('list_notes_folder');
        <?php
    } else {
        ?>
        var eleframe1 = window.opener.top.document.getElementById('list_notes_doc');
        <?php
    }
    if (isset($_SESSION['origin']) && $_SESSION['origin'] == "show_folder") {
        ?>
        eleframe1.src = '<?php
        echo $_SESSION['config']['businessappurl'];
        ?>index.php?display=true&module=notes&page=frame_notes_folder<?php
        echo $extendUrl;
        ?>';
        <?php
    } else {
        ?>
        eleframe1.src = '<?php
        echo $_SESSION['config']['businessappurl'];
        ?>index.php?display=true&module=notes&page=frame_notes_doc<?php
        echo $extendUrl;
        ?>';
        <?php
    }
    ?>
    window.top.close();
	</script>
    <?php
} else {
    ?>
    <h2 class="tit" style="padding:10px;"><img src="<?php
    echo $_SESSION['config']['businessappurl'];
    ?>static.php?filename=picto_add_b.gif" alt=""/> <?php
    echo _ADD_NOTE;
    ?> </h2>

	<div class="block" style="padding:10px;">
      <form name="form1" method="post" action="<?php
    echo $_SESSION['config']['businessappurl'];
    ?>index.php?display=true&module=notes&page=note_add&identifier=<?php
    echo $_GET['identifier'];
    ?>&table=<?php
    echo $table;
    ?>&coll_id=<?php
    echo $collId;?>" >
		<input type="hidden" name="display" value="true" />
		<input type="hidden" name="modules" value="notes" />
		<input type="hidden" name="page" value="note_add" />
		<input type="hidden" value="<?php
    echo $identifier;?>" name="identifier" id="identifier">
		<input type="hidden" value="<?php
    echo $extendUrlValue;?>" name="size" id="size">
		<textarea  cols="65" rows="10"  name="notes"  id="notes" ></textarea>
	   <br/>
        <p class="buttons">
			<input type="submit" name="Submit" value="<?php
    echo _ADD_NOTE;?>" class="button"/>
             <input type="submit" name="Submit2" value="<?php
    echo _CANCEL;?>" onclick="javascript:self.close();" class="button"/>
        </p>
      </form>
	</div>
	<div class="block_end">&nbsp;</div>
 <?php
}
$core->load_js();
?>
</body>
</html>
