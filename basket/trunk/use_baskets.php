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

/*
* Deprecated file
*/
if(!isset($_REQUEST['noinit']))
{
	$_SESSION['current_basket'] = array();
}
require_once($_SESSION['pathtomodules']."basket".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_modules_tools.php");
$bask = new basket();
$bask->load_basket();
if(isset($_REQUEST['baskets']) && !empty($_REQUEST['baskets']))
{
	$_SESSION['tmpbasket']['service'] = $_SESSION['user']['services'][0]['ID'];
	$_SESSION['tmpbasket']['status'] = "all";
	$bask->load_current_basket(trim($_REQUEST['baskets']), 'frame');
}
if(count($_SESSION['user']['baskets']) <> 0)
{
	?>
	<div align="center" >
    <form name="select_basket" method="get" action="<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=welcome" id="bbb">
    	<label><?php echo _BASKETS;?> :</label>
        <select name="baskets" onchange="this.form.submit();" class="listext_big" >
        	<option value=""><?php echo _CHOOSE_BASKET;?></option>
            <?php
			for ($i=0;$i<=count($_SESSION['user']['baskets']);$i++)
			{
				if($i <> count($_SESSION['user']['baskets']))
				{
					?>
					<option value="<?php echo $_SESSION['user']['baskets'][$i]['basket_id'];?>" <?php if($_SESSION['current_basket']['id'] == $_SESSION['user']['baskets'][$i]['basket_id']) { echo 'selected="selected"'; }?>>
					<?php echo $_SESSION['user']['baskets'][$i]['desc'];?>
				  	</option>
					<?php
				}
				else
				{

				}
			}
			?>
		</select>
	</form>
	</div>
	<hr/>
	<?php
}
if(isset($_SESSION['current_basket']['page_frame']) && !empty($_SESSION['current_basket']['page_frame']))
{
	?>
	<iframe src="<?php echo $_SESSION['current_basket']['page_frame'];?>" name="result_basket" id="result_basket" frameborder="0" width="815" height="555" scrolling="auto"></iframe>
	<?php
}
?>
	<iframe src="<?php echo $_SESSION['current_basket']['page_frame'];?>" name="result_basket" id="result_basket" frameborder="0" width="815" height="555" scrolling="auto"></iframe>
