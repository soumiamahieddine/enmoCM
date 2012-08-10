<?php

// Schema
require_once 'core/tests/class/Schema.php';

// Document & objects
require_once 'core/tests/class/DataObject.php';

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
    private $XRefs = array();
    protected $messageController;
    protected $messages = array();
    
     
    //*************************************************************************
    // CONSTRUCTOR
    //*************************************************************************
    public function DataObjectController() 
    {
        //$this->XRefs = new SchemaXRefs();
        
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
        $schema = new Schema();
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
        $objectElement = $this->getElementByName($objectName);
        $keyFields = $this->getKeyFields($objectElement);
        $return = array();
        for($i=0; $i<$keyFields->length; $i++) {
            $keyField = $keyFields->item($i);
            $keyName = str_replace(
                "@",
                "",
                $keyField->getAttribute('xpath')
            );
            $return[] = $keyName;  
        }
        return $return;
    }
    
    public function getFilterProperties($objectName)
    {
        $objectElement = $this->getElementByName($objectName);
        $filters = explode(' ', $objectElement->getFilter());
        $return = array();
        for($i=0; $i<count($filters); $i++) {
            $filter = $filters[$i];
            $propertyName = str_replace(
                "@", 
                "", 
                $filter->getAttribute('xpath')
            );
            $return[] = $propertyName;  
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
        
        $objectElement = $this->getElementByName($objectName);
        $dataObject = $this->createDataObject($objectElement, $dataObjectDocument);
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
        
        $rootElement = $this->query(
            '/xsd:schema/xsd:element[@das:module != ""]'
            )->item(0);
        $rootName = $rootElement->getName();
        $rootDataObject = $dataObjectDocument->createElement($rootName);
        $dataObjectDocument[] = $rootDataObject;
        
        $objectElement = $this->getElementByName($objectName);
        
        $this->readDataObject(
            $objectElement,
            $rootDataObject,
            $dataObjectDocument,
            $key,
            $filter,
            $sort, 
            $order,
            $limit
        );
        
        return $dataObjectDocument->documentElement;
    }
    
    public function read(
        $objectName, 
        $key
    )
    {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        
        $objectElement = $this->getElementByName($objectName);
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
        $objectElement = $this->getElementByName($dataObject->tagName);
        $dataObjectDocument = $dataObject->ownerDocument;
        $this->saveDataObject(
            $objectElement, 
            $dataObject, 
            $dataObjectDocument
        );   
    }
    
    public function load($xml)
    {
        $dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocuments[] = $dataObjectDocument;
        $dataObjectDocument->loadXML($xml);
        $dataObject = $dataObjectDocument->documentElement;
        return $dataObject;
    }
  
    public function delete($objectName, $key)
    {
        $objectElement = $this->getElementByName($objectName);
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
    protected function createDataObject($objectElement, $dataObjectDocument)
    {
        $objectName = $objectElement->getName();
        $dataObject = $dataObjectDocument->createElement($objectName);
        
        // Add properties
        $objectProperties = $this->getObjectProperties($objectElement);
        $objectPropertiesLength = count($objectProperties);
        for($i=0; $i<$objectPropertiesLength; $i++) {
            $propertyNode = $objectProperties[$i];
            $propertyName = $propertyNode->getName();
            $propertyValue = null;            
            if($propertyNode->hasAttribute('fixed')) {
                $propertyValue = $propertyNode->getAttribute('fixed');
            } elseif($propertyNode->hasAttribute('default')) {
                $propertyValue = $propertyNode->getAttribute('default');
            }
            switch($propertyNode->tagName) {
            case 'xsd:attribute':
                $dataObjectPrototype->setAttribute($propertyName, $propertyValue);
                break;
            case 'xsd:element':
                $property = $this->XRefs->createElement($propertyName, $columnValue);
                $dataObjectPrototype->appendChild($property);
            }            
        }
        
        return $dataObject;
    }
    
    protected function readDataObject(
        $objectElement,
        $parentObject,
        $dataObjectDocument,
        $key=false,
        $filter=false,
        $sort=false,
        $order='ascending',
        $limit=99999999
        ) 
    {      
     
        try {
            // Process object & Properties
            $objectName = $objectElement->getName();
            if($dataAccessService = 
                $this->getDataAccessService($objectElement)
            ) {
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
                $dataObject = 
                    $dataObjectDocument->createElement($objectName);
                $dataObject->logRead();
                $parentObject->appendChild($dataObject);
            }
            // Process child objects
            $objectChildren = $this->getObjectChildren($objectElement);
            $objectChildrenLength = count($objectChildren);
            for($i=0; $i<$objectChildrenLength; $i++) {
                $childElement = $objectChildren[$i];
                $dataObjects = $parentObject->getChildren($objectName);
                $dataObjectsLength = count($dataObjects);
                for($j=0; $j<$dataObjectsLength; $j++) {
                    $dataObject = $dataObjects[$j];
                    $this->readDataObject(
                        $childElement, 
                        $dataObject, 
                        $dataObjectDocument
                    );
                }    
            }
        } catch (maarch\Exception $e) {   
            throw $e;
        }
    }
    
    protected function saveDataObject(
        $objectElement, 
        $dataObject, 
        $dataObjectDocument
    )
    {
        
        $dataAccessService = $this->getDataAccessService($objectElement);
        
        if($dataAccessService) {
            $dataAccessService->saveData(
                $objectElement, 
                $dataObject, 
                $dataObjectDocument
            );
        } 
        
        $objectChildren = $this->getObjectChildren($objectElement);
        $objectChildrenLength = count($objectChildren);
        for($i=0; $i<$objectChildrenLength; $i++) {
            $childElement = $objectChildren[$i];
            $childName = $childElement->getName();
            $childObjects = $dataObject->$childName;
            $childObjectsLength = count($childObjects);
            for($j=0; $j<$childObjectsLength; $j++) {
                $dataObject = $childObjects[$j];
                $this->saveDataObject(
                    $childElement, 
                    $dataObject, 
                    $dataObjectDocument
                );
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
        return $this->getElementByName($objectName);
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
                    './xsd:annotation/xsd:appinfo/das:source', 
                    $node 
                )->length > 0
        ){
            return true;
        }
    
    }
    
    protected function getDataAccessService($node)
    {
        if($node->hasAttribute('das:source')) {
            $sourceName = $node->getAttribute('das:source');
            if(!isset($this->dataAccessServices[$sourceName])) {
                $sourceNode = $this->query(
                    '//das:source[@name="'
                    . $sourceName
                    . '"]'
                )->item(0);
                $this->createDataAccessService($sourceName, $sourceNode);
            }
        } elseif($sourceNode = $this->query(
                './xsd:annotation/xsd:appinfo/das:source', 
                $node
            )->item(0)
        ) {
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
            $this->dataAccessServices[$sourceName] = 
                new DataAccessService_Database();
            $this->dataAccessServices[$sourceName]->loadSchema($this->schema);
            $this->dataAccessServices[$sourceName]->connect($sourceNode);
            break;
        case 'xml':
            break;
        } 
    }

    protected function getElementByName($name)
    {
        $element = $this->query(
            '//xsd:element[@name = "'
            . $name
            . '"]'
        )->item(0);
        if(!$element) Die("Element $name is unknown");
        return $element;
    }
    
    protected function getAttributeByName($name)
    {
        $attribute = $this->query(
            '//xsd:attribute[@name = "'
            . $name
            . '"]'
        )->item(0);
        if(!$attribute) Die("Attribute $name is unknown");
        return $attribute;
    }
    
    protected function getPropertyByName($name)
    {
        $property = $this->query(
            '//xsd:attribute[@name = "'
            . $name
            . '"]'
            . ' | '
            . '//xsd:element[@name = "'
            . $name
            . '"]'
        )->item(0);
        if(!$property) Die("Property $name is unknown");
        return $property;
    
    }
    
    protected function getType($node)
    {
        //echo "<br/>Get type of $node->tagName " . $node->getAttribute('name');
        if(!$type = $this->getXRefs($node, 'type')) {
            if($typeName = $node->getTypeName()) { 
                if(substr($typeName, 0, 3) == 'xsd') {
                    $this->addBuiltInType($typeName);
                }
                $types = $this->query(
                    '//xsd:complexType[@name="'
                    . $typeName
                    . '"] | //xsd:simpleType[@name="'
                    . $typeName
                    . '"]'
                );
            } else {
                $typeName = "defined online";
                $types = $this->query(
                    './xsd:complexType | ./xsd:simpleType',
                    $node
                );
            }
            if($types->length == 0) die("Type $typeName not found for " . $node->tagName . " " . $node->getAttribute('name'));
            $type = $types->item(0);
            $this->addXRefs($node, 'type', $type);
        } 
        
        return $type;
    }
    
    protected function addBuiltInType($typeName)
    {
        $builtInType = $this->schema->createElement('xsd:simpleType');
        $builtInType->setAttribute('name', $typeName);
        
        // Define if enclosed or not
        $nonEnclosedTypes = array(
            'xsd:float',
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
            'xsd:boolean',
        );
        $enclosedTypes = array(
            'xsd:string',
                'xsd:normalizedString',
                    'xsd:token',
                        'xsd:language',
                        'xsd:name',
                            'xsd:NCName',
                                'xsd:ID',
                                'xsd:IDREF',
                                'xsd:ENTITY',
                        'xsd:NMTOKEN',
            'xsd:QNAME',
            'xsd:NOTATION',
            'xsd:date',
            'xsd:time',
            'xsd:datetime',
            'xsd:gYear',
            'xsd:gYearMonth',
            'xsd:gMonth',
            'xsd:gMonthDay',
            'xsd:gDay',
            'xsd:duration',
            'xsd:base64binary',
            'xsd:hexBinary',
            'xsd:anyURI',
        );
        
        if(!in_array($typeName, $nonEnclosedTypes)) {
            $builtInType->setAttribute('das:enclosed', 'true');
        }
        $this->schema->appendChild($builtInType);
    }
    
    protected function getView($element)
    {
        if($element->hasAttribute('das:view')) {
            $viewName = $element->getAttribute('das:view');
            $views = 
                $this->query('//xsd:annotation/xsd:appinfo/das:view[@name="'
                    . $viewName
                    . '"]');
            if($views->length == 0) die("Referenced view $viewName not found");
            return $views->item(0);
        } elseif($views = $this->query('./xsd:annotation/xsd:appinfo/das:view', $element)) {
            return $views->item(0);
        }
    }
    
    protected function getRefElement($refName)
    {
        $elements = $this->query(
            '/xsd:schema/xsd:element[@name="'
            . $refName
            . '"]'
        );
        if($elements->length == 0) die("Referenced element $refName not found");
        return $elements->item(0);
    }
    
    protected function getRefAttribute($refName)
    {
        $attributes = $this->query(
            '/xsd:schema/xsd:attribute[@name="'
            . $refName
            . '"]'
        );
        if($attributes->length == 0) die("Referenced attribute $refName not found");
        return $attributes->item(0);
    }
    
    protected function getKey($objectElement)
    {      
        $key = $this->query('./xsd:key', $objectElement);
        if($key->length == 0) return false;
        return $key->item(0);
    }
    
    protected function getKeyFields($objectElement) 
    {
        if($keyNode = $this->getKey($objectElement)) {
            $keyFields = $this->query('./xsd:field', $keyNode);
            return $keyFields;
        }
    }
    
    protected function getFilter($objectElement)
    {
        return $objectElement->getFilter();
    }
    
    protected function getRelation(
        $objectElement, 
        $dataObject
    ) {
        $relations = $this->query(
            '//xsd:annotation/xsd:appinfo/das:relation['
            . '@parent="' . $dataObject->tagName
            . '" and @child="' . $objectElement->getName()
            . '"]'
        );
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
        $sequenceElements = array();
        //any, choice, element, group, sequence
        $sequenceAny = $this->query('./xsd:any', $sequence);
        if($sequenceAny->length == 1) {
            $sequenceElements[] = $sequenceAny->item(0);   
        }
        /*$sequencechoice = $this->query('./xsd:choice', $sequence);
        $sequenceGroup = $this->query('./xsd:group', $sequence);
        $sequenceSequence = $this->query('./xsd:sequence', $sequence);*/
        
        $sequenceChildElements = $this->getElements($sequence);
        for($i=0; $i<$sequenceChildElements->length; $i++) {
            $sequenceElements[] = $sequenceChildElements->item($i);
        }
        return $sequenceElements;
    }    
    
    protected function getQuery($node) 
    {
        if($node->hasAttribute('das:query')) {
            $queryName = $node->getAttribute('das:query');
            $queryNode = $this->query(         
                '//das:query[@name="'
                . $queryName
                . '"]'
            )->item(0);
            return $queryNode;
        } elseif(
            $queryNode = $this->query(
                './xsd:annotation/xsd:appinfo/das:query'
                , $node
            )->item(0)
        ) {
            return $queryNode;
        }
    }
    
    
    //*************************************************************************
    // CROSSED REFERENCES
    //*************************************************************************
    protected function getXRefs($element, $queryTag) 
    {
        $XRefs = $this->XRefs[$element->tagName][$element->getName()][$queryTag];
        return $XRefs;
        
    }
    
    protected function addXRefs($element, $queryTag, $XRefsArray) 
    {
        $this->XRefs
            [$element->tagName][$element->getName()][$queryTag] = $XRefsArray;
    }
    
    protected function nodeListAsArray($nodeList)
    {
        $nodeArray = array();
        for($i=0; $i<$nodeList->length; $i++) {
            $nodeArray[] = $nodeList->item($i);
        }
        return $nodeArray;
    }
    
    //*************************************************************************
    // GET OBJECT PROPERTIES (ATTRIBUTES / ELEMENT WITH SAME SOURCE)
    //*************************************************************************
    protected function getObjectProperties($objectElement)
    {
        if(!$objectProperties = 
            $this->getXRefs($objectElement, 'objectProperties')
        ) {
            $objectProperties = array();
            $objectType = $this->getType($objectElement);
            // Attributes
            $attributeProperties = array();
            if($typeAttributes = $this->getAttributes($objectType)) {
                $attributes = $this->nodeListAsArray($typeAttributes);
                $attributeProperties = $this->selectProperties($attributes);
            }
            
            // Sequence
            $sequenceProperties = array();
            if($sequence = $this->getSequence($objectType)) {
                $sequenceElements = $this->getSequenceElements($sequence);
                $sequenceProperties = $this->selectProperties($sequenceElements);
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
                $attributeProperties,
                $sequenceProperties
            );
            $this->addXRefs($objectElement, 'objectProperties', $objectProperties);
        } 
        return $objectProperties;
    }
    
    protected function selectProperties($nodeArray)
    {
        $selectedProperties = array();
        $nodeArrayLength = count($nodeArray);
        for($i=0; $i<$nodeArrayLength; $i++) {
            $node = $nodeArray[$i];
            if($ref = $node->getRef()) {
                if($node->tagName == 'xsd:attribute') 
                    $node = $this->getRefAttribute($ref);
                if($node->tagName == 'xsd:element') 
                    $node = $this->getRefElement($ref);
            }
            if(!$node->hasDatasource()) {
                $selectedProperties[] = $node;
            }
        }
        //echo "<br/>selectProperties() => " . count($selectedProperties);
        return $selectedProperties;
    }   
    
    //*************************************************************************
    // GET CHILD OBJECT (ATTRIBUTES / ELEMENTS WITH DIFFERENT SOURCE)
    //*************************************************************************
    protected function getObjectChildren($objectElement)
    {
        if(!$objectChildren = 
            $this->getXRefs($objectElement, 'objectChildren')
        ) {
            $objectChildren = array();
            $objectType = $this->getType($objectElement);
            
            // type/sequence/element
            if($sequence = $this->getSequence($objectType)) {
                $sequenceElements = $this->getSequenceElements($sequence);
                $sequenceChildren = $this->selectChildren($sequenceElements);
            }

            /*$complexTypeSimpleContent = $this->query('./xsd:simpleContent', $objectType);
            $complexTypeComplexContent = $this->query('./xsd:complexContent', $objectType);
            $complexTypeGroup = $this->query('./xsd:group', $objectType);
            $complexTypeAll = $this->query('./xsd:all', $objectType);
            $complexTypechoice = $this->query('./xsd:choice', $objectType);
            $complexTypeAttributes = $this->query('./xsd:attribute', $objectType);

            $complexTypeAttributeGroup = $this->query('./xsd:attributeGroup', $objectType);
            $complexTypeAnyAttribute = $this->query('./xsd:anyAttribute', $objectType);*/
            
            $objectChildren = array_merge(
                $sequenceChildren
            );

            $this->addXRefs($objectElement, 'objectChildren', $objectChildren);
        }
        echo "<br/>getObjectChildren() => " . count($objectChildren);
        return $objectChildren;
    }
   
    protected function selectChildren($nodeArray) 
    {
        $selectedChildren = array();
        $nodeArrayLength = count($nodeArray);
        for($i=0; $i<$nodeArrayLength; $i++) {
            $node = $nodeArray[$i];
            if($ref = $node->getRef()) {
                if($node->tagName == 'xsd:attribute') 
                    $node = $this->getRefAttribute($ref);
                if($node->tagName == 'xsd:element') 
                    $node = $this->getRefElement($ref);
            } 
            if($node->hasDatasource()) {
                $selectedChildren[] = $node;
            }
        }
        return $selectedChildren;
    }

}