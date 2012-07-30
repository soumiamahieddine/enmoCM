<?php

// Schema
require_once 'core/tests/class/Schema.php';
//require_once 'core/tests/class/SchemaController.php';
require_once 'core/tests/class/SchemaXRefs.php';

// Document & objects
require_once 'core/tests/class/DataObjectDocument.php';
require_once 'core/tests/class/DataObject.php';
require_once 'core/tests/class/DataObjectProperty.php';
require_once 'core/tests/class/DataObjectLog.php';

// Data access services
require_once 'core/tests/class/DataAccessService_Database.php';
require_once 'core/tests/class/DataAccessService_XML.php';

// Messages
require_once 'core/tests/class/MessageController.php';
require_once 'core/tests/class/Message.php';
require_once 'core/tests/class/Exception.php';

class DataObjectController 
    extends DOMXPath
{
 
    public $dataObjectDocuments = array();
    public $dataAccessServices = array();
    protected $messageController;
    protected $messages = array();
    
    //*************************************************************************
    // CONSTRUCTOR
    //*************************************************************************
    public function DataObjectController() 
    {
        $this->XRefs = new SchemaXRefs();
        
        $this->messageController = new MessageController();
        $this->messageController->logLevel = Message::INFO;
        $this->messageController->debug = true;
        $this->messageController->loadMessageFile(
            $_SESSION['config']['corepath'] 
                . '/core/xml/DataObjectController_Messages.xml'
        );
    }
    
    // Load schema from master Xsd file
    public function loadXSD($XSDFile)
    {
        $schema = new Schema();
        $schema->loadXSD($XSDFile);
        parent::__construct($schema);
        $this->schema = $this->document;
    }
    
    // Load schema from Schema document
    public function loadSchema($schemaXML)
    {
        parent::__construct($schemaXML);
        $this->schema = $this->document;
    }
    
    //*************************************************************************
    // PUBLIC SCHEMA INFO
    //*************************************************************************
    public function getKeyProperties($objectName)
    {
        $objectElement = $this->getObjectElement($objectName);
        $key = $this->getKey($objectElement);
        $keyFields = $this->getKeyFields($key);
        $return = array();
        for($i=0; $i<$keyFields->length; $i++) {
            $keyField = $keyFields->item($i);
            $keyName = str_replace("@", "", $keyField->getAttribute('xpath'));
            $return[] = $keyName;  
        }
        return $return;
    }
    
    //*************************************************************************
    // PUBLIC OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    public function createRoot($objectName) 
    {
        $this->dataObjectDocument = new DataObjectDocument();
        $rootObject = $this->create($objectName);
        $this->dataObjectDocument->appendChild($rootObject);
        return $rootObject;
    }
    
    public function create($objectName) 
    {
        $objectElement = $this->getObjectElement($objectName);
        $dataObject = $this->getDataObjectPrototype($objectElement)->cloneNode(true);
        $dataObject->logCreate();
        return $dataObject;
    }
    
    public function enumerate(
        $objectName, 
        $filter=false, 
        $sort=array(), 
        $order=false
        ) 
    {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        $rootNode = $this->query('/xsd:schema/xsd:element[@das:module != ""]')->item(0);
        $rootObject = $dataObjectDocument->createDataObject($rootNode->getAttribute('name'));
        $dataObjectDocument[] = $rootObject;
        
        $objectElement = $this->getObjectElement($objectName);
        
        $this->readDataObject(
            $objectElement, 
            $rootObject, 
            $key=false,
            $filter, 
            $sort, 
            $order
        );
        
        return $dataObjectDocument->documentElement;
    
    }
    
    public function read($objectName, $key)
    {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        $objectElement = $this->getObjectElement($objectName);
        $this->readDataObject($objectElement, $dataObjectDocument, $key);
        return $dataObjectDocument->documentElement;
    }
    
    public function save($dataObject)
    {
        $this->saveDataObject($dataObject);   
    }
    
    public function load($xml)
    {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        $dataObjectDocument->loadXML($xml);
        return $dataObjectDocument->documentElement;
    }
  
    public function delete($dataObject)
    {
        
    }
    
    public function copy($dataObject, $keepParent=true)
    {
        $dataObject = unserialize(serialize($dataObject));

        $key = $this->getKey($dataObject->name);
        $keyNames = explode(' ', $key);
        for($i=0; $i<count($keyNames); $i++) {
            $keyName = $keyNames[$i];
            $dataObject->{$keyName}->clear();
        }
        
        $children = $dataObject->children;
        for($i=0; $i<count($children); $i++) {
            $children[$i]->clear();
        }
        
        if(!$keepParent) $dataObject->parentObject = false;
        
        $dataObject->beginLogging();
        $dataObject->logCreation();
                
        return $dataObject;    
    
    }
    
    public function validate($dataObjectDocument=false) 
    {
        $messageController = new MessageController();
        $messageController->loadMessageFile(
            $_SESSION['config']['corepath'] 
                . '/core/xml/DataObjectController_Messages.xml'
        );
        $this->messages = array();
        // Validate with specific business script
        //*********************************************************************
        /*$objectSchema = $this->schema->getObjectSchema($dataObject->tagName);
        if($objectSchema->hasAttribute('das:validation')) {
            include_once $objectSchema->getAttribute('das:validation');
        }*/
        
        // Validate against schema
        //*********************************************************************
        $XsdString = $this->schema->saveXML();
        libxml_use_internal_errors(true);
        if(!$this->dataObjectDocument->schemaValidateSource($XsdString)) {
            $libXMLErrors = libxml_get_errors();
            foreach ($libXMLErrors as $libXMLError) {
                $message = $messageController->createMessage(
                    'libxml::' . $libXMLError->code,
                    $_SESSION['config']['lang'],
                    array($libXMLError->message)
                );
                $this->messages[] = $message;
            }
        } 
        libxml_clear_errors();
        if(count($this->messages) > 0) return $this->messages;
        return true;
    }
    
    
    //*************************************************************************
    // protected OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    protected function getDataObjectPrototype($objectElement)
    {
        $objectName = $objectElement->getAttribute('name');
        if(!isset($this->dataObjectPrototypes[$objectName])) {
            $this->dataObjectPrototypes[$objectName] = $this->createDataObjectPrototype($objectElement);
        }
        return $this->dataObjectPrototypes[$objectName];
    }
    
    protected function createDataObjectPrototype($objectElement)
    {
        $objectName = $objectElement->getAttribute('name');
        $dataObject = $this->dataObjectDocument->createDataObject($objectName);
        
        // Add properties
        $properties = $this->getProperties($objectElement, $excludeOptional=true);
        for($i=0; $i<count($properties); $i++) {
            $property = $properties[$i];
            $propertyName = $property->getAttribute('name');
            $propertyValue = null;
            if($property->hasAttribute('fixed')) {
                $propertyValue = $property->getAttribute('fixed');
            } elseif($property->hasAttribute('default')) {
                $propertyValue = $property->getAttribute('default');
            }
            $dataObject->$propertyName = $propertyValue;
        }
        
        // Add child objects
        $children = $this->getChildren($objectElement, $excludeOptional=true);
        $childrenLength = count($children);
        for($i=0; $i<$childrenLength; $i++) {
            $child = $children[$i];
            $childObject = $this->getDataObjectPrototype($child);
            $dataObject[] = $childObject;
        }
        
        return $dataObject;
    }
    
    protected function readDataObject(
        $objectElement,
        $parentObject,
        $key=false, 
        $filter=false, 
        $sort=false,
        $order='ascending'
        ) 
    {      
        try {
            $dataAccessService = $this->getDataAccessService($objectElement);
            
            // Process Properties
            if($dataAccessService) {
                $dataAccessService->loadData(
                    $objectElement, 
                    $parentObject, 
                    $key,
                    $filter, 
                    $sort,
                    $order
                    );
            } else {
                $dataObject = $this->createDataObject($objectName);
                $dataObject->logRead();
                $parentObject[] = $dataObject;
            }

            // Process child objects
            $children = $this->getChildren($objectElement, $excludeOptional=false);
            $childrenLength = count($children);
            for($i=0; $i<$childrenLength; $i++) {
                $child = $children[$i];
                $newObjects = $parentObject->childNodes;
                $newObjectsLength = $newObjects->length;
                for($j=0; $j<$newObjectsLength; $j++) {
                    $newObject = $newObjects->item($j);
                    $this->readDataObject($child, $newObject);
                }    
            }
        } catch (maarch\Exception $e) {   
            throw $e;
        }

    }
    
    protected function saveDataObject($dataObject)
    {
        $objectName = $dataObject->tagName;
        $objectElement = $this->getObjectElement($objectName);
        $dataAccessService = $this->getDataAccessService($objectElement);
        
        if($dataAccessService) {
            $dataAccessService->saveData($dataObject);
        } 

        $children = $this->getChildren($objectElement, $excludeOptional=false);
        $childrenLength = count($children);
        for($i=0; $i<$childrenLength; $i++) {
            $child = $children[$i];
            $childName = $child->getAttribute('name');
            $childObjects = $dataObject->$childName;
            $childObjectsLength = count($childObjects);
            for($j=0; $j<$childObjectsLength; $j++) {
                $childObject = $childObjects[$j];
                $this->saveDataObject($childObject);
            }
        }
        
    }
    
    //*************************************************************************
    // protected SCHEMA QUERY FUNCTIONS
    //*************************************************************************
    protected function getDataAccessService($objectElement)
    {
        if($objectElement->hasAttribute('das:source')) {
            $sourceName = $objectElement->getAttribute('das:source');
            if(!isset($this->dataAccessServices[$sourceName])) {
                $sourceNode = $this->query('//das:source[@name="'.$sourceName.'"]')->item(0);
                $this->createDataAccessService($sourceName, $sourceNode);
            }
        } elseif($sourceNode = $this->query('./xsd:annotation/xsd:appinfo/das:source', $objectElement)->item(0)) {
            $sourceName = $objectElement->getAttribute('name');
            if(!isset($this->dataAccessServices[$sourceName])) {
                $this->createDataAccessService($sourceName, $sourceNode);
            }
        }
        
        return $this->dataAccessServices[$sourceName];

    }
    
    protected function createDataAccessService($sourceName, $sourceNode) 
    {
        switch($sourceNode->getAttribute('type')) {
        case 'database':
            $this->dataAccessServices[$sourceName] = new DataAccessService_Database();
            $this->dataAccessServices[$sourceName]->loadSchema($this->schema);
            $this->dataAccessServices[$sourceName]->connect($sourceNode);
            break;
        case 'xml':
            break;
        } 
    }
    
    //*************************************************************************
    // CROSSED REFERENCES
    //*************************************************************************
    
    protected function getObjectElement($objectName)
    {   
        $objectElement = $this->query('/xsd:schema/xsd:element[@name = "'.$objectName.'"]')->item(0);
        if(!$objectElement) Die("Object $objectName is unknown");
        return $objectElement;
    }
    
	protected function getTableName($objectElement) 
    {
        if($objectElement->hasAttribute('das:table')) {
            $tableName = $objectElement->getAttribute('das:table');
        } else {
            $tableName = $objectElement->getAttribute('name');
        }
        return $tableName;
    }
    
    protected function getComplexType($element)
    {
        if(!$complexTypePath = $this->XRefs->getXRefPath($element, 'complexType')) {
            if($element->hasAttribute('type')) {           
                $complexTypeName = $element->getAttribute('type');
                if(substr($complexTypeName, 0, 3) == 'xsd') {
                    $complexType = $this->schema->createElement('xsd:simpleType');
                    $complexType->setAttribute('name', $complexTypeName);
                    // Define if enclosed or not
                    $complexType->setAttribute('das:enclosed', 'true');
                    
                    $this->schema->appendChild($typeNode);
                } else {            
                    $complexType = $this->query('//xsd:complexType[@name="'.$complexTypeName.'"]')->item(0);
                }
            } elseif($complexType = $this->query('./xsd:complexType', $element)->item(0)) {
                $complexTypeName = $element->getAttribute('name');
            } else {
                return false;
            }
            $this->XRefs->addXRefPath($element, $complexType);
            return $complexType;
        } else {
            $complexType = $this->query($complexTypePath)->item(0);
        }
        return $complexType;
    }
    
    protected function getSimpleType($element)
    {
        if(!$simpleTypePath = $this->XRefs->getXRefPath($element, 'simpleType')) {
            if($element->hasAttribute('type')) {           
                $simpleTypeName = $element->getAttribute('type');
                if(substr($simpleTypeName, 0, 3) == 'xsd') {
                    $simpleType = $this->schema->createElement('xsd:simpleType');
                    $simpleType->setAttribute('name', $simpleTypeName);
                    // Define if enclosed or not
                    $simpleType->setAttribute('das:enclosed', 'true');
                    
                    $this->schema->appendChild($typeNode);
                } else {            
                    $simpleType = $this->query('//xsd:simpleType[@name="'.$simpleTypeName.'"]')->item(0);
                }
            } elseif($simpleType = $this->query('./xsd:simpleType', $element)->item(0)) {
                $simpleTypeName = $element->getAttribute('name');
            } else {
                return false;
            }
            $this->XRefs->addXRefPath($element, $simpleType);
            return $simpleType;
        } else {
            $simpleType = $this->query($simpleTypePath)->item(0);
        }
        return $simpleType;
    }
    
    protected function getRefElement($refName)
    {
        $element = $this->query('/xsd:schema/xsd:element[@name="'.$refName.'"]')->item(0);
        return $element;
    }
    
    protected function getRefAttribute($refName)
    {
        $attribute = $this->query('/xsd:schema/xsd:attribute[@name="'.$refName.'"]')->item(0);
        return $attribute;
    }
    
    protected function getKey($objectElement)
    {      
        $key = $this->query('./xsd:key', $objectElement)->item(0);
        return $key;
    }
    
    protected function getKeyFields($keyNode) 
    {
        $keyFields = $this->query('./xsd:field', $keyNode);
        return $keyFields;
    }
    
    protected function getFilter($objectElement)
    {
        if($objectElement->hasAttribute('das:filter')) {
            return $objectElement->getAttribute('das:filter');
        } 
    }
    
    protected function getRelation($objectElement, $parentName)
    {
        $relation = $this->query('./xsd:annotation/xsd:appinfo/das:relation[@parent="'.$parentName.'"]', $objectElement)->item(0);
        return $relation;
    }
    
    protected function getAttributes($parentNode)
    {
        $attributes = $this->query('./xsd:attribute', $parentNode);
        //echo "<br/>getAttributes() => " . $attributes->length;
        if($attributes->length == 0) return false;
        return $attributes;
    }
    
    protected function getSequence($parentNode)
    {
        $sequences = $this->query('./xsd:sequence', $parentNode);
        //echo "<br/>getSequence() => " . $sequences->length;
        if($sequences->length == 0) return false;
        return $sequences->item(0);
    }
    
    protected function getElements($parentNode)
    {
        $elements = $this->query('./xsd:element', $parentNode);
        //echo "<br/>getElements() => " . $elements->length;
        if($elements->length == 0) return false;
        return $elements;
    }
     
    //*************************************************************************
    // GET OBJECT PROPERTIES (ATTRIBUTES)
    //*************************************************************************
    protected function getProperties($element, $excludeOptional=false)
    {
        $elementName = $element->getAttribute('name');
        if(!isset($this->dataObjectProperties[$elementName][(integer)$excludeOptional])) {
            $this->dataObjectProperties[$elementName][(integer)$excludeOptional] = $this->getDataObjectProperties($element, $excludeOptional);
        }
        return $this->dataObjectProperties[$elementName][(integer)$excludeOptional];
    }

    protected function getDataObjectProperties($element, $excludeOptional)
    {
        $type = $this->getComplexType($element);
        $properties = array();
        if($typeAttributes = $this->getAttributes($type)) {
            $properties = $this->selectAttributes($typeAttributes, $excludeOptional);
        }
               
        /*$complexTypeSimpleContent = $this->query('./xsd:simpleContent', $objectType);
        $complexTypeComplexContent = $this->query('./xsd:complexContent', $objectType);
        $complexTypeGroup = $this->query('./xsd:group', $objectType);
        $complexTypeAll = $this->query('./xsd:all', $objectType);
        $complexTypeSequence = $this->getSequence($objectType);
        $complexTypeChoice = $this->query('./xsd:choice', $objectType);
        $complexTypeAttributeGroup = $this->query('./xsd:attributeGroup', $objectType);
        $complexTypeAnyAttribute = $this->query('./xsd:anyAttribute', $objectType);*/
        
        $properties = array_merge(
            $properties
        );
        //echo "<br/>getProperties() => " . count($properties);
        return $properties;
    }
    
    protected function selectAttributes($attributes, $excludeOptional)
    {
        $selectedAttributes = array();
        $attributesLength = $attributes->length;
        for($i=0; $i<$attributesLength; $i++) {
            $attribute = $attributes->item($i);
            if($excludeOptional 
                && $attribute->hasAttribute('use') 
                && ($attribute->getAttribute('use') == 'optional' 
                    ||$attribute->getAttribute('use') == 'prohibited')) {
                continue;
            }
            if($attribute->hasAttribute('ref')) {
                $attribute = $this->getRefAttribute($attribute->getAttribute('ref'));
            }  
            $selectedAttributes[] = $attribute;           
        }
        //echo "<br/>selectAttributes() => " . count($selectedAttributes);
        return $selectedAttributes;
    }   
    
    //*************************************************************************
    // GET CHILD OBJECT
    //*************************************************************************
    protected function getChildren($element, $excludeOptional=false)
    {
        $elementName = $element->getAttribute('name');
        if(!isset($this->dataObjectChildren[$elementName][(integer)$excludeOptional])) {
            $this->dataObjectChildren[$elementName][(integer)$excludeOptional] = $this->getDataObjectChildren($element, $excludeOptional);
        }
        return $this->dataObjectChildren[$elementName][(integer)$excludeOptional];
    }

    protected function getDataObjectChildren($element, $excludeOptional)
    {
        $type = $this->getComplexType($element);
        $children = array();
        
        // type/sequence/element
        $sequenceElements = array();
        if($sequence = $this->getSequence($type)) {
            $sequenceElements = $this->getSequenceElements($sequence, $excludeOptional);
        }
        
        
        /*$complexTypeSimpleContent = $this->query('./xsd:simpleContent', $objectType);
        $complexTypeComplexContent = $this->query('./xsd:complexContent', $objectType);
        $complexTypeGroup = $this->query('./xsd:group', $objectType);
        $complexTypeAll = $this->query('./xsd:all', $objectType);
        $complexTypechoice = $this->query('./xsd:choice', $objectType);
        $complexTypeAttributes = $this->query('./xsd:attribute', $objectType);

        $complexTypeAttributeGroup = $this->query('./xsd:attributeGroup', $objectType);
        $complexTypeAnyAttribute = $this->query('./xsd:anyAttribute', $objectType);*/
        
        // Merge all results
        $children = array_merge(
            $sequenceElements
        );
        //echo "<br/>getChildren() => " . count($children);
        return $children;
    }
   
    protected function getSequenceElements($sequence, $excludeOptional)
    {
        $sequenceElements = array();
        //echo "<br/>getSequenceChildObjectElements()";
        //any, choice, element, group, sequence
        /*$sequenceAny = $this->query('./xsd:any', $sequence);
        $sequencechoice = $this->query('./xsd:choice', $sequence);
        $sequenceGroup = $this->query('./xsd:group', $sequence);
        $sequenceSequence = $this->query('./xsd:sequence', $sequence);*/
        
        if($elements = $this->getElements($sequence)) {
            $sequenceElements = $this->selectChildElements($elements, $excludeOptional);
        }
        
        // Merge all results
        $sequenceElements = array_merge(
            $sequenceElements
        );
        //echo "<br/>getSequenceElements() => " . count($sequenceElements);
        return $sequenceElements;
    }
    
    protected function selectChildElements($elements, $excludeOptional) 
    {
        $selectedChildElements = array();
        //echo "<br/>getElementsChildObjectElements for $elements->length elements";
        $elementsLength = $elements->length;
        for($i=0; $i<$elementsLength; $i++) {
            $element = $elements->item($i);
            if($excludeOptional 
                && $element->hasAttribute('minOccurs') 
                && $element->getAttribute('minOccurs') == 0) {
                continue;
            }
            if($element->hasAttribute('ref')) {
                $element = $this->getRefElement($element->getAttribute('ref'));
            } 
            $selectedChildElements[] = $element;
        }
        //echo "<br/>selectChildElements() => " . count($selectedChildElements);
        return $selectedChildElements;
    }

}