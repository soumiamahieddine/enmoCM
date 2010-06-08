<?php  /**
* File : frame_notes_doc.php
*
* Frame, shows the notes of a document
*
* @package Maarch LetterBox 2.3
* @version 1.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_service('manage_notes_doc', 'notes');
$func = new functions();
//$db = new dbquery();
//$db->connect();
if(empty($_SESSION['collection_id_choice']))
{
	$_SESSION['collection_id_choice']= $_SESSION['user']['collections'][0];
}

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
$func = new functions();
$select[$_SESSION['tablename']['users']] = array();
array_push($select[$_SESSION['tablename']['users']],"user_id","lastname","firstname");
$select[$_SESSION['tablename']['not_notes']] = array();
array_push($select[$_SESSION['tablename']['not_notes']],"id", "date_note", "note_text", "user_id");
$where = " identifier = ".$_SESSION['current_folder_id']." ";
$request= new request;
$tabNotes=$request->select($select,$where,"order by ".$_SESSION['tablename']['not_notes'].".date_note desc",$_SESSION['config']['databasetype'], "500", true,$_SESSION['tablename']['not_notes'], $_SESSION['tablename']['users'], "user_id" );
$ind_notes1d = '';


if($_GET['size'] == "full")
{
	$size_medium = "15";
	$size_small = "15";
	$size_full = "70";
	$css = "listing spec detailtabricatordebug";
	$body = "";
	$cut_string = 100;
	$extend_url = "&size=full";
}
else
{
	$size_medium = "18";
	$size_small = "10";
	$size_full = "30";
	$css = "listingsmall";
	$body = "iframe";
	$cut_string = 20;
	$extend_url = "";
}

for ($ind_notes1=0;$ind_notes1<count($tabNotes);$ind_notes1++)
{
	for ($ind_notes2=0;$ind_notes2<count($tabNotes[$ind_notes1]);$ind_notes2++)
	{
		foreach(array_keys($tabNotes[$ind_notes1][$ind_notes2]) as $value)
		{
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="id")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["id"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]= 'ID';
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_small;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
				$ind_notes1d = $tabNotes[$ind_notes1][$ind_notes2]['value'];
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="user_id")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["user_id"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]= _ID;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_small;
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
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_small ;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="date_note")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["date_note"]=$tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]=_DATE;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_small;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="left";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="firstname")
			{
				$tabNotes[$ind_notes1][$ind_notes2]["firstname"]= $tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]=_FIRSTNAME;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_small;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
			if($tabNotes[$ind_notes1][$ind_notes2][$value]=="note_text")
			{
				$tabNotes[$ind_notes1][$ind_notes2]['value'] = '<a href="javascript://" onclick="ouvreFenetre(\''.$_SESSION['config']['businessappurl'].'index.php?display=true&module=notes&page=note_details&id='.$ind_notes1d.'&amp;resid='.$_SESSION['current_folder_id'].'&amp;coll_id='.$_SESSION['collection_folders'].$extend_url.'\', 450, 300)">'.$func->cut_string($request->show_string($tabNotes[$ind_notes1][$ind_notes2]['value']), $cut_string).'<span class="sstit"> > '._READ.'</span>';
				$tabNotes[$ind_notes1][$ind_notes2]["note_text"]= $tabNotes[$ind_notes1][$ind_notes2]['value'];
				$tabNotes[$ind_notes1][$ind_notes2]["label"]=_NOTES;
				$tabNotes[$ind_notes1][$ind_notes2]["size"]=$size_full;
				$tabNotes[$ind_notes1][$ind_notes2]["label_align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["align"]="center";
				$tabNotes[$ind_notes1][$ind_notes2]["valign"]="bottom";
				$tabNotes[$ind_notes1][$ind_notes2]["show"]=true;
			}
		}
	}
}
//$request->show_array($tabNotes);
$core_tools->load_html();
//here we building the header
$core_tools->load_header('', true, false);
?>
<body id="<?php echo $body; ?>">
<?php
$title = '';
$list_notes = new list_show();
$list_notes->list_simple($tabNotes, count($tabNotes), $title,'id','id', false, '',$css);
$core_tools->load_js();
?>
</body>
</html>
