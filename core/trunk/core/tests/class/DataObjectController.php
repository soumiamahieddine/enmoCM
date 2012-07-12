<?php
class DataObjectController 
{
    const NONE      = 0;
    const CREATE    = 1;
    const READ      = 2;
    const UPDATE    = 3;
    const DELETE    = 4;
    
    private $schema;
    public $document;
    public $prototype;
    public $changeLog = array();
    public $dataAccessServices = array();
    private $messageController;
    private $messages = array();
    
    public function dataObjectController() 
    {
        // Schema
        require_once 'core/tests/class/DataObjectSchema.php';
        
        // Document & objects
        require_once 'core/tests/class/DataObjectDocument.php';
        require_once 'core/tests/class/DataObject.php';
        
        // Data access services
        require_once 'core/tests/class/DataAccessService_Abstract.php';
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
        
    }
    
    public function loadSchema($xsdFile) 
    {
        $this->schema = new DataObjectSchema();
        $this->schema->loadSchema($xsdFile);
        $this->parseSchema();
    }
    
    //*************************************************************************
    // PUBLIC OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    public function createDocument() 
    {
        $this->document = new DataObjectDocument();
    }
    
    public function create($objectName) 
    {
        if(!$this->document) $this->createDocument();
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dataObject = $this->createDataObject($objectSchema);
        $this->document->appendChild($dataObject);
        $this->document->logChange(DataObjectChange::CREATE, $dataObject);
        return $dataObject;      
    }
    
    public function enumerate($listName) 
    {
        $this->createDocument();
        $listSchema = $this->schema->getObjectSchema($listName);
                
        $listDataObject = $this->createDataObject($listSchema);
        $this->document->appendChild($listDataObject);
        
        $childSchemas = $listSchema->getChildSchemas();
        for($j=0; $j<count($childSchemas);$j++) {
            $childSchema = $childSchemas[$j];
            $this->loadDataObject($childSchema, $listDataObject);
        }
        return $listDataObject;
    
    }
    
    public function read($objectName, $key)
    {
        $this->createDocument();
        $objectSchema = $this->schema->getObjectSchema($objectName);        
        $dataObject = $this->loadDataObject($objectSchema, $this->document, $key);
        return $dataObject;
    }
    
    public function save($dataObject) 
    {
        $objectSchema = $this->schema->getObjectSchema($dataObject->tagName);
        $das = $this->getDataAccessService($objectSchema);
        if(!$das) return;
        try {
            $das->saveData($dataObject);
        } catch (maarch\Exception $e) {
            throw $e;
        }
    }
    
    public function load($xml)
    {
        if(!$this->document) $this->createDocument();
        $this->document->loadXML($xml);
        return $this->document->documentElement;
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
        $XmlDocument = $dataObject->asXmlDocument();
        $XsdString = $this->schema->saveXML();
        libxml_use_internal_errors(true);
        if(!$XmlDocument->schemaValidateSource($XsdString)) {
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
    // PUBLIC DAS FUNCTIONS 
    //*************************************************************************
    public function setKey($objectName, $key) 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $das = $this->getDataAccessService($objectSchema);
        if(!$das) return;
        $dasTable = $das->getTable($objectName);
        $dasTable->setKey($key);
    }
    
    public function setOrder($objectName, $orderElements, $orderMode='ascending') 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $das = $this->getDataAccessService($objectSchema);
        if(!$das) return;
        $dasTable = $das->getTable($objectName);
        $dasTable->setOrder($orderElements, $orderMode);
    }
    
    public function setFilter($objectName, $filterValue) 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $das = $this->getDataAccessService($objectSchema);
        if(!$das) return;
        $dasTable = $das->getTable($objectName);
        $dasTable->setFilter($filterValue);
    }

    public function getKey($objectName) 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $das = $this->getDataAccessService($objectSchema);
        if(!$das) return;
        $dasTable = $das->getTable($objectName);
        return $dasTable->getKey();
    }
        
    //*************************************************************************
    // PRIVATE OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    private function createDataObject($objectSchema)
    {
        $prototypeXpath = new DOMXPath($this->prototype);
        $objectName = $objectSchema->getAttribute('name');
        $dataObjectPrototype = $prototypeXpath->query('//' . $objectName)->item(0);
        $dataObject = $this->document->importNode($dataObjectPrototype,true);
        
        return $dataObject;
    }
    
    private function loadDataObject(
        $objectSchema, 
        $parentObject, 
        $key=false, 
        $filter=false, 
        $order=false, 
        $limit=500) 
    {      
        try {
            if($das = $this->getDataAccessService($objectSchema)) {
                $das->loadData($objectSchema, $parentObject, $key);
                $this->document->logChange(DataObjectChange::READ, $parentObject);
            } 
            $dataObjects = $parentObject->childNodes;
            $childSchemas = $objectSchema->getChildSchemas();
            for($j=0; $j<count($childSchemas);$j++) {
                $childSchema = $childSchemas[$j];
                for($i=0; $i<$dataObjects->length; $i++) {
                    $dataObject = $dataObjects->item($i);
                    $this->loadDataObject($childSchema, $dataObject);
                    $this->document->logChange(DataObjectChange::READ, $dataObject);
                }
            }
            
        } catch (maarch\Exception $e) {
            throw $e;
        }
        return $dataObject;
    }
    
    //*************************************************************************
    // PRIVATE DAS FUNCTIONS
    //*************************************************************************
    private function parseSchema()
    {
        // Data types
        $dasTypes = $this->schema->getDatatypes();
        // Data sources
        $dasSources = $this->schema->getSources();
        for($i=0; $i<count($dasSources); $i++) {
            $dasSource = $dasSources[$i];
            $dasName = $dasSource->getAttribute('name');
            $dasType = $dasSource->getAttribute('type');
            
            switch($dasType) {
            case 'database':
                //$options = $this->schema->getSourceOptions($dasSource, $dasSource->driver);
                $this->dataAccessServices[$dasName] = 
                    new DataAccessService_Database(
                        $dasSource->getAttribute('name'),
                        $dasSource->getAttribute('driver'), 
                        $dasSource->getAttribute('host'),  
                        $dasSource->getAttribute('port'), 
                        $dasSource->getAttribute('dbname'),
                        $dasSource->getAttribute('user'), 
                        $dasSource->getAttribute('password')
                    );
                
                for($i=0; $i<count($dasTypes); $i++) {
                    $dasType = $dasTypes[$i];
                    $dasTypeName = $dasType->getAttribute('name');
                    $dasTypeSqltype = $this->schema->getDatatypeSqltype($dasType, $dasSource->getAttribute('driver'));
                    if($dasTypeSqltype) {
                        $this->dataAccessServices[$dasName]->addDatatype(
                            $dasTypeName,
                            $dasTypeSqltype->nodeValue,
                            $dasType->getAttribute('das:enclosed'));
                    }
                }
                break;
            
            case 'xml':
                $this->dataAccessServices[$dasName] = 
                    new DataAccessService_XML(
                        $dasName,
                        $dasSource->getAttribute('file')
                    );
                break;
            
            case 'include':
                $this->dataAccessServices[$dasName] = 
                    new DataAccessService_Include(
                        $dasName,
                        $dasSource->getAttribute('parse')
                    );
                break;
            }
            
        }
        
        // Fill DataAccessServices for object definitions and make prototypes
        $this->prototype = new DOMDocument();
        $prototype = $this->prototype->createElement('prototype');
        $this->prototype->appendChild($prototype);
        
        $objectSchemas = $this->schema->getObjectSchemas();
        for($i=0; $i<$objectSchemas->length; $i++) {
            $objectSchema = $objectSchemas->item($i);
            $this->parseObjectSchema($objectSchema);
        }
    }
    
    private function parseObjectSchema($objectSchema)
    {
        // Create prototype
        //*********************************************************************
        $objectName = $objectSchema->getAttribute('name');
        $protoDataObject = $this->prototype->createElement($objectName);
        $this->prototype->documentElement->appendChild($protoDataObject);
        
        // Load associated Das
        //*********************************************************************
        $das = $this->getDataAccessService($objectSchema);
        if(!$das) return;
        
        // Add table
        //*********************************************************************
        $tableName = $objectSchema->getTableName();
        $dasTable = $das->addTable($tableName);
        if($objectSchema->hasAttribute('das:key-columns')) {
            $dasTable->addPrimaryKey(
                $objectSchema->getAttribute('das:key-columns')
            );
        }
        if($objectSchema->hasAttribute('das:filter-columns')) {
            $dasTable->addFilter(
                $objectSchema->getAttribute('das:filter-columns')
            );
        }
        
        // Add Relations
        //*********************************************************************
        $dasRelation = $objectSchema->getRelation();
        if($dasRelation) {
            //echo "<br/> Add relation between " .$dasRelation->{'parent'} ." and ". $dasRelation->{'child'};
            $das->addRelation(
                $dasRelation->getAttribute('parent'),
                $dasRelation->getAttribute('child'), 
                $dasRelation->getAttribute('parent-keys'), 
                $dasRelation->getAttribute('child-keys'),
                $dasRelation->getAttribute('name')
            );
        }
        
        // Add columns
        //*********************************************************************
        $objectType = $objectSchema->getType();
        $columnElements = $objectType->getColumnElements();
        for($i=0; $i<count($columnElements);$i++) {
            $columnElement = $columnElements[$i];
            $columnType = $columnElement->getType();
            $columnName = $columnElement->getColumnName();
            $columnValue = false;
            $dasColumn = $dasTable->addColumn($columnName, $columnType->getAttribute('name'));
            if($columnName != $columnElement->getAttribute('name')) {
                $dasColumn->alias = $columnElement->getAttribute('name');
            }
            if($columnElement->hasAttribute('default')) {
                $defaultValue = $columnElement->getAttribute('default');
                $dasColumn->{'default'} = $defaultValue;
                $columnValue = $defaultValue;
            }
            if($columnElement->hasAttribute('fixed')) {
                $fixedValue = $columnElement->getAttribute('fixed');
                $dasColumn->{'fixed'} = $fixedValue;
                $columnValue = $fixedValue;
            }
            if(strtolower($columnElement->getAttribute('nillable')) === 'false') {
                $dasColumn->nillable = false;
            } else {
                $dasColumn->nillable = true;
            }
            
            $columnNode = $this->prototype->createElement($columnName, $columnValue);
            $protoDataObject->appendChild($columnNode);
        }

        // Process children
        //*********************************************************************
        $childSchemas = $objectSchema->getChildSchemas();
        for($i=0; $i<count($childSchemas);$i++) {
            $childSchema = $childSchemas[$i]; 
            $childName = $childSchema->getAttribute('name');
            $childNode = $this->prototype->createElement($childName);
            $protoDataObject->appendChild($childNode);
            
            $prototypeXpath = new DOMXPath($this->prototype);
            if($prototypeXpath->query('//' . $childName)->length == 0) {
                $this->parseObjectSchema($childSchema);
            }
        }
    }
    
    private function getDataAccessService($objectSchema) 
    {
        $dasSourceName = $objectSchema->getSourceName();
        if(!$dasSourceName) return;
        $Das = $this->dataAccessServices[$dasSourceName];
        return $Das;
    }  
    
}