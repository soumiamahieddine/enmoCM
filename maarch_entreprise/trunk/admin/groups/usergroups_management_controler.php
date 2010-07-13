<?php

try{
	require_once("apps/maarch_entreprise/class/UsergroupControler.php");
	require_once("core/class/SecurityControler.php");
	require_once("core/class/ServicesControler.php");	
	if($core_tools->is_module_loaded('basket'))
	{
		require_once("modules/basket/class/BasketControler.php");
	}
} catch (Exception $e){
	echo $e->getMessage();
}


// passer le mode en param + id si mode up
// 

if(isset($_REQUEST['group_id']) && !empty($_REQUEST['group_id']))
{
	$group_id = $_REQUEST['group_id'];
}

$mode = 'add';
if(isset($_REQUEST['mode']) && !empty($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
}

$users = array();
$baskets = array();
	
if($mode == "up")
{
	//$_SESSION['m_admin']['mode'] = "up";
	if(empty($_SESSION['error']))
	{		
		// DB Connexion 
		UsergroupControler::connect();
		SecurityControler::connect();
		ServicesControler::connect();
		
		$usergroup = UsergroupsControler::get($group_id ); // ramène l'objet usergroup
		
		$access = SecurityControler::get_access_for_group($group_id); // ramène le tableau des accès
		$services = ServicesControler::get($group_id);  // ramène le tableau des services
				
		$users_id = UsergroupsControler::getUsers($group_id ); //ramène le tableau des user_id appartenant au groupe
		$baskets_id = UsergroupsControler::getBaskets($group_id ); //ramène le tableau des basket_id associées au groupe
				
		for($i=0; $i<count($users_id);$i++)
		{
			array_push($users, UserControler::get($users_id[$i]));
		}
		
		if($core_tools->is_module_loaded('basket'))
		{
			for($i=0; $i<count($baskets_id);$i++)
			{
				array_push($baskets, BasketControler::get($baskets_id[$i]));
			}
		}
		
					$this->query("select * from ".$_SESSION['tablename']['security']." where group_id = '".$id."'");
					$i=0;
					while($line = $this->fetch_object())
					{
						$_SESSION['m_admin']['groups']['security'][$i]['COLL_ID'] = $this->show_string($line->coll_id);
						$_SESSION['m_admin']['groups']['security'][$i]['WHERE_CLAUSE'] = $this->show_string($line->where_clause);
						$i++;
					}
				}

				if (! isset($_SESSION['m_admin']['load_security']) || $_SESSION['m_admin']['load_security'] == true)
				{
					$_SESSION['m_admin']['groups']['security']=$sec->load_security_group($id);
					$_SESSION['m_admin']['load_security'] = false ;
				}

				if (! isset($_SESSION['m_admin']['load_services']) || $_SESSION['m_admin']['load_services'] == true)
				{
					$sec->load_services_group($id);
					$_SESSION['m_admin']['load_services'] = false ;
				}
				
				
				if($core_tools->is_module_loaded('basket'))
				{
					$this->query("select b.basket_name, b.basket_id, b.basket_desc, b.coll_id
										from ".$_SESSION['tablename']['bask_baskets']." b, ".$_SESSION['tablename']['bask_groupbasket']." bg where b.basket_id = bg.basket_id AND bg.group_id = '".$id."' order by b.basket_name asc");

					while($res = $this->fetch_object())
					{
						array_push($baskets, array( 'ID' => $res->basket_id, 'NAME' => $res->basket_name, 'DESC' => $res->basket_desc, 'COLL_ID' => $res->coll_id));
					}
				}
			}
		}
		elseif($mode == "add")
		{
			$_SESSION['m_admin']['mode'] = "add";
			if ($_SESSION['m_admin']['init']== true || !isset($_SESSION['m_admin']['init'] ))
			{
				$sec->init_session();
			}
		}

include('usergroups_management.php');
?>
