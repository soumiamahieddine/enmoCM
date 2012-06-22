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
    
    public function setParentObject($parentObject) 
    {
        $this->parentObject = $parentObject;
    }
    
    public function __get($name) {
        if($name === 'isDataObjectProperty') {
            return true;
        }
        if($name === 'name') {
            return $this->name;
        }
        if($name === 'schemaPath') {
            return $this->schemaPath;
        }
        if($name === 'parentObject') {
            return $this->parentObject;
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