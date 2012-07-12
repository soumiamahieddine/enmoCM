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
    public $changeLog;
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
        
        $this->createDataAccessServices();
    }
    
    //*************************************************************************
    // PUBLIC OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    public function createDocument() {
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
        if($objectSchema->{'das:validation'}) {
            include_once $objectSchema->{'das:validation'};
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
        $dataObject = $this->document->createElement($objectSchema->name);
        $objectType = $objectSchema->getType();
        $columnElements = $objectType->getColumnElements();
        for($i=0; $i<count($columnElements);$i++) {
            $columnElement = $columnElements[$i];
            $columnName = $columnElement->getColumnName();
            if($columnElement->{'default'}) {
                $columnValue = $columnElement->{'default'};
            }
            else if($columnElement->fixed) {
                $columnValue = $columnElement->fixed;
            } else {
                $columnValue = false;
            }
            $columnNode = $this->document->createElement($columnName, $columnValue);
            $dataObject->appendChild($columnNode);
        }
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
    private function createDataAccessServices()
    {
        // Data types
        $dasTypes = $this->schema->getDatatypes();
        // Data sources
        $dasSources = $this->schema->getSources();
        for($i=0; $i<count($dasSources); $i++) {
            $dasSource = $dasSources[$i];
            switch($dasSource->type) {
            case 'database':
                //$options = $this->schema->getSourceOptions($dasSource, $dasSource->driver);
                $this->dataAccessServices[$dasSource->name] = 
                    new DataAccessService_Database(
                        $dasSource->name,
                        $dasSource->driver, 
                        $dasSource->host,  
                        $dasSource->port, 
                        $dasSource->dbname,
                        $dasSource->user, 
                        $dasSource->password
                    );
                
                for($i=0; $i<count($dasTypes); $i++) {
                    $dasType = $dasTypes[$i];
                    $dasTypeSqltype = $this->schema->getDatatypeSqltype($dasType, $dasSource->driver);
                    if($dasTypeSqltype) {
                        $this->dataAccessServices[$dasSource->name]->addDatatype(
                            $dasType->name,
                            $dasTypeSqltype->nodeValue,
                            $dasType->{'das:enclosed'});
                    }
                }
                break;
            
            case 'xml':
                $this->dataAccessServices[$dasSource->name] = 
                    new DataAccessService_XML(
                        $dasSource->name,
                        $dasSource->file
                    );
                break;
            
            case 'include':
                $this->dataAccessServices[$dasSource->name] = 
                    new DataAccessService_Include(
                        $dasSource->parse
                    );
                break;
            }
            
        }
        
        // Fill DataAccessServices for object definitions
        $objectSchemas = $this->schema->getObjectSchemas();
        for($i=0; $i<$objectSchemas->length; $i++) {
            $objectSchema = $objectSchemas->item($i);
            $this->loadDataAccessServices($objectSchema);
        }
    }
    
    private function loadDataAccessServices($objectSchema)
    {
        $das = $this->getDataAccessService($objectSchema);
        if(!$das) return;
        
        // Add table
        //*********************************************************************
        $tableName = $objectSchema->getTableName();
        $dasTable = $das->addTable($tableName);
        if($objectSchema->{'das:key-columns'}) {
            $dasTable->addPrimaryKey(
                $objectSchema->{'das:key-columns'}
            );
        }
        if($objectSchema->{'das:filter-columns'}) {
            $dasTable->addFilter(
                $objectSchema->{'das:filter-columns'}
            );
        }
        
        // Add Relations
        //*********************************************************************
        $dasRelation = $objectSchema->getRelation();
        if($dasRelation) {
            //echo "<br/> Add relation between " .$dasRelation->{'parent'} ." and ". $dasRelation->{'child'};
            $das->addRelation(
                $dasRelation->{'parent'},
                $dasRelation->{'child'}, 
                $dasRelation->{'parent-keys'}, 
                $dasRelation->{'child-keys'},
                $dasRelation->name
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
            $dasColumn = $dasTable->addColumn($columnName, $columnType->name);
            if($columnName != $columnElement->name) {
                $dasColumn->alias = $columnElement->name;
            }
            if($columnElement->{'default'}) {
                $dasColumn->{'default'} = $columnElement->{'default'};
            }
            if($columnElement->{'fixed'}) {
                $dasColumn->{'fixed'} = $columnElement->{'fixed'};
            }
            if(strtolower($columnElement->{'nillable'}) === 'true') {
                $dasColumn->nillable = true;
            }
        }

        // Process children
        //*********************************************************************
        $childSchemas = $objectSchema->getChildSchemas();
        for($i=0; $i<count($childSchemas);$i++) {
            $childSchema = $childSchemas[$i];  
            $this->loadDataAccessServices($childSchema);
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