<?php

class DataObject
{
	
    private $typeName;
    
    public function DataObject($typeName) 
    {
        $this->typeName = $typeName;
    }
    
    public function getTypeName() 
    {
        return $this->typeName;
    }
    

}