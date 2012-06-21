<?php

class DataObjectArray extends ArrayObject 
{
    private $name;
    private $schemaPath;
    private $parentObject;
   
    public function DataObjectArray($name, $schemaPath) 
    {
        $this->name = $name;
        $this->schemaPath = $schemaPath;
        $this->setFlags(ArrayObject::ARRAY_AS_PROPS);
        $this->setFlags(ArrayObject::STD_PROP_LIST);
    }
    
    public function getSchemaPath() 
    {
        return $this->schemaPath;
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
        if($name === 'isDataObjectArray') {
            return true;
        }
        if($name === 'name') {
            return $this->name;
        }
    }
    
    public function getChildren() 
    {
        $return = array();
        for($i=0; $i<count($this->storage); $i++) {
            $child = $this->storage[$i];
            if(is_object($child) && ($child->isDataObject || $child->isDataObjectArray)) {
                $return[] = $this->storage[$i];
            }
        }
        return $return;
    }
    
    // Interface to ArrayAccess
    //*************************************************************************
    /*public function offsetSet($offset, $value) {
        $value->setParentObject($this->parentObject);
        echo "<br/>Adding array data object item to offset #$offset";
        print_r($this);
        if (is_null($offset)) {
            $this->storage[] = $value;
        } else {
            $this->storage[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->storage[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->storage[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->storage[$offset]) ? $this->storage[$offset] : null;
    }*/
    
}