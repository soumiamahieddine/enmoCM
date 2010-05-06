<?php
/*
*
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
* @brief   Form to add , modify or remove an action for a group in a basket
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/

$core_tools = new core_tools();

$core_tools->load_lang();

$ind = 0;
if(isset($_REQUEST['ind']) && !empty($_REQUEST['ind']))
{
	$ind = $_REQUEST['ind'];
}
$core_tools->load_html();
$core_tools->load_header('', true, false);
 ?>
<body id="iframe">
	<form name="manage" id="manage" method="get" action="-<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=basket&page=groupbasket_allowed_actions">
		<input type="hidden" name="display" value="true" />
		<input type="hidden" name="module" value="basket" />
		<input type="hidden" name="page" value="groupbasket_allowed_actions" />
	<?php if(count($_SESSION['actions']) > count($_SESSION['m_admin']['basket']['actions']))
	{?>
		<input type="button" class="button" name="popuplink" id="popuplink" onClick="window.open('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=basket&page=groupbasket_action_popup', 'action','toolbar=no,status=yes,width=500,height=800,left=500,top=150,scrollbars=no,top=no,location=no,resize=yes,menubar=no');" value="<?php echo _ADD_ACTION; ?>"/>
  <?php } ?>
	<br/><br/>
	<div align="center">
		<h3 class="sstit"><?php echo _ASSOCIATED_ACTIONS;?> :</h3>
	</div>
	<div id="actions" style="height:400px;">
	<?php
	if(count($_SESSION['m_admin']['basket']['groups'][$ind]['ACTIONS']) > 0)
	{
		?>
		<ul>
		<?php
		for($i=0; $i < count($_SESSION['m_admin']['basket']['groups'][$ind]['ACTIONS']); $i++)
		{
		?>
			<li><input type="checkbox" class="check" name="actions[]" value="<?php echo $_SESSION['m_admin']['basket']['groups'][$ind]['ACTIONS'][$i]['ID_ACTION']; ?>" class="check" />&nbsp;&nbsp;&nbsp;<a href="javascript://"  onclick="window.open('g<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&module=basket&page=groupbasket_action_popup&id=<?php echo $_SESSION['m_admin']['basket']['groups'][$ind]['ACTIONS'][$i]['ID_ACTION']; ?>', 'action','toolbar=no,status=yes,width=500,height=800,left=500,top=150,scrollbars=no,top=no,location=no,resize=yes,menubar=no');"><?php echo $_SESSION['m_admin']['basket']['groups'][$ind]['ACTIONS'][$i]['LABEL_ACTION']; ?></a>
			</li>
		<?php
		}
		?>
		</ul>
		<br/><br/>
		<input type="submit" name="remove" id="remove" value="<?php echo _DEL_ACTIONS;?>" class="button" />
		<?php
	}
	else
	{
	?>
		<div  align="center">&nbsp;&nbsp;&nbsp;<i><?php echo _NO_ACTIONS_DEFINED;?></i></div>
	<?php
	}
	?>
	</div>
	</form>
	<?php $core_tools->load_js();?>
	<script type="text/javascript">
		sb = new ScrollBox(document.getElementById('actions'), {auto_hide: true});
	</script>
</body>
</html>
