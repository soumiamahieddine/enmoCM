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
* @brief Choose a collection
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");

$_SESSION['m_admin']['group']['show_check'] = false;
$sec = new security();
$tabdiff = array();
$tab1 = array();
for($cpt_0=0; $cpt_0 < count($_SESSION['collections']); $cpt_0++)
{
	array_push($tab1, $_SESSION['collections'][$cpt_0]['id']);
}

$tab2 = array();
for($cpt_1=0; $cpt_1 < count($_SESSION['m_admin']['groups']['security'][$cpt_1]); $cpt_1++)
{
	array_push($tab2, $_SESSION['m_admin']['groups']['security'][$cpt_1]['COLL_ID']);
}

for($cpt_2=0; $cpt_2 < count($tab1); $cpt_2++)
{
	if(!in_array($tab1[$cpt_2], $tab2))
	{
		array_push($tabdiff, $tab1[$cpt_2]);
	}
}

$tmp = array();

for($cpt_3=0; $cpt_3 < count($tabdiff); $cpt_3++)
{
	$j = $sec->get_ind_collection($tabdiff[$cpt_3]);

	if(!isset($_SESSION['collections'][$j]['table'])|| empty($_SESSION['collections'][$j]['table']))
	{
		$show_checkbox = false;
	}
	array_push($tmp, array('id' => $tabdiff[$cpt_3], 'label' => $_SESSION['collections'][$j]['label']));

}
$tabdiff = $tmp;
if(count($tab_diff) == 1)
{
	 $_SESSION['m_admin']['group']['coll_id'] = $tabdiff[0];
	 $_SESSION['m_admin']['group']['show_check'] = true;
}

if(isset($_REQUEST['collselect']) && !empty($_REQUEST['collselect']))
{
	$_SESSION['m_admin']['group']['coll_id'] = $_REQUEST['collselect'];
	$ind = $sec->get_ind_collection($_SESSION['m_admin']['group']['coll_id']);

	if(isset($_SESSION['collections'][$ind]['table']) && !empty($_SESSION['collections'][$ind]['table']))
	{
		$_SESSION['m_admin']['group']['show_check'] = true;
	}

	?>
    <script language="javascript" type="text/javascript">window.top.location.href = "<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&admin=groups&page=add_grant";</script>
    <?php
}

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();
$core_tools->test_admin('admin_groups', 'apps');
$core_tools->load_html();
//here we building the header
$core_tools->load_header('', true, false);
?>
<body>
<form name="choose_coll"  method="get" class="forms">
<input type="hidden" name="display" value="true" />
<input type="hidden" name="admin" value="groups" />
<input type="hidden" name="page" value="choose_coll" />
	<p>
		<label><?php  echo _COLLECTION;?> :</label>
		<select name="collselect" id="collselect" onchange="this.form.submit();return false;">
			<option value=""><?php  echo _CHOOSE_COLLECTION;?></option>
			<?php
				for($cpt_4=0; $cpt_4 < count($tabdiff); $cpt_4++)
				{
					?>
					<option value="<?php  echo $tabdiff[$cpt_4]['id']; ?>" <?php  if ($_SESSION['m_admin']['group']['coll_id'] == $tabdiff[$cpt_4]['id']) {echo 'selected="selected"';  $_SESSION['m_admin']['group']['show_check'] = true;}?>><?php  echo $tabdiff[$cpt_4]['label']; ?></option>
					<?php
				}
				?>
		</select>
	</p>
</form>
<?php $core_tools->load_js();?>
</body>
</html>
