<?php

class dataObjectController extends DOMDocument
{

    private $schema;
    private $prototypes = array();
    private $dataAccessService_Database;
    private $dataAccessService_XML;
    private $dataObjectValidator;
    
    public function dataObjectController() 
    {
        require_once 'core/tests/class/DataObjectSchema.php';
        require_once 'core/tests/class/DataObjectArray.php';
        require_once 'core/tests/class/DataObject.php';
        require_once 'core/tests/class/DataObjectProperty.php';
        
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
    }
    
    //*************************************************************************
    // PUBLIC OBJECT HANDLING FUNCTIONS
    //*************************************************************************
    /**************************************************************************
    ** createRootDataObject
    **
    ** @description : 
    ** Creates the root object of DataObjectController
    **   Instanciate empty DataObjects (root + children)
    **   Load Das parameters (source, properties)
    **
    ** @param (string) $rootTypeName : Name of a schema root element
    **
    ** @return (object) empty RootDataObject
    */
    public function createRootDataObject($rootTypeName) 
    {
        $this->RootDataObject = $this->createDataObject($rootTypeName);
        
        return $this->RootDataObject;
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
    public function createDataObject($rootTypeName) 
    {
        //echo "<br/><br/><b> createDataObject($rootTypeName)</b>"; 
        $objectSchema = $this->schema->getObjectSchema($rootTypeName);
        if(!$objectSchema) die("<br/><b>Unable to find root element named $rootTypeName</b>");
        
        $dataObject = $this->instanciateDataObject($objectSchema->getNodePath());
    
        return $dataObject;      
    }
    
    /**************************************************************************
    ** loadRootDataObject
    **
    ** @description : 
    ** Loads the root object with data 
    **
    ** @param 
    **
    ** @return (object) RootDataObject
    */
    public function loadRootDataObject() 
    {
        //echo "<br/>Load RootDataObject";
        $this->loadDataObject($this->RootDataObject);
        return $this->RootDataObject;
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
    public function loadDataObject($dataObject) 
    {      
        $objectDatas = $this->getData($dataObject);
        //echo "<br/><br/><b>loadDataObject() from schema element $schemaPath = ".count($objectDatas)." results</b>"; 
        if($dataObject->isDataObject) {
            $objectData = $objectDatas[0];
            //echo "<br/> Result has " . count($result) . " properties for object";
            $this->loadProperties($dataObject, $objectData);
            $this->loadChildren($dataObject);
        }
        if($dataObject->isDataObjectArray) {
            $schemaPath = $dataObject->schemaPath;
            for($i=0; $i<count($objectDatas); $i++) {
                $objectData = $objectDatas[$i];
                $itemDataObject = $this->instanciateDataObject($schemaPath);
                $dataObject->append($itemDataObject);          
                //echo "<br/> Result has " . count($objectData) . " properties for object #$i of array";
                $this->loadProperties($itemDataObject, $objectData);
                $this->loadChildren($itemDataObject);
            }
        }
    }
    
    public function validateDataObject($dataObject) 
    {
        return $this->dataObjectValidator->validateDataObject($dataObject, $this->schema);
    }
    
    public function getValidationErrors()
    {
        return $this->dataObjectValidator->getErrors();
    }
    
    public function saveDataObject($dataObject) 
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
        elseif($objectSchema->name) return $objectSchema->name;
        elseif($objectSchema->ref) {
            $objectSchema = $objectSchema->getRefElement();
            return $this->getLabel($objectSchema->name);
        }
    }
    
    public function getContentLabels($objectName) {
        $objectSchema = $this->schema->getObjectSchema($objectName);
        $childElements = $objectSchema->getChildElements();
        for($i=0; $i<$childElements->length;$i++) {
            $childElement = $childElements->item($i);
            if($childElement->{'das:label'}) $label = $childElement->{'das:label'};
            elseif($childElement->name) $label = $childElement->name;
            elseif($childElement->ref) {
                $childElement = $childElement->getRefElement();
                if($childElement->{'das:label'}) $label = $childElement->{'das:label'};
                else $label = $childElement->name;
            }
            $labels[$childElement->name] = $label;
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
    ** @param (string) $objectSchema : Online element definition
    **
    ** @return (object) new DataObject / DataObjectArray
    */
    private function instanciateDataObject($schemaPath, $inlineChildElement=false)
    {
        //echo "<br/><br/><b>instanciateDataObject() for $schemaPath</b>";
        if(!isset($this->prototypes[$schemaPath])) {
            //echo "<br/>Create prototype object";
            $this->prototypeDataObject($schemaPath);
        }
        
        if($inlineChildElement && $inlineChildElement->isDataObjectArray()) {
            $objectSchema = $this->schema->getSchemaElement($schemaPath);
            $arraySchemaPath = $inlineChildElement->getNodePath();
            $dataObject = new DataObjectArray($objectSchema->name, $schemaPath, $arraySchemaPath);
        } else {
            $dataObject = unserialize(serialize($this->prototypes[$schemaPath]));
        }
        return $dataObject;
    }
     
    private function prototypeDataObject($schemaPath)
    {
        $objectSchema = $this->schema->getSchemaElement($schemaPath);

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
                $childDataObject = $this->instanciateDataObject($childPath, $inlineChildElement);
                $prototypeDataObject->$childName = $childDataObject;
            }
        }
        $this->prototypes[$schemaPath] = $prototypeDataObject;
    }
    
    private function loadProperties($dataObject, $objectData)
    {
        $propertiesObjects = $dataObject->getProperties();
        for($i=0; $i<count($propertiesObjects); $i++) {
            $propertyObject = $propertiesObjects[$i];
            $propertyName = $propertyObject->name;
            $propertyValue = $objectData[$propertyName];
            $propertyObject->setValue($propertyValue);
        }
    }
    
    private function loadChildren($dataObject) 
    {
        $childrenObjects = $dataObject->getChildren();
        for($i=0; $i<count($childrenObjects); $i++) {
            $childObject = $childrenObjects[$i];
            $this->loadDataObject($childObject);
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
        switch($objectSchema->{'das:source'}) {
        case 'database':
            return $this->dataAccessService_Database->getData($dataObject);
            break;
            
        case 'xml':
            break;
        }
    }
    
}