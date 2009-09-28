<?php
/**
* modules tools Class for entities
*
*  Contains all the functions to load modules tables for entities
*
* @package  maarch
* @version 1
* @since 03/2009
* @license GPL
* @author  Cédric Ndoumba  <dev@maarch.org>
*/

class entities extends dbquery
{
	/**
	* Build Maarch module tables into sessions vars with a xml configuration file
	*/
	public function build_modules_tables()
	{
		$xmlconfig = simplexml_load_file($_SESSION['pathtomodules']."entities".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml");
		foreach($xmlconfig->TABLENAME as $TABLENAME)
		{
			$_SESSION['tablename']['ent_entities'] = (string) $TABLENAME->ent_entities;
			$_SESSION['tablename']['ent_users_entities'] = (string) $TABLENAME->ent_users_entities;
			$_SESSION['tablename']['ent_listmodels'] = (string) $TABLENAME->ent_listmodels;
			$_SESSION['tablename']['ent_listinstance'] = (string) $TABLENAME->ent_listinstance;
			$_SESSION['tablename']['ent_groupbasket_redirect'] = (string) $TABLENAME->ent_groupbasket_redirect;
		}

		$HISTORY = $xmlconfig->HISTORY;
		$_SESSION['history']['entityadd'] = (string) $HISTORY->entityadd;
		$_SESSION['history']['entityup'] = (string) $HISTORY->entityup;
		$_SESSION['history']['entitydel'] = (string) $HISTORY->entitydel;
		$_SESSION['history']['entityval'] = (string) $HISTORY->entityval;
		$_SESSION['history']['entityban'] = (string) $HISTORY->entityban;
	}

	public function load_module_var_session()
	{
		$_SESSION['user']['entities'] = array();
		$_SESSION['entities_types'] = array();
		$_SESSION['user']['primaryentity'] = array();
		$type = "root";
		$this->connect();
		$this->query('select ue.entity_id, ue.user_role, ue.primary_entity, e.entity_label
					from '.$_SESSION['tablename']['ent_users_entities'].' ue,
					'.$_SESSION['tablename']['users'].' u,
					'.$_SESSION['tablename']['ent_entities']." e
					where ue.user_id = u.user_id
					and ue.entity_id = e.entity_id
					and e.enabled = 'Y'
					and ue.user_id = '".$_SESSION['user']['UserId']."'");

		while($line = $this->fetch_object())
		{
			array_push($_SESSION['user']['entities'],array('ENTITY_ID'=>$line->entity_id, 'ENTITY_LABEL'=>$line->entity_label, 'ROLE'=>$line->user_role));

			if($line->primary_entity == 'Y')
			{
				$_SESSION['user']['primaryentity']['id'] = $line->entity_id;
			}
		}

		$xmltype = simplexml_load_file($_SESSION['pathtomodules']."entities".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."typentity.xml");
		$entypes = array();

		foreach($xmltype->TYPE as $TYPE)
		{
			$_SESSION['entities_types'][] = array('id' =>  (string) $TYPE->id, 'label' =>  (string) $TYPE->label, 'level'=>  (string) $TYPE->typelevel);
		}

		$this->load_redirect_groupbasket_session();
	}

	public function process_where_clause($where_clause, $user_id)
	{
		if(!preg_match('/@/', $where_clause))
		{
			return $where_clause;
		}
		$where = $where_clause;
		// We must create a new object because the object connexion can already be used
		$db = new dbquery();
		$db->connect();
		require_once("class_manage_entities.php");
		$obj = new entity();
		if(preg_match('/@my_entities/', $where))
		{
			$entities = '';
			if($user == $_SESSION['user']['UserId'])
			{
				for($i=0; $i< count($_SESSION['user']['entities']);$i++)
				{
					$entities .= "'".$_SESSION['user']['entities'][$i]['ENTITY_ID']."', ";
				}
			}
			else
			{
				$db->query("select entity_id from ".$_SESSION['tablename']['ent_users_entities']." where user_id = '".$this->protect_string_db($user_id)."'");
				while($res = $db->fetch_object())
				{
					$entities .= "'".$res->entity_id."', ";
				}
			}
			$entities = preg_replace('/, $/', '', $entities);

			if($entities == '' && $user_id == 'superadmin')
			{
				$entities = "''";
			}
			$where = str_replace("@my_entities",$entities, $where);
		}
		if(preg_match('/@all_entities/', $where))
		{
			$entities = '';
			$db->query("select entity_id from ".$_SESSION['tablename']['ent_entities']." where enabled ='Y'");
			while($res = $db->fetch_object())
			{
				$entities .= "'".$res->entity_id."', ";
			}
			$entities = preg_replace("|, $|", '', $entities);
			$where = str_replace("@all_entities",$entities, $where);
		}
		if(preg_match('/@my_primary_entity/', $where))
		{
			$prim_entity = '';
			if($user == $_SESSION['user']['UserId'])
			{
				$prim_entity = "'".$_SESSION['user']['primary_entity']['id']."'";
			}
			else
			{
				$db->query("select entity_id from ".$_SESSION['tablename']['ent_users_entities']." where user_id = '".$this->protect_string_db($user_id)."' and primary_entity = 'Y'");
				$res = $db->fetch_object();
				$prim_entity = "'".$res->entity_id."'";
			}
			if($prim_entity == '' && $user_id == 'superadmin')
			{
				$prim_entity = "''";
			}
			$where = str_replace("@my_primary_entity",$prim_entity, $where);
		}
		$total = preg_match_all("|@subentities\[('[^\]]*')\]|", $where, $arr_tmp, PREG_PATTERN_ORDER);
		if($total > 0)
		{
			//$this->show_array( $arr_tmp);
			for($i=0; $i< $total;$i++)
			{
				$entities_tab = array();
				$tmp = str_replace("'", '', $arr_tmp[1][$i]);
				if(preg_match('/,/' ,$tmp))
				{
					$entities_tab = preg_split('/,/', $tmp);
				}
				else
				{
					array_push($entities_tab, $tmp);
				}

				$children = array();
				for($j=0; $j< count($entities_tab);$j++)
				{
					$arr = $obj->getTabChildrenId($entities_tab[$j]);
					$children = array_merge($children, $arr);
				}
				$entities = '';
				for($j=0; $j< count($children);$j++)
				{
					//$entities .= "'".$children[$j]."', ";
					$entities .= $children[$j].", ";
				}
				$entities = preg_replace("|, $|", '', $entities);
				if($entities == '' && $user_id == 'superadmin')
				{
					$entities = "''";
				}
				$where = preg_replace("|@subentities\['[^\]]*'\]|",$entities, $where, 1);
			}
		}
		$total = preg_match_all("|@immediate_children\[('[^\]]*')\]|", $where, $arr_tmp, PREG_PATTERN_ORDER);
		if($total > 0)
		{
			//$this->show_array( $arr_tmp);
			for($i=0; $i< $total;$i++)
			{
				$entities_tab = array();
				$tmp = str_replace("'", '', $arr_tmp[1][$i]);
				if(preg_match('/,/' ,$tmp))
				{
					$entities_tab = preg_split('/,/', $tmp);
				}
				else
				{
					array_push($entities_tab, $tmp);
				}

				$children = array();
				for($j=0; $j< count($entities_tab);$j++)
				{
					$arr = $obj->getTabChildrenId($entities_tab[$j], '', true);
					$children = array_merge($children, $arr);
				}
				$entities = '';
				for($j=0; $j< count($children);$j++)
				{
					//$entities .= "'".$children[$j]."', ";
					$entities .= $children[$j].", ";
				}
				$entities = preg_replace("|, $|", '', $entities);
				if($entities == '' && $user_id == 'superadmin')
				{
					$entities = "''";
				}
				$where = preg_replace("|@immediate_children\['[^\]]*'\]|",$entities, $where, 1);
			}
		}
		$total = preg_match_all("|@sisters_entities\[('[^\]]*')\]|", $where, $arr_tmp, PREG_PATTERN_ORDER);
		if($total > 0)
		{
			//$this->show_array( $arr_tmp);
			for($i=0; $i< $total;$i++)
			{
				$tmp = str_replace("'", '', $arr_tmp[1][$i]);
				$tmp = trim($tmp);
				$entities = $obj->getTabSisterEntityId($tmp);
				$sisters = '';
				for($j=0; $j< count($entities);$j++)
				{
					$sisters .= $entities[$j].", ";
				}
				$sisters = preg_replace("|, $|", '', $sisters);
				if($sisters == '' && $user_id == 'superadmin')
				{
					$sisters = "''";
				}
				$where = preg_replace("|@sisters_entities\['[^\]]*'\]|",$sisters, $where, 1);
			}
		}
		$total = preg_match_all("|@parent_entity\[('[^\]]*')\]|", $where, $arr_tmp, PREG_PATTERN_ORDER);
		if($total > 0)
		{
			//$this->show_array( $arr_tmp);
			for($i=0; $i< $total;$i++)
			{
				$tmp = str_replace("'", '', $arr_tmp[1][$i]);
				$tmp = trim($tmp);
				$entity = $obj->getParentEntityId($tmp);
				$entity = "'".$entity."'";
				if($entity == '' && $user_id == 'superadmin')
				{
					$entity = "''";
				}
				$where = preg_replace("|@parent_entity\['[^\]]*'\]|",$entity, $where, 1);
			}
		}
		$where = str_replace("or DESTINATION in ()", "", $where);
		//echo $where;exit;
		return $where;
	}

	public function update_redirect_groupbasket_db($group_id, $basket_id, $action_id, $entities = array(), $users_entities = array())
	{
		//$this->show_array($users_entities);
		$this->connect();
		$this->query("DELETE FROM ".$_SESSION['tablename']['ent_groupbasket_redirect'] ." where basket_id= '".$basket_id."' and group_id = '".$group_id."' and action_id = ".$action_id);
		$redirect_mode = 'ENTITY';
		for($i=0; $i<count($entities);$i++)
		{
			if($entities[$i]['KEYWORD']  == true)
			{
				$keyword = $entities[$i]['ID'];
				$entity_id = '';

			}
			else
			{
				$keyword = '';
				$entity_id = $entities[$i]['ID'];
			}
			$this->query("INSERT INTO ".$_SESSION['tablename']['ent_groupbasket_redirect']." (group_id, basket_id, action_id, entity_id, keyword, redirect_mode ) values ( '".$group_id."', '".$basket_id."', ".$action_id.", '".$entity_id."', '".$keyword."', '".$redirect_mode."')" );
		}

		$redirect_mode = 'USERS';
		for($i=0; $i<count($users_entities);$i++)
		{
			if($users_entities[$i]['KEYWORD']  == true)
			{
				$keyword = $users_entities[$i]['ID'];
				$entity_id = '';

			}
			else
			{
				$keyword = '';
				$entity_id = $users_entities[$i]['ID'];
			}
			$this->query("INSERT INTO ".$_SESSION['tablename']['ent_groupbasket_redirect']." (group_id, basket_id, action_id, entity_id, keyword, redirect_mode ) values ( '".$group_id."', '".$basket_id."', ".$action_id.", '".$entity_id."', '".$keyword."', '".$redirect_mode."')" );
		}
	}

	public function get_values_redirect_groupbasket_db($group_id, $basket_id, $action_id)
	{
		$db = new dbquery();
		$this->connect();
		$db->connect();

		$arr['ENTITY'] = array();
		$this->query("select entity_id, keyword from ".$_SESSION['tablename']['ent_groupbasket_redirect']."  where  group_id = '".$group_id."' and basket_id = '".$basket_id."' and redirect_mode = 'ENTITY' and action_id = ".$action_id);

		while($res = $this->fetch_object())
		{
			if($res->entity_id <> '')
			{
				$db->query("select entity_label from ".$_SESSION['tablename']['ent_entities']." where entity_id = '".$res_entity_id."'");
				$line = $db->fetch_object();
				$label = $db->show_string($line->entity_label);
				$tab = array('ID' => $res->entity_id, 'LABEL' => $label, 'KEYWORD' => false);
				array_push($arr['ENTITY'] , $tab);
			}
			else if($res->keyword <> '')
			{
				for($i=0; $i<count($_SESSION['m_admin']['redirect_keywords']); $i++)
				{
					if($_SESSION['m_admin']['redirect_keywords'][$i]['ID'] == $res->keyword)
					{
						$label = $_SESSION['m_admin']['redirect_keywords'][$i]['LABEL'];
						break;
					}
				}
				$tab = array('ID' => $res->keyword, 'LABEL' => $label, 'KEYWORD' => true);
				array_push($arr['ENTITY'] , $tab);
			}

		}

		$arr['USERS'] = array();
		$this->query("select entity_id, keyword from ".$_SESSION['tablename']['ent_groupbasket_redirect']."  where  group_id = '".$group_id."' and basket_id = '".$basket_id."' and redirect_mode = 'USERS' and action_id = ".$action_id);


		while($res = $this->fetch_object())
		{
			if($res->entity_id <> '')
			{
				$db->query("select entity_label from ".$_SESSION['tablename']['ent_entities']." where entity_id = '".$res_entity_id."'");
				$line = $db->fetch_object();
				$label = $db->show_string($line->entity_label);
				$tab = array('ID' => $res->entity_id, 'LABEL' => $label, 'KEYWORD' => false);
				array_push($arr['USERS'] , $tab);
				array_push($arr['USERS'] , $tab);
			}
			else if($res->keyword <> '')
			{
				for($i=0; $i<count($_SESSION['m_admin']['redirect_keywords']); $i++)
				{
					if($_SESSION['m_admin']['redirect_keywords'][$i]['ID'] == $res->keyword)
					{
						$label = $_SESSION['m_admin']['redirect_keywords'][$i]['LABEL'];
						break;
					}
				}
				$tab = array('ID' => $res->keyword, 'LABEL' => $label, 'KEYWORD' => true);
				array_push($arr['USERS'] , $tab);
			}
		}
		return $arr;
	}

	public function get_info_entity($entity_id)
	{
		$arr = array();
		$arr['label']= '';
		$arr['keyword'] = false;
		if(empty($entity_id))
		{
			return $arr;
		}
		for($i=0;$i<count($_SESSION['m_admin']['entities']);$i++)
		{
			if($_SESSION['m_admin']['entities'][$i]['ID'] == $entity_id)
			{
				$arr['label'] = $_SESSION['m_admin']['entities'][$i]['LABEL'];
				$arr['keyword'] = $_SESSION['m_admin']['entities'][$i]['KEYWORD'];
				return $arr;
			}
		}
		return $arr;
	}

	public function load_redirect_groupbasket_session()
	{
		$_SESSION['user']['redirect_groupbasket'] = array();
		$this->connect();
		$this->query('select distinct basket_id from '.$_SESSION['tablename']['ent_groupbasket_redirect']." where group_id = '".$_SESSION['user']['primarygroup']."'");

		$db = new dbquery();
		$db->connect();
		while($res = $this->fetch_object())
		{
			//echo "basket ".$res->basket_id.'<br/>';
			$basket_id = $res->basket_id;
			$_SESSION['user']['redirect_groupbasket'][$basket_id] = array();

			$db->query("select distinct action_id from ".$_SESSION['tablename']['ent_groupbasket_redirect']." where group_id = '".$_SESSION['user']['primarygroup']."' and basket_id = '".$basket_id."'");
			while($line = $db->fetch_object())
			{
				$action_id = $line->action_id;
				$_SESSION['user']['redirect_groupbasket'][$basket_id][$action_id]['entities'] = '';
				$_SESSION['user']['redirect_groupbasket'][$basket_id][$action_id]['users_entities'] = '';
				$tmp_arr = $this->get_redirect_groupbasket($_SESSION['user']['primarygroup'], $basket_id, $_SESSION['user']['UserId'], $action_id);
				$_SESSION['user']['redirect_groupbasket'][$basket_id][$action_id]['entities'] = $tmp_arr['entities'];
				$_SESSION['user']['redirect_groupbasket'][$basket_id][$action_id]['users_entities'] = $tmp_arr['users'];
			}
		}
	}

	public function get_redirect_groupbasket($group_id, $basket_id, $user_id, $action_id)
	{
		$arr = array();
		$db = new dbquery();
		$db->connect();
		$db->query("select entity_id, keyword from ".$_SESSION['tablename']['ent_groupbasket_redirect']." where basket_id = '".$basket_id."' and group_id = '".$group_id."' and redirect_mode = 'ENTITY' and action_id = ".$action_id);
		//$db->show();
		$entities = '';
		while($line = $db->fetch_object())
		{
			if(empty($line->keyword))
			{
				$entities .= "'".$line->entity_id."', ";
			}
			else
			{
				$entities .= $this->translate_entity_keyword($line->keyword).", ";
			}
		}
		$entities = preg_replace("/, $/", '', $entities);
		$entities = $this->process_where_clause($entities, $user_id);
		$entities = preg_replace("/^,/", '', $entities);
		$entities = preg_replace("/, ,/", ',', $entities);

		$db->query("select entity_id, keyword from ".$_SESSION['tablename']['ent_groupbasket_redirect']." where basket_id = '".$basket_id."' and group_id = '".$group_id."' and redirect_mode = 'USERS' and action_id = ".$action_id);
		//$db->show();
		$users = '';
		while($line = $db->fetch_object())
		{
			if(empty($line->keyword))
			{
				$users .= "'".$line->entity_id."', ";
			}
			else
			{
				$users .= $this->translate_entity_keyword($line->keyword).", ";
			//	echo '<br/>'.$users.'<br/>';
			}
		}
		$users = preg_replace("/, $/", '', $users);
		$users = $this->process_where_clause($users, $user_id);
		$users = preg_replace("/^,/", '', $users);
		$users = preg_replace("/, ,/", ',', $users);
	//	echo $users;
		$arr['entities'] = $entities;
		$arr['users'] = $users;
		//print_r($arr);
		return $arr;
	}

	public function translate_entity_keyword($keyword)
	{
		if($keyword == 'ALL_ENTITIES')
		{
			return '@all_entities';
		}
		elseif($keyword == 'ENTITIES_JUST_BELOW')
		{
			return '@immediate_children[@my_primary_entity]';
		}
		elseif($keyword == 'ALL_ENTITIES_BELOW')
		{
			return '@subentities[@my_primary_entity]';
		}
		elseif($keyword == 'ENTITIES_JUST_UP')
		{
			return '@parent_entity[@my_primary_entity]';
		}
		elseif($keyword == 'MY_ENTITIES')
		{
			return '@my_entities';
		}
		elseif($keyword == 'MY_PRIMARY_ENTITY')
		{
			return '@my_primary_entity';
		}
		elseif($keyword == 'SAME_LEVEL_ENTITIES')
		{
			return '@sisters_entities[@my_primary_entity]';
		}
		else
		{
			return '';
		}
	}
}
?>
