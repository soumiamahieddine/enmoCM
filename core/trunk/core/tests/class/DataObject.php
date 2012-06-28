<?php

class DataObject 
    implements IteratorAggregate
{
    
    private $name;
    private $schemaPath;
    private $parentObject;
    private $changeLog;
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
    
    //*************************************************************************
    // XML INTERFACE
    //*************************************************************************
    public function asXmlDocument() 
    {
        $Document = new DOMDocument();
        $rootElement = $this->asXmlElement($Document);
        $Document->appendChild($rootElement);
        return $Document;
    }
    
    public function getXmlString()
    {
        $XmlDocument = $this->asXmlDocument();
        $XmlString = $XmlDocument->saveXML();
        $XmlPrettyString = $this->formatXmlString($XmlString);
        return $XmlPrettyString;
    }
    
    public function asXmlElement($XmlDocument) 
    {
        $objectElement = $XmlDocument->createElement($this->name);
        foreach($this as $childDataObject) {
            if($childDataObject->isDataObjectArray) {
                //echo "<br/><b>Adding array of $childDataObject->name</b>";
                $childElements = $childDataObject->asXmlNodeList($XmlDocument);
                while($childElement = $childElements->item(0)) {
                    $childElement = $childElements->item($i);
                    $objectElement->appendChild($childElement);
                }
            } else {
                $childElement = $childDataObject->asXmlElement($XmlDocument);
                $objectElement->appendChild($childElement);
            }
        }
        return $objectElement;
    }
    
    private function formatXmlString($xml) 
    {  
        // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);
        
        // now indent the tags
        $token      = strtok($xml, "\n");
        $result     = ''; // holds formatted version as it is built
        $pad        = 0; // initial indent
        $matches    = array(); // returns from preg_matches()
        
        // scan each line and adjust indent based on opening/closing tags
        while ($token !== false) {
            // 1. open and closing tags on same line - no change
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) { 
                $indent = 0;
            // 2. closing tag - outdent now
            } elseif (preg_match('/^<\/\w/', $token, $matches)) {
                $indent = 0;
                $pad--;
            // 3. opening tag - don't pad this one, only subsequent tags
            } elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) {
                $indent = 1;
            // 4. no indentation needed
            } else {
                $indent = 0; 
            }
            
            // pad the line with the required number of leading spaces
            $line    = str_pad($token, strlen($token)+($pad*4), ' ', STR_PAD_LEFT);
            $result .= $line . "\n"; // add to the cumulative result, with linefeed
            $token   = strtok("\n"); // get the next token
            $pad    += $indent; // update the pad size for subsequent lines    
        } 
        
        return $result;
    }
    
    //*************************************************************************
    // Web Service Object (properties/children - no method)
    //*************************************************************************
    public function asObject()
    {
        $object = new StdClass();
        foreach($this as $childDataObject) {
            $childName = $childDataObject->name;
            $childObject = $childDataObject->asObject();
            $object->$childName = $childObject;
        }
        
        return $object;
    }
    
    
}