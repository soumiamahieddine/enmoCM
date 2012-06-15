<?php

class DataObject
{
	
    private $typeName;
    private $parentObject;
    
    public function DataObject($typeName) 
    {
        $this->typeName = $typeName;
    }
    
    public function getTypeName() 
    {
        return $this->typeName;
    }
    
    public function getParentObject() 
    {
        return $this->parentObject;
    }
    
    public function setParentObject($parentObject) 
    {
        $this->parentObject = $parentObject;
    }
    
    public function __set($name, $value) {
        if(is_object($value) 
            && (get_class($value) == 'DataObject' || get_class($value) == 'ArrayDataObject')) {
            $value->setParentObject($this);
        }
        $this->$name = $value;
    }
}