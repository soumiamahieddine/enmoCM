<?php  /**
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
session_name('PeopleBox'); 
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_service('manage_notes_doc', 'notes');
?>
<!--<h2 onclick="change(23)" id="h223" class="categorie">
	<img src="<?php  echo $_SESSION['config']['businessappurl'].$_SESSION['config']['img'];?>/plus.png" alt="" />&nbsp;<b><?php  echo _NOTES;?> :</b>
	<span class="lb1-details">&nbsp;</span>
</h2>-->
<br>
<!--<div class="desc" id="desc23" style="display:none">
	<div class="ref-unit">-->
<img src="<?php  echo $_SESSION['urltomodules'];?>notes/img/modif_note.png" border="0" alt="" /> 
<a href="javascript://" onclick="ouvreFenetre('<?php  echo $_SESSION['urltomodules']."notes/";?>note_add.php?identifier=<?php  echo $_SESSION['doc_id']; ?>&coll_id=<?php  echo $_SESSION['collection_id_choice'];?>', 550, 350)"><?php  echo _ADD_NOTE;?></a>
<iframe name="list_notes_doc" id="list_notes_doc" src="<?php  echo $_SESSION['urltomodules'].'notes/';?>frame_notes_doc.php" frameborder="0" width="100%" height="440px"></iframe>
<!--<div>

</div>
	</div>
</div>-->
