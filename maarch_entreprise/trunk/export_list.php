<?php 
/**
* File : export_list.php
*
* Export a result list in a csv file
*
* @package  Maarch PeopleBox 1.0
* @version 2.0
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*/
include('core/init.php'); 

if(file_exists($_SESSION['config']['lang'].'.php'))
{
	include($_SESSION['config']['lang'].'.php');
}
else
{
	$_SESSION['error'] = "Language file missing...<br/>";
}
require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require_once("core/class/class_request.php");

//----------------------- suppression fichiers d'export existants ----------------------------------------//

$source = $_SESSION['config']['exportdirectory'];
$source = "export";
$reps = scandir($source);
$find = false;

for($i=2; $i < count($reps); $i++)
{
	if($reps[$i] == $_SESSION['user']['UserId'])
	{
		$find = true;
		break;
	}
}

//$source = $_SESSION['config']['exportdirectory'].$_SESSION['user']['UserId'];

if($find)
{
//	unlink($source."\\export.csv");
//	$files = scandir($source."\\extract\\");
//	for($i = 2; $i < count($files); $i++)
//	{
//		unlink($source."\\extract\\".$files[$i]);
//	} 
}
else
{
//	if(!mkdir($source."\\"))
//	{
//		$_SESSION['error'] .= "Erreur lors de la cr&eacute;ation du r&eacute;pertoire d'export de l'utilisateur : ".$_SESSION['user']['UserId']."<br/>";
//	}
//	else
//	{
//		if(!mkdir($source."\\extract\\"))
//		{
//			$_SESSION['error'] .= "Erreur lors de la cr&eacute;tion du r&eacute;pertoire extract lors de l'export";
//		}
//	}
}

//----------------------- Constitution de la liste de résultats ----------------------------------------//
$result = array();

$result = $_SESSION['SEARCH_ADV_RESULT']['PARAM']['RESULT'];
$listvalue = array();
$listcolumn = array();
$listshow = array();
//print_r($result);
$i = $_SESSION['SEARCH_ADV_RESULT']['PARAM']['NB_TOTAL'];
// put in tab the different label of the column
for ($i=0;$i<1;$i++)
{
	for ($j=0;$j<count($result[$j]);$j++)
	{
		for ($j=0;$j<count($result[$i][$j]);$j++)
		{	
				array_push($listcolumn,$result[$i][$j]["LABEL"]);
				array_push($listshow,$result[$i][$j]["SHOW"]);
		}
	}
}
$func = new functions();

//columns
for($count_column = 0;$count_column < count($listcolumn);$count_column++)
{
	if($listshow[$count_column]==true)
	{
		$texte .=$listcolumn[$count_column].";";
	}
}
//$texte .= "lien vers le fichier".chr(13);
$texte .= chr(13);
//infos
$nb = $_SESSION['SEARCH_ADV_RESULT']['PARAM']['NB_TOTAL'];

$connexion = new dbquery();
$connexion->connect();
for($theline = 0; $theline < $nb ; $theline++)
{
	//$select = array();
	//$select["res_x"]= array();
	//$tab_select = array();
	//array_push($select["res_x"],"RES_ID, DOCSERVER_ID, PATH, FILENAME, FORMAT");
	//$where_request = "RES_ID = ".$result[$theline][0]['VALUE_EXPORT']." ";
	//$request = new request();
	//$tab_select=$request->select($select,$where_request,"",$_SESSION['config']['databasetype']);
	/*for ($i=0;$i<count($tab_select);$i++)
	{
		for ($j=0;$j<count($tab_select[$i]);$j++)
		{
			foreach(array_keys($tab_select[$i][$j]) as $value)
			{
				if($tab_select[$i][$j][$value]=='res_id')
				{
					$res_id = $tab_select[$i][$j]['value'];
				}
				if($tab_select[$i][$j][$value]=="DOCSERVER_ID")
				{
					$docserver = $tab_select[$i][$j]['value'];
				}
				if($tab_select[$i][$j][$value]=="PATH")
				{
					$path = $tab_select[$i][$j]['value'];
				}
				if($tab_select[$i][$j][$value]=="FILENAME")
				{
					$filename = $tab_select[$i][$j]['value'];
				}
				if($tab_select[$i][$j][$value]=="FORMAT")
				{
					$format = $tab_select[$i][$j]['value'];
				}
			} 
		}
	}
	//$select2 = array();
	//$select2["DOCSERVERS"]= array();
	//$tab_select2 = array();
	//array_push($select2["DOCSERVERS"],"PATH_TEMPLATE");
	//$where_request2 = " DOCSERVER_ID = '".$docserver."'";
	//$request2 = new request();
	//$tab_select2=$request2->select($select2,$where_request2,"",$_SESSION['config']['databasetype']);
	/*for ($i=0;$i<count($tab_select2);$i++)
	{
		for ($j=0;$j<count($tab_select2[$i]);$j++)
		{
			foreach(array_keys($tab_select2[$i][$j]) as $value)
			{
				if($tab_select2[$i][$j][$value]=="PATH_TEMPLATE")
				{
					$docserver= $tab_select2[$i][$j]['value'];
				}
			}
		}
	}*/
	$file = $docserver.$path.$filename;
	$file = str_replace("#","\\",$file);
	$destination = $source."\\extract\\";
	//copy($file, $destination.$filename);
	
	//print_r($result);

	for($count_column = 0;$count_column < count($listcolumn);$count_column++)
	{
		if ($result[$theline][$count_column]['VALUE_EXPORT'] <> "NO_VISIBILITY037")
		{ $texte .= $result[$theline][$count_column]['VALUE_EXPORT'].";"; }
	}
	//$texte .= "file:///".$source."/extract/".$filename.chr(13);
	$texte .= chr(13);
	//echo $texte;
}

//----------------------- constitution du fichier d'index ----------------------------------------//
echo $source."/export.csv <br/>";
$monfichier = fopen($source."/export.csv","w+,");
fwrite($monfichier, $texte);

?>
<a href="export/export.csv"><?php  echo _CONSULT_EXTRACTION;?></a><br /><br/>
<input type="button" onclick="window.close();" value="<?php  echo _CLOSE_WINDOW;?>" class="button" />
