<?php
if (file_exists('../../../core/init.php')) {
    include_once '../../../core/init.php';
}
if (!isset($_SESSION['config']['coreurl'])) {
    header('location: ../../../');
}
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
require_once('core/class/class_db.php');
require_once('core/class/class_security.php');
require_once('core/class/class_history.php');
require_once('modules/notes/notes_tables.php');
require_once('core/core_tables.php');
require_once('apps/maarch_entreprise/apps_tables.php');

$core = new core_tools();
$core->load_lang();
if (isset($_REQUEST['oldtext']) && isset($_REQUEST['notes_id'])) { 
	$_REQUEST['oldtext'] = str_replace("<br>", "\n", $_REQUEST['oldtext']);?> 
	<div id="notes" title="Notes" class="panel">
		<form id="editnotes" title="EditNotes" class="panel" action="modify_notes.php" method="POST" selected="true">
			<fieldset>
				<h4>
					<?php
					echo $_SESSION['user']['FirstName'] . ' ' 
						. $_SESSION['user']['LastName']
						. ' le ' . date('d/m/Y'); 
					?>
				</h4>
				<div class="row">
					<textarea cols="30" rows="3" name="newtext" style="width:96%;"><?php echo $_REQUEST['oldtext']; ?></textarea>
					<input type="hidden" name="oldnote" value="<?php echo $_REQUEST['oldtext'];?>"/>
					<input type="hidden" name="notes_id" value="<?php echo $_REQUEST['notes_id'];?>"/>
				</div><br/>
				<a type="submit"  href="#" style="text-decoration:none;"><input class="whiteButton" type="submit" value="modifier"></a>
			</fieldset>
		</form>
	</div>
<?php
}

if (isset($_REQUEST['newtext']) && isset($_REQUEST['notes_id'])) {
	$_REQUEST['newtext'] = str_replace("\n", "<br>", $_REQUEST['newtext']);
	echo 'bonjour';
	$db = new dbquery();
	$db->connect();
	$date = $db->current_datetime();
	$query = " UPDATE " . NOTES_TABLE . " SET 
	note_text='" .$_REQUEST['newtext']. "'
	 , date_note=" .$date. "
	  WHERE note_text='" .$_REQUEST['oldnote'].
	  "' AND id=" .$_REQUEST['notes_id'];
	if($db->query($query)){
		//echo 'query ' . $query;
		$result = _NOTES_MODIFIED;
	} else {
		$result = _NOTES_NOT_MODIFIED;
	}
}
?>
<div id="<?php echo _NOTES;?>" title="<?php echo _NOTES;?>" class="panel">
	<fieldset>
		<h4 align="center">
			<?php
				echo $result;
			?>
		</h4>
	</fieldset>
	<a class="whiteButton" type="submit" href="index.php?page=welcome">retour</a>
</div>


