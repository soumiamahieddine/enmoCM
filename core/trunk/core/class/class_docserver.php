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
* @brief   Manages docservers and  filenames in docservers directory
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

/**
* @brief   Manages docservers and  filenames in docservers directory
*
*<ul>
*  <li>Selection of available docserver</li>
*  <li>Copy of a new file into the docserver</li>
*  <li>Update of the docserver informations in the database</li>
*</ul>
* @ingroup core
*/
 class docserver extends dbquery
 {
 	/**
	* Docserver identifier
         * String
         */
 	private $docserver_id;

	/**
	* Readonly mode activated or not
	* Boolean
	*/
 	private $is_readonly;

	/**
	* Docserver enabled or not
	* Boolean
	*/
 	private $is_enabled;

	/**
	* Docserver size limit
         * Integer
         */
 	private $size_limit;

	/**
	* Actual size of the docserver
         * Integer
          */
 	private $actual_size;

	/**
	* Path of the docserver in the network or local
         * String
         */
 	private $path;

	/**
	* Creation date of the docserver
	* Date
	*/
 	private $creation_date;

	/**
	* Closing date of the docserver
	* Date
	*/
 	private $close_date;

	/**
	* Collection identifier
	* String
	*/
	private $coll_id;

	/**
	* Error variable
	* String
	*/
 	private $error ;

	/**
	* Constructor  :  by default, the construct method chooses the first available docserver for a collection
	*
	* @param  $table  string Docserver table in the database
	* @param  $coll_id  string Collection identifier
	*/
 	function __construct($table, $coll_id)
 	{
		parent::__construct();
		$this->error = '';
 		$this->connect();
 		$this->query("select min(priority) as priority from ".$table." where is_readonly = 'N' and enabled = 'Y' and coll_id = '".$coll_id."'");
 		if($this->nb_result() == 0)
		{
			$this->error = _NO_AVAILABLE_DOCSERVER.". "._MORE_INFOS.".";
		}
		else
		{
			$res = $this->fetch_object();

			$priority = $res->priority;
			$this->query("select * from ".$table." where is_readonly = 'N' and enabled = 'Y' and coll_id = '".$coll_id."' and priority = ".$priority);

			$res = $this->fetch_object();
 			$this->docserver_id = $res->docserver_id;
 			$this->is_readonly = $res->is_readonly;
 			$this->is_enabled = $res->is_enabled;
 			$this->size_limit = $res->size_limit;
 			$this->actual_size = $res->actual_size;
 			$this->path = $res->path_template;
 			$this->creation_date = $res->creation_date;
 			$this->close_date = $res->closing_date;
			$this->coll_id = $res->coll_id;
		}
 	}

	/**
	* Checks the size of the docserver plus a new file to see if there is enough disk space
	*
	* @param  $filesize integer File size
	* @return integer New docserver size or 0 if not enough disk space available
	*/
 	public function check_size($filesize)
 	{
 		$new_docserver_size = $this->actual_size + $filesize;
 		if($this->size_limit > 0 && $new_docserver_size >= $this->size_limit)
		{
			$this->error = _NOT_ENOUGH_DISK_SPACE.". "._MORE_INFOS.".";
			return 0;
		}
		else
		{
			return $new_docserver_size;
		}
 	}

	/**
	* Docserver error management
	*
	* @return string Error message
	*/
 	public function get_error()
 	{
 		return $this->error;
 	}

	/**
	* Gets the identifier of the docserver object
	*
	* @return string Docserver Identifier
	*/
 	public function get_id()
 	{
 		return $this->docserver_id;
 	}

	/**
	* Gets the path of the docserver object
	*
	* @return string Docserver path
	*/
 	public function get_path()
 	{
 		return $this->path;
 	}

	/**
	* Calculates the next file name in the docserver
	*
	* @return array Contains 2 items : subdirectory path and new filename
	*/
	public function filename()
	{
		$path_template = $this->path;
		//Scans the docserver path
		$file_tab = scandir($path_template);
		// Removes . and .. lines
		array_shift($file_tab);
		array_shift($file_tab);
		$nb_files = count($file_tab);
		// Docserver is empty
		if ($nb_files == 0 )
		{
			// Creates the directory
			if (!mkdir($path_template."1",0000700))
			{
				$this->error = _FILE_SEND_ERROR;
				$_SESSION['error'] = _FILE_SEND_ERROR.". "._TRY_AGAIN.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
			}
			else
			{
				$destination_rept = $path_template."1".DIRECTORY_SEPARATOR;
				$file_destination_name = "1";
				return array("destination_rept" => $destination_rept, "file_destination_name" => $file_destination_name);
			}
		}
		//Docserver not empty
		else
		{
			//Gets next usable subdirectory in the docserver
			$destination_rept = $path_template.count($file_tab).DIRECTORY_SEPARATOR;
			$file_tab2 = scandir($path_template.strval(count($file_tab)));
			// Removes . and .. lines
			array_shift($file_tab2);
			array_shift($file_tab2);
			$nb_files2 = count($file_tab2);
			//If number of files => 2000 then creates a new subdirectory
			if($nb_files2 >= 2000 )
			{
				$new_rept = ($nb_files) + 1;
				if (!mkdir($path_template.$new_rept,0000700))
				{
					$this->error = _FILE_SEND_ERROR;
					$_SESSION['error'] = _FILE_SEND_ERROR.". "._TRY_AGAIN.". "._MORE_INFOS." : <a href=\"mailto:".$_SESSION['config']['adminmail']."\">".$_SESSION['config']['adminname']."</a>";
				}
				else
				{
					$destination_rept = $path_template.$new_rept.DIRECTORY_SEPARATOR;
					$file_destination_name = "1";
					return array("destination_rept" => $destination_rept, "file_destination_name" => $file_destination_name);
				}
			}
			// Docserver contains less than 2000 files
			else
			{
				$new_file_name = ($nb_files2) + 1;
				$greater = $new_file_name;
				for($n=0;$n<count($file_tab2);$n++)
				{
					$current_file_name = array();
					$current_file_name = explode(".",$file_tab2[$n]);
					if((int)$greater  <= (int)$current_file_name[0])
					{
						if((int)$greater  == (int)$current_file_name[0])
						{
							$greater ++;
						}
						else // $greater < current
						{
							$greater = (int)$current_file_name[0] +1;
						}
					}
				}
				$file_destination_name = $greater ;
				return array("destination_rept" => $destination_rept, "file_destination_name" => $file_destination_name);
			}
		}
	}

	/**
	* Sets the size of the docserver
	*
	* @param  $new_size integer New size of the docserver
	* @param $table string Docserver table in the database
	*/
	public function set_size($new_size, $table)
	{
		$this->connect();
		$this->query("update ".$table." set actual_size=".$new_size." where docserver_id='".$this->docserver_id."'");
		$this->actual_size = $new_size;
	}

	/**
	* Selects a writable docserver
	*
	* @param  $id string Docserver identifier
	* @param $table  string Docserver table in the database
	*/
	public function select_docserver($id, $table)
 	{
 		$this->connect();
 		$this->query("select * from ".$table." where docserver_id = '".$id."'");
 		$res = $this->fetch_object();
 		$this->docserver_id = $id;
 		$this->is_readonly = $res->is_readonly;
 		$this->is_enabled = $res->is_enabled;
 		$this->size_limit = $res->size_limit;
 		$this->actual_size = $res->actual_size;
 		$this->path = $res->path_template;
 		$this->creation_date = $res->creation_date;
 		$this->close_date = $res->closing_date;
 		$this->error = '';
 	}


 	/**
	* Empties the docserver error variable
	*
	*/
 	public function init_error()
 	{
 		$this->error = '';
	 }
}
?>