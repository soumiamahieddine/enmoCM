<?php

class DataObjectController extends DOMDocument
{
    const NONE      = 0;
    const CREATE    = 1;
    const READ      = 2;
    const UPDATE    = 3;
    const DELETE    = 4;
    
    private $schema;
    public $prototypes = array();
    public $dataAccessServices = array();
    private $messageController;
    private $messages = array();
    
    public function dataObjectController() 
    {
        // DataObject classes
        require_once 'core/tests/class/DataObjectSchema.php';
        require_once 'core/tests/class/DataObjectArray.php';
        require_once 'core/tests/class/DataObject.php';
        require_once 'core/tests/class/DataObjectProperty.php';
        
        require_once 'core/tests/class/DataAccessService_Abstract.php';
        require_once 'core/tests/class/DataAccessService_Database.php';
        require_once 'core/tests/class/DataAccessService_XML.php';
        
        // ChangeLog classes
        require_once 'core/tests/class/DataObjectChangeLog.php';
        require_once 'core/tests/class/DataObjectChange.php';
        
        // Validator classes
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
            }
            
        }
        
        // Relations
        $dasRelations = $this->schema->getRelations();
        for($i=0; $i<count($dasRelations); $i++) {
            $dasRelation = $dasRelations[$i];
            //echo "<br/> Add relation between " .$dasRelation->{'parent'} ." and ". $dasRelation->{'child'};
            $this->dataAccessServices[$dasSource->name]->addRelation(
                $dasRelation->{'parent'},
                $dasRelation->{'child'}, 
                $dasRelation->{'parent-keys'}, 
                $dasRelation->{'child-keys'},
                $dasRelation->name
            );
        }
        
        // Make prototypes of root objects
        $objectSchemas = $this->schema->getObjectSchemas();
        for($i=0; $i<$objectSchemas->length; $i++) {
            $objectSchema = $objectSchemas->item($i);
            $this->prototypeDataObject($objectSchema);
        }
       
    }
    
    //*************************************************************************
    // PUBLIC OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    /**************************************************************************
    ** public create
    **
    ** @description : 
    ** Creates a new empty DataObject from a root element name
    **
    ** @param (string) $objectName : Name of a root element definition
    **
    ** @return (object) new DataObject / DataObjectArray
    */
    public function create($objectName) 
    {
        //echo "<br/><br/><b> createDataObject($rootTypeName)</b>"; 
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dataObject = $this->instanciateDataObject($objectSchema);
        $dataObject->beginLogging();
        $dataObject->logCreation();
        
        return $dataObject;      
    }
    
    /**************************************************************************
    ** public read
    **
    ** @description : 
    ** Read a DataObject from with a data source
    **
    ** @param (string) $objectName : Name of a root element definition
    **
    ** @return (object) loaded DataObject / DataObjectArray
    */
    public function read($objectName)
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dataObject = $this->instanciateDataObject($objectSchema);
        $this->loadDataObject($dataObject);
        $dataObject->beginLogging();
        $dataObject->logRead();
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
        $objectSchema = $this->schema->getSchemaElement($dataObject->schemaPath);
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
    
    public function save($dataObject) 
    {
        $schemaPath = $dataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        $dataSourceName = $objectSchema->{'das:source'};
        $dataSource = $this->dataAccessServices[$dataSourceName];
        try {
            $dataSource->saveData($dataObject);
        } catch (maarch\Exception $e) {
            throw $e;
        }
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
    
    public function serialize($dataObject)
    {
        return serialize($dataObject);
    }
    
    public function unserialize($serializedDataObject)
    {
        $dataObject = unserialize($serializedDataObject);
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
    /**************************************************************************
    ** private instanciateDataObject
    **
    ** @description : 
    ** Creates a new instance of DataObject or DataObjectArray including
    **  - DataObjectProperties
    **  - Children (instance of data objects or array data object)
    **
    ** @param (string) $schemaPath : Path to object in schema
    **
    ** @return (object) new DataObject / DataObjectArray
    */
    private function instanciateDataObject($objectSchema, $inlineObjectSchema=false)
    {
        $schemaPath = $objectSchema->getNodePath();
        
        if($inlineObjectSchema) {
            if($inlineObjectSchema->isDataObjectArray()) {
                $arraySchemaPath = $inlineObjectSchema->getNodePath();
                $dataObject = new DataObjectArray($objectSchema->name, $schemaPath, $arraySchemaPath);
            } else {
                $this->prototypeDataObject($objectSchema);
                $serializedDataObject = $this->serialize($this->prototypes[$schemaPath]);
                $dataObject = $this->unserialize($serializedDataObject);
            }
        } else {
            $serializedDataObject = $this->serialize($this->prototypes[$schemaPath]);
            $dataObject = $this->unserialize($serializedDataObject);
        }
        return $dataObject;
    }
     
    private function prototypeDataObject($objectSchema)
    {
        //$objectSchema = $this->schema->getSchemaElement($schemaPath);
        $schemaPath = $objectSchema->getNodePath();
        //echo "<br/>Create prototype object with $objectSchema->name";
        $prototypeDataObject = new DataObject($objectSchema->name, $schemaPath);
        
        // Set Das Source
        //*********************************************************************
        $das = $this->getDataAccessService($objectSchema);
        if($das) {
            $dasTable = $das->addTable($objectSchema->name);
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
        }
        // Create Properties and children
        // ******************************************************************** 
        $childElements = $objectSchema->getChildElements();
        //echo "<br/>   Object has $childElements->length properties/children";
        for($i=0; $i<$childElements->length;$i++) {
            $inlineChildElement = $childElements->item($i);
            $childElement = $inlineChildElement->getRefElement();
            $childPath = $childElement->getNodePath();
            $childName = $childElement->name;
            $childType = $childElement->getType();
            if ($childType->tagName == 'xsd:simpleType') {
                // DEFAULT and FIXED
                if($childElement->{'default'}) {
                    $childValue = $childElement->{'default'};
                }
                else if($childElement->fixed) {
                    $childValue = $childElement->fixed;
                } else {
                    $childValue = false;
                }
                //echo "<br/>    Adding property '$childName'";
                $dataObjectProperty = new DataObjectProperty($childName, $childPath, $childValue);
                $prototypeDataObject->$childName = $dataObjectProperty;
                
                // Set Das property
                //*************************************************************
                if($das) {
                    $enclose = $this->schema->isEnclosedType($childType);
                    $dasColumn = $dasTable->addColumn($childName, $childType->name, $enclose);
                    if($childElement->{'default'}) {
                        $dasColumn->{'default'} = $childElement->{'default'};
                    }
                    if($childElement->{'fixed'}) {
                        $dasColumn->{'fixed'} = $childElement->{'fixed'};
                    }
                    if(strtolower($childElement->{'nillable'}) === 'true') {
                        $dasColumn->nillable = true;
                    }
                }
            }
            if ($childType->tagName == 'xsd:complexType') {
                $childDataObject = $this->instanciateDataObject($childElement, $inlineChildElement);
                $prototypeDataObject->$childName = $childDataObject;
            }
        }
        $this->prototypes[$schemaPath] = $prototypeDataObject;
    }
    
    /**************************************************************************
    ** public createDataObject
    **
    ** @description : 
    ** Creates a new DataObject or DataObjectArray from a root element name
    **  - DataObjectProperties
    **  - Children (instance of data objects or array data object)
    **
    ** @param (string) $rootTypeName : Name of a root element definition
    **
    ** @return (object) new DataObject / DataObjectArray
    */
    private function loadDataObject($dataObject) 
    {      
        try {
            $objectDatas = $this->getData($dataObject);
        } catch (maarch\Exception $e) {
            throw $e;
        }
        
        $objectData = $objectDatas[0];
        $this->loadProperties($dataObject, $objectData);
        $this->loadChildren($dataObject);
        
    }
    
    private function loadDataObjectArray($arrayDataObject)
    {
        try {
            $objectDatas = $this->getData($arrayDataObject);
        } catch (maarch\Exception $e) {
            throw $e;
        }
        $schemaPath = $arrayDataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        for($i=0; $i<count($objectDatas); $i++) {
            $dataObject = $this->instanciateDataObject($objectSchema);
            $dataObject->beginLogging();
            $dataObject->logRead();
            $arrayDataObject->append($dataObject);  
            $objectData = $objectDatas[$i];            
            $this->loadProperties($dataObject, $objectData);
            $this->loadChildren($dataObject);
        }
    }
    
    private function loadProperties($dataObject, $objectData)
    {
        $properties = $dataObject->properties;
        for($i=0; $i<count($properties); $i++) {
            $property = $properties[$i];
            $propertyName = $property->name;
            if(is_array($objectData)) {
                $propertyValue = $objectData[$propertyName];
            } elseif(is_object($objectData)) {
                $propertyValue = $objectData->$propertyName;
            }
            $property->setValue($propertyValue);
        }
    }
    
    
    private function loadChildren($dataObject) 
    {
        $childrenObjects = $dataObject->children;
        for($i=0; $i<count($childrenObjects); $i++) {
            $childObject = $childrenObjects[$i];
            if($childObject->isDataObjectArray) {
                $this->loadDataObjectArray($childObject);
            } else {
                $this->loadDataObject($childObject);
            }
        }
    }
    
    //*************************************************************************
    // PRIVATE DAS FUNCTIONS
    //*************************************************************************
    private function getDataAccessService($objectSchema) 
    {
        $dasSourceName = $objectSchema->getSourceName();
        if(!$dasSourceName) return;
        $Das = $this->dataAccessServices[$dasSourceName];
        return $Das;
    }
    
    private function getData($dataObject) 
    {
        $schemaPath = $dataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        $das = $this->getDataAccessService($objectSchema);
        if(!$das) return;
        return $das->getData($dataObject);
    }
    
    //*************************************************************************
    // Web Service Object (properties/children - no method)
    //*************************************************************************
    public function loadFromObject($stdObject, $objectName, $mode)
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dataObject = $this->instanciateDataObject($objectSchema);
        $this->loadDataObjectFromObject($dataObject, $stdObject, $mode);
        $dataObject->beginLogging();
        if($mode == self::CREATE) $dataObject->logCreation();
        if($mode== self::READ) $dataObject->logRead();
        return $dataObject;
    }
    
    private function loadDataObjectFromStdObject($dataObject, $stdObject, $mode) 
    {      
        $this->loadPropertiesFromStdObject($dataObject, $stdObject);
        $this->loadChildrenFromStdObject($dataObject, $stdObject, $mode);
    }
    
    private function loadDataObjectArrayFromStdObject($arrayDataObject, $stdArray, $mode)
    {
        $schemaPath = $arrayDataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        for($i=0; $i<count($stdArray); $i++) {
            $stdObject = $stdArray[$i];
            $dataObject = $this->instanciateDataObject($objectSchema);
            $dataObject->beginLogging();
            if($mode == self::CREATE) $dataObject->logCreation();
            if($mode== self::READ) $dataObject->logRead();
            $arrayDataObject->append($dataObject);          
            $this->loadPropertiesFromStdObject($dataObject, $stdObject);
            $this->loadChildrenFromStdObject($dataObject, $stdObject, $mode);
        }
    }
     
    private function loadPropertiesFromStdObject($dataObject, $stdObject)
    {
        $propertiesObjects = $dataObject->properties;
        for($i=0; $i<count($propertiesObjects); $i++) {
            $propertyObject = $propertiesObjects[$i];
            $propertyName = $propertyObject->name;
            $propertyValue = $stdObject->{$propertyName};
            $propertyObject->setValue($propertyValue);
        }
    }
    
    private function loadChildrenFromStdObject($dataObject, $stdObject, $mode) 
    {
        $childrenObjects = $dataObject->children;
        for($i=0; $i<count($childrenObjects); $i++) {
            $childObject = $childrenObjects[$i];
            $childName = $childObject->name;
            $childStdObject = $stdObject->{$childName};
            if($childObject->isDataObjectArray) {
                $this->loadDataObjectArrayFromStdObject($childObject, $childStdObject, $mode);
            } else {
                $this->loadDataObjectFromStdObject($childObject, $childStdObject, $mode);
            }
        }
    }
      
}