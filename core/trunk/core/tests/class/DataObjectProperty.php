<?php 

class DataObjectProperty {
	
    private $name;
    private $schemaPath;
    private $parentObject;
    private $storage;
	
	public function DataObjectProperty($name, $schemaPath, $value=false)
	{
		$this->schemaPath = $schemaPath;
        $this->name = $name;
        $this->storage = $value;
	}
	
    public function getParentObject() 
    {
        return $this->parentObject;
    }
    
    public function setParentObject($parentObject) 
    {
        $this->parentObject = $parentObject;
    }
    
    public function getSchemaPath() 
    {
        return $this->schemaPath;
    }
    
    public function __get($name) {
        if($name === 'isDataObjectProperty') {
            return true;
        }
        if($name === 'name') {
            return $this->name;
        }
    }
    
    
    public function setValue($value) 
    {
        $this->storage = $value;
    }
    
    public function __toString() 
    {
        return (string)$this->storage;
    }
    
}