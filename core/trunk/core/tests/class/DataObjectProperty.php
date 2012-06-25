<?php 

class DataObjectProperty 
{
	private $name;
    private $schemaPath;
    private $parentObject;
    private $storage;
	
	public function DataObjectProperty($name, $schemaPath, $value=false)
	{
		$this->name = $name;
        $this->schemaPath = $schemaPath;
        $this->storage = $value;
	}
    
    public function __get($name) {
        switch($name) {
        case 'isDataObjectProperty' : return true;
        case 'name'                 : return $this->name;
        case 'schemaPath'           : return $this->schemaPath;
        case 'parentObject'         : return $this->parentObject;
        }
    }
    
    public function __set($name, $value) 
    {
        switch($name) {
        case 'parentObject'    :
            $this->parentObject = $value;
            break;
        }
    }
    
    public function setValue($value) 
    {      
        $this->storage = $value;        
    }
    
    public function clear()
    {
        $this->storage = null;
    }
    
    public function __toString() 
    {
        return (string)$this->storage;
    }
    
}