<?php
class BaseObject {
	private $data = array(); 

	/**
	 * Initialize an object.
	 */
	function __construct(){
	}

	/**
	 * Set value of a property of current object.
	 * @param string $name Name of property to set
	 * @param object $value Value of property $name
	 */
	function __set($name, $value){
		$this->data[$name] = $value;
	}

	/**
	 * Get value of a property of current object.
	 * @param string $name Name of property to get
	 * @return string Value of $name
	 * @exception $e Exception Sent if $name does not exist
	 */
	function __get($name) {
		try {
			return $this->data[$name];
		} catch (Exception $e) {
			echo 'Exception catched: '.$e->getMessage().', null returned<br/>';
			return null;
		}
	}

	
	public function __isset($name) {
	        if (isset($this->data[$name])) {
	            return (false === empty($this->data[$name]));
	        } else {
	            return false;
	        }
	}
	
	public function getArray() {
		return $this->data;
	}
	
	public function setArray($array) {
		$this->data = $array;
	}
	
	//abstract function toString();
}
?>
