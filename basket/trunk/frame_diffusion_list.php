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
* @brief   Frame : displays a model of diffusion list
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @author  Laurent Giovannoni  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup basket
*/
session_name('PeopleBox');
session_start();

require_once($_SESSION['pathtocoreclass']."class_functions.php");
require_once($_SESSION['pathtocoreclass']."class_db.php");
require_once($_SESSION['pathtomodules']."basket".$_SESSION['slash_env']."class".$_SESSION['slash_env']."class_admin_entity.php");
require_once($_SESSION['pathtocoreclass']."class_core_tools.php");

$core_tools = new core_tools();
$core_tools->test_admin('admin_entities', 'basket');
$core_tools->load_lang();
$core_tools->load_html();
$core_tools->load_header();
$time = $core_tools->get_session_time_expire();
?>
<body id="iframe" onLoad="setTimeout(window.close, <?php echo $time;?>*60*1000);">
<?php
if(isset($_SESSION['m_admin']['entity']['listmodel']) && count($_SESSION['m_admin']['entity']['listmodel']) > 0)
{
	?>
	<h2 class="tit"><?php echo _LINKED_DIFF_LIST;?> :</h2>
	<p class="sstit"><?php echo _RECIPIENT;?></p>
	<table cellpadding="0" cellspacing="0" border="0" class="listing liste_diff spec">
		<thead>
			<tr>
				<th><?php echo _LASTNAME;?></th>
				<th><?php echo _FIRSTNAME;?></th>
				<th><?php echo _DEPARTMENT;?></th>
			</tr>
		</thead>
        <tr class="col">
            <td><?php echo $_SESSION['m_admin']['entity']['listmodel'][0]['FIRSTNAME'];?></td>
            <td><?php echo $_SESSION['m_admin']['entity']['listmodel'][0]['LASTNAME'];?></td>
            <td><?php echo $_SESSION['m_admin']['entity']['listmodel'][0]['DEPARTMENT'];?></td>
        </tr>
	</table>
	<br/>
	<?php
    if(count($_SESSION['m_admin']['entity']['listmodel']) > 1)
    {
        ?>
        <p class="sstit"><?php echo _TO_CC;?></p>
        <table cellpadding="0" cellspacing="0" border="0" class="listing liste_diff spec">
            <thead>
                <tr>
                    <th><?php echo _LASTNAME;?></th>
                    <th><?php echo _FIRSTNAME;?></th>
                    <th><?php echo _DEPARTMENT;?></th>
                </tr>
            </thead>
        <?php
		$color = ' class="col"';
		for($i=1;$i<count($_SESSION['m_admin']['entity']['listmodel']);$i++)
		{
            if($color == ' class="col"')
            {
                $color = '';
            }
            else
            {
                $color = ' class="col"';
            }
                ?>
            <tr <?php echo $color; ?>>
                <td><?php echo $_SESSION['m_admin']['entity']['listmodel'][$i]['FIRSTNAME'];?></td>
                <td><?php echo $_SESSION['m_admin']['entity']['listmodel'][$i]['LASTNAME'];?></td>
                <td><?php echo $_SESSION['m_admin']['entity']['listmodel'][$i]['DEPARTMENT']; ?></td>
            </tr>
            <?php
        }
        ?>
        </table>
    <?php
    }
    ?>
    <p class="buttons">
        <p>
        	<input type="button" onClick="window.open('popup_listmodel_creation.php?what=A', '', 'scrollbars=yes,menubar=no,toolbar=no,status=no,resizable=yes,width=900,height=850,location=no');" class="button" value="<?php echo _MODIFY_LIST;?>" />
        </p>
    </p>
    <?php
}
else
{
	?>
    <h2 class="tit"><?php echo _NO_LINKED_DIFF_LIST;?>.</h2>
    <p class="buttons">
        <p>
        	<input type="button" onClick="window.open('popup_listmodel_creation.php', '', 'toolbar=no,menubar=no,status=no,resizable=yes,scrollbars=yes,width=900,height=850,location=no');" class="button" value="<?php echo _CREATE_LIST;?>" />
        </p>
    </p>
	<?php
}
?>
</body>
</html>
