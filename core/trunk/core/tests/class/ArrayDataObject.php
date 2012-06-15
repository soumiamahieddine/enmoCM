<?php

class ArrayDataObject extends ArrayObject 
{
    
    private $parentObject;
    
    public function ArrayDataObject($parentObject=false) 
    {
        $this->parentObject = $parentObject;
        $this->setFlags(ArrayObject::ARRAY_AS_PROPS);
        $this->setFlags(ArrayObject::STD_PROP_LIST);
    }
    
    public function getParentObject() 
    {
        return $this->parentObject;
    }

}