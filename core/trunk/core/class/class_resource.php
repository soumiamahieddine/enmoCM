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
* @brief   Contains all the function to manage the resources
*
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/

/**
* @brief   Contains all the function to manage the resources
*
* <ul>
*<li>Standardized methods to insert, update and delete a resource</li>
*</ul>
* @ingroup core
*/
 class resource extends request
{

	/**
	* Resource identifier
	* Integer
	*/
	private $res_id;


	/**
	* Type identifier of the resource
	* String
	*/
	private $type_id;

	/**
	* Person who inserts the resource in the application
	* String
	*/
	private $typist;

	/**
	* File format of the resource
	* String
	*/
	private $format;

	/**
	* Docserver identifier of the resource
	* String
	*/
	private $docserver_id;

	/**
	* Path of the resource in the docserver
	* String
	*/
	private $path;

	/**
	* Fingerprint of the resource
	* String
	*/
	private $fingerprint;

	/**
	* File name of the resource
	* String
	*/
	private $filename;

	/**
	* File Size of the resource
	* Integer
	*/
	private $filesize;

	/**
	* Offset
	* Integer
	*/
	private $offset;

	/**
	* Logical address
	* Integer
	*/
	private $log_adr;

	/**
	* Status of the resource
	* String
	*/
	private $status;

	/**
	* Error message
	* String
	*/
	private $error;

	/**
	* Inserts the Resource Object data into the data base
	*
 	* @param  $table_res string Resource table where to insert
	* @param  $path  string Resource path in the docserver
	* @param  $filename string Resource file name
	* @param  $docserver_path  string Docserver path
	* @param  $docserver_id  string Docserver identifier
	* @param  $data  array Data array
	* @param  $databasetype string Type of the db (MYSQL, SQLSERVER, ...)
	*/
	function load_into_db( $table_res, $path, $filename, $docserver_path, $docserver_id, $data, $databasetype)
	{
		$filetmp = $docserver_path;
		$tmp = $path;
		$tmp = str_replace('#',DIRECTORY_SEPARATOR,$tmp);
		$filetmp .= $tmp;
		$filetmp .= $filename;
		$md5 = md5_file($filetmp);
		$filesize = filesize($filetmp);


		array_push($data, array('column' => "fingerprint", 'value' => $md5, 'type' => "string"));
		array_push($data, array('column' => "filesize", 'value' => $filesize, 'type' => "int"));
		array_push($data, array('column' => "path", 'value' => $path, 'type' => "string"));
		array_push($data, array('column' => "filename", 'value' => $filename, 'type' => "string"));
		array_push($data, array('column' => 'creation_date', 'value' => $this->current_datetime(), 'type' => "function"));

		if(!$this->check_basic_fields($data))
		{
			$_SESSION['error'] = $this->error;
			return false;
		}
		else
		{
			if(!$this->insert($table_res, $data, $_SESSION['config']['databasetype']))
			{
				$this->error = _INDEXING_INSERT_ERROR."<br/>".$this->show();
				return false;
			}
			else
			{
				$this->connect();
				$this->query("select res_id from ".$table_res." where docserver_id = '".$docserver_id."' and path = '".$path."' and filename= '".$filename."' and typist ='".$_SESSION['user']['UserId']."' order by res_id desc ");
				$res = $this->fetch_object();
				return $res->res_id;
			}
		}
	}

	/**
	* Gets the resource identifier
	*
	* @return integer Resource identifier (res_id)
	*/
	public function get_id()
 	{
 		return $this->res_id;
 	}

	/**
	* Gets the error message of the resource object
	*
	* @return string Error message of the resource object
	*/
	public function get_error()
 	{
 		return $this->error;
 	}

	/**
	* Checks the mininum fields required for an insert into the database
	*
	* @param  $data array Array of the fields to insert into the database
	* @return bool True if all the fields are ok, False otherwise
         */
	private function check_basic_fields($data)
	{
		$error = '';
		$this->connect();
		$find_format = false;
		$find_typist = false;
		$find_creation_date = false;
		$find_docserver_id = false;
		$find_path = false;
		$find_filename = false;
		$find_offset = false;
		$find_logical_adr = false;
		$find_fingerprint = false;
		$find_filesize = false;
		$find_status = false;
		for($i=0; $i < count($data);$i++)
		{
			if($data[$i]['column'] == 'format')
			{
				$find_format = true;
				// must be tested in the file_index.php file (module = indexing_searching)
			}
			elseif($data[$i]['column'] == 'typist' )
			{
				$find_typist = true;
/*
				if( $data[$i]['value'] <> $_SESSION['user']['UserId'])
				{
					$error .= _TYPIST_ERROR.'<br/>';
				}
*/
			}
			elseif($data[$i]['column'] == 'creation_date')
			{
				$find_creation_date = true;
				if($data[$i]['value'] <> $this->current_datetime())
				{
					$error .= _CREATION_DATE_ERROR.'<br/>';
				}
			}
			elseif($data[$i]['column'] == 'docserver_id')
			{
				$find_docserver_id =  true;
				if(!$this->query("select docserver_id from ".$_SESSION['tablename']['docservers']." where docserver_id = '".$data[$i]['value']."'", true))
				{
					$error .= _DOCSERVER_ID_ERROR.'<br/>';
				}
			}
			elseif($data[$i]['column'] == 'path' )
			{
				$find_path = true;
				if( empty($data[$i]['value']))
				{
					$error .= _PATH_ERROR.'<br/>';
				}
			}
			elseif($data[$i]['column'] == 'filename' )
			{
				$find_filename = true;
				if(!preg_match("/^[0-9]+.([a-zA-Z][a-zA-Z][a-zA-Z][a-zA-Z]?|maarch)$/", $data[$i]['value']))
				{
					$error .= _FILENAME_ERROR.'<br/>';
				}
			}
			elseif($data[$i]['column'] == "offset_doc")
			{
				$find_offset = true;
			}
			elseif($data[$i]['column'] == 'logical_adr')
			{
				$find_logical_adr = true;
			}
			elseif($data[$i]['column'] == 'fingerprint'  )
			{
				$find_fingerprint  = true;
				if(!preg_match("/^[0-9A-Fa-f]+$/", $data[$i]['value']))
				{
					$error .= _FINGERPRINT_ERROR.'<br/>';
				}
			}
			elseif($data[$i]['column'] == 'filesize'  )
			{
				$find_filesize = true;
				if( $data[$i]['value'] <= 0)
				{
					$error .= _FILESIZE_ERROR.'<br/>';
				}
			}
			elseif($data[$i]['column'] == 'status' )
			{
				$find_status = true;
				if( !preg_match("/^[A-Z][A-Z][A-Z][A-Z]*$/", $data[$i]['value']))
				{
					$error .= _STATUS_ERROR.'<br/>';
				}
			}
		}

		if($find_format == false)
		{
			$error .= _MISSING_FORMAT.'<br/>';
		}
		if($find_typist == false)
		{
			$error .= _MISSING_TYPIST.'<br/>';
		}
		if($find_creation_date == false)
		{
			$error .= _MISSING_CREATION_DATE.'<br/>';
		}
		if($find_docserver_id == false)
		{
			$error .= _MISSING_DOCSERVER_ID.'<br/>';
		}
		if($find_path == false)
		{
			$error .= _MISSING_PATH.'<br/>';
		}
		if($find_filename == false)
		{
			$error .= _MISSING_FILENAME.'<br/>';
		}
		if($find_offset == false)
		{
			$error .= _MISSING_OFFSET.'<br/>';
		}
		if($find_logical_adr == false)
		{
			$error .= _MISSING_LOGICAL_ADR.'<br/>';
		}
		if($find_fingerprint == false)
		{
			$error .= _MISSING_FINGERPRINT.'<br/>';
		}
		if($find_filesize == false)
		{
			$error .= _MISSING_FILESIZE.'<br/>';
		}
		if($find_status == false)
		{
			$error .= _MISSING_STATUS.'<br/>';
		}

		$this->error = $error;
		if(!empty($error))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
?>
