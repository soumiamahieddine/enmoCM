<?php 
/**
*  Admin Class
*
* Contains all the administration functions and content 
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
* 
*/

/**
* Class admin : contains all the administration functions and the administration menu management
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package  Maarch PeopleBox 1.0
* @version 2.1
*/
class admin extends functions
{
	/**
	* Retrieve the board of the admin app
	*/
	public function retrieve_app_admin_services($app_services)
	{
		echo '<h2 class="admin_subtitle block" >Application</h2>';
		echo '<div  id="admin_apps">';
		for($i=0;$i<count($app_services);$i++)
		{
			if($app_services[$i]['servicetype'] == "admin" && $_SESSION['user']['services'][$app_services[$i]['id']])
			{
				?>
                <div class="admin_item" id="<?php  echo $app_services[$i]['style'];?>" title="<?php  echo $app_services[$i]['comment'];?>" onclick="window.top.location='<?php  echo $app_services[$i]['servicepage'];?>';">
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
	* Retrieve the board of the admin app
	*/
	public function retrieve_modules_admin_services($modules_services)
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
					<div class="admin_item" id="<?php  echo $modules_services[$value][$i]['style'];?>" title="<?php  echo 'Module '.$value.' : '.$modules_services[$value][$i]['comment'];?>" onclick="window.top.location='<?php  echo $modules_services[$value][$i]['servicepage'];?>';">
						<div class="sum_margin">
						
								<strong><?php  echo $modules_services[$value][$i]['name'];?></strong><!--<br/>
                                <em><?php  echo $modules_services[$value][$i]['comment'];?></em>-->
						
						</div>
					</div>
                 <!--   <hr /> -->
					<?php 	
				}
			}
		}
		echo '</div>';
	}
}
?>