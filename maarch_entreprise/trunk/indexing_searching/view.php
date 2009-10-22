<?php
/*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief  View a document
*
* @file view.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/

session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");
require_once($_SESSION['pathtocoreclass']."class_security.php");

$core_tools = new core_tools();
$core_tools->test_user();
//here we loading the lang vars
$core_tools->load_lang();
$function = new functions();
$sec = new security();
if(isset($_REQUEST['id']))
{
	$s_id = $_REQUEST['id'];
}
else
{
	$s_id = "";
}

if($s_id =='' )
{
	$_SESSION['error'] = _THE_DOC.' '._IS_EMPTY;
	header("location: ".$_SESSION['config']['businessappurl']."index.php");
	exit();
}
else
{
	$connexion = new dbquery();
	$connexion->connect();
	$table ="";

	if(isset($_SESSION['collection_id_choice']) && !empty($_SESSION['collection_id_choice']))
	{
		$table = $sec->retrieve_view_from_coll_id($_SESSION['collection_id_choice']);

		if(!$table)
		{
			$table = $sec->retrieve_table_from_coll($_SESSION['collection_id_choice']);
		}
	}
	else
	{
		if(isset($_SESSION['collections'][0]['view'])&& !empty($_SESSION['collections'][0]['view']))
		{
			$table = $_SESSION['collections'][0]['view'];
		}
		else
		{
			$table = $_SESSION['collections'][0]['table'];
		}
	}

	$where2 = "";

	if($_SESSION['origin'] <> "basket" && $_SESSION['origin'] <> "workflow")
	{
		$cpt_access_to_coll = 0;
		for($i=0; $i < count($_SESSION['user']['security']); $i++)
		{
			if($_SESSION['collection_id_choice'] == $_SESSION['user']['security'][$i]['coll_id'])
			{
				$where2 = " and ( ".$_SESSION['user']['security'][$i]['where']." ) ";
			}
			$cpt_access_to_coll++;
		}
		if($cpt_access_to_coll==0)
		{
			$where2 = " and 1=-1";
		}
	}
	$connexion->query("select res_id, docserver_id, path, filename, format, fingerprint from ".$table." where res_id = ".$s_id.$where2);
	//$connexion->show();
	if($connexion->nb_result() == 0)
	{
		//$_SESSION['error'] = _THE_DOC." "._EXISTS_OR_RIGHT."&hellip;";
		header("location: ".$_SESSION['config']['businessappurl']."index.php?page=no_right");
		exit();
	}
	else
	{
		$line = $connexion->fetch_object();

		$docserver = $line->docserver_id;
		$path = $line->path;
		$filename = $line->filename;
		$format = $line->format;
		$md5 = $line->fingerprint;
		$fingerprint_from_db = $line->fingerprint;
		$connexion->query("select path_template from ".$_SESSION['tablename']['docservers']." where docserver_id = '".$docserver."'");
		//$connexion->show();
		$line_doc = $connexion->fetch_object();
		$docserver = $line_doc->path_template;
		$file = $docserver.$path.$filename;
		$file = str_replace("#",DIRECTORY_SEPARATOR,$file);
		$use_tiny_mce = false;

		if(strtolower($format) ==  'maarch' && $core_tools->is_module_loaded('templates'))
		{
			$type_state = true;
			$use_tiny_mce = true;
		}
		else
		{
			require_once($_SESSION['config']['businessapppath'].'class'.DIRECTORY_SEPARATOR."class_indexing_searching_app.php");
			$is = new indexing_searching_app();
			$type_state = $is->is_filetype_allowed($format);
			//control of the fingerprint of the document
		}
		$fingerprint_from_docserver = @md5_file($file);
		if($fingerprint_from_db == $fingerprint_from_docserver)
		{
			if($type_state <> false)
			{
				if($_SESSION['history']['resview'] == "true")
				{
					require_once($_SESSION['pathtocoreclass']."class_history.php");
					$users = new history();
					$users->add($table, $s_id ,"VIEW", _VIEW_DOC_NUM."".$s_id, $_SESSION['config']['databasetype'],'indexing_searching');
				}

				if(!$use_tiny_mce || strtolower($format) <>  'maarch')
				{
					$mime_type = $is->get_mime_type($format);
				}
				// ***************************************
				// Begin contribution of Mathieu DONZEL
				// ***************************************
				if (strtolower($format) == "pdf")
				{
					$Arguments = "";
					if(isset($_SESSION['search']['plain_text'])) if (strlen($_SESSION['search']['plain_text']) > 0)
					{
						$Arguments = "#search=". $_SESSION['search']['plain_text'] ."";
					}
					@copy($file, $_SESSION['config']['tmppath'].DIRECTORY_SEPARATOR.'tmp_file_'.$md5.$_SESSION['user']['UserId'].'.'.$format);
					echo "<iframe frameborder=\"0\" scrolling=\"no\" width=\"100%\" HEIGHT=\"100%\" src=\"". $_SESSION['config']['businessappurl'] ."/tmp/tmp_file_".$md5.$_SESSION['user']['UserId'].".".$format ."$Arguments\">"._FRAME_ARE_NOT_AVAILABLE_FOR_YOUR_BROWSER."</iframe>";
				}
				elseif($use_tiny_mce && strtolower($format) ==  'maarch')
				{
					$myfile = fopen($file, "r");

					$data = fread($myfile, filesize($file));
					fclose($myfile);
					$content = stripslashes($data);
					$core_tools->load_html();
					$core_tools->load_header();
					?>
                    <body id="validation_page" onLoad="javascript:moveTo(0,0);resizeTo(screen.width, screen.height);">
                     <div id="template_content" style="width:100%;"  >

                    <?php  echo $content;?>

                    </div>
                    </body>
                    </html> <?php
				}
				else
				{
					// ***************************************
					// End contribution ofMathieu DONZEL
					// ***************************************
					header("Pragma: public");
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Cache-Control: public");
					header("Content-Description: File Transfer");
					header("Content-Type: ".$mime_type);
					header("Content-Disposition: inline; filename=".basename('maarch.'.$format).";");
					header("Content-Transfer-Encoding: binary");
					readfile($file);
					exit();
				}
			}
			else
			{
				$core_tools->load_html();
				$core_tools->load_header('', true, false);
				echo '<body>';
				echo '<br/><div class="error">'._DOCTYPE.' '._UNKNOWN.'</div>';
				echo '</body></html>';
				exit();
			}
		}
		else
		{
			$core_tools->load_html();
			$core_tools->load_header('', true, false);
			echo '<body>';
			echo '<br/><div class="error">'._PB_WITH_FINGERPRINT_OF_DOCUMENT.'</div>';
			echo '</body></html>';
		}
	}
}
?>
