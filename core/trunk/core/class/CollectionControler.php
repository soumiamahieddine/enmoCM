<?php
/*
*    Copyright 2008,2009,2010 Maarch
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
* @brief  Contains the controler of the Collection Object 
* 
* 
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/


// To activate de debug mode of the class
$_ENV['DEBUG'] = false;
/*
define("_CODE_SEPARATOR","/");
define("_CODE_INCREMENT",1);
*/

// Loads the required class
try {
	require_once("core/class/Collection.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}

/**
* @brief  Controler of the Collection Object 
*
* @ingroup core
*/
class CollectionControler
{
	public function getAll($path_xml, $path_lang = '')
	{
		$xmlconfig = simplexml_load_file($path_xml);
		if ($xmlconfig <> false) {
			$CONFIG = $xmlconfig->CONFIG;
			$collections = array();
			foreach ($xmlconfig->COLLECTION as $col) {
				$tmp = (string) $col->label;
				if (!empty($tmp) && defined($tmp) 
	            	&& constant($tmp) <> NULL
	            ) {
	           		$labelVal = constant($tmp);
				}
				$extensions = $col->extensions;
				$tab = array();
				foreach ($extensions->table as $table) {
					array_push($tab, (string) $table);
				}
				$collections[$col->id] = array(
					"label" => (string) $tmp,
					"view" => (string) $col->view, 
					"index_file" => (string) $col->index_file, 
					"script_add" => (string) $col->script_add, 
					"script_search" => (string) $col->script_search, 
					"script_search_result" => (string) $col->script_search_result, 
					"script_details" => (string) $col->script_details, 
					"path_to_lucene_index" => (string) $col->path_to_lucene_index, 
					"extensions" => $tab
				);
				
				if (isset($col->table) && !empty($col->table)) {
					$collections[$col->id]["table"] = (string) $col->table;
				}
			}
			return $collections;
		}
		return false;			
	}
}
