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
    
    // Globals
    protected $dataObjectDocument;
    protected $dataObject;
    protected $dataObjectProperty;
    
    protected $objectName;
    protected $objectElement;
    protected $objectType;
    protected $objectProperties;
    protected $objectChildren;
    
    protected $propertyName;
    protected $propertyElement;
    protected $propertyType;
    
    protected $dataObjectPrototype;
    
    protected $dataAccessService;
    protected $table;
    protected $column;
    protected $relation;
    
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
        $this->getObjectSchema($objectName);
        $key = $this->getKey();
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
    public function create($objectName) 
    {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        
        $objectElement = $this->getObjectElement($objectName);
        $dataObjectPrototype = $this->getDataObjectPrototype($objectElement);
        $dataObject = $dataObjectDocument->importNode($dataObjectPrototype, true);
        $dataObject->logCreate();
        $dataObjectDocument[] = $dataObject;
        
        return $dataObject;
    }
    
    public function enumerate(
        $objectName, 
        $filter=false, 
        $sort=false, 
        $order=false
        ) 
    {
        $dataObjectDocument = new DataObjectDocument();
        //$this->dataObjectDocuments[] = $dataObjectDocument;
        $rootElement = $this->query('/xsd:schema/xsd:element[@das:module != ""]')->item(0);
        $rootName = $this->getObjectName($rootElement);
        $rootDataObject = $dataObjectDocument->createDataObject($rootName);
        $dataObjectDocument[] = $rootDataObject;
        
        $objectElement = $this->getObjectElement($objectName);
        
        $this->readDataObject(
            $objectElement,
            $rootDataObject,
            $dataObjectDocument,
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
        $dataObject = $dataObjectDocument;
        $this->readDataObject(
            $objectElement, 
            $dataObject, 
            $dataObjectDocument,
            $key
        );
        return $dataObjectDocument->documentElement;
    }
    
    public function save($dataObject)
    {
        $objectElement = $this->getObjectElement($dataObject->tagName);
        $dataObjectDocument = $dataObject->ownerDocument;
        $this->saveDataObject($objectElement, $dataObject, $dataObjectDocument);   
    }
    
    public function load($xml)
    {
        global $dataObjectDocument;
        global $dataObject;
        
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        $dataObjectDocument->loadXML($xml);
        
        $dataObject = $dataObjectDocument->documentElement;
        
        return $dataObject;
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
    
    public function validate($dataObject) 
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
        $dataObjectDocument = $dataObject->ownerDocument;
        if(!$dataObjectDocument->schemaValidateSource($XsdString)) {
            $libXMLErrors = libxml_get_errors();
            foreach ($libXMLErrors as $libXMLError) {
                /*$message = $messageController->createMessage(
                    'libxml::' . $libXMLError->code,
                    $_SESSION['config']['lang'],
                    array($libXMLError->message)
                );*/
                switch ($libXMLError->level) {
                    case LIBXML_ERR_WARNING:
                        $level = DataObjectLog::WARNING;
                        break;
                     case LIBXML_ERR_ERROR:
                        $level = DataObjectLog::ERROR;
                        break;
                    case LIBXML_ERR_FATAL:
                        $level = DataObjectLog::FATAL;
                        break;
                }
                $dataObject->logValidate($level, $libXMLError->message);
            }
            libxml_clear_errors();
            return false;
        } else {
            $dataObject->logValidate(DataObjectLog::INFO, 'Valid');
            libxml_clear_errors();
            return true;
        }

    }
        
    //*************************************************************************
    // protected OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    protected function getDataObjectPrototype($objectElement)
    {
        $objectName = $this->getObjectName($objectElement);
        $dataObjectPrototype = $this->XRefs->createElement($objectName);
        
        // Add properties
        $objectType = $this->getObjectType($objectElement);
        $objectProperties = $this->getObjectProperties($objectType);
        for($i=0; $i<count($objectProperties); $i++) {
            $propertyElement = $objectProperties[$i];
            $propertyName = $propertyElement->getAttribute('name');
            $propertyValue = null;
            if($propertyElement->hasAttribute('fixed')) {
                $propertyValue = $propertyElement->getAttribute('fixed');
            } elseif($propertyElement->hasAttribute('default')) {
                $propertyValue = $propertyElement->getAttribute('default');
            }
            $dataObjectPrototype->setAttribute($propertyName, $propertyValue);
        }
        
        return $dataObjectPrototype;
    }
    
    protected function readDataObject(
        $objectElement,
        $parentObject,
        $dataObjectDocument,
        $key=false, 
        $filter=false, 
        $sort=false,
        $order='ascending'
        ) 
    {      
     
        try {
            // Process Properties
            if($dataAccessService = $this->getDataAccessService($objectElement)) {
                $dataAccessService->loadData(
                    $objectElement,
                    $parentObject,
                    $dataObjectDocument,
                    $key,
                    $filter, 
                    $sort,
                    $order
                    );
            } else {
                $objectName = $this->getObjectName($objectElement);
                $childDataObject = $dataObjectDocument->createDataObject($objectName);
                $childDataObject->logRead();
                $parentObject[] = $childDataObject;
            }
            // Process child objects
            $objectChildren = $this->getObjectChildren($objectElement);
            $childrenLength = count($objectChildren);
            for($i=0; $i<$childrenLength; $i++) {
                $childElement = $objectChildren[$i];
                $childDataObjects = $parentObject->childNodes;
                $childDataObjectsLength = $childDataObjects->length;
                for($j=0; $j<$childDataObjectsLength; $j++) {
                    $childDataObject = $childDataObjects->item($j);
                    echo "<br/>read children " . $childElement->getAttribute('name') ." of " .$childDataObject->tagName;
                    $this->readDataObject(
                        $childElement, 
                        $childDataObject, 
                        $dataObjectDocument
                    );
                }    
            }
        } catch (maarch\Exception $e) {   
            throw $e;
        }
    }
    
    protected function saveDataObject($objectElement, $dataObject, $dataObjectDocument)
    {
        
        $dataAccessService = $this->getDataAccessService($objectElement);
        
        if($dataAccessService) {
            $dataAccessService->saveData($objectElement, $dataObject, $dataObjectDocument);
        } 
        
        $objectChildren = $this->getObjectChildren($objectElement);
        $childrenLength = count($objectChildren);
        for($i=0; $i<$childrenLength; $i++) {
            $childElement = $objectChildren[$i];
            $childName = $this->getObjectName($childElement);
            $childObjects = $dataObject->$childName;
            $childObjectsLength = count($childObjects);
            for($j=0; $j<$childObjectsLength; $j++) {
                $dataObject = $childObjects[$j];
                $this->saveDataObject($childElement, $dataObject, $dataObjectDocument);
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
    
    protected function getDataObjectElement($dataObject)
    {
        $objectName = $dataObject->tagName;
        return $this->getObjectElement($objectName);
    }
    
    protected function getDataObjectDocument($dataObject)
    {
        return $dataObject->documentElement();
    }
    
    protected function getObjectName($objectElement)
    {
        return $objectElement->getAttribute('name');
    }
    
	protected function getObjectTable($objectElement) 
    {
        if($objectElement->hasAttribute('das:table')) {
            $table = $objectElement->getAttribute('das:table');
        } else {
            $table = $objectElement->getAttribute('name');
        }
        return $table;
    }
    
    protected function getObjectType($objectElement)
    {
        if(!$objectTypePath = $this->XRefs->getXRefPath($objectElement, 'objectType')) {
            if($objectElement->hasAttribute('type')) {           
                $objectTypeName = $objectElement->getAttribute('type');
                $objectType = $this->query('//xsd:complexType[@name="'.$objectTypeName.'"]')->item(0);
            } elseif($objectType = $this->query('./xsd:complexType', $objectElement)->item(0)) {
                //$objectTypeName = $objectElement->getAttribute('name');
            } else {
                return false;
            }
            $this->XRefs->addXRefPath($objectElement, $objectType);
        } else {
            $objectType = $this->query($objectTypePath)->item(0);
        }
        return $objectType;
    }
    
    protected function getPropertyType($propertyElement)
    {
        if(!$propertyTypePath = $this->XRefs->getXRefPath($propertyElement, 'propertyType')) {
            if($propertyElement->hasAttribute('type')) {           
                $propertyTypeName = $propertyElement->getAttribute('type');
                if(substr($propertyTypeName, 0, 3) == 'xsd') {
                    $propertyType = $this->schema->createElement('xsd:simpleType');
                    $propertyType->setAttribute('name', $propertyTypeName);
                    // Define if enclosed or not
                    $propertyType->setAttribute('das:enclosed', 'true');
                    
                    $this->schema->appendChild($propertyType);
                } else {            
                    $propertyType = $this->query('//xsd:simpleType[@name="'.$propertyTypeName.'"]')->item(0);
                }
            } elseif($propertyType = $this->query('./xsd:simpleType', $propertyElement)->item(0)) {
                //$propertyTypeName = $propertyElement->getAttribute('name');
            } else {
                return false;
            }
            $this->XRefs->addXRefPath($propertyElement, $propertyType);
        } else {
            $propertyType = $this->query($propertyTypePath)->item(0);
        }
        return $propertyType;
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
    
    protected function getRelation($objectElement, $dataObject)
    {
        $relation = $this->query('//xsd:annotation/xsd:appinfo/das:relation[@parent="'.$dataObject->tagName.'" and @child="'.$objectElement->getAttribute('name').'"]')->item(0);
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
    protected function getObjectProperties($objectElement)
    {
        if(!$objectProperties = $this->XRefs->getXRefData($objectElement, 'objectProperties')) {
            $objectProperties = array();
            $objectType = $this->getObjectType($objectElement);
            if($typeAttributes = $this->getAttributes($objectType)) {
                $objectProperties = $this->selectAttributes($typeAttributes);
            }
                  
            /*$complexTypeSimpleContent = $this->query('./xsd:simpleContent', $objectType);
            $complexTypeComplexContent = $this->query('./xsd:complexContent', $objectType);
            $complexTypeGroup = $this->query('./xsd:group', $objectType);
            $complexTypeAll = $this->query('./xsd:all', $objectType);
            $complexTypeSequence = $this->getSequence($objectType);
            $complexTypeChoice = $this->query('./xsd:choice', $objectType);
            $complexTypeAttributeGroup = $this->query('./xsd:attributeGroup', $objectType);
            $complexTypeAnyAttribute = $this->query('./xsd:anyAttribute', $objectType);*/
            
            $objectProperties = array_merge(
                $objectProperties
            );
        }
        return $objectProperties;
    }
    
    protected function selectAttributes($attributes)
    {
        $selectedAttributes = array();
        $attributesLength = $attributes->length;
        for($i=0; $i<$attributesLength; $i++) {
            $attribute = $attributes->item($i);
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
    protected function getObjectChildren($objectElement)
    {
        if(!$objectChildren = $this->XRefs->getXRefData($objectElement, 'children')) {
            $objectChildren = array();
            $objectType = $this->getObjectType($objectElement);
            // type/sequence/element
            $sequenceElements = array();
            if($sequence = $this->getSequence($objectType)) {
                $sequenceElements = $this->getSequenceElements($sequence);
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
            $objectChildren = array_merge(
                $sequenceElements
            );
        }
        return $objectChildren;
    }
   
    protected function getSequenceElements($sequence)
    {
        $sequenceElements = array();

        //any, choice, element, group, sequence
        /*$sequenceAny = $this->query('./xsd:any', $sequence);
        $sequencechoice = $this->query('./xsd:choice', $sequence);
        $sequenceGroup = $this->query('./xsd:group', $sequence);
        $sequenceSequence = $this->query('./xsd:sequence', $sequence);*/
        
        if($elements = $this->getElements($sequence)) {
            $sequenceElements = $this->selectChildElements($elements);
        }
        
        // Merge all results
        $sequenceElements = array_merge(
            $sequenceElements
        );
        //echo "<br/>getSequenceElements() => " . count($sequenceElements);
        return $sequenceElements;
    }
    
    protected function selectChildElements($elements) 
    {
        $selectedChildElements = array();
        $elementsLength = $elements->length;
        for($i=0; $i<$elementsLength; $i++) {
            $element = $elements->item($i);
            if($element->hasAttribute('ref')) {
                $element = $this->getRefElement($element->getAttribute('ref'));
            } 
            $selectedChildElements[] = $element;
        }
        //echo "<br/>selectChildElements() => " . count($selectedChildElements);
        return $selectedChildElements;
    }

}