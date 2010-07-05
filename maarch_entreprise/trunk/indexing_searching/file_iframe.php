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
* @brief  Frame to show the file to index (action index_mlb.php)
*
* @file file_iframe.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/
$func = new functions();
$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_indexing_searching_app.php");
$is = new indexing_searching_app();
$show_file = $is->show_index_frame($_SESSION['upfile']['format']);
$ext_list = $is->filetypes_showed_indexation();
$ext = strtolower($_SESSION['upfile']['format']);
if($_SESSION['origin'] == "scan")
{
	//echo 'modules/indexing_searching'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'tmp_file_'.$_SESSION['upfile']['md5'].'.'.$ext;
	if(file_exists($_SESSION['config']['tmppath'].'tmp_file_'.$_SESSION['upfile']['md5'].'.'.$ext))
	{
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: ".$_SESSION['upfile']['mime']);
		header("Content-Disposition: inline; filename=".basename('maarch').".".$ext.";");
		header("Content-Transfer-Encoding: binary");
		$loc = $_SESSION['config']['tmppath'].'tmp_file_'.$_SESSION['upfile']['md5'].'.'.$ext;
		readfile($loc);
	}
	else
	{
		echo "<br/>PROBLEM DURING FILE SEND";
	}
	exit();
}
elseif(isset($_SESSION['upfile']['mime']) && !empty($_SESSION['upfile']['mime']) && isset($_SESSION['upfile']['format']) && !empty($_SESSION['upfile']['format'])  && $_SESSION['upfile']['error'] <> 1)
{
	if($show_file)
	{
		$mime_type = $is->get_mime_type($_SESSION['upfile']['mime']);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: ".$mime_type);
		header("Content-Disposition: inline; filename=".basename('maarch').".".$ext.";");
		header("Content-Transfer-Encoding: binary");
		$ext = strtolower($_SESSION['upfile']['format']);
		if(file_exists($_SESSION['config']['tmppath'].DIRECTORY_SEPARATOR.'tmp_file_'.$_SESSION['user']['UserId'].'.'.$ext))
		{
			$loc = $_SESSION['config']['MaarchUrl'].$_SESSION['config']['tmppath'].DIRECTORY_SEPARATOR.'tmp_file_'.$_SESSION['user']['UserId'].'.'.$ext;
			readfile($loc);
		}
		exit();
	}
	else
	{
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php  echo $_SESSION['config']['lang'] ?>" lang="<?php  echo $_SESSION['config']['lang'] ?>">
		<head>
			<title><?php  echo $_SESSION['config']['applicationname']; ?></title>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
			<meta http-equiv="Content-Language" content="<?php  echo $_SESSION['config']['lang'] ?>" />
			<link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['css']; ?>" media="screen" />
			<!--[if lt IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['css_IE']; ?>" media="screen" />  <![endif]-->
			<!--[if gte IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['css_IE7']; ?>" media="screen" />  <![endif]-->
			<!--<script type="text/javascript" src="js/functions.js"></script>-->
		</head>
		<body background="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=bg_home_home.gif" style="background-repeat:no-repeat;background-position:center">
		<?php
   		$ext = strtolower($_SESSION['upfile']['format']);
		if(file_exists($_SESSION['config']['tmppath'].DIRECTORY_SEPARATOR.'tmp_file_'.$_SESSION['user']['UserId'].'.'.$ext))
		{
			echo "<br/><br/><div class=\"error\">"._FILE_LOADED_BUT_NOT_VISIBLE.			_ONLY_FILETYPES_AUTHORISED." <br/><ul>";
				for($i=0; $i< count($ext_list); $i++)
				{
					echo "<li>".$ext_list[$i]."</li>";
				}
				echo "</ul></div>";
		}
		else
		{
			echo "<br/><br/><div class=\"error\">"._PROBLEM_LOADING_FILE_TMP_DIR.".</div>";
		}
	?>
    &nbsp;
    </body>
    </html>
    <?php
	}
}
else
{
	?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php  echo $_SESSION['config']['lang'] ?>" lang="<?php  echo $_SESSION['config']['lang'] ?>">
	<head>
		<title><?php  echo $_SESSION['config']['applicationname']; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta http-equiv="Content-Language" content="<?php  echo $_SESSION['config']['lang'] ?>" />
		<link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['css']; ?>" media="screen" />
		<!--[if lt IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['css_IE']; ?>" media="screen" />  <![endif]-->
		<!--[if gte IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php  echo $_SESSION['config']['css_IE7']; ?>" media="screen" />  <![endif]-->
		<!--<script type="text/javascript" src="js/functions.js"></script>-->
    </head>
    <body background="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=bg_home_home.gif" style="background-repeat:no-repeat;background-position:center">
	<?php

		if($_SESSION['upfile']['error'] == 1)
		{
			$filesize = $func->return_bytes(ini_get("upload_max_filesize"));
			echo "<br/><br/><div class=\"error\">"._MAX_SIZE_UPLOAD_REACHED." (".round($filesize/1024,2)."Ko Max)</div>";
		}
		else
		{
			echo "<br/><br/><div class=\"advertissement\">".$_SESSION['error']." <br/>"._ONLY_FILETYPES_AUTHORISED." <br/><ul>";
				for($i=0; $i< count($ext_list); $i++)
				{
					$displayed_ext_list .= $ext_list[$i].", ";
				}

			echo "<li>".substr($displayed_ext_list, 0 ,-2)."</li>";
			echo "</ul></div>";
		}
		$_SESSION['error'] ='';
	?>
    &nbsp;
    </body>
    </html>
    <?php
}
?>
