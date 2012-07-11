<?php

class DataObjectDocument
	extends DOMDocument
    implements ArrayAccess
{
	
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
        $this->documentElement->appendChild($value);
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
        $this->appendChild($value);
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
    
}