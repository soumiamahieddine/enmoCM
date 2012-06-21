<?php

class DataObject implements IteratorAggregate
{
    
    private $name;
    private $schemaPath;
    private $DasSource;
    private $parentObject;
    private $storage;
    
    
    public function DataObject($name, $schemaPath) 
    {
        $this->name = $name;
        $this->schemaPath = $schemaPath;
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
    
    public function getIterator() 
    {
        return new ArrayIterator($this->storage);
    }
    
    public function __set($name, $value) 
    {
        //echo "<br/>Assign value to $name";
        if(is_object($value)) { 
            if(get_class($value) == 'DataObject' 
                || get_class($value) == 'DataObjectArray'
                || get_class($value) == 'DataObjectProperty') {
                //echo "<br/>Adding child object as $name = " . get_class($value);
                $value->setParentObject($this);
                $this->storage[$name] = $value;
            } else {
                Die("<br/><b>Permission denied</b>");
            }
        } elseif(is_scalar($value) || !$value) {
            //echo "<br/>Adding scalar $name = $value";
            $this->storage[$name]->setValue($value);
        }
    }
    
    public function __get($name) 
    {
        if(isset($this->storage[$name])) {
            return $this->storage[$name];
        }
        if($name === 'isDataObject') {
            return true;
        }
        if($name === 'name') {
            return $this->name;
        }
    }
    
    public function __isset($name)
    {
        if(isset($this->storage[$name])) {
            return true;
        }
    }
  
    public function getProperties() 
    {
        $return = array();
        if(count($this->storage) > 0) {
            foreach($this->storage as $child) {
                if(is_object($child) && $child->isDataObjectProperty) {
                    $return[] = $child;
                }
            }
        }
        return $return;
    }
    
    public function getChildren() 
    {
        $return = array();
        if(count($this->storage) > 0) {
            foreach($this->storage as $child) {
                if(is_object($child) && ($child->isDataObject || $child->isDataObjectArray)) {
                    $return[] = $child;
                }
            }
        }
        return $return;
    }
    
    public function asXmlDocument() 
    {
        $Document = new DOMDocument();
        $this->objectToXml($this, $Document, $Document); 
        
        return $Document;
    }
    
    public function asXmlString()
    {
        $XmlDocument = $this->asXmlDocument();
        $XmlString = $XmlDocument->saveXML();
        $XmlPrettyString = $this->formatXmlString($XmlString);
        return $XmlPrettyString;
    }
    
    private function childrenToXml($dataObject, $parentXml, $Document) 
    {
        //echo "<br/><b>Adding ".count($dataObject)." children elements to $parentName</b>";
        foreach($dataObject as $childObject) {
            if(!is_object($childObject)) Die("Non object value are forbidden");
            if($childObject->isDataObjectProperty) {
                //echo "<br/>Adding property element $childName => $childObject";
                $this->propertyToXml($childObject, $parentXml, $Document);
            } elseif($childObject->isDataObject) {
                //echo "<br/><b>Adding child object $childName</b>";
                $this->objectToXml($childObject, $parentXml, $Document);
            } elseif($childObject->isDataObjectArray) {
                //echo "<br/><b>Adding array of $childName</b>";
                $this->arrayToXml($childObject, $parentXml, $Document);
            }
        }
    }
    
    private function objectToXml($dataObject, $parentXml, $Document) 
    {
        $objectXml = $Document->createElement($dataObject->name);
        $parentXml->appendChild($objectXml);
        $this->childrenToXml($dataObject, $objectXml, $Document);
    }
    
    private function arrayToXml($dataObjectArray, $parentXml, $Document)
    {
        for($i=0; $i<count($dataObjectArray); $i++) {
            $childObject = $dataObjectArray[$i];
            //echo "<br/>Adding array item #$i";
            $this->objectToXml($childObject, $parentXml, $Document);
        }
    }
    
    private function propertyToXml($dataObjectProperty, $parentXml, $Document) 
    {
        if(strlen($dataObjectProperty->value) > 0) {
            $propertyXml = $Document->createElement($dataObjectProperty->name, $dataObjectProperty->value);
        } else {
            $propertyXml = $Document->createElement($dataObjectProperty->name);
        }
        $parentXml->appendChild($propertyXml);
    }
    
    private function formatXmlString($xml) {  
      
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

    
}