<?php
/**
* File : notes_doc.php
*
* Shows the notes of a document
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
$core = new core_tools();
//here we loading the lang vars
$core->load_lang();
$core->test_service('manage_notes_doc', 'notes');
?>

<img src="<?php
echo $_SESSION['config']['businessappurl'];
?>static.php?filename=modif_note.png&module=notes" border="0" alt="" />
<a href="javascript://" onclick="ouvreFenetre('<?php
echo $_SESSION['config']['businessappurl'];
?>index.php?display=true&module=notes&page=note_add&identifier=<?php
echo $_SESSION['doc_id'];

?>&coll_id=<?php
echo $_SESSION['collection_id_choice'];
?>', 550, 350)"><?php  echo _ADD_NOTE;?></a>
<iframe name="list_notes_doc" id="list_notes_doc" src="<?php
echo $_SESSION['config']['businessappurl'];
?>index.php?display=true&module=notes&page=frame_notes_doc" frameborder="0" width="100%" height="440px"></iframe>

