<?php

class DataObject 
    extends DOMElement
    implements IteratorAggregate, ArrayAccess
{
    
    public $changeLog = array();
    
    private function xpath($query) 
    {
        $xpath = new DOMXpath($this->ownerDocument);
        return $xpath->query($query, $this);
    }
    
    //*************************************************************************
    // MAGIC METHODS
    //*************************************************************************
    public function __set($name, $value) 
    {
        if(is_scalar($value) || !$value || is_null($value)) {
            $resultNodes = $this->xpath('./' . $name);
            switch ((string)$resultNodes->length) {
            case '0' :
                $resultNode = $this->ownerDocument->createElement($name, $value);
                $this->appendChild($resultNode);
                break;
            case '1' :
                $resultNode = $resultNodes->item(0);
                if((string)$resultNode->nodeValue == $value) {
                    return;
                }
                if(isset($this->changeLog) && $this->changeLog->active) {
                    $this->changeLog->logChange(DataObjectChange::UPDATE, $name, (string)$resultNode->nodeValue, $value);
                }
                $resultNode->nodeValue = $value;
                break;
            }
        }
    }
    
    public function __get($name) 
    {
        switch($name) {
        case 'childrenObjects':
            $resultNodes = $this->xpath('./*');
            $dataObjectArray = array();
            for($i=0; $i<$resultNodes->length; $i++) {
                $dataObject = $resultNodes->item($i);
                $dataObjectArray[] = $dataObject;
            }
            return $dataObjectArray;
            
        default:
            $resultNodes = $this->xpath('./'.$name);
            switch ((string)$resultNodes->length) {
            case '0' :
                return false;
            case '1' :
                return (string)$resultNodes->item(0)->nodeValue;
            default :
                $dataObjectArray = array();
                for($i=0; $i<$resultNodes->length; $i++) {
                    $dataObjectArray[] = $resultNodes->item($i);
                }
                return $dataObjectArray;
            }
        }
    }
    
    public function __isset($name)
    {
        $resultNodes = $this->xpath('./'.$name);
        if($resultNodes->length > 0) return true;
    }
    
    public function __toString()
    {
        return $this->nodeValue;
    }
    
    //*************************************************************************
    // ITERATOR METHODS
    //*************************************************************************
    public function getIterator() {
        $childNodes = $this->childNodes;
        $childArray = array();
        for($i=0; $i<$childNodes->length; $i++) {
            $childNode = $childNodes->item($i);
            $childName = $childNode->tagName;
            $childValue = $childNode->nodeValue;
            $childArray[$childName] = $childValue;
        } 
        return new ArrayIterator($childArray);
    }
    
    //*************************************************************************
    // ARRAYACCESS METHODS
    //*************************************************************************
    public function offsetSet($offset, $value) 
    {
        $this->appendChild($value);
    }
    
    public function offsetExists($offset) 
    {
        
    }
    
    public function offsetUnset($offset) 
    {

    }
    
    public function offsetGet($offset) 
    {
    
    }
   
    //*************************************************************************
    // CHANGELOG
    //*************************************************************************
    public function logCreate()
    {
        $this->changeLog = new DataObjectChangeLog();
        $this->changeLog->logCreation($this->tagName);
    }
    
    public function logRead() 
    {
        $this->changeLog = new DataObjectChangeLog();
        $this->changeLog->logRead($this->tagName);
    }
    
    //*************************************************************************
    // XML INTERFACE
    //*************************************************************************
    public function asXmlString() 
    {  
        // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $this->ownerDocument->documentElement->C14N());
        
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
        for($i=0; $i<$this->childNodes->length;$i++) {
            $childNode = $this->childNodes->item($i);
            // Property
            if($childNode->nodeType == XML_TEXT_NODE) {
                return $childNode->asObject();
            // Array
            } else {
                $childName = $childNode->tagName;
                if($this->xpath('./'.$childName)->length > 1) {
                    echo "<br/>$childName is an array";
                    $object->{$childName}[] = $childNode->asObject();
                } else {
                    echo "<br/>$childName is an extension";
                    $object->$childName = $childNode->asObject();
                }
            }
        }
        
        return $object;
    }
    
    
}