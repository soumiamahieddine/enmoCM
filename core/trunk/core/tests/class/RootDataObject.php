<?php

class RootDataObject 
{
    private $typeName;
    private $controllerObject;
    
    public function RootDataObject($typeName, $controllerObject) 
    {
        $this->typeName = $typeName;
        $this->controllerObject = $controllerObject;

    }
    
    public function getTypeName() 
    {
        return $this->typeName;
    }

}