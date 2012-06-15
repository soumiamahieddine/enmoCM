<?php

class DataObject
{
	
    private $typeName;
    private $parentObject;
    
    public function DataObject($typeName, $parentObject=false) 
    {
        $this->typeName = $typeName;
        $this->parentObject = $parentObject;
    }
    
    public function getTypeName() 
    {
        return $this->typeName;
    }
    
    public function getParentObject() 
    {
        return $this->parentObject;
    }
    
}