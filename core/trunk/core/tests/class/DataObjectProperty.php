<?php 

class DataObjectProperty {
	
    private $schemaElement;
    private $parentObject;
    private $name;
    private $storage;
	
	public function DataObjectProperty($schemaElement, $name, $value=false)
	{
		$this->schemaElement = $schemaElement;
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
    
    public function getSchemaElement() 
    {
        return $this->schemaElement;
    }
    
    public function __get($name) {
        if($name === 'isDataObjectProperty') {
            return true;
        }
        if($name === 'typeName') {
            if($this->schemaElement->ref) {
                return $this->schemaElement->ref;
            } else {
                return $this->schemaElement->name;
            }
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