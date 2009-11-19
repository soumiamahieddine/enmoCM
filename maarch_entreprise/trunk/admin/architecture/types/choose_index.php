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
* @brief Form to choose the index for a doctype (used in doctypes administration)
*
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

include('core/init.php');


require_once("core/class/class_functions.php");
require_once("core/class/class_db.php");
require("core/class/class_core_tools.php");

$core_tools = new core_tools();
//here we loading the lang vars
$core_tools->load_lang();

$core_tools->load_html();
$core_tools->load_header();

if(isset($_REQUEST['valid']) && isset($_SESSION['m_admin']['doctypes']['COLL_ID']) && !empty($_SESSION['m_admin']['doctypes']['COLL_ID']))
{
	//$coll_id = $_SESSION['m_admin']['doctypes']['COLL_ID'];
	for($i=0;$i<count($_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']]);$i++)
	{

		if($_REQUEST["field_".$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] == "Y")
		{
			if($_REQUEST["mandatory_".$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] == "Y")
			{
				$_SESSION['m_admin']['doctypes'][$_SESSION['index'][$i]['COLUMN']] = "1100000000";
				$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN'].", ";
				$_SESSION['m_admin']['doctypes']['custom_query_insert_values'] .= "'1100000000', ";
				$_SESSION['m_admin']['doctypes']['custom_query_update'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']." = "."'1100000000', ";
			}
			else
			{
				$_SESSION['m_admin']['doctypes'][$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] = "1000000000";
				$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN'].", ";
				$_SESSION['m_admin']['doctypes']['custom_query_insert_values'] .= "'1000000000', ";
				$_SESSION['m_admin']['doctypes']['custom_query_update'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']." = "."'1000000000', ";
			}
		}
		elseif($_REQUEST["field_".$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] == "")
		{
			$_SESSION['m_admin']['doctypes'][$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] = "0000000000";
			$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN'].", ";
			$_SESSION['m_admin']['doctypes']['custom_query_insert_values'] .= "'0000000000', ";
			$_SESSION['m_admin']['doctypes']['custom_query_update'] .= $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']." = '0000000000', ";
		}
	}

	if(trim($_SESSION['m_admin']['doctypes']['custom_query_insert_colums']) <> "")
	{
		$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'] = ", ".$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'];
		$_SESSION['m_admin']['doctypes']['custom_query_insert_colums'] = substr($_SESSION['m_admin']['doctypes']['custom_query_insert_colums'],0,strlen($_SESSION['m_admin']['doctypes']['custom_query_insert_colums'])-2);
	}
	if($_SESSION['m_admin']['doctypes']['custom_query_insert_values'] <> "")
	{
		$_SESSION['m_admin']['doctypes']['custom_query_insert_values'] = ", ".$_SESSION['m_admin']['doctypes']['custom_query_insert_values'];
		$_SESSION['m_admin']['doctypes']['custom_query_insert_values'] = substr($_SESSION['m_admin']['doctypes']['custom_query_insert_values'],0,strlen($_SESSION['m_admin']['doctypes']['custom_query_insert_values'])-2);
	}
	if($_SESSION['m_admin']['doctypes']['custom_query_update'] <> "")
	{
		$_SESSION['m_admin']['doctypes']['custom_query_update'] = ", ".$_SESSION['m_admin']['doctypes']['custom_query_update'];
		$_SESSION['m_admin']['doctypes']['custom_query_update'] = substr($_SESSION['m_admin']['doctypes']['custom_query_update'],0,strlen($_SESSION['m_admin']['doctypes']['custom_query_update'])-2);
	}

	?>
    <script language="javascript" type="text/javascript">window.parent.document.forms['frmtype'].submit();</script>
    <?php
	exit();
}
	?>
<body>
 <div align="center">
 <?php  if(!isset($_SESSION['m_admin']['doctypes']['COLL_ID']) || empty($_SESSION['m_admin']['doctypes']['COLL_ID']))
 {
 	echo _MUST_CHOOSE_COLLECTION_FIRST;
 }
 else
 {?>
 	<form name="frm_choose_index" action="choose_index.php" method="get" id="frm_choose_index">
    <input type="hidden" name="valid" id="valid" value="true"/>
    <?php if(count($_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']]) > 0)
	{?>
                    <table>
                        <tr>
                        	<th width='150'>
                            	<?php  echo _FIELD;?>
                            </th>
                            <th align="center" width='100'>
                            	<?php  echo _USED;?>
                            </th>
                            <th align="center" width='100'>
                            	<?php  echo _MANDATORY;?>
                            </th>
                           <!-- <td align="center" width='100'>
                            	<?php  // echo _ITERATIVE;?>
                            </td>-->
                        </tr>
					<?php
					for($i=0;$i<count($_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']]);$i++)
					{
						echo "<tr>";
						echo "<td width='150'>";
						echo "	".$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['LABEL'];
						echo "</td>";
						echo "<td align='center'>";
						?>
                        <input name="field_<?php  echo $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN'];?>" type="checkbox"  class="check"  value="Y"
                        <?php
                        if ($_SESSION['m_admin']['doctypes'][$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] == '1100000000' || $_SESSION['m_admin']['doctypes'][$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] == '1000000000')
                        {
                            echo "checked=\"checked\"";
                        }
                        ?>
                        />
						</td>
                        <td align="center" width='100'>
                        	<input name="mandatory_<?php  echo $_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN'];?>" type="checkbox"   class="check" value="Y"
                            <?php
							if ($_SESSION['m_admin']['doctypes'][$_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['COLUMN']] == '1100000000')
							{
								echo "checked=\"checked\"";
							}
							?>
                        	/>
                        </td>
						<?php
					/*	if($_SESSION['index'][$_SESSION['m_admin']['doctypes']['COLL_ID']][$i]['ITERATIVE'] == "no")
						{
							?>
							<td align="center" width='100'>
								<input name="iterative"  class="check" type="checkbox"  disabled="disabled" />
							</td>
							<?php
						}
						else
						{
							?>
							<td align="center" width='100'>
								<input name="iterative" class="check" type="checkbox" checked="checked"  disabled="disabled" />
							</td>
							<?php
						}*/
						echo "</tr>";
					}
					?>
                    </table>
        <?php

		} ?>
        </form>
<?php } ?>
    </div>
</body>
</html>
