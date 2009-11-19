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
include('core/init.php');

require_once("core/class/class_functions.php");
require_once("core/class/class_core_tools.php");
$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_service('show_notes_list', 'notes');


require_once("core/class/class_db.php");
$func = new functions();

if(empty($_SESSION['collection_id_choice']))
{
	$_SESSION['collection_id_choice']= $_SESSION['user']['collections'][0];
}

require_once("core/class/class_request.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");

/*
$func = new functions();
$select[$_SESSION['tablename']['users']] = array();
array_push($select[$_SESSION['tablename']['users']],"user_id","lastname","firstname");
$select[$_SESSION['tablename']['not_notes']] = array();
array_push($select[$_SESSION['tablename']['not_notes']],"id", "date", "note_text", "user_id");
$where = " identifier = ".$_SESSION['doc_id']." ";
$request= new request;
$tabNotes=$request->select($select,$where,"order by ".$_SESSION['tablename']['not_notes'].".date desc",$_SESSION['config']['databasetype'], "500", true,$_SESSION['tablename']['not_notes'], $_SESSION['tablename']['users'], "user_id" );
$ind_notes1d = '';
for ($ind_notes1=0;$ind_notes1<count($tabNotes);$ind_notes1++)
{
	for ($ind_notes2=0;$ind_notes2<count($tabNotes[$ind_notes1]);$ind_notes2++)
	{
		foreach(array_keys($tabNotes[$ind_notes1][$ind_notes2]) as $value)
		{
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="id")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["id"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]= _ID;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]="18";
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=false;
				$ind_notes1d = $tabNotes[$ind_notes1][$ind_notes2]['value'];
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="user_id")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["user_id"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]= _ID;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]="18";
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=false;
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="lastname")
			{
				$tabNotes[$ind_notes1][$ind_notes2]['value']=$request->show_string($tabNotes[$ind_notes1][$ind_notes2]['value']);
				$tabNotes[$ind_notes1][$ind_notes2]["lastname"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]=_LASTNAME;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]="10";
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="date")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["date"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]=_DATE;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]="10";
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="firstname")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["firstname"]= $tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]=_FIRSTNAME;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]="10";
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="note_text")
			{
				$tabNotes[$ind_notes1][$ind_notes2]['value'] = '<a href="javascript://" onclick="ouvreFenetre(\''.$_SESSION['urltomodules'].'notes/note_details.php?id='.$ind_notes1d.'&amp;resid='.$_SESSION['doc_id'].'&amp;coll_id='.$_SESSION['collection_id_choice'].'\', 450, 300)">'.substr($request->show_string($tabNotes[$ind_notes1][$ind_notes2]['value']), 0, 20).'... <span class="sstit"> > '._READ.'</span>';
				$tabNotes[$ind_notes1][$ind_notes2]["note_text"]= $tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]=_NOTES;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]="30";
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
		}
	}
}

$core_tools->load_html();

$core_tools->load_header();
?>

<?php
$title = '';
$list_notes = new list_show();
$list_notes->list_simple($tabNotes, count($tabNotes), $title,'id','id', false, '','listing2');
*/
?>



<div id="welcome_box_right_notes" >
				<div class="block">
				<h2>Dernières annotations</h2>
				</div>
				<div class="blank_space">&nbsp;</div>
				Annotations de mes 10 dernieres affaires.... (pas finalisé)
</div>

</html>
