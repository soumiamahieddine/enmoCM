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
	* System Identifier of the parent folder
    * @access private
    * @var integer
    */
	private $parent_id;

	/**
	* Folder name
    * @access private
    * @var string
    */
	private $folder_name;

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
	* subject
    * @access private
    * @var integer
    */
	private $subject;

	/**
	* description
    * @access private
    * @var integer
    */
	private $description;

	/**
	* author
    * @access private
    * @var integer
    */
	private $author;
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
	* true if the folder is out, false otherwise
    * @access private
    * @var boolean
    */
	private $coll_id;

	/**
	* Collection identifier
    * @access private
    * @var string
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
		$xmlconfig = simplexml_load_file($_SESSION['pathtomodules']."folder/xml/config.xml");

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

	public function load_module_var_session()
	{
		$func = new functions();
		$_SESSION['folder_index'] = array();
		$xmlfile = simplexml_load_file($_SESSION['pathtomodules']."folder".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."folder_index.xml");
		$i = 0;
		$path_lang = $_SESSION['pathtomodules']."folder".DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';
		foreach($xmlfile->INDEX as $INDEX)
		{
			$_SESSION['folder_index'][$i]['COLUMN'] = (string) $INDEX->COLUMN;
				$tmp = (string) $INDEX->LABEL;
				$tmp2 = $this->retrieve_constant_lang($tmp, $path_lang);
				if($tmp2 <> false)
				{
					$_SESSION['folder_index'][$i]['LABEL'] = $tmp2;
				}
				else
				{
					$_SESSION['folder_index'][$i]['LABEL']= $tmp;
				}
				if(isset($INDEX->FOREIGN_KEY) && !empty($INDEX->FOREIGN_KEY))
				{
					$_SESSION['folder_index'][$i]['FOREIGN_KEY']= (string) $INDEX->FOREIGN_KEY;
				}
				if(isset($INDEX->FOREIGN_LABEL) && !empty($INDEX->FOREIGN_LABEL))
				{
					$_SESSION['folder_index'][$i]['FOREIGN_LABEL']= (string) $INDEX->FOREIGN_LABEL;
				}
				if(isset($INDEX->TABLENAME) && !empty($INDEX->TABLENAME))
				{
					$_SESSION['folder_index'][$i]['TABLENAME']= (string) $INDEX->TABLENAME;
				}
				if(isset($INDEX->ORDER) && !empty($INDEX->ORDER))
				{
					$_SESSION['folder_index'][$i]['ORDER']= (string) $INDEX->ORDER;
				}
				if(isset($INDEX->WHERE) && !empty($INDEX->WHERE))
				{
					$_SESSION['folder_index'][$i]['WHERE']= (string) $INDEX->WHERE;
				}

				if(count($INDEX->VALUES) > 0)
				{
					$_SESSION['folder_index'][$i]['VALUES'] = array();
					$k=0;
					foreach($INDEX->VALUES as $value)
					{
						//$_SESSION['folder_index'][$i]['VALUES'][$k]['id'] = (string) $value->ID;
						$_SESSION['folder_index'][$i]['VALUES'][$k]['label'] = (string) $value->LABEL;
						$k++;
					}
				}
			$i++;
		}
	}



	/**
	* load folder object from the folder system id
	*
	* @param int $id folder system id
	* @param string $table folder table
	*/
	function load_folder1($id, $table)
	{
		$this->connect();
		$this->query("select foldertype_id from ".$table." where folders_system_id = ".$id."");
		$res = $this->fetch_object();
		$this->foldertype_id = $res->foldertype_id;
		$this->system_id = $id;
		$tab_index = $this->get_folder_index($this->foldertype_id);

		$fields = " folder_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date,folder_out_id, is_complete, is_folder_out";
		for($i=0; $i<count($tab_index);$i++)
		{
			$fields .= ", ".$tab_index[$i]['column'];
		}
		$this->query("select ".$fields." from ".$_SESSION['tablename']['fold_folders']." where folders_system_id = ".$id."");
		$res = $this->fetch_object();

		$this->folder_id = $this->show_string($res->folder_id);
		$this->parent_id = $res->parent_id;
		$this->folder_name = $this->show_string($res->folder_name);
		$this->subject = $this->show_string($res->subject);
		$this->description = $this->show_string($res->description);
		$this->author = $this->show_string($res->author);
		$this->typist = $this->show_string($res->typist);
		$this->status = $res->status;
		$this->level = $res->folder_level;
		$this->creation_date = $res->creation_date;
		$this->folder_out_id = $res->folder_out_id;
		$this->complete = $res->is_complete;
		$this->desarchive = $res->is_folder_out;

		for($i=0; $i<count($tab_index);$i++)
		{
			$tab_index[$i]['value'] = $res->$tab_index[$i]['column'];
		}
		$this->index = array();
		$this->index = $tab_index;

		$this->query("select foldertype_label, coll_id from ".$_SESSION['tablename']['fold_foldertypes']." where foldertype_id = ".$this->foldertype_id);
		$res = $this->fetch_object();
		$this->foldertype_label = $this->show_string($res->foldertype_label);
		$this->coll_id = $this->show_string($res->coll_id);

	}

	/**
	* load folder object from the folder id
	*
	* @param int $id folder id
	* @param string $table folder table
	*/
	function load_folder2($id, $table)
	{
		$this->connect();
		$this->query("select foldertype_id from ".$table." where folder_id = '".$id."'");
		 //$this->show();
		$res = $this->fetch_object();
		$this->foldertype_id = $res->foldertype_id;
		$this->folder_id = $id;

		$tab_index = $this->get_folder_index($this->foldertype_id);

		$fields = " folders_system_id, parent_id, folder_name, subject, description, author, typist, status, folder_level, creation_date,folder_out_id, is_complete, is_folder_out";
		for($i=0; $i<count($tab_index);$i++)
		{
			$fields .= ", ".$tab_index[$i]['column'];
		}
		$this->query("select ".$fields." from ".$_SESSION['tablename']['fold_folders']." where folder_id = '".$id."'");
		$res = $this->fetch_object();

		$this->system_id = $res->folders_system_id;
		$this->parent_id = $res->parent_id;
		$this->folder_name = $this->show_string($res->folder_name);
		$this->subject = $this->show_string($res->subject);
		$this->description = $this->show_string($res->description);
		$this->author = $this->show_string($res->author);
		$this->typist = $this->show_string($res->typist);
		$this->status = $res->status;
		$this->level = $res->folder_level;
		$this->creation_date = $res->creation_date;
		$this->folder_out_id = $res->folder_out_id;
		$this->complete = $res->is_complete;
		$this->desarchive = $res->is_folder_out;

		for($i=0; $i<count($tab_index);$i++)
		{
			$tab_index[$i]['value'] = $res->$tab_index[$i]['column'];
		}
		$this->index = array();
		$this->index = $tab_index;

		$this->query("select foldertype_label, coll_id from ".$_SESSION['tablename']['fold_foldertypes']." where foldertype_id = ".$this->foldertype_id);
		$res = $this->fetch_object();
		$this->foldertype_label = $this->show_string($res->foldertype_label);
		$this->coll_id = $this->show_string($res->coll_id);

	}

	private function get_folder_index( $foldertype_id)
	{
		$folder_index = array();
		$array = array();
		$this->connect();

		$this->query('select * from '.$_SESSION['tablename']['fold_foldertypes']." where foldertype_id = ".$foldertype_id);
		$array = $this->fetch_array();
		$z = 0;
		for($i=0;$i<count($_SESSION['folder_index']);$i++)
		{
			foreach(array_keys($array) as $value)
			{
				if($value <> "folders_system_id" && $value <> "folder_id" && $value <> "comment" && $value <> "retention_time" && !is_numeric($value))
				{
					if($array[$value] == '1000000000' || $array[$value] == '1100000000')
					{

						if($_SESSION['folder_index'][$i]['COLUMN'] == $value)
						{
							$folder_index[$z]['column'] = $_SESSION['folder_index'][$i]['COLUMN'];
							$folder_index[$z]['label'] = $_SESSION['folder_index'][$i]['LABEL'];

							$folder_index[$z]['date'] = $this->is_date_column($_SESSION['folder_index'][$i]['COLUMN']);
							if($array[$value] == '1100000000')
							{
								$folder_index[$z]['mandatory'] = true;
							}
							if(isset($_SESSION['folder_index'][$i]['FOREIGN_KEY']) && !empty($_SESSION['folder_index'][$i]['FOREIGN_KEY']))
							{
								$folder_index[$z]['foreign_key'] = $_SESSION['folder_index'][$i]['FOREIGN_KEY'];
							}
							if(isset($_SESSION['folder_index'][$i]['FOREIGN_LABEL']) && !empty($_SESSION['folder_index'][$i]['FOREIGN_LABEL']))
							{
								$folder_index[$z]['foreign_label'] = $_SESSION['folder_index'][$i]['FOREIGN_LABEL'];
							}
							if(isset($_SESSION['folder_index'][$i]['TABLENAME']) && !empty($_SESSION['folder_index'][$i]['TABLENAME']))
							{
								$folder_index[$z]['tablename'] = $_SESSION['folder_index'][$i]['TABLENAME'];
							}
							if(isset($_SESSION['folder_index'][$i]['WHERE']) && !empty($_SESSION['folder_index'][$i]['WHERE']))
							{
								$folder_index[$z]['where'] = $_SESSION['folder_index'][$i]['WHERE'];
							}
							if(isset($_SESSION['folder_index'][$i]['ORDER']) && !empty($_SESSION['folder_index'][$i]['ORDER']))
							{
								$folder_index[$z]['order'] = $_SESSION['folder_index'][$i]['ORDER'];
							}

							if(isset($_SESSION['folder_index'][$i]['VALUES']) && count($_SESSION['folder_index'][$i]['VALUES']) > 0)
							{
								$folder_index[$z]['values'] = $_SESSION['folder_index'][$i]['VALUES'];
							}
							$z++;
						}
					}
				}
			}
		}
		return $folder_index;
	}

	/**
	* create and insert a new folder into table_folder (create a temporary folder id using table_param)
	*
	* @param string $table_param parameters table
	* @param string $table_folder folder table
	* @param array $data array which contains the data necessary to create a new folder
	*/
/*
	public function create_folder($table_param, $table_folder, $data, $databasetype)
	{
		$today = date("Y/m/d");
		$this->connect();

		// temporary folder_id
		$this->query("select param_value_int from ".$table_param." where id = 'folder_id_increment'");

		$res = $this->fetch_object();
		$num = (string)$res->param_value_int;

		$len = strlen($num);
		if($len < 6)
		{
			while(strlen($num) < 6)
			{
				$num = '0'.$num;
			}
		}

		// after 9999 we restart at 1
		if($num == "9999" )
		{
			$num = "1";
			$folder_id = "T_000001";

			$this->query("update ".$table_param." set param_value_int = 2 where id = 'folder_id_increment'");
		}
		else
		{
			$folder_id = 'T_'.$num;
			$this->query("update ".$table_param." set param_value_int = (param_value_int + 1) where id = 'folder_id_increment'");
		}

		array_push($data, array('column' => "folder_id", 'value' => $folder_id, 'type' => "string"));
		array_push($data, array('column' => "status", 'value' => 'NEW', 'type' => "string"));

		if($databasetype == "SQLSERVER")
		{
			$func_date = 'getdate()';
		}
		else // MYSQL & POSTGRESQL
		{
			$func_date = 'now()';
		}
		array_push($data, array('column' => 'creation_date', 'value' => $func_date, 'type' => "function"));

		$this->insert($table_folder, $data, $_SESSION['config']['databasetype']);
		//echo "ici";
		//exit;
		return $folder_id;
	}
*/
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
				$this->query("INSERT INTO ".$_SESSION['tablename']['fold_folders']." (folder_id, foldertype_id, description, creation_date, typist) VALUES ('".$this->show_string($_SESSION['m_admin']['folder']['folder_id'])."', ".$_SESSION['m_admin']['folder']['foldertype_id'].", '".$this->show_string($_SESSION['m_admin']['folder']['desc'])."', ".$this->current_datetime().", '".$_SESSION['user']['UserId']."');");
				$this->query('select folders_system_id from '.$_SESSION['tablename']['fold_folders']." where folder_id = '".$this->show_string($_SESSION['m_admin']['folder']['folder_id'])."';");
				$res = $this->fetch_object();
				$id = $res->folders_system_id;

				require_once($_SESSION['pathtomodules'].'folder'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR."class_admin_foldertypes.php");
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
					require($_SESSION['pathtocoreclass']."class_history.php");
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
		require_once($_SESSION['pathtomodules'].'folder'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR."class_admin_foldertypes.php");
		$foldertype = new foldertype();

		if(isset($_REQUEST['folder_id']) && !empty($_REQUEST['folder_id']))
		{
			$_SESSION['m_admin']['folder']['folder_id'] = $this->wash($_REQUEST['folder_id'], "no", _FOLDER_ID);
		}
		else
		{
			$_SESSION['m_admin']['folder']['folder_id'] = '';
			$_SESSION['error'] .= _FOLDER_ID.' '._IS_EMPTY;
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
			$_SESSION['error'] .= _FOLDERTYPE.' '._IS_EMPTY;
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
		$folder['foldertype_label'] = $this->foldertype_label ;
		$folder['folder_id'] = $this->folder_id ;
		$folder['parent_id'] = $this->parent_id ;
		$folder['folder_name'] = $this->folder_name;
		$folder['subject'] = $this->subject ;
		$folder['description'] = $this->description;
		$folder['author'] = $this->author ;
		$folder['typist'] = $this->typist ;
		$folder['status'] = $this->status;
		$folder['level'] = $this->level ;
		$folder['creation_date'] = $this->creation_date ;
		$folder['folder_out_id'] = $this->folder_out_id ;
		$folder['complete'] = $this->complete ;
		$folder['desarchive'] = $this->desarchive ;
		$folder['coll_id'] = $this->coll_id ;
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
		elseif($field_name == 'folder_id')
		{
			return $this->folder_id;
		}
		elseif($field_name == 'parent_id')
		{
			return $this->parent_id;
		}
		elseif($field_name == 'folder_name')
		{
			return $this->folder_name;
		}
		elseif($field_name == 'subject')
		{
			return $this->subject;
		}
		elseif($field_name == 'description')
		{
			return $this->description;
		}
		elseif($field_name == 'author')
		{
			return $this->author;
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
			//$this->query("select type_id, DESCRIPTION from ".$table_doctypes." where type_id = ".$temp[$i]." and CUSTOM_T1 = 'Y'");
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
	* get the history data about contracts of the current folder
	*
	* @param string $table_history history table
	* @param string $table_folder folders table
	*/
	public function get_contract_history($table_history, $table_folder)
	{
		$this->connect();

		$history = array();

		$this->query("select event_date, info from ".$table_history." where table_name = '".$table_folder."' and record_id = '".$this->system_id."' and event_type = 'UP_CONTRACT' order by event_date desc ");

		while($res = $this->fetch_object())
		{
			array_push($history, array( 'DATE' => $res->event_date, 'EVENT' => $this->show_string($res->info)));
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

	public function retrieve_index($array)
	{
		$z = 0;
		for($i=0;$i<count($_SESSION['folder_index']);$i++)
		{

			foreach(array_keys($array) as $value)
			{
				if($value <> "folders_system_id" && $value <> "folder_id" && $value <> "comment" && $value <> "retention_time" && !is_numeric($value) && ($array[$value] == '1000000000' || $array[$value] == '1100000000'))
				{
					if($_SESSION['folder_index'][$i]['COLUMN'] == $value)
					{
						$_SESSION['folder_index_to_use'][$z]['column'] = $_SESSION['folder_index'][$i]['COLUMN'];
						$_SESSION['folder_index_to_use'][$z]['label'] = $_SESSION['folder_index'][$i]['LABEL'];

						$_SESSION['folder_index_to_use'][$z]['date'] = $this->is_date_column($_SESSION['folder_index'][$i]['COLUMN']);
						if($array[$value] == '1100000000')
						{
							$_SESSION['folder_index_to_use'][$z]['mandatory'] = true;
						}
						if(isset($_SESSION['folder_index'][$i]['FOREIGN_KEY']) && !empty($_SESSION['folder_index'][$i]['FOREIGN_KEY']))
						{
							$_SESSION['folder_index_to_use'][$z]['foreign_key'] = $_SESSION['folder_index'][$i]['FOREIGN_KEY'];
						}
						if(isset($_SESSION['folder_index'][$i]['FOREIGN_LABEL']) && !empty($_SESSION['folder_index'][$i]['FOREIGN_LABEL']))
						{
							$_SESSION['folder_index_to_use'][$z]['foreign_label'] = $_SESSION['folder_index'][$i]['FOREIGN_LABEL'];
						}
						if(isset($_SESSION['folder_index'][$i]['TABLENAME']) && !empty($_SESSION['folder_index'][$i]['TABLENAME']))
						{
							$_SESSION['folder_index_to_use'][$z]['tablename'] = $_SESSION['folder_index'][$i]['TABLENAME'];
						}
						if(isset($_SESSION['folder_index'][$i]['WHERE']) && !empty($_SESSION['folder_index'][$i]['WHERE']))
						{
							$_SESSION['folder_index_to_use'][$z]['where'] = $_SESSION['folder_index'][$i]['WHERE'];
						}
						if(isset($_SESSION['folder_index'][$i]['ORDER']) && !empty($_SESSION['folder_index'][$i]['ORDER']))
						{
							$_SESSION['folder_index_to_use'][$z]['order'] = $_SESSION['folder_index'][$i]['ORDER'];
						}

						if(isset($_SESSION['folder_index'][$i]['VALUES']) && count($_SESSION['folder_index'][$i]['VALUES']) > 0)
						{
							$_SESSION['folder_index_to_use'][$z]['values'] = $_SESSION['folder_index'][$i]['VALUES'];
						}
							$z++;
					}

				}
			}
		}

	}

	public function retrieve_index_label($column)
	{
		for($i=0;$i<count($_SESSION['folder_index']);$i++)
		{
			if($_SESSION['folder_index'][$i]['COLUMN'] == $column)
			{
				return $_SESSION['folder_index'][$i]['LABEL'];
			}
		}
	}

	public function user_exit($value, $data, $data_is_array = true)
	{

		if($this->is_date_column($value))
		{
			if(preg_match($_SESSION['date_pattern'],$_REQUEST[$value])== false)
			{
				$_SESSION['error'] .= $this->retrieve_index_label($value)." "._WRONG_FORMAT.".<br/>";
				$_SESSION['field_error'][$value] = true;
			}
			else
			{
				$_SESSION['field_error'][$value] = false;
			}

			 $tmp = $this->format_date_db($_REQUEST[$value]);
			if($data_is_array)
			{
				array_push($data, array('column' => $value, 'value' => $tmp, 'type' => 'date'));
			}
			else
			{
				$data .= $value." >= '".$tmp."' and ";
			}
		}
		if($this->is_text_column($value))
		{
			$field_value = $this->wash($_REQUEST[$value],"no",$this->retrieve_index_label($value));
			if($data_is_array)
			{
				array_push($data, array('column' => $value, 'value' => $this->protect_string_db($_REQUEST[$value]), 'type' => 'string'));
				if($field_value == "")
				{
					$_SESSION['field_error'][$value] = true;
				}
				else
				{
					$_SESSION['field_error'][$value] = false;
				}
			}
			else
			{
				if($_SESSION['config']['databasetype'] == "POSTGRESQL")
				{
					$data .= $value." ilike '".$this->protect_string_db($field_value)."%' and ";
				}
				else
				{
					$data .= $value." like '".$this->protect_string_db($field_value)."%' and ";
				}
			}
		}
		if($this->is_numeric_column($value))
		{
			$field_value = $this->wash($_REQUEST[$value],"num",$this->retrieve_index_label($value));
			if($data_is_array)
			{
				array_push($data, array('column' => $value, 'value' => $_REQUEST[$value], 'type' => 'int'));
				if($field_value == "")
				{
					$_SESSION['field_error'][$value] = true;
				}
				else
				{
					$_SESSION['field_error'][$value] = false;
				}
			}
			else
			{
				$data .= $value.' ='.$field_value.' and ';;
			}
		}
		if($this->is_float_column($value))
		{
			$field_value = $this->wash($_REQUEST[$value],"float",$this->retrieve_index_label($value));
			if($data_is_array)
			{
				array_push($data, array('column' => $value, 'value' => $_REQUEST[$value], 'type' => 'float'));
				if($field_value == "")
				{
					$_SESSION['field_error'][$value] = true;
				}
				else
				{
					$_SESSION['field_error'][$value] = false;
				}
			}
			else
			{
				$data .= $value.' ='.$field_value.' and ';;
			}
		}
		return $data;
	}



	public function is_date_column($column)
	{
		if(preg_match('/custom_d/', $column))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function is_text_column($column)
	{

		if(preg_match('/custom_t/', $column) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function is_numeric_column($column)
	{
		if(preg_match('/custom_n/', $column))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function is_float_column($column)
	{
		if(preg_match('/custom_f/', $column))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function is_mandatory_field($column)
	{
		$string = $column;
		$search="'mandatory_'";
		preg_match($search,$string,$out);
		$count=count($out[0]);
		if($count == 1)
		{
			$find = true;
		}
		return $find;
	}

	public function is_mandatory($column)
	{
		if($_REQUEST['mandatory_'.$column] == "true")
		{
			$find = true;
		}
		return $find;
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
