<?php

class DataObjectDocument
	extends DOMDocument
    implements ArrayAccess
{
	
    //*************************************************************************
    // DOM METHODS
    //************************************************************************* 
	public function DataObjectDocument()
	{
		parent::__construct();
		$this->registerNodeClass('DOMElement', 'DataObject');
        $this->registerNodeClass('DOMAttr', 'DataObjectProperty');
        $this->registerNodeClass('DOMComment', 'DataObjectLog');
	}
    
    public function createDataObject($objectName)
    {
        $dataObject = parent::createElement($objectName);
        return $dataObject;
    }
    
    public function createDataObjectProperty($propertyName, $propertyValue)
    {
        $dataObjectProperty = parent::createAttribute($propertyName);
        $dataObjectProperty->nodeValue = $propertyValue;
        return $dataObjectProperty;
    }
    
    public function createDataObjectLog($operation, $level=DataObjectLog::INFO, $detail=false)
    {
        $messageStrings[] = 'log';
        $messageStrings[] = 'operation="' . $operation . '"';
        $messageStrings[] = 'level="' . (string)$level . '"';
        if($detail) $messageStrings[] = $detail;
        $DataObjectLog = $this->createComment(implode(" ", $messageStrings));
        return $DataObjectLog;
    }
    
    private function xpath($query) 
    {
        $xpath = new DOMXpath($this);
        return $xpath->query($query, $this->documentElement);
    }
    
    //*************************************************************************
    // MAGIC METHODS
    //************************************************************************* 
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
        case '' :
            $this->appendChild($value);
            break;
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
                //$this->logChange(DataObjectChange::UPDATE, $name, (string)$resultNode->nodeValue, $value);
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