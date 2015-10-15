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

$db = new dbquery();
$db->connect();
$sec = new security();
$table = $sec->retrieve_table_from_coll($_REQUEST['coll_id']);
$date = $db->current_datetime();
$query = "INSERT INTO " . NOTES_TABLE . "(identifier, note_text, date_note, "
	. "user_id, coll_id, tablename) VALUES"
	. " (".$_REQUEST['id'] . ", '" . $db->protect_string_db($_REQUEST['fieldNotes'])
	. "', " . $date . ", '"
	. $db->protect_string_db($_SESSION['user']['UserId']) . "', '"
	. $db->protect_string_db($_REQUEST['coll_id']) . "', '"
	. $db->protect_string_db($table) . "')";

$returnId = $db->query($query);	

if (!$returnId) {
	$return['status'] = 0;
	$return['msg']    = 'fail';
	echo json_encode($return);
	exit;
}

$return['status']  = 1;
$return['msg']     = 'note ajoutée';
$return['newNote'] = '<div id="newNote" class="row" style="display: none;">';
$return['newNote'] .= '</div>';
$return['newNote'] .= '<div class="row">';
$return['newNote'] .= '<div>'.$_SESSION['user']['FirstName'].' '.$_SESSION['user']['LastName'].' le '.date('Y-m-d').'</div>';
$return['newNote'] .= '<table>
							<tr>
								<td>
									<form class="panel" methode="POST" action="delete_notes.php" selected="true">
										<input type="hidden" name="id" value="' . $line->notes_id . '"/>
										<a type="submit" href="#" style="color: #58585A;"><i class="fa fa-remove fa-2x" title="' . _DELETE . '"></i></a>&nbsp;<br>
									</form>
									<form class="panel" methode="POST" action="modify_notes.php" selected="true">
										<input type="hidden" name="oldtext" value="' . $_REQUEST['fieldNotes'] . '"/>
										<a type="submit" href="#" style="color: #58585A;"><i class="fa fa-pencil-square-o fa-2x" title="'. _MODIFY .'"></i></a>&nbsp;<br>
									</form>
								</td>
								<td align="left">
									' . $_REQUEST['fieldNotes'] . '&nbsp;&nbsp;
								</td>
							</tr>
						</table>';
$return['newNote'] .= '</div>';
echo json_encode($return);