<?php  /**
* File : frame_notes_doc.php
*
* Frame, shows the notes of a document
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 06/2006
* @license GPL
* @author  Loïc Vinet  <dev@maarch.org>
*/

$core = new core_tools();
$core->load_lang();
$core->test_service('show_notes_list', 'notes');

$func = new functions();
if (empty($_SESSION['collection_id_choice'])) {
	$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
}

require_once "core/class/class_request.php";
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    . "class_list_show.php"
);
?>
<div id="welcome_box_right_notes" >
				<div class="block">
				<h2>Dernières annotations</h2>
				</div>
				<div class="blank_space">&nbsp;</div>
				Annotations de mes 10 dernieres affaires.... (pas finalisé)
</div>

</html>
