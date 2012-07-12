<?php

class DataObjectDocument
	extends DOMDocument
    implements ArrayAccess
{
	
    public $changeLog = array();
    
	public function DataObjectDocument()
	{
		parent::__construct();
		$this->registerNodeClass('DOMElement', 'DataObject');
	}
    
    private function xpath($query) 
    {
        $xpath = new DOMXpath($this);
        return $xpath->query($query, $this->documentElement);
    }
    
    public function __get($name) 
    {
        switch($name) {
        default:
            $resultNodes = $this->xpath('./'.$name);
            if($resultNodes->length == 0) {
                return false;
            } else {
                $dataObjectArray = array();
                for($i=0; $i<$resultNodes->length; $i++) {
                    $dataObjectArray[] = $resultNodes->item($i);
                }
                return $dataObjectArray;
            }
        }
    }
    
    public function __set($name, $value) 
    {
        switch($name) {
        default:
            $resultNodes = $this->xpath('./'.$name);
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
                $this->logChange(DataObjectChange::UPDATE, $name, (string)$resultNode->nodeValue, $value);
                $resultNode->nodeValue = $value;
                break;
            }
        }
    }
    
    //*************************************************************************
    // ITERATOR METHODS
    //*************************************************************************
    public function getIterator() {
        $childNodes = $this->documentElement->childNodes;
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
        $elementsWithTagName = $this->xpath('./' . $value->tagName)->length;
        $this->appendChild($value);
        return $elementsWithTagName;
    }
    
    public function offsetExists($offset) 
    {
        return isset($this->container[$offset]);
    }
    
    public function offsetUnset($offset) 
    {
        unset($this->container[$offset]);
    }
    
    public function offsetGet($offset) 
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
    
    //*************************************************************************
    // CHANGELOG
    //*************************************************************************
    public function logChange($type, $dataObject, $valueBefore=false, $valueAfter=false) 
    {
        $newChange = new DataObjectChange($type, $dataObject, $valueBefore, $valueAfter);
        //echo "<br/>DataObjectChange($type, $name, $valueBefore, $valueAfter)";
        $this->changeLog[] = $newChange;
    }
    
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
    
}