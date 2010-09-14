<?php 
/*
*    Copyright 2008-2010 Maarch
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
* @brief  Contains the functions to load administration services
* 
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/


/**
* @brief  Contains the functions to load administration services
* 
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/
class admin extends functions
{
	/**
	* Displays the administration services for the application
	* 
	* @param $app_services Array Application services
	*/
	public function display_app_admin_services($app_services)
	{
		echo '<h2 class="admin_subtitle block" >Application</h2>';
		echo '<div  id="admin_apps">';
		for($i=0;$i<count($app_services);$i++)
		{
			if($app_services[$i]['servicetype'] == "admin" && $_SESSION['user']['services'][$app_services[$i]['id']])
			{
				?>
                <div class="admin_item" id="<?php  echo $app_services[$i]['style']; ?>" title="<?php  echo $app_services[$i]['comment'];?>" onclick="window.top.location='<?php  echo preg_replace("/(&(?!amp;))/", "&amp;", $app_services[$i]['servicepage']) ;?>';">
                    <div class="sum_margin" >
                       
                            <strong><?php  echo $app_services[$i]['name'];?></strong>
                           <!-- <em><br/><?php  echo $app_services[$i]['comment'];?></em>-->
                       
                    </div>				
                </div>
                <?php 	
			}
		}
		echo '</div>';
	}
	
	/**
	* Displays the administration services for each module
	* 
	* @param $modules_services Array Modules services
	*/
	public function display_modules_admin_services($modules_services)
	{
		echo '<h2 class="admin_subtitle block">Modules</h2>';
		echo '<div id="admin_modules">';
		foreach(array_keys($modules_services) as $value)
		{
			$nb = 0;
			for($i=0;$i<count($modules_services[$value]);$i++)
			{
				if($modules_services[$value][$i]['servicetype'] == "admin" && $_SESSION['user']['services'][$modules_services[$value][$i]['id']])
				{
					if($nb == 0)
					{
						//echo '<h2 class="admin_subtitle">Module : '.$value.'</h2>';
					}
					$nb ++;
					?>
					<div class="admin_item" id="<?php  echo $modules_services[$value][$i]['style'];?>" title="<?php  echo 'Module '.$value.' : '.$modules_services[$value][$i]['comment'];?>" onclick="window.top.location='<?php  echo preg_replace("/(&(?!amp;))/", "&amp;", $modules_services[$value][$i]['servicepage']) ;?>';">
						<div class="sum_margin">					
							<strong><?php  echo $modules_services[$value][$i]['name'];?></strong>			
						</div>
					</div>
					<?php 	
				}
			}
		}
		echo '</div>';
	}
}
?>
