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
* @brief Pop up to select a user (used in history.php)
*
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/
session_name('PeopleBox');
session_start();
require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
$core_tools->test_user();
$core_tools->load_lang();

require_once($_SESSION['pathtocoreclass']."class_request.php");
require_once($_SESSION['config']['businessapppath']."class".DIRECTORY_SEPARATOR."class_list_show.php");
$func = new functions();

$what = "all";
$where = "";
$_SESSION['chosen_user'] = '';
if(isset($_GET['what']) && !empty($_GET['what']))
{
	if($_GET['what'] == "all")
	{
		$what = "all";

	}
	else
	{
		$what = addslashes($func->wash($_GET['what'], "no", "", "no"));
		$where = "(".$_SESSION['tablename']['users'].".lastname like '".strtolower($what)."%' or ".$_SESSION['tablename']['users'].".lastname like '".strtoupper($what)."%') ";
	}
}
	$db = new dbquery();
	$db->connect();

	$select[$_SESSION['tablename']['users']] = array();
	array_push($select[$_SESSION['tablename']['users']],"user_id","lastname","firstname" );

	$req = new request();

	$tab = $req->select($select, $where, " order by ".$_SESSION['tablename']['users'].".lastname desc", $_SESSION['config']['databasetype'], $limit="500",false);

for ($i=0;$i<count($tab);$i++)
{
		for ($j=0;$j<count($tab[$i]);$j++)
		{
			foreach(array_keys($tab[$i][$j]) as $value)
			{


				if($tab[$i][$j][$value]== "user_id" )
				{
					$tab[$i][$j]["user_id"]= $tab[$i][$j]['value'];
					$tab[$i][$j]["label"]= _ID;
					$tab[$i][$j]["size"]="30";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}

				if($tab[$i][$j][$value]=='lastname')
				{
					$tab[$i][$j]['value']= $req->show_string($tab[$i][$j]['value']);
					$tab[$i][$j]['lastname']= $tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_LASTNAME;
					$tab[$i][$j]["size"]="30";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
				if($tab[$i][$j][$value]=="firstname")
				{
					$tab[$i][$j]['value']= $req->show_string($tab[$i][$j]['value']);
					$tab[$i][$j]["info"]= $tab[$i][$j]['value'];
					$tab[$i][$j]["label"]=_FIRSTNAME;
					$tab[$i][$j]["size"]="30";
					$tab[$i][$j]["label_align"]="left";
					$tab[$i][$j]["align"]="left";
					$tab[$i][$j]["valign"]="bottom";
					$tab[$i][$j]["show"]=true;
				}
			}
		}
	}
	if(isset($_REQUEST['field']) && !empty($_REQUEST['field']))
	{
		$_SESSION['chosen_user'] = $_REQUEST['field'];

		?>
			<script language="javascript">
			var tmp = window.opener.$('user');
			tmp.value = '<?php echo $_SESSION['chosen_user'];?>';
			self.close();
			</script>
			<?php
		exit();
	}

//here we loading the html
$core_tools->load_html();
//here we building the header
$core_tools->load_header(_CHOOSE_USER2);
$time = $core_tools->get_session_time_expire();
?>
<body onLoad="javascript:setTimeout(window.close, <?php  echo $time;?>*60*1000);">
<div class="popup_content">
<?php
$nb = count($tab);

$list=new list_show();
$list->list_doc($tab, $nb, _USERS_LIST,'user_id', "select_user",'user_id','',false,true,'get',$_SESSION['config']['businessappurl'].'admin/history/select_user.php',_CHOOSE_USER2, false, false, false,false, true, true,  true, false, '', '',  true, _ALL_USERS,_USER);
		?>
</div>
</body>
</html>
