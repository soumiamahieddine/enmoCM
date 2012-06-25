<?php

class dataObjectController extends DOMDocument
{

    private $schema;
    public $prototypes = array();
    public $dataAccessService_Database;
    private $dataAccessService_XML;
    private $dataObjectValidator;
    
    public function dataObjectController() 
    {
        require_once 'core/tests/class/DataObjectSchema.php';
        require_once 'core/tests/class/DataObjectArray.php';
        require_once 'core/tests/class/DataObject.php';
        require_once 'core/tests/class/DataObjectProperty.php';
        require_once 'core/tests/class/DataObjectChangeLog.php';
        
        require_once 'core/tests/class/DataAccessService_Database.php';
        $this->dataAccessService_Database = new dataAccessService_Database();
        require_once 'core/tests/class/DataAccessService_XML.php';
                
        require_once 'core/tests/class/DataObjectValidator.php';
        $this->dataObjectValidator = new DataObjectValidator();
        
    }
    
    public function loadSchema($xsdFile) 
    {
        $this->schema = new DataObjectSchema();
        $this->schema->loadSchema($xsdFile);
        
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
        if(!$objectSchema) die("<br/><b>Unable to find root element named $rootTypeName</b>");
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
        if(!$objectSchema) die("<br/><b>Unable to find root element named $rootTypeName</b>");
        $dataObject = $this->instanciateDataObject($objectSchema);
        $this->loadDataObject($dataObject);
        $dataObject->beginLogging();
        return $dataObject;
    }
    
    public function validate($dataObject) 
    {
        return $this->dataObjectValidator->validateDataObject($dataObject, $this->schema);
    }
    
    public function getValidationErrors()
    {
        return $this->dataObjectValidator->getErrors();
    }
    
    public function save($dataObject) 
    {
        $schemaPath = $dataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        switch($objectSchema->{'das:source'}) {
        case 'database':
            $this->dataAccessService_Database->saveData($dataObject);
            break;
            
        case 'xml':
            break;
        }
    }
    
    public function delete($dataObject)
    {
    
    }
    
    public function copy($dataObject)
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
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            $this->dataAccessService_Database->setKey($objectSchema->name, $key);
            break;
            
        case 'xml':
            break;
        }
    }
    
    public function setOrder($objectName, $orderElements, $orderMode='ASC') 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            $this->dataAccessService_Database->setOrder($objectSchema->name, $orderElements, $orderMode);
            break;
        case 'xml':
            break;
        }
    }
    
    public function setFilter($objectName, $filterValue) 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            //echo "<br/>Setting filter $filterValue for $objectSchema->name";
            $this->dataAccessService_Database->setFilter($objectSchema->name, $filterValue);
            break;
        case 'xml':
            break;
        }
        
    }

    public function getKey($objectName) 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $keyColumnNames = $objectSchema->{'das:key-columns'};
        return $keyColumnNames;
    }
    
    public function getLabel($objectName)
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        if($objectSchema->{'das:label'}) return $objectSchema->{'das:label'};
        else return $objectSchema->name;
    }
    
    public function getContentLabels($objectName) 
    {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $childElements = $objectSchema->getChildElements();
        for($i=0; $i<$childElements->length;$i++) {
            $childElement = $childElements->item($i);
            if($childElement->{'das:label'}) $label = $childElement->{'das:label'};
            elseif($childElement->ref) $label = $childElement->ref;
            else $label = $childElement->name;
            
            if($childElement->ref) $childName = $childElement->ref;
            else $childName = $childElement->name;
            $labels[$childName] = $label;
        }
        return $labels;
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
        if($inlineObjectSchema && $inlineObjectSchema->isDataObjectArray()) {
            $arraySchemaPath = $inlineObjectSchema->getNodePath();
            $dataObject = new DataObjectArray($objectSchema->name, $schemaPath, $arraySchemaPath);
        } else {
            $dataObject = unserialize(serialize($this->prototypes[$schemaPath]));
        }
        return $dataObject;
    }
     
    private function prototypeDataObject($objectSchema)
    {
        //$objectSchema = $this->schema->getSchemaElement($schemaPath);
        $schemaPath = $objectSchema->getNodePath();
        //echo "<br/>Create prototype object with $objectSchema->name";
        $prototypeDataObject = new DataObject($objectSchema->name, $schemaPath);
        
        // Set Das parameters
        $this->setDasSource($objectSchema);
        
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
            if(!$childType) die("Unable to find data type for property " . $childElement->name);
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
                
                $this->setDasProperty($objectSchema, $childElement);
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
        $objectDatas = $this->getData($dataObject);
        $objectData = $objectDatas[0];
        $this->loadProperties($dataObject, $objectData);
        $this->loadChildren($dataObject);
    }
    
    private function loadDataObjectArray($arrayDataObject)
    {
        $objectDatas = $this->getData($arrayDataObject);
        $schemaPath = $arrayDataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        for($i=0; $i<count($objectDatas); $i++) {
            $objectData = $objectDatas[$i];
            $dataObject = $this->instanciateDataObject($objectSchema);
            $arrayDataObject->append($dataObject);          
            $this->loadProperties($dataObject, $objectData);
            $this->loadChildren($dataObject);
        }
    }
     
    private function loadProperties($dataObject, $objectData)
    {
        $propertiesObjects = $dataObject->properties;
        for($i=0; $i<count($propertiesObjects); $i++) {
            $propertyObject = $propertiesObjects[$i];
            $propertyName = $propertyObject->name;
            $propertyValue = $objectData[$propertyName];
            $propertyObject->setValue($propertyValue);
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
    private function setDasSource($objectSchema)
    {
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            // Main source
            //echo "<br/> Add table $objectSchema->name";
            $dasTable = $this->dataAccessService_Database->addTable($objectSchema->name);
            $dasTable->addPrimaryKey(
                $objectSchema->{'das:key-columns'}
            );
            $dasTable->addFilter(
                $objectSchema->{'das:filter-columns'}
            );
            if($propertyElement->{'das:label'}) {
                $dasColumn->{'label'} = $propertyElement->{'das:label'};
            } else {
                $dasColumn->{'label'} = $propertyElement->name;
            }
            if($propertyElement->{'das:comment'}) {
                $dasColumn->{'comment'} = $propertyElement->{'das:comment'};
            }
            // Relation with parent
            $relationElements = $objectSchema->getRelationElements();
            for($i=0; $i<$relationElements->length; $i++) {
                $relationElement = $relationElements->item(0);
                //echo "<br/> Add relation between " .$relationElement->{'parent'} ." and ". $relationElement->{'child'};
                $this->dataAccessService_Database->addRelation(
                    $relationElement->{'parent'},
                    $relationElement->{'child'}, 
                    $relationElement->{'parent-keys'}, 
                    $relationElement->{'child-keys'}
                );
            }
            
            break;
            
        case 'xml':
            break;
        }

    }
    
    private function setDasProperty($objectSchema, $propertyElement)
    {
        $propertyType = $propertyElement->getType();
        
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            ////echo "<br/> Add column $propertyName to $objectName";
            $dasColumn = $this->dataAccessService_Database->addColumn($objectSchema->name, $propertyElement->name, $propertyType->name);
            if($propertyElement->{'default'}) {
                $dasColumn->{'default'} = $propertyElement->{'default'};
            }
            if($propertyElement->{'fixed'}) {
                $dasColumn->{'fixed'} = $propertyElement->{'fixed'};
            }
            if(strtolower($propertyElement->{'nillable'}) === 'true') {
                $dasColumn->nillable = true;
            }
            break;
        case 'xml':
            break;
        }
    }   

    private function setDasOrder($objectSchema, $orderElements, $orderMode)
    {
        $dasSource = $objectSchema->{'das:source'};
        switch($dasSource) {
        case 'database':
            $this->dataAccessService_Database->setOrder($objectSchema->name, $orderElements, $orderMode);
            break;
        }
    }
    
    private function getData($dataObject) 
    {
        $schemaPath = $dataObject->schemaPath;
        $objectSchema = $this->schema->getSchemaElement($schemaPath);
        //echo "<br/>Get data from $objectSchema->name$objectSchema->ref ($schemaPath)";
        switch($objectSchema->{'das:source'}) {
        case 'database':
            return $this->dataAccessService_Database->getData($dataObject);
            break;
            
        case 'xml':
            break;
        }
    }
    
}