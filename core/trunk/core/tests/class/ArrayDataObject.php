<?php

class ArrayDataObject extends ArrayObject 
{
    private $schemaElement;
    private $parentObject;
   
    public function ArrayDataObject($schemaElement) 
    {
        $this->schemaElement = $schemaElement;
        $this->setFlags(ArrayObject::ARRAY_AS_PROPS);
        $this->setFlags(ArrayObject::STD_PROP_LIST);
    }
    
    public function getTypeName() 
    {
        if($this->schemaElement->ref) {
            return $this->schemaElement->ref;
        } else {
            return $this->schemaElement->name;
        }
    }
    
    public function getSchemaElement() 
    {
        return $this->schemaElement;
    }
    
    public function getParentObject() 
    {
        return $this->parentObject;
    }
    
    public function setParentObject($parentObject) 
    {
        $this->parentObject = $parentObject;
    }
    
    public function append($childObject) 
    {
        $this->offsetSet(null, $childObject);
        $childObject->setParentObject($this->parentObject);
    }
    
    public function __get($name) {
        if($name === 'isArrayDataObject') {
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
    
    public function getChildren() 
    {
        $return = array();
        for($i=0; $i<count($this->storage); $i++) {
            $child = $this->storage[$i];
            if(is_object($child) && ($child->isDataObject || $child->isArrayDataObject)) {
                $return[] = $this->storage[$i];
            }
        }
        return $return;
    }
    
}