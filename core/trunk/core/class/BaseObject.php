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
* @brief  Contains the BaseObject object (Object used as a base for more advanced object as User, Usergroup, ...)
* 
* 
* @file
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup core
*/


/**
* @brief BaseObject Object
*
* @ingroup core
*/
class BaseObject 
{
	/**
	* Array of all the object properties (key => value)
    */
	private $data = array(); 

	/**
	 * Initializes an object
	 */
	function __construct(){
	}

	/**
	 * Sets value of a property of current object
	 * 
	 * @param string $name Name of property to set
	 * @param object $value Value of property $name
	 */
	function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	/**
	 * Gets value of a property of current object
	 * 
	 * @param string $name Name of property to get
	 * @return string Value of $name  or null
	 * @exception $e Exception Sent if $name does not exist
	 */
	function __get($name) 
	{
		try {
			return $this->data[$name];
		} catch (Exception $e) {
			echo 'Exception catched: '.$e->getMessage().', null returned<br/>';
			return null;
		}
	}

	/**
	 * Checks if a given property is set in the current object
	 * 
	 * @param string $name Name of property to check
	 * @return Bool
	 */
	public function __isset($name)
	{
		if (isset($this->data[$name])) 
			return (false === empty($this->data[$name]));
		 else 
			return false;
	        
	}
	
	/**
	 * Gets values of all properties of the current object in an array
	 * 
	 * @return Array properties ( key=>value)
	 */
	public function getArray() 
	{
		return $this->data;
	}
	
	/**
	 * Sets values of all properties of the current object 
	 * 
	 * @param Array $array Array of the properties to set
	 */
	public function setArray($array) 
	{
		$this->data = $array;
	}
	
	//abstract function toString();
}
?>
