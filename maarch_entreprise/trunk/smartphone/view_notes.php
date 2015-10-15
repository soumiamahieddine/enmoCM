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
require_once('core/core_tables.php');
require_once('apps/maarch_entreprise/apps_tables.php');
require_once('modules/notes/notes_tables.php');
$core = new core_tools();
$core->load_lang();
$db = new dbquery();
$db->connect();

$db->query(
    "SELECT u.lastname as lastname, u.firstname as firstname, notes.note_text, "
    . "notes.date_note as date_note, notes.id as notes_id FROM " . NOTES_TABLE . " notes, "
    . $_SESSION['tablename']['users'] ." u where notes.user_id = u.user_id and " 
    . "notes.identifier = " . $_REQUEST['id']
    . " and notes.coll_id ='" . $_REQUEST['collId'] 
    . "' order by notes.date_note desc"
);
//$db->show();
$notesList = '<div id="newNote" class="row" style="display: none;"></div>';
if ($db->nb_result() < 1) {
    $notesList .= '<span id="noNotes">aucune note</span>';
} else {
    while ($line = $db->fetch_object()) {
		$line->note_text = str_replace("\n", "<br>", $line->note_text);
        $notesList .= '<div class="row" id="'.$line->notes_id.'">
        <div>' . $line->firstname . ' ' 
        . $line->lastname . ' le ' . $line->date_note . '</div>
		<table>
			<tr>
				<td>
					<form>
                        <i class="fa fa-remove fa-2x" title="' . _DELETE . '" onClick="delNotes('.$line->notes_id.');"></i>
					</form>
					<form class="panel" methode="POST" action="modify_notes.php" selected="true">
						<input type="hidden" name="oldtext" value="' . $line->note_text . '"/>
						<input type="hidden" name="notes_id" value="' . $line->notes_id . '"/>
						<a type="submit" href="#" style="color: #58585A;"><i class="fa fa-pencil-square-o fa-2x" title="'. _MODIFY .'"></i></a>&nbsp;<br>
					</form>
				</td>
				<td align="left">
					' . $line->note_text 
					. '&nbsp;&nbsp;
					
				</td>
			</tr>
		</table>
        </div>';
    }
}
?>

<div id="notes" title="Notes" class="panel">
    <fieldset>
        <h4>
            <?php
            echo $_SESSION['user']['FirstName'] . ' ' 
                . $_SESSION['user']['LastName']
                . ' le ' . date('d/m/Y'); 
            ?>
        </h4>
        <div class="row">
            <textarea cols="30" rows="3" id="fieldNotes" name="fieldNotes" style="width:98%;"></textarea>
            <input type="hidden" id='collId' name="collId" value="<?php echo $_REQUEST['collId'];?>"/>
            <input type="hidden" id='id' name="id" value="<?php echo $_REQUEST['id'];?>"/>
        </div>
    </fieldset>
	<div align="center">
		<input type="button" class="whiteButton" onclick="addNotes($('fieldNotes').value.replace('\n', '<br>'), $('collId').value, $('id').value);" value="<?php echo _ADD_NOTE;?>" />
	</div>
    <br/>
    <fieldset>
        <?php
        echo $notesList;
        ?>
    </fieldset>
</div>
