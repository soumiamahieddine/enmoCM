<?php
/**
* modules tools Class for workflow
*
*  Contains all the functions to  modules tables for workflow
*
* @package  maarch
* @version 3.0
* @since 10/2005
* @license GPL v3
* @author  Laurent Giovannoni  <dev@maarch.org>
*
*/
require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
. 'class_request.php';
require_once 'core' . DIRECTORY_SEPARATOR . 'core_tables.php';
class folder extends request
{
	/**
	* System Identifier of the folder
    * @access private
    * @var integer
    */
	private $system_id;
	/**
	* Identifier of the foldertype
    * @access private
    * @var integer
    */
	private $foldertype_id;

	/**
	* Label of the foldertype
    * @access private
    * @var integer
    */
	private $foldertype_label;

	/**
	* Identifier of the folder (Matricule)
    * @access private
    * @var integer
    */
	private $folder_id;

	/**
	* Folder Name
    * @access private
    * @var integer
    */
	private $folder_name;

	/**
	* System Identifier of the parent folder
    * @access private
    * @var integer
    */
	private $parent_id;


	/**
	* Identifier of the folder creator
    * @access private
    * @var string
    */
	private $typist;

	/**
	* Folder status
    * @access private
    * @var string
    */
	private $status;

	/**
	* Level of the folder
    * @access private
    * @var integer
    */
	private $level;


	/**
	* Time of the folder retention
    * @access private
    * @var string
    */
	//private $retention_time;

	/**
	* Creation date of the folder
    * @access private
    * @var date
    */
	private $creation_date;

	/**
	* identifier of the folder out card
    * @access private
    * @var integer
    */
	private $folder_out_id;

	/**
	* folder is complete or not
    * @access private
    * @var boolean
    */
	private $complete;

	/**
	* Collection identifier
    * @access private
    * @var string
    */
	private $coll_id;
	/**
	* Collection identifier
    * @access private
    * @var string
    */
	private $last_modified_date;

	/**
	* Last modification date
    * @access private
    * @var date
    */
	private $desarchive;

	/**
	* Dynamic index
    * @access private
    * @var array
    */
	private $index;

	function __construct()
	{
		parent::__construct();
		$this->index = array();
	}

	/**
	* Build Maarch module tables into sessions vars with a xml configuration file
	*/
	public function build_modules_tables()
	{
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."folder".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."folder".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		else
		{
			$path = "modules".DIRECTORY_SEPARATOR."folder".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."config.xml";
		}
		$xmlconfig = simplexml_load_file($path);

		$TABLENAME = $xmlconfig->TABLENAME;
		$_SESSION['tablename']['fold_folders'] = (string) $TABLENAME->fold_folders;
		$_SESSION['tablename']['fold_folders_out'] = (string) $TABLENAME->fold_folders_out;
		$_SESSION['tablename']['fold_foldertypes'] = (string) $TABLENAME->fold_foldertypes;
		$_SESSION['tablename']['fold_foldertypes_doctypes'] = (string) $TABLENAME->fold_foldertypes_doctypes;
		$_SESSION['tablename']['fold_foldertypes_indexes'] = (string) $TABLENAME->fold_foldertypes_indexes;
		$_SESSION['tablename']['fold_foldertypes_doctypes_level1'] = (string) $TABLENAME->fold_foldertypes_doctypes_level1;

		$HISTORY = $xmlconfig->HISTORY;
		$_SESSION['history']['folderdel'] = (string) $HISTORY->folderdel;
		$_SESSION['history']['folderadd'] = (string) $HISTORY->folderadd;
		$_SESSION['history']['folderup'] = (string) $HISTORY->folderup;
		$_SESSION['history']['folderview'] = (string) $HISTORY->folderview;
		$_SESSION['history']['foldertypeadd'] = (string) $HISTORY->foldertypeadd;
		$_SESSION['history']['foldertypeup']= (string) $HISTORY->foldertypeup;
		$_SESSION['history']['foldertypedel']= (string) $HISTORY->foldertypedel;
	}


	/**
	* load folder object from the folder system id
	*
	* @param int $id folder system id
	* @param string $table folder table
	*/
	function load_folder($id, $table)
	{
		require_once('modules'.DIRECTORY_SEPARATOR.'folder'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_admin_foldertypes.php');
		$ft = new foldertype();
		$this->connect();
		$this->query("select foldertype_id from ".$table." where folders_system_id = ".$id."");
		$res = $this->fetch_object();
		$this->foldertype_id = $res->foldertype_id;
		$this->system_id = $id;
		$tab_index = $ft->get_indexes($this->foldertype_id);

		$fields = " folder_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date,folder_out_id, is_complete, is_folder_out, last_modified_date, folder_name";
		foreach(array_keys($tab_index) as $key)
		{
			$fields .= ", ".$key;
		}
		$this->query("select ".$fields." from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$id."");
		//$this->show();
		$res = $this->fetch_object();

		$this->folder_id = $this->show_string($res->folder_id);
		$this->folder_name = $this->show_string($res->folder_name);
		$this->parent_id = $res->parent_id;
		$this->typist = $this->show_string($res->typist);
		$this->status = $res->status;
		$this->level = $res->folder_level;
		$this->creation_date = $this->format_date_db($res->creation_date, true);
		$this->folder_out_id = $res->folder_out_id;
		$this->complete = $res->is_complete;
		$this->desarchive = $res->is_folder_out;
		$this->last_modified_date = $this->format_date_db($res->last_modified_date, true);

		foreach(array_keys($tab_index) as $key)
		{
			$tab_index[$key]['value'] = $res->$key;
			$tab_index[$key]['show_value'] = $res->$key;
			if($tab_index[$key]['type_field'] == 'select')
			{
				for($i=0;$i<count($tab_index[$key]['values']);$i++)
				{
					if($tab_index[$key]['values'][$i]['id'] == $tab_index[$key]['value'] )
					{
						$tab_index[$key]['show_value'] = $tab_index[$key]['values'][$i]['label'];
						break;
					}
				}
			}
			elseif($tab_index[$key]['type'] == 'date')
			{
				$tab_index[$key]['show_value'] = $this->format_date_db($tab_index[$key]['value'], true);
			}
			elseif($tab_index[$key]['type'] == 'string')
			{
				$tab_index[$key]['show_value'] = $this->show_string($tab_index[$key]['value']);
			}
		}
		$this->index = array();
		$this->index = $tab_index;

		$this->query("select foldertype_label, coll_id from ".$_SESSION['tablename']['fold_foldertypes']." where foldertype_id = ".$this->foldertype_id);
		$res = $this->fetch_object();
		$this->foldertype_label = $this->show_string($res->foldertype_label);
		$this->coll_id = $this->show_string($res->coll_id);

	}

	/**
	* Creates a folder
	*/
	public function create_folder()
	{
		$this->checks_folder_data();
		if(!empty($_SESSION['error']))
		{
			header("location: ".$_SESSION['config']['businessappurl']."index.php?page=create_folder_form&module=folder");
			exit();
		}
		else
		{
			$this->connect();
			$this->query("select folder_id from ".$_SESSION['tablename']['fold_folders']." where folder_id= '".$_SESSION['m_admin']['folder']['folder_id'] ."'");
			if($this->nb_result() > 0)
			{
				$_SESSION['error'] = $_SESSION['m_admin']['folder']['folder_id'] ." "._ALREADY_EXISTS."<br />";
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=create_folder_form&module=folder");
				exit();
			}
			else
			{
				$this->connect();
				$this->query("INSERT INTO ".$_SESSION['tablename']['fold_folders']." (folder_id, folder_name, foldertype_id,creation_date, typist, last_modified_date, parent_id,folder_level) VALUES ('".$this->protect_string_db($_SESSION['m_admin']['folder']['folder_id'])."', '".$this->protect_string_db($_SESSION['m_admin']['folder']['folder_name'])."',".$_SESSION['m_admin']['folder']['foldertype_id'].",  ".$this->current_datetime().", '".$_SESSION['user']['UserId']."', ".$this->current_datetime().", ".$_SESSION['m_admin']['folder']['folder_parent'].", ".$_SESSION['m_admin']['folder']['folder_level']." )");
				$this->query('select folders_system_id from '.$_SESSION['tablename']['fold_folders']." where folder_id = '".$this->protect_string_db($_SESSION['m_admin']['folder']['folder_id'])."'");
				$res = $this->fetch_object();
				$id = $res->folders_system_id;

				require_once('modules'.DIRECTORY_SEPARATOR.'folder'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR."class_admin_foldertypes.php");
				$foldertype = new foldertype();

				$query = $foldertype->get_sql_update($_SESSION['m_admin']['folder']['foldertype_id'], $_SESSION['m_admin']['folder']['indexes']);
				if(!empty($query))
				{
					$query = preg_replace('/^,/', '', $query);
					$query = "update ".$_SESSION['tablename']['fold_folders']." set ".$query." where folders_system_id = ".$id;
					$this->query($query);
				}
				if($_SESSION['history']['folderadd'] == "true")
				{
					require("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
					$hist = new history();
					$hist->add($_SESSION['tablename']['fold_folders'], $id ,"ADD",_FOLDER_ADDED." : ".$_SESSION['m_admin']['folder']['folder_id'] , $_SESSION['config']['databasetype'], 'folder');
				}

				$_SESSION['error'] = _FOLDER_ADDED;
				unset($_SESSION['m_admin']);
				header("location: ".$_SESSION['config']['businessappurl']."index.php?page=show_folder&module=folder&id=".$id);
				exit();
			}

		}
	}

	/**
	* Processes data during folder creation
	*/
	private function checks_folder_data()
	{
		require_once('modules'.DIRECTORY_SEPARATOR.'folder'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR."class_admin_foldertypes.php");
		$foldertype = new foldertype();

		if(isset($_REQUEST['folder_id']) && !empty($_REQUEST['folder_id']))
		{
			$_SESSION['m_admin']['folder']['folder_id'] = $this->wash($_REQUEST['folder_id'], "no", _FOLDER_ID);
		}
		else
		{
			$_SESSION['m_admin']['folder']['folder_id'] = '';
			$_SESSION['error'] .= _FOLDER_ID.' '._IS_EMPTY.'<br/>';
		}

		if(isset($_REQUEST['folder_name']) && !empty($_REQUEST['folder_name']))
		{
			$_SESSION['m_admin']['folder']['folder_name'] = $this->wash($_REQUEST['folder_name'], "no", _FOLDERNAME);
		}
		else
		{
			$_SESSION['m_admin']['folder']['folder_name'] = '';
			$_SESSION['error'] .= _FOLDERNAME.' '._IS_EMPTY.'<br/>';
		}

		$_SESSION['m_admin']['folder']['folder_parent'] = 0;
		$_SESSION['m_admin']['folder']['folder_level'] = 1;

		if(isset($_REQUEST['folder_parent']) && !empty($_REQUEST['folder_parent']))
		{
			$_SESSION['m_admin']['folder']['folder_parent'] = $this->wash($_REQUEST['folder_parent'], "num", _FOLDER_PARENT);
			$_SESSION['m_admin']['folder']['folder_level'] = 2;
		}

		if(isset($_REQUEST['foldertype']) && !empty($_REQUEST['foldertype']))
		{
			$_SESSION['m_admin']['folder']['foldertype_id'] = $this->wash($_REQUEST['foldertype'], "no", _FOLDERTYPE);
			$indexes = $foldertype->get_indexes($_SESSION['m_admin']['folder']['foldertype_id']);

			$values = array();
			foreach( array_keys($indexes) as $key)
			{
				if(isset($_REQUEST[$key]))
				{
					$values [$key] = $_REQUEST[$key];
				}
				else
				{
					$values [$key] = '';
				}
			}

			$_SESSION['m_admin']['folder']['indexes'] = $values ;
			$foldertype->check_indexes($_SESSION['m_admin']['folder']['foldertype_id'], $values );
		}
		else
		{
			$_SESSION['m_admin']['folder']['foldertype'] = '';
			$_SESSION['error'] .= _FOLDERTYPE.' '._IS_EMPTY.'<br/>';
		}
	}


	/**
	* get all data from the current folder object
	*/
	public function get_folder_info()
	{
		$folder = array();
		$folder['system_id'] = $this->system_id ;
		$folder['foldertype_id'] = $this->foldertype_id ;
		$folder['folder_name'] = $this->folder_name ;
		$folder['foldertype_label'] = $this->foldertype_label ;
		$folder['folder_id'] = $this->folder_id ;
		$folder['parent_id'] = $this->parent_id ;
		$folder['folder_name'] = $this->folder_name;
		$folder['typist'] = $this->typist ;
		$folder['status'] = $this->status;
		$folder['level'] = $this->level ;
		$folder['creation_date'] = $this->creation_date ;
		$folder['folder_out_id'] = $this->folder_out_id ;
		$folder['complete'] = $this->complete ;
		$folder['desarchive'] = $this->desarchive ;
		$folder['coll_id'] = $this->coll_id ;
		$folder['last_modified_date'] = $this->last_modified_date ;
		$folder['index'] = array();
		$folder['index'] = $this->index;

		return $folder;
	}

	public function get_field($field_name, $in_index = false)
	{
		if($field_name == 'folders_system_id')
		{
			return $this->system_id;
		}
		elseif($field_name == 'foldertype_id')
		{
			return $this->foldertype_id;
		}
		elseif($field_name == 'folder_name')
		{
			return $this->folder_name;
		}
		elseif($field_name == 'folder_id')
		{
			return $this->folder_id;
		}
		elseif($field_name == 'parent_id')
		{
			return $this->parent_id;
		}
		elseif($field_name == 'subject')
		{
			return $this->subject;
		}
		elseif($field_name == 'typist')
		{
			return $this->typist;
		}
		elseif($field_name == 'status')
		{
			return $this->status;
		}
		elseif($field_name == 'level')
		{
			return $this->level;
		}
		elseif($field_name == 'creation_date')
		{
			return $this->creation_date;
		}
		elseif($field_name == 'folder_out_id')
		{
			return $this->folder_out_id;
		}
		elseif($field_name == 'complete')
		{
			return $this->complete;
		}
		elseif($field_name == 'desarchive')
		{
			return $this->desarchive;
		}
		elseif($field_name == 'coll_id')
		{
			return $this->coll_id;
		}
		elseif($in_index)
		{
			for($i=0; $i < count($this->index);$i++)
			{
				if($field_name == $this->index[$i]['column'])
				{
					return $this->index[$i]['value'];
				}
			}
			return '';
		}
		else
		{
			return '';
		}
	}

	/**
	* calculate the missing document types from a folder
	*
	* @param string $table_res resources table
	* @param string $table_foldertypes_doc foldertypes doctypes table
	* @param string $table_doctypes doctypes table
	* @param string $id folder system id
	* @param string $foldertype_id id of the foldertype
	*/
	public function missing_res($table_res,  $table_foldertypes_doc, $table_doctypes, $id, $foldertype_id)
	{
		$this->connect();
		$indexed_types = array();
		$this->query("select distinct type_id as type from ".$table_res." where status <> 'DEL' and folders_system_id = ".$id);
		while($res = $this->fetch_object())
		{
			array_push($indexed_types, $res->type );
		}

		$waited_types = array();
		$this->query("select doctype_id from ".$table_foldertypes_doc." where foldertype_id = ".$foldertype_id);
		//$this->show();
		while($res = $this->fetch_object())
		{
			array_push($waited_types, $res->doctype_id );
		}
		$temp = array();
		$temp = array_diff($waited_types, $indexed_types);
		$temp = array_values($temp);
		$missing_res = array();
		for($i=0; $i < count($temp); $i++)
		{
			$this->query("select type_id, description from ".$table_doctypes." where type_id = ".$temp[$i]);
			//$this->show();
			$res = $this->fetch_object();
			if($res->type_id <> "")
			{
				array_push($missing_res, array('ID' => $res->type_id, 'LABEL' => $this->show_string($res->description)));
			}
		}
		return $missing_res ;
	}


	/* calculate the missing document types from a folder
	*
	* @param string $table_res resources table
	* @param string $table_foldertypes_doc foldertypes doctypes table
	* @param string $table_doctypes doctypes table
	* @param string $id folder system id
	* @param string $foldertype_id id of the foldertype
	*/
	public function missing_res2($table_res,  $table_foldertypes_doc, $table_doctypes, $id, $foldertype_id)
	{
		$this->connect();
		$indexed_types = array();
		$this->query("select distinct type_id as type from ".$table_res." where status <> 'DEL' and folders_system_id = ".$id);

		while($res = $this->fetch_object())
		{
			array_push($indexed_types, $res->type );
		}
		$waited_types = array();
		$this->query("select doctype_id from ".$table_foldertypes_doc." where foldertype_id = ".$foldertype_id);
		while($res = $this->fetch_object())
		{
			array_push($waited_types, $res->doctype_id );
		}
		$html_tab = "<table width=\"100%\" border=\"1\" align=\"center\"><tr><td>"._PIECE_TYPE."</td><td>"._MISSING."</td></tr>";
		for($i=0; $i < count($waited_types); $i++)
		{
			$this->query("select type_id, description from ".$table_doctypes." where type_id = ".$waited_types[$i]);
			$res = $this->fetch_object();
			if($res->type_id <> "")
			{
				$this->query("select type_id as type from ".$table_res." where status <> 'DEL' and folders_system_id = ".$id." and type_id = '".$waited_types[$i]."'");
				$res2 = $this->fetch_object();
				if($waited_types[$i] <> $res2->type)
				{
					$html_tab .= "<tr><td><b>". $this->show_string($res->description)."</b></td><td>";
					$html_tab .= "<b>X</b>";
					//echo $waited_types[$i]." X\n";
				}
				else
				{
					$html_tab .= "<tr><td>".$this->show_string($res->description)."</td><td>";
					$html_tab .= "&nbsp;";
				}
			}
			$html_tab .= "</td></tr>";
		}
		$html_tab .= "</table>";
		return $html_tab ;
	}


	/**
	* calculate if a folder is complete or no
	*
	* @param string $table_res resources table
	* @param string $table_foldertypes_doc foldertypes doctypes table
	* @param string $table_doctypes doctypes table
	* @param string $id folder system id
	*/
	public function is_complete($table_res, $table_foldertypes_doc, $table_doctypes, $id)
	{
		$tab = $this->missing_res($table_res, $table_foldertypes_doc, $table_doctypes, $id, $this->foldertype_id);

		if(count($tab) > 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	* get all the history data of the current folder
	*
	* @param string $table_history history table
	* @param string $table_folder folders table
	*/
	public function get_history()
	{
		$this->connect();

		$history = array();

		$this->query("select h.event_date, h.info, h.user_id, u.lastname, u.firstname from ".$_SESSION['tablename']['history']." h, ".$_SESSION['tablename']['users']." u where h.table_name = '".$_SESSION['tablename']['fold_folders']."' and h.record_id = '".$this->system_id."' and h.event_type <> 'UP_CONTRACT' and h.user_id = u.user_id order by h.event_date desc ");
		//$this->show();
		while($res = $this->fetch_object())
		{
			array_push($history, array( 'DATE' => $res->event_date, 'EVENT' => $this->show_string($res->info), 'USER' => $this->show_string($res->lastname.' '.$res->firstname) ));
		}

		return $history;
	}


	/**
	* modify the folder in the users table
	*
	* @param string $id folder identifier
	* @param string $user_id user identifier
	* @param string $table users table
	*/
	public function modify_default_folder_in_db($id, $user_id, $table)
	{
		$this->connect();
		$this->query('update '.$table." set custom_t1 = '".$id."' where user_id = '".$user_id."'");
	}

	public function update_folder($values, $id_to_update)
	{
		require_once('modules'.DIRECTORY_SEPARATOR.'folder'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_admin_foldertypes.php');
		require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_request.php');
		$data = array();
		$foldertype = new foldertype();
		$request = new request();
		$foldertype_id =  $values['foldertype_id'];
		if(!empty($foldertype_id))
		{
			$indexes = $foldertype->get_indexes( $foldertype_id,'minimal');
			$val_indexes = array();
			for($i=0; $i<count($indexes);$i++)
			{
				$val_indexes[$indexes[$i]] =  $values[$indexes[$i]];
			}
			$test_type = $foldertype->check_indexes($foldertype_id, $val_indexes );
			if($test_type)
			{
				$data = $foldertype->fill_data_array($foldertype_id, $val_indexes, $data);
			}
		}
		else
		{
			$_SESSION['error'] .= _FOLDERTYPE.' '._IS_EMPTY;
		}
		if(empty($_SESSION['error']))
		{
			$where = " folders_system_id = ".$id_to_update;
			array_push($data, array('column' => 'last_modified_date', 'value' => $request->current_datetime(), 'type' => "date"));
			$request->update($_SESSION['tablename']['fold_folders'], $data, $where, $_SESSION['config']['databasetype']);

			$_SESSION['error'] = _FOLDER_INDEX_UPDATED." (".strtolower(_NUM).$values['folder_id'].")";
			if($_SESSION['history']['folderup'])
			{
				require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_history.php");
				$hist = new history();
				$hist->add($_SESSION['tablename']['fold_folders'], $id_to_update, "UP", $_SESSION['error'], $_SESSION['config']['databasetype'],'apps');
			}
		}
		$_SESSION['error_page'] = $_SESSION['error'];
		$_SESSION['error']= '';
		?>
		<script  type="text/javascript">
			//window.opener.reload();
			var error_div = $('main_error');
			if(error_div)
			{
				error_div.innerHTML = '<?php echo $_SESSION['error_page'];?>';
			}
		</script>
		<?php
	}

	public function delete_folder($folder_sys_id, $foldertype)
	{
		$this->connect();
		$this->query("select coll_id from ".$_SESSION['tablename']['fold_foldertypes']." where foldertype_id = ".$foldertype);
		$res = $this->fetch_object();
		$coll_id = $res->coll_id;
		require_once('core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_security.php');
		$sec = new security();
		$table = $sec->retrieve_table_from_coll($coll_id);

		if(!empty($table) && !empty($folder_sys_id))
		{
			$this->query("select folder_level, folder_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$folder_sys_id);
			$res = $this->fetch_object();
			$level = $res->folder_level;
			$fold_id = $res->folder_id;
			$where = '';

			if($level == 1)
			{
				$this->query("select folders_system_id from ".$_SESSION['tablename']['fold_folders']." where parent_id = ".$folder_sys_id." and folder_level = 2");
				if($this->nb_result() > 0)
				{
					while($res = $this->fetch_object())
					{
						$where .= " or folders_system_id = ".$res->folders_system_id;
					}
				}
			}
			$this->query("update ".$table." set status = 'DEL' where folders_system_id = ".$folder_sys_id.$where);
			$this->query("update ".$_SESSION['tablename']['fold_folders']." set status = 'DEL' where folders_system_id = ".$folder_sys_id.$where);
			$_SESSION['error'] = _FOLDER_DELETED." (".$fold_id.")";
		}
	}

	public function is_folder_exists($folder_system_id)
	{
		if($folder_system_id <> "")
		{
			$this->connect();
			$this->query("select folder_id from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$folder_system_id);
			$res = $this->fetch_object();
			if($res->folder_id <> "")
			{
				$find = true;
			}
			else
			{
				$find = false;
			}
		}
		else
		{
			$find = false;
		}
		return $find;
	}

	/**
	* calculate if a folder is empty
	*
	* @param string $table_res resources table
	* @param string $id folder system id
	*/
	public function is_folder_empty($table_res, $id)
	{
		$this->connect();
		$indexed_types = array();
		$this->query("select * from ".$table_res." where status <> 'DEL' and folders_system_id = ".$id);
		if($this->nb_result() > 0)
		{
			$empty = false;
		}
		else
		{
			$empty = true;
		}
		return $empty;
	}
}
?>
