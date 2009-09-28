<?php 
/**
*  Usergroup_content class
*
* Contains all the functions to manage groups and users
*
* @package  Maarch PeopleBox 1.0
* @version 2.1
* @since 06/2006
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
* 
*/

/**
* Class usergroup_content : contains all the functions to manage the groups and users through session variables
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package  Maarch PeopleBox 1.0
* @version 2.1
*/

class usergroup_content extends dbquery
{
	/**
	* Inits the session variables related to the user administration.
	*
	*/
	public function init_session()
	{
			$_SESSION['m_admin']['users'] = array();
			$_SESSION['m_admin']['users']['UserId'] = "";
			$_SESSION['m_admin']['users']['pass'] = "";
			$_SESSION['m_admin']['users']['FirstName'] = "";
			$_SESSION['m_admin']['users']['LastName'] = "";
			$_SESSION['m_admin']['users']['Phone'] = "";
			$_SESSION['m_admin']['users']['Mail'] = "";
			$_SESSION['m_admin']['users']['Department'] = "";
			$_SESSION['m_admin']['users']['Enabled'] = "";
			$_SESSION['m_admin']['users']['groups'] = array();
			$_SESSION['m_admin']['users']['nbbelonginggroups'] = 0;
			$_SESSION['m_admin']['init'] = false ;
	}

	/**
	* Removes the group on the tables passed in parameters for the user.
	*
	* @param array $tab 
	*/
	public function remove_session($tab)
	{	
		$tabtmp = array();
		for($i=0; $i < count($_SESSION['m_admin']['users']['groups']); $i++)
		{
			if( !in_array($_SESSION['m_admin']['users']['groups'][$i]['GROUP_ID'], $tab))
			{
				array_push($tabtmp, $_SESSION['m_admin']['users']['groups'][$i]);
			}
		}
	
		$_SESSION['m_admin']['users']['groups'] = array();
		$_SESSION['m_admin']['users']['groups'] = $tabtmp;
	
	}
	
	/**
	* No group is the primary group for the user.
	*
	*/
	public function erase_primary_group_session()
	{
		for($i=0; $i < count($_SESSION['m_admin']['users']['groups']); $i++)
		{
			$_SESSION['m_admin']['users']['groups'][$i]["PRIMARY"] = 'N';
		}
	
	}
	
	/**
	* Set the primary group for a user in the session variables.
	*
	* @param 	string  $group_id group identifier
	*/
	public function set_primary_group_session($group_id)
	{
		for($i=0; $i < count($_SESSION['m_admin']['users']['groups']); $i++)
		{
			if ( $_SESSION['m_admin']['users']['groups'][$i]["GROUP_ID"] == $group_id)
			{
				$_SESSION['m_admin']['users']['groups'][$i]["PRIMARY"] = 'Y';
				break;
			}
		}
	}
	
	/**
	* Adds a group in the session variables related to the user administration
	*
	* @param 	string  $group group identifier
	* @param 	string  $role role in the group (empty by default)
	*/
	public function add_usertmp_to_group_session($group, $role = "", $label)
	{
		$tab = array();
		$tab = array("USER_ID" => "", "GROUP_ID" => $group , "LABEL" => $this->show_string($label), "PRIMARY" => 'N', "ROLE" => $this->show_string($role) );
		array_push($_SESSION['m_admin']['users']['groups'], $tab);
		
	}

	/**
	* Loads in the session variables the groups of the user passed in parameter
	*
	* @param 	string  $user_id user identifier
	*/
	public function load_group_session($user_id)
	{
		
			$this->connect();
			$this->query("select uc.user_id, uc.group_id, uc.primary_group, uc.role, u.group_desc from ".$_SESSION['tablename']['usergroup_content']." uc, ".$_SESSION['tablename']['usergroups']." u where uc.user_id = '".$user_id."' and uc.group_id = u.group_id");
			if($this->nb_result() == 0)
			{
				$_SESSION['m_admin']['users']['groups'] = array();
			}
			else
			{
				$grouptab=array();
				while($res = $this->fetch_object())
				{
					array_push($grouptab, array("USER_ID" => $res->user_id,"GROUP_ID" => $res->group_id, "LABEL" => $this->show_string($res->group_desc), "PRIMARY" => $res->primary_group, "ROLE" => $this->show_string($res->role) ));
				} 
				$_SESSION['m_admin']['users']['groups'] = $grouptab;
				$_SESSION['m_admin']['users']['nbbelonginggroups'] = count($grouptab);
				
			}
		$_SESSION['m_admin']['load_group']  = false;
	}

	/**
	* Updates the database (usergroup_content table) with the session variables.
	*
	*/
	public function load_db()
	{
		$this->connect();
		
		$this->query("DELETE FROM ".$_SESSION['tablename']['usergroup_content'] ." where user_id = '".$_SESSION['m_admin']['users']['UserId']."'");
		//$this->show();
		
		for($i=0; $i < count($_SESSION['m_admin']['users']['groups'] ); $i++)
		{
			$tmp_r = $this->protect_string_db($_SESSION['m_admin']['users']['groups'][0]['ROLE']);
			$this->query("INSERT INTO ".$_SESSION['tablename']['usergroup_content']." VALUES ('".$_SESSION['m_admin']['users']['UserId']."', '".$_SESSION['m_admin']['users']['groups'][$i]['GROUP_ID']."', '".$_SESSION['m_admin']['users']['groups'][$i]['PRIMARY']."', '".$tmp_r."')");
		}
	
	}
	
}
?>