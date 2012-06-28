<?php

class DataObjectArray 
    extends ArrayObject
{
    private $name;
    private $schemaPath;
    private $arraySchemaPath;
    private $parentObject;
    private $dataAccessService;
    private $changeLog;
    
    public function DataObjectArray($name, $schemaPath, $arraySchemaPath) 
    {
        $this->name = $name;
        $this->schemaPath = $schemaPath;
        $this->arraySchemaPath = $arraySchemaPath;
        $this->setFlags(ArrayObject::ARRAY_AS_PROPS);
        $this->setFlags(ArrayObject::STD_PROP_LIST);
    }
    
    public function append($childObject) 
    {
        $this->offsetSet(null, $childObject);
        $childObject->parentObject = $this->parentObject;
    }
    
    public function remove($offset)
    {
        $objectBefore = $this->offsetGet($offset);
        $this->offsetUnset($offset);
        if($this->changeLog && $this->changeLog->active) {
            $this->changeLog->logChange(DataObjectChange::DELETE, $objectBefore->name, serialize($objectBefore));
        }
    }
    
    public function clear()
    {
        if(count($this) == 0) return;
        foreach($this as $offset => $childObject) {
            $this->offsetUnset($offset);
        }
    }
    
    
    public function __set($name, $value)
    {
        switch($name) {
        case 'parentObject'     : 
            $this->parentObject = $value;
            break;
        case 'dataAccessService' :
            $this->dataAccessService = $value;
            break;
        }
    }
    
    public function __get($name) {
        switch($name) {
        case 'isDataObjectArray': return true;
        case 'name'             : return $this->name;
        case 'schemaPath'       : return $this->schemaPath;
        case 'arraySchemaPath'  : return $this->arraySchemaPath;
        case 'parentObject'     : return $this->parentObject;
        case 'children'         :
            if(count($this) == 0) return array();
            foreach($this as $i => $childObject) {
                if(is_object($childObject) 
                    && ($childObject->isDataObject 
                        || $childObject->isDataObjectArray)) {
                    $children[] = $childObject;
                }
            }
            return $children;
        case 'changes'          : return $this->changes;
        }
    }
    
    public function removeDataAccessService()
    {
        $this->dataAccessService = null;
    }
    
    public function load()
    {
        $objectDatas = $this->dataAccessService->getData($this);
        $schemaPath = $arrayDataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        for($i=0; $i<count($objectDatas); $i++) {
            $objectData = $objectDatas[$i];
            $dataObject = $this->instanciateDataObject($objectSchema);
            $dataObject->beginLogging();
            $dataObject->logRead();
            $arrayDataObject->append($dataObject);          
            $dataObject->loadData($objectData);
            $this->loadChildren($dataObject);
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
        $this->changeLog->logCreation();
    }
    
    //*************************************************************************
    // XML INTERFACE
    //*************************************************************************
    public function asXmlNodeList($XmlDocument)
    {
        $childElements = $XmlDocument->createElement($this->name);
        if(count($this) == 0) {
            $childElement = $XmlDocument->createElement($this->name);
            $childElements->appendChild($childElement);
        } 
        for($i=0; $i<count($this); $i++) {
            $childDataObject = $this->offsetGet($i);
            $childElement = $childDataObject->asXmlElement($XmlDocument);
            $childElements->appendChild($childElement);
        }
        return $childElements->childNodes;
    }
    
    //*************************************************************************
    // Web Service Object (properties/children - no method)
    //*************************************************************************    
    public function asObject() 
    {
        $array = Array();
        for($i=0; $i<count($this); $i++) {
            $childDataObject = $this->offsetGet($i);
            $childObject = $childDataObject->asObject();
            $array[] = $childObject;
        }
        return $array;
    }
}