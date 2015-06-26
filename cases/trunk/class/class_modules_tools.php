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
* @defgroup basket Basket Module
*/

/**
* @brief   Module Cases :  Module Tools Class
*
* <ul>
* <li>Set the session variables needed to run the basket module</li>
*</ul>
*
* @file
* @author Loïc Vinet <dev@maarch.org>
* @date $date$
* @version $Revision$
*/
require_once("core/class/class_request.php");
require_once("core/class/class_functions.php");
require_once("core/class/class_history.php");
class cases extends dbquery
{

	public function build_modules_tables()
	{
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."cases".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."cases".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		else
		{
			$path = "modules".DIRECTORY_SEPARATOR."cases".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		$xmlconfig = simplexml_load_file($path);
		$CONFIG = $xmlconfig->CONFIG;


		// Loads the tables of the module basket  into session ($_SESSION['tablename'] array)
		$TABLENAME =  $xmlconfig->TABLENAME ;
		$_SESSION['tablename']['cases'] = (string) $TABLENAME->cases;
		$_SESSION['tablename']['cases_res'] = (string) $TABLENAME->cases_res;

		// Loads the log setting of the module basket  into session ($_SESSION['history'] array)
		$HISTORY = $xmlconfig->HISTORY;
		$_SESSION['history']['casesup'] = (string) $HISTORY->casesup;
		$_SESSION['history']['casesadd'] = (string) $HISTORY->casesadd;
		$_SESSION['history']['casesdel'] = (string) $HISTORY->casesdel;
		$_SESSION['history']['caseslink'] = (string) $HISTORY->caseslink;
		$_SESSION['history']['casesunlink'] = (string) $HISTORY->casesunlink;
	}

	/**
	 * Create a new case in Maarch Entreprise with one document. A case need one ressource to be seen
	 *
	 * @param $desc     			 string   Description: used for this case, can be null
	 * @param $res_id     		 int        Description: id of first ressource to add
	 * @param $parent_case     int        Description: id of his parent case
	 */
	public function create_case($res_id, $label, $desc = '', $parent_case = '', $type = 'standard')
	{
		if (empty($res_id))
			echo "create_case ::arg2 error!</br>";

		//########################

		$request = new request();
		$current_date = $request->current_datetime();
		$data = array();
		$func = new functions();

		//Create a new batch when this box is empty
		array_push($data, array('column' => "case_description", 'value' => $func->protect_string_db($desc), "type" => "string"));
		array_push($data, array('column' => "case_label", 'value' => $func->protect_string_db($label), "type" => "string"));
		array_push($data, array('column' => "case_creation_date", 'value' => $current_date, "type" => ""));
		array_push($data, array('column' => "case_last_update_date", 'value' => $current_date, "type" => ""));
		array_push($data, array('column' => "case_typist", 'value' => $_SESSION['user']['UserId'], "type" => "string"));
		array_push($data, array('column' => "case_type", 'value' => $type, "type" => "string"));
		//array_push($data, array('column' => "case_parent", 'value' => $parent_case, "type" => "int"));
		if(!$request->insert($_SESSION['tablename']['cases'], $data, $_SESSION['config']['databasetype']))
		{
			$request->show();
			echo "create_case:: sql index error<br/>";
		}
		$db = new dbquery();
		$db -> connect();
		$db->query("SELECT max(case_id) as case_id FROM ".$_SESSION['tablename']['cases']." WHERE  CASE_TYPIST = '".$_SESSION['user']['UserId']."' ");

		$res = $db->fetch_object();
		$caseId = $res->case_id;



		//Now we can attach the first document at this case


		$data_relation = array();
		array_push($data_relation, array('column' => "case_id", 'value' => $caseId, "type" => "int"));
		array_push($data_relation, array('column' => "res_id", 'value' => $res_id, "type" => "int"));
		if(!$request->insert($_SESSION['tablename']['cases_res'], $data_relation, $_SESSION['config']['databasetype']))
		{
			$request->show();
			echo "create_case:: attach sql error<br/>";
		}

		//History adds
		if ($_SESSION['history']['casesadd'] == "true")
		{
			$hist = new history();
			$hist->add($_SESSION['tablename']['cases'], $caseId,"NEW",'casesadd',_NEW_CASE." ", $_SESSION['config']['databasetype']);
		}
		//History adds
		if ($_SESSION['history']['caseslink'] == "true")
		{
			$hist = new history();
			$hist->add($_SESSION['tablename']['cases'], $caseId,"LINK",'caseslink',_RES_ATTACH_ON_CASE." ".$res_id, $_SESSION['config']['databasetype']);
		}

		//Limitation (1,1) Cases V1
		$this->detach_all_from_cases($res_id,$caseId);

		return $caseId;
	}


	/**
	 *Update indexes from the case
	 *
	 * @param $caseId   int   			int	        Description: Id of selected case
	 * @param $updateValues    	 array 		    Description: Id of selected ressource
	 */
	public function update_case($caseId, $updateValues)
	{
		if (empty($caseId)) {
			echo "update_case ::arg1 error!</br>";
		}
		if (empty($updateValues)) {
			echo "update_case ::arg2 error!</br>";
		}
		$replaceValues = array();

		$request = new request();
		$table = $_SESSION['tablename']['cases'];
		$where = 'case_id = ' . $caseId;

		$allowedFields = array(
			'case_id', 'case_label', 'case_description', 'case_type',
			'case_closing_date', 'case_last_update_date', 'case_creation_date',
		    'case_typist', 'case_parent', 'case_custom_t1', 'case_custom_t2',
		    'case_custom_t3', 'case_custom_t4'
		);

		foreach (array_keys($updateValues) as $field) {
		    if (in_array($field, $allowedFields)) {
    		    $type = 'string';
    		    if (preg_match('/date$/', $field)) {
    		        $type = 'date';
    		    } else if ($field == 'case_parent') {
    		        $type = 'integer';
    		    }
    		    array_push(
    		        $replaceValues,
    		        array(
    		        	'column' => $field,
    		        	'value' => addslashes($updateValues[$field]),
    		        	'type' => $type,
    		        )
    		    );
		    }
		}

		$request->update($table, $replaceValues, $where, $_SESSION['config']['databasetype']);
		$this->change_last_update($caseId);

	}
	/**
	 *Join a new ressource to a case
	 *
	 * @param $caseId   int       Description: Id of selected case
	 * @param $res_id     int       Description: Id of selected ressource
	 */
	public function join_res($caseId, $res_id)
	{
		if (empty($caseId))
			echo "join_case ::arg1 error!</br>";

		if (empty($res_id))
			echo "join_case ::arg2 error!</br>";
		// -------


		$db = new dbquery();
		$db -> connect();
		$db->query("SELECT res_id  FROM ".$_SESSION['tablename']['cases_res']." WHERE  CASE_ID = '".$caseId."' AND RES_ID = '".$res_id."' ");
		if ($db->nb_result() > 0)
		{
			return false;
		}
		$request = new request();
		$data = array();
		array_push($data, array('column' => "case_id", 'value' => $caseId, "type" => "int"));
		array_push($data, array('column' => "res_id", 'value' => $res_id, "type" => "int"));

		if(!$request->insert($_SESSION['tablename']['cases_res'], $data, $_SESSION['config']['databasetype']))
		{
			$request->show();
			echo "join_case:: attach sql error<br/>";
			return false;
		}

		//History adds
		if ($_SESSION['history']['caseslink'] == "true")
		{
			$hist = new history();
			$hist->add($_SESSION['tablename']['cases'], $caseId,"LINK",'caseslink',_RES_ATTACH_ON_CASE." ".$res_id, $_SESSION['config']['databasetype']);
		}
		//Limitation (1,1) Cases V1
		$this->detach_all_from_cases($res_id,$caseId);
		return true;
	}




	/**
	 *detach a ressource to the case
	 *
	 * @param $caseId   int       Description: Id of selected case
	 * @param $res_id     int       Description: Id of selected ressource
	 */
	public function detach_res($caseId, $res_id)
	{
		if (empty($caseId))
			echo "detach_case ::arg1 error!</br>";

		if (empty($res_id))
			echo "detach_case ::arg2 error!</br>";
		// -------

		if ((!empty($res_id)) && (!empty($caseId)))
		{
			$db = new dbquery();
			$db->connect();

			$query = " DELETE FROM ".$_SESSION['tablename']['cases_res']." WHERE  RES_ID = '".$res_id."' AND CASE_ID = '".$caseId."' ";
			if(!$db->query($query))
				echo "detach_case:: sql error<br/>";

			if ($_SESSION['history']['casesunlink'] == "true")
			{
				$hist = new history();
				$hist->add($_SESSION['tablename']['cases'], $_SESSION['m_admin']['users']['user_id'],"UNLINK",'casesunlink',_RES_DETTACH_ON_CASE." ".$res_id, $_SESSION['config']['databasetype']);
			}

		}
	}

	public function get_where_clause_from_case($caseId)
	{
		return " and CASE_ID ='".$caseId."' ";
	}

	//Return all data from case
	public function get_case_info($caseId)
	{
		if (empty($caseId))
			echo "get_case_id ::arg1 error!</br>";

		$db = new dbquery();
		$db->connect();

		$my_return = array();
		$request = new request();

		//$query = " select case_id, case_label, case_description, date(case_creation_date) as ccd, case_typist, case_parent, case_custom_t1, case_custom_t2, case_custom_t3, case_custom_t4, case_type, date(case_closing_date) as clo, date(case_last_update_date)	as cud   FROM ".$_SESSION['tablename']['cases']." WHERE  CASE_ID = '".$caseId."' ";
		$query = " select case_id, case_label, case_description, "
			.$request->extract_date('case_creation_date', 'date')
			." as ccd, case_typist, case_parent, case_custom_t1, case_custom_t2, case_custom_t3, case_custom_t4, case_type, ".$request->extract_date('case_closing_date', 'date')." as clo, ".$request->extract_date('case_last_update_date', 'date')." as cud   FROM ".$_SESSION['tablename']['cases']." WHERE  CASE_ID = '".$caseId."' ";

		$db->query($query);
		$res = $db->fetch_object();

		$my_return['case_id'] = functions::xssafe($res->case_id);
		$my_return['case_label'] = functions::xssafe($db->show_string($res->case_label));
		$my_return['case_description'] = functions::xssafe($db->show_string($res->case_description));
		$my_return['case_creation_date'] = functions::xssafe($db->show_string($res->ccd));
		$my_return['case_typist'] = functions::xssafe($db->show_string($res->case_typist));
		$my_return['case_parent'] = functions::xssafe($db->show_string($res->case_parent));
		$my_return['case_custom_t1'] = functions::xssafe($db->show_string($res->case_custom_t1));
		$my_return['case_custom_t2'] = functions::xssafe($db->show_string($res->case_custom_t2));
		$my_return['case_custom_t3'] = functions::xssafe($db->show_string($res->case_custom_t3));
		$my_return['case_custom_t4'] = functions::xssafe($db->show_string($res->case_custom_t4));
		$my_return['case_type'] = functions::xssafe($db->show_string($res->case_type));
		$my_return['case_closing_date'] = functions::xssafe($db->show_string($res->clo));
		$my_return['case_last_update_date'] = functions::xssafe($db->show_string($res->cud));

		return $my_return;
	}

	public function get_res_id($caseId)
	{
		if (empty($caseId))
			echo "get_res_id ::arg1 error!</br>";

		$db = new dbquery();
		$db->connect();

		$my_return = array();

		$query = " select res_id FROM ".$_SESSION['tablename']['cases_res']." WHERE  case_id = '".$caseId."' ";
		$db->query($query);

		while ($res = $db->fetch_object())
		{
			array_push($my_return, $res->res_id);
		}

		return $my_return;
	}


	//Return array with number of each status for this case
	public function get_ressources_status($caseId)
	{
		$db = new dbquery();
		$db->connect();

		$coll_id = $_SESSION['collections'][0]['id'];
		$table = $_SESSION['collections'][0]['view'];

		$ressources = $this->get_res_id($caseId);
		$where_limitation = " res_id in(";
		foreach ($ressources as $i)
		{
			$where_limitation .= $i.",";
		}
		$where_limitation = substr($where_limitation, 0,-1);
		$where_limitation .= ")";

		$query="SELECT count(res_id) as nb, status from ".$table." where ".$where_limitation." group by status";
		$db->query($query);

		$my_return = array();
		while ($result=$db->fetch_object())
		{
			array_push($my_return,array( "status"=>$result->status, "nb_docs"=>$result->nb));
		}
		return $my_return;
	}

	private function change_last_update($caseId)
	{
		$table = $_SESSION['tablename']['cases'];
		$request = new request();
		$current_date = $request->current_datetime();
		$data = array();
		$where = "case_id = ".$caseId;
		array_push($data, array('column' => "case_last_update_date", 'value' => $current_date, "type" => ""));
		$request->update($table, $data, $where, $_SESSION['config']['databasetype']);

	}


	public function close_case($caseId)
	{
		if (empty($caseId))
			echo "close_case ::arg1 error!</br>";

		$db = new dbquery();
		$db->connect();

		$req = new request();
		$current_date = $req->current_datetime();

		$query="UPDATE ".$_SESSION['tablename']['cases']." SET case_closing_date = ".$current_date." where case_id = ".$caseId." ";

		if ($db->query($query))
			return true;
		else
			return false;
	}

	protected function detach_all_from_cases($res_id,$caseId)
	{
		$db = new dbquery();
		$db->connect();

		$query="DELETE FROM ".$_SESSION['tablename']['cases_res']." WHERE res_id = '".$res_id."' and case_id <> '".$caseId."' ";
		$db->query($query);
	}

	public function get_case_id($res_id, $coll_id = "")
	{
		if (empty($res_id))
			echo "get_case_id ::arg1 error!<br/>";

		$db = new dbquery();
		$db->connect();
		$query="select  case_id from  ".$_SESSION['collections'][0]['view']." where res_id = ".$res_id." ";
		$db->query($query);
		if($db->nb_result() >0)
		{
			$res = $db->fetch_object();
			return $res->case_id;
		}
		else
		{
			return false;
		}
	}
}
