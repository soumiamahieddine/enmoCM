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
    public $dataObjectTypes = array();
    private $messageController;
    private $messages = array();
    public $tmpdir;
    
    public function DataObjectController($xsdFile) 
    {
        // Document & objects
        require_once 'core/tests/class/DataObjectDocument.php';
        require_once 'core/tests/class/DataObject.php';
        
        // Data access services
        require_once 'core/tests/class/DataAccessService_Database.php';
        require_once 'core/tests/class/DataAccessService_XML.php';
        
        // ChangeLog
        require_once 'core/tests/class/DataObjectChangeLog.php';
        require_once 'core/tests/class/DataObjectChange.php';
        $this->changeLog = new DataObjectChangeLog();
        
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
    
    public function processInclusions($Schema) 
    {
        $includes = $Schema->getElementsByTagName('include');
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
    public function createRoot($moduleName) 
    {
        $this->dataObjectDocument = new DataObjectDocument();
        $rootNodeName = $this->query('/xsd:schema/xsd:element[@das:module = "'.$moduleName.'"]/@name')->item(0)->nodeValue;
        $root = $this->dataObjectDocument->createElement($rootNodeName);
        $this->dataObjectDocument->appendChild($root);
        return $this->dataObjectDocument;
    }
    
    public function create($objectName) 
    {
        $dataObject = $this->createDataObject($objectName);
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
        $rootObject = $this->createDataObject($rootNode->getAttribute('name'));
        $this->dataObjectDocument->appendChild($rootObject);
        
        $objectNode = $this->query('/xsd:schema/xsd:element[@name = "'.$objectName.'"]')->item(0);
        $this->loadDataObject($objectNode, $rootObject, array(), $filter, $sort, $order);
        
        return $this->dataObjectDocument->documentElement;
    
    }
    
    public function readRoot($objectName, $key)
    {
        $this->dataObjectDocument = new DataObjectDocument();
        $objectNode = $this->query('/xsd:schema/xsd:element[@name = "'.$objectName.'"]')->item(0);
        $this->loadDataObject($objectNode, $this->dataObjectDocument, $key);
        return $this->dataObjectDocument->documentElement;
    }
    
    public function save($dataObject) 
    {
        
    }
    
    public function load($xml)
    {
        if(!$this->dataObjectDocument) $this->createDocument();
        $this->dataObjectDocument->loadXML($xml);
        return $this->dataObjectDocument->documentElement;
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
        $objectSchema = $this->schema->getObjectSchema($dataObject->tagName);
        if($objectSchema->hasAttribute('das:validation')) {
            include_once $objectSchema->getAttribute('das:validation');
        }
        
        // Validate against schema
        //*********************************************************************
        $XsdString = $this->document->saveXML();
        libxml_use_internal_errors(true);
        if(!$this->document->schemaValidateSource($XsdString)) {
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
        if(count($this->messages) > 0) return false;
        return true;
    }
    
    public function getMessages()
    {
        $messages = $this->messages;
        $this->messages = array();
        return $messages;
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
    
    //*************************************************************************
    // PRIVATE OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    private function createDataObject($typeName)
    {
        $Xsl = new DOMDocument();
        $Xslt = new XSLTProcessor();
        $Xsl->load('core/tests/class/XSL/DataObject_Create.xsl');
        $Xslt->importStylesheet($Xsl);
        $Xslt->setParameter('', 'typeName', $typeName);
        $dataObjectPrototype = $Xslt->transformToDoc($this->schema);
        $dataObject = $this->dataObjectDocument->importNode($dataObjectPrototype->documentElement,true);
        
        return $dataObject;
    }
    
    private function loadDataObject(
        $objectNode,
        $parentObject,
        $key=array(), 
        $filter=false, 
        $sort=array(),
        $order=false
        ) 
    {      
        try {
            
            //echo "<br/>Loading " . $objectNode->getAttribute('name');
            
            //echo "<br/>Getting source";             
            $dataAccessService = $this->getDataAccessService($objectNode);
            //echo "<br/>Source = " . print_r($dataAccessService,true);

            if($dataAccessService) {
                $dataAccessService->loadData(
                    $objectNode, 
                    $parentObject, 
                    $key,
                    $filter, 
                    $sort,
                    $order
                    );
                //$this->document->logChange(DataObjectChange::READ, $parentObject);
            } else {
                $dataObject = $this->createDataObject($objectName);
                $parentObject->appendChild($dataObject);
            }
            //echo "<br/>Result = " . htmlspecialchars($parentObject->saveXML());
            
            $childNodes = $this->getChildObjects($objectNode);
            $childNodesLength = count($childNodes);
            //echo "<br/>Found " . $childNodesLength. " child nodes";
            
            // Get children sources
            for($i=0; $i<$childNodesLength; $i++) {
                $childNode = $childNodes[$i];
                $newObjects = $parentObject->childNodes;
                $newObjectsLength = $newObjects->length;
                for($j=0; $j<$newObjectsLength; $j++) {
                    $newObject = $newObjects->item($j);
                    $this->loadDataObject($childNode, $newObject);
                }    
            }
        } catch (maarch\Exception $e) {   
            throw $e;
        }

    }
    
    //*************************************************************************
    // PRIVATE SCHEMA QUERY FUNCTIONS
    //*************************************************************************
    private function getDataAccessService($objectNode)
    {
        if($objectNode->hasAttribute('das:source')) {
            $sourceName = $objectNode->getAttribute('das:source');
            if(!isset($this->dataAccessServices[$sourceName])) {
                $sourceNode = $this->query('//das:source[@name="'.$sourceName.'"]')->item(0);
                $this->createDataAccessService($sourceName, $sourceNode);
            }
        } elseif($sourceNode = $this->query('./xsd:annotation/xsd:appinfo/das:source', $objectNode)->item(0)) {
            $sourceName = $objectNode->getAttribute('name');
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