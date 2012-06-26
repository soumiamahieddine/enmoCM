<?php

class DataObject 
    implements IteratorAggregate
{
    
    private $name;
    private $schemaPath;
    private $parentObject;
    private $changeLog;
    private $validationLog;
    private $storage;
    
    public function DataObject($name, $schemaPath) 
    {
        $this->name = $name;
        $this->schemaPath = $schemaPath;
    }
    
    public function getIterator() 
    {
        return new ArrayIterator($this->storage);
    }
    
    public function __set($name, $value) 
    {
        switch($name) {
        case 'parentObject'    :
            $this->parentObject = $value;
            break;
        case 'logChanges'    :
            $this->logChanges = $value;
            break;
       
        default:    
            // Add child object
            if(is_object($value)) { 
                if(get_class($value) == 'DataObject' 
                    || get_class($value) == 'DataObjectArray'
                    || get_class($value) == 'DataObjectProperty') {
                    $value->parentObject = $this;
                    $this->storage[$name] = $value;
                } else {
                    Die("<br/><b>Permission denied</b>");
                }
                return;
            } 
            // Set property value
            if(is_scalar($value) || !$value || is_null($value)) {
                if((string)$this->storage[$name] == $value) return;
                if(isset($this->changeLog) && $this->changeLog->active) {
                    $this->changeLog->logChange(DataObjectChange::UPDATE, $name, (string)$this->storage[$name], $value);
                }
                $this->storage[$name]->setValue($value);
            }
        }
    }
    
    public function __get($name) 
    {
        switch($name) {
        case 'isDataObject'     : return true;
        case 'name'             : return $this->name;
        case 'schemaPath'       : return $this->schemaPath;
        case 'parentObject'     : return $this->parentObject;
        case 'properties'       :
            if(count($this->storage) == 0) return array();
            foreach($this->storage as $childObject) {
                if(is_object($childObject) 
                    && $childObject->isDataObjectProperty) {
                    $properties[] = $childObject;
                }
            }
            return $properties;
        case 'children'         :
            if(count($this->storage) == 0) return array();
            foreach($this->storage as $childObject) {
                if(is_object($childObject) 
                    && ($childObject->isDataObject 
                        || $childObject->isDataObjectArray)) {
                    $children[] = $childObject;
                }
            }
            return $children;
        case 'isCreated'        :
            if(isset($this->changeLog)
                && $this->changeLog->creation) {
                return true;
            } 
            break;
            
        case 'isUpdated'        :
            if(isset($this->changeLog)
                && count($this->updates) > 0) {
                return true;
            } 
            break;
        case 'updates'          :
            if(isset($this->changeLog)) {
                return $this->changeLog->updates;
            }
            break;
        default:
            if(isset($this->storage[$name])) {
                return $this->storage[$name];
            }
        }

    }
    
    public function __isset($name)
    {
        if(isset($this->storage[$name])) {
            return true;
        }
    }
    
    public function clear()
    {
        $properties = $this->properties;
        for($i=0; $i<count($properties); $i++) {
            $property = $properties[$i];
            $property->clearValue();
        }
        $children = $this->children;
        for($i=0; $i<count($children); $i++) {
            $childObject = $children[$i];
            $childObject->clear();
        }
   
    }

    //*************************************************************************
    // CHANGELOG
    //*************************************************************************
    public function beginLogging()
    {
        $this->changeLog = new DataObjectChangeLog();
    }
    
    public function logCreation()
    {
        $this->changeLog->logCreation($this->name);
    }
    
    public function logRead() 
    {
        $this->changeLog->logRead($this->name);
    }
    
    
}