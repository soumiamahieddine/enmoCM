<?php

class ArrayDataObject extends ArrayObject 
{
    
    public function ArrayDataObject() 
    {
        $this->setFlags(ArrayObject::ARRAY_AS_PROPS);
        $this->setFlags(ArrayObject::STD_PROP_LIST);
    }

}