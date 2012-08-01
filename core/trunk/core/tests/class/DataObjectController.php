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
        // Construct Schema and load XSD with includes
        $schema = new Schema($this);
        $schema->loadXSD($XSDFile);
        
        // Construct SchemaController
        parent::__construct($schema);
        $this->schema = $this->document;

    }
    
    // Load schema from Schema document
    public function loadSchema($schemaXML)
    {
        // Construct SchemaController
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
        $order=false,
        $limit=false
        ) 
    {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        
        $rootElement = $this->query('/xsd:schema/xsd:element[@das:module != ""]')->item(0);
        $rootName = $rootElement->getName();
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
            $order,
            $limit
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
  
    public function delete($objectName, $key)
    {
        $objectElement = $this->getObjectElement($objectName);
        $this->deleteDataObject($objectElement, $key);
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
        $objectName = $objectElement->getName();
        $dataObjectPrototype = $this->XRefs->createElement($objectName);
        
        // Add properties
        $objectProperties = $this->getObjectProperties($objectElement);
        $objectPropertiesLength = $objectProperties->length;
        for($i=0; $i<$objectPropertiesLength; $i++) {
            $propertyElement = $objectProperties->item($i);
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
        $order='ascending',
        $limit=false
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
                    $order,
                    $limit
                    );
            } else {
                $objectName = $objectElement->getName();
                $childDataObject = $dataObjectDocument->createDataObject($objectName);
                $childDataObject->logRead();
                $parentObject[] = $childDataObject;
            }
            // Process child objects
            $objectChildren = $this->getObjectChildren($objectElement);
            $objectChildrenLength = $objectChildren->length;
            for($i=0; $i<$childrenLength; $i++) {
                $childElement = $objectChildren->item($i);
                $childDataObjects = $parentObject->childNodes;
                $childDataObjectsLength = $childDataObjects->length;
                for($j=0; $j<$childDataObjectsLength; $j++) {
                    $childDataObject = $childDataObjects->item($j);
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
        $objectChildrenLength = $objectChildren->length;
        for($i=0; $i<$childrenLength; $i++) {
            $childElement = $objectChildren->item($i);
            $childName = $childElement->getName();
            $childObjects = $dataObject->$childName;
            $childObjectsLength = count($childObjects);
            for($j=0; $j<$childObjectsLength; $j++) {
                $dataObject = $childObjects[$j];
                $this->saveDataObject($childElement, $dataObject, $dataObjectDocument);
            }
        }
        
    }
    
    protected function deleteDataObject($objectElement, $key)
    {
        $dataAccessService = $this->getDataAccessService($objectElement);
        if($dataAccessService) {
            $dataAccessService->deleteData($objectElement, $key);
        } 
    }
    
    //*************************************************************************
    // DATA OBJECTS QUERY FUNCTIONS
    //*************************************************************************
    protected function getDataObjectElement($dataObject)
    {
        $objectName = $dataObject->tagName;
        return $this->getObjectElement($objectName);
    }
    
    protected function getDataObjectDocument($dataObject)
    {
        return $dataObject->documentElement();
    }
    
    //*************************************************************************
    // SCHEMA QUERY FUNCTIONS
    //*************************************************************************
    protected function hasDatasource($node)
    {
        if($node->hasDatasource() 
            || $this->query(
                './xsd:annotation/xsd:appinfo/das:source', $node )->length > 0
                ) 
        {
            return true;
        }
    
    }
    
    protected function getDataAccessService($node)
    {
        if($node->hasAttribute('das:source')) {
            $sourceName = $node->getAttribute('das:source');
            if(!isset($this->dataAccessServices[$sourceName])) {
                $sourceNode = $this->query('//das:source[@name="'.$sourceName.'"]')->item(0);
                $this->createDataAccessService($sourceName, $sourceNode);
            }
        } elseif($sourceNode = $this->query('./xsd:annotation/xsd:appinfo/das:source', $node)->item(0)) {
            $sourceName = $node->getAttribute('name');
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

    protected function getObjectElement($objectName)
    {
        $objectElement = $this->query('/xsd:schema/xsd:element[@name = "'.$objectName.'"]')->item(0);
        if(!$objectElement) Die("Object $objectName is unknown");
        return $objectElement;
    }
    
    protected function getType($node)
    {
        if(!$typePath = $this->XRefs->getXRefPath($node, 'type')) {
            if($typeName = $node->getTypeName()) { 
                if(substr($typeName, 0, 3) == 'xsd') {
                    $this->addBuiltInType($typeName);
                }
                $types = $this->query('//xsd:complexType[@name="'.$typeName.'"] | //xsd:simpleType[@name="'.$typeName.'"]');
            } else {
                $types = $this->query('./xsd:complexType | ./xsd:simpleType', $node);
            }
            @$this->XRefs->addXRefPath($node, $types->item(0));
        } else {
            $types = $this->query($typePath);
        }
        if($types->length == 0) return false;
        return $types->item(0);
    }
    
    protected function addBuiltInType($typeName)
    {
        $builtInType = $this->schema->createElement('xsd:simpleType');
        $builtInType->setAttribute('name', $typeName);
        
        // Define if enclosed or not
        $nonEnclosedTypes = array(
            'xsd:boolean',
                'xsd:double', 
                'xsd:decimal',
                    'xsd:integer',
                        'xsd:nonPositiveInteger',
                            'xsd:negativeInteger',
                        'xsd:long',
                            'xsd:int', 
                            'xsd:short', 
                            'xsd:byte',
                        'xsd:nonNegativeInteger',
                            'xsd:positiveInteger',
                            'xsd:unsignedLong',
                                'xsd:unsignedInt',
                                    'xsd:unsignedShort',
                                        'xsd:unsignedByte',
                'xsd:float',
        );
        if(!in_array($typeName, $nonEnclosedTypes)) {
            $builtInType->setAttribute('das:enclosed', 'true');
        }
        $this->schema->appendChild($builtInType);
    }
    
    protected function getRefElement($refName)
    {
        $elements = $this->query('/xsd:schema/xsd:element[@name="'.$refName.'"]');
        if($elements->length == 0) return false;
        return $elements->item(0);
    }
    
    protected function getRefAttribute($refName)
    {
        $attributes = $this->query('/xsd:schema/xsd:attribute[@name="'.$refName.'"]');
        if($attributes->length == 0) return false;
        return $attributes->item(0);
    }
    
    protected function getKey($objectElement)
    {      
        $key = $this->query('./xsd:key', $objectElement);
        if($key->length == 0) return false;
        return $key->item(0);
    }
    
    protected function getKeyFields($keyNode) 
    {
        $keyFields = $this->query('./xsd:field', $keyNode);
        return $keyFields;
    }
    
    protected function getFilter($objectElement)
    {
        return $objectElement->getFilter();
    }
    
    protected function getRelation($objectElement, $dataObject)
    {
        $relations = $this->query('//xsd:annotation/xsd:appinfo/das:relation[@parent="'.$dataObject->tagName.'" and @child="'.$objectElement->getAttribute('name').'"]');
        if($relations->length == 0) return false;
        return $relations->item(0);
    }
    
    protected function getAttributes($node)
    {
        $attributes = $this->query('./xsd:attribute', $node);
        if($attributes->length == 0) return false;
        return $attributes;
    }
    
    protected function getSequence($node)
    {
        $sequences = $this->query('./xsd:sequence', $node);
        if($sequences->length == 0) return false;
        return $sequences->item(0);
    }
    
    protected function getElements($node)
    {
        $elements = $this->query('./xsd:element', $node);
        if($elements->length == 0) return false;
        return $elements;
    }
    
    protected function getSequenceElements($sequence)
    {
        $sequenceElements = $this->schema->createElement('sequenceElements');

        //any, choice, element, group, sequence
        /*$sequenceAny = $this->query('./xsd:any', $sequence);
        $sequencechoice = $this->query('./xsd:choice', $sequence);
        $sequenceGroup = $this->query('./xsd:group', $sequence);
        $sequenceSequence = $this->query('./xsd:sequence', $sequence);*/
        
        $sequenceChildElements = $this->getElements($sequence);
        for($i=0; $i<$sequenceChildElements->length; $i++) {
            $sequenceChildElement = $sequenceChildElements->item($i)->cloneNode(true);
            $sequenceElements->appendChild($sequenceChildElement);
        }
        return $sequenceElements->childNodes;
    }    
     
     
    //*************************************************************************
    // GET OBJECT PROPERTIES (ATTRIBUTES / ELEMENT WITH SAME SOURCE)
    //*************************************************************************
    protected function getObjectProperties($objectElement)
    {
        if(!$objectProperties = $this->XRefs->getXRefData($objectElement, 'objectProperties')) {
            $objectProperties = $this->schema->createElement('objectProperties');
            
            $objectType = $this->getType($objectElement);
            
            // Attributes
            if($typeAttributes = $this->getAttributes($objectType)) {
                $this->selectProperties($typeAttributes, $objectProperties);
            }
            
            // Sequence
            if($sequence = $this->getSequence($objectType)) {
                $sequenceElements = $this->getSequenceElements($sequence);
                $this->selectProperties($sequenceElements, $objectProperties);
            }
            
            /*$complexTypeSimpleContent = $this->query('./xsd:simpleContent', $objectType);
            $complexTypeComplexContent = $this->query('./xsd:complexContent', $objectType);
            $complexTypeGroup = $this->query('./xsd:group', $objectType);
            $complexTypeAll = $this->query('./xsd:all', $objectType);
            $complexTypeSequence = $this->getSequence($objectType);
            $complexTypeChoice = $this->query('./xsd:choice', $objectType);
            $complexTypeAttributeGroup = $this->query('./xsd:attributeGroup', $objectType);
            $complexTypeAnyAttribute = $this->query('./xsd:anyAttribute', $objectType);*/

        }
        return $objectProperties->childNodes;
    }
    
    protected function selectProperties($nodeList, $mergeNode)
    {
        $nodeListLength = $nodeList->length;
        for($i=0; $i<$nodeListLength; $i++) {
            $node = $nodeList->item($i);
            if($ref = $node->getRef()) {
                if($node->tagName == 'xsd:attribute') $node = $this->getRefAttribute($ref);
                if($node->tagName == 'xsd:element') $node = $this->getRefElement($ref);
            }
            if(!$node->hasDatasource()) {
                $propertyNode = $node->cloneNode(true);
                $mergeNode->appendChild($propertyNode);
            }
        }
        //echo "<br/>selectAttributes() => " . $selectedProperties->length;
    }   
    
    //*************************************************************************
    // GET CHILD OBJECT (ATTRIBUTES / ELEMENTS WITH DIFFERENT SOURCE)
    //*************************************************************************
    protected function getObjectChildren($objectElement)
    {
        if(!$objectChildren = $this->XRefs->getXRefData($objectElement, 'children')) {
            $objectChildren = $this->schema->createElement('objectChildren');
            
            $objectType = $this->getType($objectElement);
            
            // type/sequence/element
            if($sequence = $this->getSequence($objectType)) {
                $sequenceElements = $this->getSequenceElements($sequence);
                $this->selectChildren($sequenceElements, $objectChildren);
            }

            /*$complexTypeSimpleContent = $this->query('./xsd:simpleContent', $objectType);
            $complexTypeComplexContent = $this->query('./xsd:complexContent', $objectType);
            $complexTypeGroup = $this->query('./xsd:group', $objectType);
            $complexTypeAll = $this->query('./xsd:all', $objectType);
            $complexTypechoice = $this->query('./xsd:choice', $objectType);
            $complexTypeAttributes = $this->query('./xsd:attribute', $objectType);

            $complexTypeAttributeGroup = $this->query('./xsd:attributeGroup', $objectType);
            $complexTypeAnyAttribute = $this->query('./xsd:anyAttribute', $objectType);*/
            
        }
        return $objectChildren->childNodes;
    }
   

    
    protected function selectChildren($nodeList, $mergeNode) 
    {
        $nodeListLength = $nodeList->length;
        for($i=0; $i<$nodeListLength; $i++) {
            $node = $nodeList->item($i);
            if($ref = $node->getRef()) {
                if($node->tagName == 'xsd:attribute') $node = $this->getRefAttribute($ref);
                if($node->tagName == 'xsd:element') $node = $this->getRefElement($ref);
            } 
            if($node->hasDatasource()) {
                $childNode = $node->cloneNode(true);
                $mergeNode->appendChild($childNode);
            }
        }
    }

}