<?php
class DataObjectController 
    extends SchemaController
{
    const NONE      = 0;
    const CREATE    = 1;
    const READ      = 2;
    const UPDATE    = 3;
    const DELETE    = 4;
    
    public $schema;
    public $dataObjectDocument;
    public $changeLog = array();
    public $dataAccessServices = array();
    private $messageController;
    private $messages = array();
    public $tmpdir;
    
    public function DataObjectController($xsdFile) 
    {
        // Document & objects
        require_once 'core/tests/class/DataObjectDocument.php';
        require_once 'core/tests/class/DataObject.php';
        require_once 'core/tests/class/DataObjectProperty.php';
        
        // Data access services
        require_once 'core/tests/class/DataAccessService_Database.php';
        require_once 'core/tests/class/DataAccessService_XML.php';
        
        // ChangeLog
        require_once 'core/tests/class/DataObjectLog.php';
       
        // Messages
        require_once 'core/tests/class/MessageController.php';
        require_once 'core/tests/class/Message.php';
        require_once 'core/tests/class/Exception.php';
        $this->messageController = new MessageController();
        $this->messageController->logLevel = Message::INFO;
        $this->messageController->debug = true;
        
        $this->messageController->loadMessageFile(
            $_SESSION['config']['corepath'] 
                . '/core/xml/DataObjectController_Messages.xml'
        );
        
        $this->schema = new DOMDocument();
        $this->schema->load($xsdFile);
        $this->processInclusions($this->schema);
        
        parent::__construct($this->schema);
  
    }
    
    public function processInclusions($schema) 
    {
        $includes = $schema->getElementsByTagName('include');
        while($includes->length > 0) {
            $include = $includes->item(0);
            $schemaLocation = $include->getAttribute('schemaLocation');
            
            $includeSchema = new DOMDocument();
            $includeSchema->load($_SESSION['config']['corepath'] . $schemaLocation);
            $this->processInclusions($includeSchema);
            $schemaContents = $includeSchema->documentElement->childNodes;
            for($j=0; $j<$schemaContents->length; $j++) {
                $importNode = $schemaContents->item($j);
                $importedNode = $this->schema->importNode($importNode, true);
                $this->schema->documentElement->appendChild($importedNode);
            }

            $include->parentNode->removeChild($include);
        }
    }
    
   
    //*************************************************************************
    // PUBLIC OBJECT HANDLING FUNCTIONS
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
        $this->dataObjectDocument = new DataObjectDocument();
        $rootNode = $this->query('/xsd:schema/xsd:element[@das:module != ""]')->item(0);
        $rootObject = $this->dataObjectDocument->createDataObject($rootNode->getAttribute('name'));
        $this->dataObjectDocument->appendChild($rootObject);
        
        $objectElement = $this->getObjectElement($objectName);
        
        $this->readDataObject(
            $objectElement, 
            $rootObject, 
            $key = array(), 
            $filter, 
            $sort, 
            $order
        );
        
        return $this->dataObjectDocument->documentElement;
    
    }
    
    public function read($objectName, $key)
    {
        $this->dataObjectDocument = new DataObjectDocument();
        $objectElement = $this->getObjectElement($objectName);
        $this->readDataObject($objectElement, $this->dataObjectDocument, $key);
        return $this->dataObjectDocument->documentElement;
    }
    
    public function save($dataObjectDocument=false)
    {
        $rootDataObject = $this->dataObjectDocument->documentElement;
        $this->saveDataObject($rootDataObject);   
    }
    
    public function load($xml)
    {
        if(!$this->dataObjectDocument) $this->dataObjectDocument = new DataObjectDocument();
        $this->dataObjectDocument->loadXML($xml);
        return $this->dataObjectDocument->documentElement;
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
    // PRIVATE OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    private function getDataObjectPrototype($objectElement)
    {
        $objectName = $objectElement->getAttribute('name');
        if(!isset($this->dataObjectPrototypes[$objectName])) {
            $this->dataObjectPrototypes[$objectName] = $this->createDataObjectPrototype($objectElement);
        }
        return $this->dataObjectPrototypes[$objectName];
    }
    
    private function createDataObjectPrototype($objectElement)
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
    
    private function readDataObject(
        $objectElement,
        $parentObject,
        $key=array(), 
        $filter=false, 
        $sort=array(),
        $order=false
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
    
    private function saveDataObject($dataObject)
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
            //echo "<br/>Found $childObjectsLength children of $dataObject->tagName named $childName";
            for($j=0; $j<$childObjectsLength; $j++) {
                $childObject = $childObjects[$j];
                //echo "<br/>Processing child of $dataObject->tagName named $childName (".$childObject->tagName.") #$j";
                $this->saveDataObject($childObject);
            }
        }
        
    }
    
    //*************************************************************************
    // PRIVATE SCHEMA QUERY FUNCTIONS
    //*************************************************************************
    private function getDataAccessService($objectElement)
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
    
    private function createDataAccessService($sourceName, $sourceNode) 
    {
        switch($sourceNode->getAttribute('type')) {
        case 'database':
            $this->dataAccessServices[$sourceName] = new DataAccessService_Database($this->document);
            $this->dataAccessServices[$sourceName]->connect($sourceNode);
            break;
        case 'xml':
            break;
        } 
    }

}