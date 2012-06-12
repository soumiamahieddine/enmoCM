<?php

class ArrayDataObject extends ArrayObject 
{
    private $parentObject;
    private $typeName;
    
    public function ArrayDataObject($typeName, $parentObject=false) 
    {
        $this->typeName = $typeName;
        $this->parentObject = $parentObject;
        $this->setFlags(ArrayObject::ARRAY_AS_PROPS);
        $this->setFlags(ArrayObject::STD_PROP_LIST);
    }
    
    public function getParentObject() 
    {
        return $this->parentObject;
    }

}