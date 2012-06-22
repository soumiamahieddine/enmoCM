<?php 

class DataObjectProperty 
{
	
    private $name;
    private $label;
    private $comment;
    private $schemaPath;
    private $parentObject;
    private $storage;
	
	public function DataObjectProperty($name, $schemaPath, $value=false, $label=false, $comment=false)
	{
		$this->schemaPath = $schemaPath;
        $this->name = $name;
        $this->storage = $value;
        $this->label = $label;
        $this->comment = $comment;
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
        if($name === 'value') {
            return $this->storage;
        }   
        if($name === 'label') {
            if($this->label) {
                return $this->label;
            } else {
                return $this->name;
            }
        }
        if($name === 'comment') {
            return $this->comment;
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