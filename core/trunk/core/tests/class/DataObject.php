<?php

class DataObject 
    extends DOMElement
    implements IteratorAggregate, ArrayAccess
{
    
    private $xpath;
    
    //*************************************************************************
    // DOM METHODS
    //*************************************************************************
    private function xpath($query) 
    {
        if(!$this->xpath) $this->xpath = new DOMXpath($this->ownerDocument);
        return $this->xpath->query($query, $this);
    }
    
    //*************************************************************************
    // MAGIC METHODS
    //*************************************************************************
    public function __set($name, $value) 
    {
        if(is_scalar($value) || !$value || is_null($value)) {
            // Property
            $this->setAttribute($name, $value); 
            $this->logUpdate($name, $value);
        } 
        if(is_object($value) && get_class($value) == 'DataObject') { 
            // Child Element
            $this->appendChild($value);
            return;
        }
    }
    
    public function __get($name) 
    {
        // Property
        $propertyNodes = $this->xpath('./@'.$name);
        if($propertyNodes->length > 0) {
            return $propertyNodes->item(0)->nodeValue;
        }
        // Child
        $childNodes = $this->xpath('./'.$name);
        $childrenLength = $childNodes->length;
        for($i=0; $i<$childrenLength; $i++) {
            $childOjects[] = $childNodes->item($i);
        }
        return $childOjects;
    }
    
    public function __isset($name)
    {
        $resultNodes = $this->xpath('./@'.$name . ' | ./' . $name);
        if($resultNodes->length > 0) return true;
    }
    
    public function __toString()
    {
        return (string)$this->textContent;
    }
    
    //*************************************************************************
    // ITERATOR METHODS
    //*************************************************************************
    public function getIterator() {
        $returnArray = $this->asArray('*');
        return new ArrayIterator($returnArray);
    }
    
    private function asArray($name)
    {
        $returnArray = array();
        $attrNodes = $this->xpath('./@'. $name);
        for($i=0; $i<$attrNodes->length; $i++) {
            $attrNode = $attrNodes->item($i);
            $childName = $attrNode->name;
            $childValue = $attrNode->value;
            $returnArray[$childName] = $childValue;
        } 
        $childNodes = $this->xpath('./'. $name);
        for($i=0; $i<$childNodes->length; $i++) {
            $childNode = $childNodes->item($i);
            $childName = $childNode->tagName;
            $childValue = $childNode->nodeValue;
            $returnArray[$i] = $childValue;
        } 
        return $returnArray;
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
    // MESSAGES INTERFACE
    //*************************************************************************  
    public function logCreate()
    {
        $message = $this->ownerDocument->createDataObjectLog(DataObjectLog::CREATE, DataObjectLog::INFO);
        $this->appendChild($message);
    }    
    
    public function logRead()
    {
        $message = $this->ownerDocument->createDataObjectLog(DataObjectLog::READ, DataObjectLog::INFO);
        $this->appendChild($message);
    }
    
    public function logUpdate($propertyName, $propertyValue)
    {
        $messageDetail = 'property-name="' . $propertyName . '" property-value="' . $propertyValue . '"';
        $message = $this->ownerDocument->createDataObjectLog(DataObjectLog::UPDATE, DataObjectLog::INFO, $messageDetail);
        $this->appendChild($message);
    }
    
    public function logValidate($level, $message)
    {
        $messageDetail = 'validation-message="' . $message . '"';
        $message = $this->ownerDocument->createDataObjectLog(DataObjectLog::VALIDATE, $level, $messageDetail);
        $this->appendChild($message);
    }
    
    public function isCreated()
    {
        $createOperation = $this->xpath("./comment()[contains(., 'operation=\"1\"')]")->item(0);
        if($createOperation) return true;
    }
    
    public function isUpdated()
    {
        $updateOperations = $this->xpath('./comment()[contains(., "operation=3")]');
        if($updateOperations->length > 0) return true;
    }
    
    public function getUpdatedProperties()
    {
        $updateOperations = $this->xpath('./comment()[contains(., "operation=3")]');
        for($i=0; $i<$updateOperations->length; $i++) {
            $updateOperation = $updateOperations->item($i);
            
        }
    }
    
    
    //*************************************************************************
    // XML INTERFACE
    //*************************************************************************
    public function asXml() 
    {  
        // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $this->C14N(false,true));
        
        // now indent the tags
        $token      = strtok($xml, "\n");
        $result     = ''; // holds formatted version as it is built
        $pad        = 0; // initial indent
        $matches    = array(); // returns from preg_matches()
        
        // scan each line and adjust indent based on opening/closing tags
        while ($token !== false) {
            $comment = false;
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
            if(preg_match('/<\!\-\-\w[^>]*\-\->/', $token, $matches)) {
                // nothing
            } else {
                $token = $this->attrAsXml($token, $pad);
            }
            // pad the line with the required number of leading spaces
            $line    = str_pad($token, strlen($token)+($pad*2), ' ', STR_PAD_LEFT);
            $result .= $line . "\n"; // add to the cumulative result, with linefeed
            $token   = strtok("\n"); // get the next token
            $pad    += $indent; // update the pad size for subsequent lines    
        } 
        
        return $result;
    }
    
    private function attrAsXml($token, $pad)
    {
        $return = '';
        $array = preg_split("/\s(\w+=)/", $token, -1, PREG_SPLIT_DELIM_CAPTURE);
        for($i=0; $i<count($array); $i++) {
            $item = $array[$i];
            if(preg_match('/^\w+=$/', $item)) {
                if($i>0 && $i<count($array)) $attrpad = $pad + 1;
                else $attrpad = $pad;
                $attrname = str_pad($item, strlen($item)+($attrpad*2), ' ', STR_PAD_LEFT);
                $return .= $attrname;
            } else {
                $return .= $item;
                if($i<count($array)-1) $return .= "\n";
            }
        }
        return $return;
    }
    
    public function show()
    {
        echo "<pre>";
        echo htmlspecialchars($this->asXml());
        echo "</pre>";
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
                    //echo "<br/>$childName is an array";
                    $object->{$childName}[] = $childNode->asObject();
                } else {
                    //echo "<br/>$childName is an extension";
                    $object->$childName = $childNode->asObject();
                }
            }
        }
        
        return $object;
    }
    
    
}