<?php

class DataObject
{
	
    private $parentObject;
    private $typeName;
    
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