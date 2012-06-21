<?php

class dataObjectController extends DOMDocument
{

    private $Schema;
    public $prototypes = array();
    private $RootdataObject;
    private $Document;
    private $dataAccessService_Database;
    private $dataAccessService_XML;
    
    public function dataObjectController() 
    {
        require_once 'core/tests/class/Schema.php';
        require_once 'core/tests/class/XMLDocument.php';
        require_once 'core/tests/class/DataObjectArray.php';
        require_once 'core/tests/class/DataObject.php';
        require_once 'core/tests/class/DataObjectProperty.php';
        
        require_once 'core/tests/class/DataAccessService_Database.php';
        require_once 'core/tests/class/DataAccessService_XML.php';
        
        $this->dataAccessService_Database = new dataAccessService_Database();
        
    }
    
    public function loadSchema($xsdFile) 
    {
        $this->Schema = new Schema();
        $this->Schema->load($xsdFile);
        $this->Schema->processInclusions($this->Schema);

    }
    
    /**************************************************************************
    ** xpath
    **
    ** @description : 
    ** Executes xpath queries on xsd
    **
    ** @param (string) $query : xpath query
    ** @param (DOMElement) $contextElement : current element to query from
    */
    private function xpath($query, $contextElement=false) 
    {
        if(!$contextElement) $contextElement = $this->Schema->documentElement;
        $xpath = new DOMXpath($this->Schema);
        return $xpath->query($query, $contextElement);
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
        ////echo "<br/><br/><b> createDataObject($rootTypeName)</b>"; 
        $objectElement = $this->getRootElement($rootTypeName);
        if(!$objectElement) die("<br/><b>Unable to find root element named $rootTypeName</b>");
        
        $dataObject = $this->instanciateDataObject($objectElement->getNodePath());
    
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
            $schemaPath = $dataObject->getSchemaPath();
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
        
    //*************************************************************************
    // SET / GET FUNCTIONS 
    //*************************************************************************
    
    public function setKey($elementName, $key) 
    {
        $objectElement = $this->getRootElement($elementName);
        $this->setDasKey($objectElement, $key);  
    }
    
    public function getKey($elementName) 
    {
        $objectElement = $this->getRootElement($elementName);
        $keyColumnNames = $objectElement->{'das:key-columns'};
        return $keyColumnNames;
    }
     
    public function setOrder($elementName, $orderElements, $orderMode='ASC') 
    {
        $objectElement = $this->getRootElement($elementName);
        $this->setDasOrder($objectElement, $orderElements, $orderMode);  
    }
    
    public function setQuery($elementName, $queryExpression) 
    {
        $objectElement = $this->getRootElement($elementName);
        $objectType = $this->getElementType($objectElement);
        
        $DasType = $this->getDasType($objectElement);
        switch($DasType) {
        case 'database':
            $this->Queries[$elementName]['queryExpressions'][] = $queryExpression;
        }
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
    ** @param (string) $objectElement : Online element definition
    **
    ** @return (object) new DataObject / DataObjectArray
    */
    private function instanciateDataObject($schemaPath, $isDataObjectArray=false)
    {
        //echo "<br/><br/><b>instanciateDataObject() for $schemaPath</b>";
        if(!isset($this->prototypes[$schemaPath])) {
            //echo "<br/>Create prototype object";
            $this->prototypeDataObject($schemaPath);
        }
        
        if($isDataObjectArray) { 
            $objectElement = $this->getSchemaElement($schemaPath);
            $dataObject = new DataObjectArray($objectElement->name, $schemaPath);
        } else {
            $dataObject = unserialize(serialize($this->prototypes[$schemaPath]));
        }
        return $dataObject;
    }
     
    private function prototypeDataObject($schemaPath)
    {
        $objectElement = $this->getSchemaElement($schemaPath);
              
        if($objectElement->ref) {
            $objectElement = $this->getRootElement($objectElement->ref);
            if(!$objectElement) die ("Referenced element named '" . $objectElement->ref . "' not found in schema");
        }
        //echo "<br/>Create prototype object with $objectElement->name";
        $prototypeDataObject = new DataObject($objectElement->name, $schemaPath);
        
        // Set Das parameters
        $this->setDasSource($objectElement);
        
        // Create Properties and children
        // ********************************************************************
        $objectType = $this->getElementType($objectElement);
        $childElements = $this->xpath("./*[name()='xsd:sequence' or name()='xsd:all']/xsd:element", $objectType);
        ////echo "<br/>   Object has $childElements->length properties/children";
        for($i=0; $i<$childElements->length;$i++) {
            $inlineChildElement = $childElements->item($i);
            $isDataObjectArray = $this->isDataObjectArray($inlineChildElement);
            
            $childElement = $this->getRefElement($inlineChildElement);
            $childPath = $childElement->getNodePath();
            $childName = $childElement->name;
            $childType = $this->getElementType($childElement);
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
                ////echo "<br/>    Adding property '$childName'";
                $dataObjectProperty = new DataObjectProperty($childName, $childPath, $childValue);
                $prototypeDataObject->$childName = $dataObjectProperty;
                
                $this->setDasItem($objectElement, $childElement);
                
            }
            if ($childType->tagName == 'xsd:complexType') {
                $childDataObject = $this->instanciateDataObject($childPath, $isDataObjectArray);
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
            $propertyObject->setValue($objectData[$propertyName]);
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
    // SAVE DATA OBJECT
    //*************************************************************************
    public function saveDataObject($dataObject) 
    {
        $objectName = $dataObject->getTypeName();
        //echo "<br/><br/><b>Saving element " . $objectName . "</b>";
        $objectElement = $this->getRootElement($objectName);
        
        $parentObject = $dataObject->getParentObject();
        $parentName = $parentObject->getTypeName();
        $parentElement = $this->getRootElement($parentName);
        //echo "<br/>Element has a parent named " . $parentObjectName;

        // Ref
        // If specified type, fixed, default, form, block, nillable not allowed
        //  das:table not allowed
        //*********************************************************************
        $objectElement = $this->getRefElement($objectElement);
        $objectType = $this->getElementType($objectElement);
        
        
        $DasType = $this->getDasType($objectElement);
        switch($DasType) {
        case 'database':
            // TABLE
            $table = $this->getDasSource($objectElement, $objectDasType);
            // KEYS
            $keyExpression = false;
            $keyElementsList = $this->listKeyElements($objectElement, $objectType);
            $keyExpression = $this->getKeyExpression($keyElementsList, $dataObject);
            // UPDATES
            $updateExpression = false;
            $propertyElementsList = $this->listPropertyElements($objectType);
            $updateExpression = $this->getUpdateExpression($propertyElementsList, $keyElementsList, $dataObject);
            // QUERY
            //*********************************************************************
            $dbquery = "UPDATE " . $table
                . " SET " . $updateExpression
                . " WHERE " . $keyExpression;
            //////echo "<br/>" . $dbquery; 
            $db = new dbquery();
            $db->query($dbquery);
            break;
        }
      
    }
    
    //*************************************************************************
    // SUB ROUTINES
    //*************************************************************************
    private function isDataObjectArray($inLineObjectElement) 
    {
        if($inLineObjectElement->minOccurs > 1 
            || ($inLineObjectElement->maxOccurs > 1 
                || $inLineObjectElement->maxOccurs == 'unbounded')) {
            return true;
        }
    }
    
    private function getRootElement($elementName)
    {
        return $this->xpath("/xsd:schema/xsd:element[@name='".$elementName."']")->item(0);
    }
    
    //*************************************************************************
    // DAS FUNCTIONS
    //*************************************************************************
    private function setDasSource($objectElement)
    {
        $dasSource = $objectElement->{'das:source'};
        switch($dasSource) {
        case 'database':
            ////echo "<br/> Found new data access service definition 'database'";
            // Main source
            //echo "<br/> Add table $objectElement->name";
            $dasTable = $this->dataAccessService_Database->addTable($objectElement->name);
            $dasTable->addPrimaryKey(
                $objectElement->{'das:key-columns'}
            );
            
            // Relation with parent
            if($objectElement->{'das:relation'}) {
                $relationElements = $this->xpath("/xsd:schema/xsd:annotation/xsd:appinfo/das:relation[@name='".$objectElement->{'das:relation'}."']");
            } else {
                $relationElements = $this->xpath("./xsd:annotation/xsd:appinfo/das:relation", $objectElement);
            }
            for($i=0; $i<$relationElements->length; $i++) {
                $relationElement = $relationElements->item(0);
                //echo "<br/> Add relation between " .$relationElement->{'parent'} ." and ". $relationElement->{'child'};
                $this->setDasRelation($relationElement);
            }
            
            break;
            
        case 'xml':
            break;
        }

    }
    
    private function setDasRelation($relationElement)
    {
        $this->dataAccessService_Database->addRelation(
            $relationElement->{'parent'},
            $relationElement->{'child'}, 
            $relationElement->{'parent-keys'}, 
            $relationElement->{'child-keys'}
        );
    }
    
    private function setDasItem($objectElement, $propertyElement)
    {
        $tableName = $objectElement->name;
        $propertyName = $propertyElement->name;
        $propertyType = $this->getElementType($propertyElement);
        
        $dasSource = $objectElement->{'das:source'};
        switch($dasSource) {
        case 'database':
            ////echo "<br/> Add column $propertyName to $tableName";
            $dasColumn = $this->dataAccessService_Database->addColumn($tableName, $propertyName, $propertyType->name);
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
    
    private function setDasKey($objectElement, $key) 
    {
        $dasSource = $objectElement->{'das:source'};
        switch($dasSource) {
        case 'database':
            $this->dataAccessService_Database->setKey($objectElement->name, $key);
            break;
            
        case 'xml':
            break;
        }
    
    
    }
    
    private function setDasOrder($objectElement, $orderElements, $orderMode)
    {
        $dasSource = $objectElement->{'das:source'};
        switch($dasSource) {
        case 'database':
            $this->dataAccessService_Database->setOrder($objectElement->name, $orderElements, $orderMode);
            break;
        }
        if(count($orderExpressionParts) > 0) {
            $this->Queries[$elementName]['orderByExpression'] = implode(', ', $orderExpressionParts) . " " . $orderMode;
        }
    }
    
    private function getData($dataObject) 
    {
        $schemaPath = $dataObject->getSchemaPath();
        $objectElement = $this->getSchemaElement($schemaPath);

        ////echo "<br/><br/><b>Loading DAS from element $objectElement->name</b>";

        $dasSource = $objectElement->{'das:source'};

        // Database, XML ?
        switch($dasSource) {
        case 'database':
            return $this->dataAccessService_Database->getData($dataObject);
            break;
            
        case 'xml':
            break;
        }
        
     
    }
    
    //*************************************************************************
    // SCHEMA FUNCTIONS
    //*************************************************************************
    private function getSchemaElement($schemaPath)
    {
        //echo "<br/>getSchemaElement($schemaPath)";
        return $this->xpath($schemaPath)->item(0);
    }
    
    private function getRefElement($objectElement) 
    {
        if($objectElement->ref) {
            $refObjectElement = $this->getRootElement($objectElement->ref);
            if(!$refObjectElement) die ("Referenced element named " . $objectElement->ref . " not found in schema");
            return $refObjectElement;
        } else {
            return $objectElement;
        }
    }
    
    private function getElementType($element) 
    {
        if($element->type) {
            $typeName = $element->type;
            if(substr($typeName, 0, 4) == 'xsd:') {
                $elementType = $this->Schema->createElement('xsd:simpleType');
                $elementType->name = $typeName;
            } else {
                $elementType = $this->xpath("//*[(name()='xsd:complexType' or name()='xsd:simpleType') and @name='".$typeName."']")->item(0);
            }
        } else { 
            $elementType = $this->xpath("./*[(name()='xsd:complexType' or name()='xsd:simpleType')]", $element)->item(0);
        }
        if(!$elementType) die("Unable to find type for element $element->name");
        return $elementType;
    }
    
    
    //*************************************************************************
    // OLD FUNCTIONS
    //*************************************************************************
    
    private function getSimpleTypeBaseTypeName($simpleType) 
    {
        if(substr($simpleType->name, 0, 4) == 'xsd:') {
            $simpleTypeBaseName = $simpleType->name;
        } else {
            $simpleTypeBase = $this->getSimpleTypeBaseType($simpleType);
            $simpleTypeBaseName = $simpleTypeBase->name;
        }
        return $simpleTypeBaseName;
    }
    
    private function getSimpleTypeBaseType($simpleType) 
    {
        $typeContents = $this->xpath("./*[name()='xsd:restriction' or name()='xsd:list' or name()='xsd:union']", $simpleType)->item(0);
        $baseTypeName = $typeContents->base;
        if(substr($baseTypeName, 0, 4) == 'xsd:') {
            $simpleTypeBase = $this->Schema->createElement('xsd:simpleType');
            $simpleTypeBase->name = $baseTypeName;
        } else {
            $baseType = $this->xpath("//xsd:simpleType[@name='".$baseTypeName."']")->item(0);
            $simpleTypeBase = $this->getSimpleTypeBaseType($baseType);
        }
        return $simpleTypeBase;
    }
    
    private function listPropertyElements($objectElement) 
    {
        $objectType = $this->getElementType($objectElement);
        $propertyElementsList = array();
        $childElements = $this->xpath("./*[name()='xsd:sequence' or name()='xsd:all']/xsd:element", $objectType);
        for($i=0; $i<$childElements->length;$i++) {
            $childElement = $childElements->item($i);
            $childElement = $this->getRefElement($childElement);
            $childType = $this->getElementType($childElement);
            if(!$childType) die("Unable to find data type for property " . $childElement->name);
            if ($childType->tagName == 'xsd:simpleType') {
                $propertyElementsList[] = $childElement;
            }
        }
        return $propertyElementsList;
    }
          
    private function getUpdateExpression($propertyElementsList, $keyElementsList, $dataObject) 
    {
        $updateExpressionParts = array();
        for($j=0; $j<count($propertyElementsList); $j++) {
            $propertyElement = $propertyElementsList[$j];
            $propertyElement = $this->getRefElement($propertyElement);
            if(in_array($propertyElement, $keyElementsList, true)) {
                //////echo "<br/>Remove element " . $this->getRefElement($propertyElement)->name . " from updated properties because it is a key";
                continue;
            }
            $propertyName = $propertyElement->name;
            $propertyType = $this->getElementType($propertyElement);
            $columnName = $this->getColumnName($propertyElement);
            $columnValue = $this->enclose($dataObject->{$propertyName}, $propertyType);
            // DEFAULT
            if($propertyElement->{'default'}) {
                $defaultValue = $this->enclose($propertyElement->{'default'}, $propertyType);
                $updateExpressionParts[] = $columnName  . " = COALESCE(" . $columnValue . ", " . $defaultValue . ")";
            }
            else {
                $updateExpressionParts[] = $columnName  . " = " . $columnValue;
            } 
        } 
        if(count($updateExpressionParts) > 0) return implode (', ', $updateExpressionParts);
        
    }   
    
    private function getRelationElements($objectElement) 
    {
        $relationElements = $this->xpath("./xsd:annotation/xsd:appinfo/das:relation", $objectElement);
        return $relationElements;
    }
    
    private function __makeForeignKeyExpression($objectElement, $parentObject) 
    {
        $relationExpressionParts = array();
        $keyrefElement = $this->xpath("./xsd:keyref", $objectElement)->item(0);
        if(!$keyrefElement) return false;
        ////////echo "<br/>Found keyref " . $keyrefElement->name . " on ref " . $keyrefElement->refer;
        // Get child key element
        ////////echo "<br/>List child keys from " . $keyrefElement->name;
        $childKeyFieldsList = $this->getKeyFieldsList($keyrefElement);

        // Get Parent element def
        ////////echo "<br/>List parent keys from " . $keyrefElement->refer;
        $referElement = $this->xpath("//xsd:key[@name='".$keyrefElement->refer."']")->item(0);
        $parentKeyFieldsList = $this->getKeyFieldsList($referElement);
        
        if(count($childKeyFieldsList) != count($parentKeyFieldsList)) die("Relation " . $keyrefElement->name . " doesn't match foreign key " . $keyrefElement->refer);  
        
        for($i=0; $i<count($childKeyFieldsList); $i++) {
            $childKeyElement = $childKeyFieldsList[$i];
            $parentKeyElement = $parentKeyFieldsList[$i];
            
            $childKeyType = $this->getElementType($childKeyElement);
            $childColumnName = $this->getColumnName($childKeyElement);
            
            $parentKeyName = $parentKeyElement->name;
            $parentKeyValue = $this->enclose($parentObject->{$parentKeyName}, $childKeyType);   
            $relationExpressionParts[] = $childColumnName . " = " . $parentKeyValue;
        }
        
        if(count($relationExpressionParts) > 0) {
            ////////echo "<br/>Relation expression is " . implode(' and ', $relationExpressionParts);
            return implode(' and ', $relationExpressionParts);
        }
    }
    
    private function __getKeyFieldsList($keyElement) 
    {
        $selectorXpath = $this->xpath("./xsd:selector/@xpath", $keyElement)->item(0)->nodeValue;
        ////////echo "<br/>Selector xPath is " . $selectorXpath;
        $fieldsXpath = $this->xpath("./xsd:field/@xpath", $keyElement);
        ////////echo "<br/>Found " . $fieldsXpath->length . " key fields";
        $contextElement = $this->xpath("./parent::*", $keyElement)->item(0);
        $contextElement = $this->getRefElement($contextElement);
        ////////echo "<br/>Context element is " . $contextElement->name;
        $selectorElement = $this->xPathOnSchema($selectorXpath, $contextElement);
        $selectorElement = $this->getRefElement($selectorElement);
        ////////echo "<br/>Selected element is " . $selectorElement->name;
        for($i=0; $i<$fieldsXpath->length; $i++) {
            ////////echo "<br/>Selecting field element with xpath " . $fieldsXpath->item($i)->nodeValue;
            $keyFieldElement = $this->xPathOnSchema($fieldsXpath->item($i)->nodeValue, $selectorElement);
            ////////echo "<br/>Key field element is " . $keyFieldElement->name;
            $keyFieldElements[] = $keyFieldElement;
        }
        return $keyFieldElements;
    }
    
    private function xPathOnSchema($xPath, $contextElement) 
    {
        $xPathParts = explode('/', $xPath);
        for($i=0; $i<count($xPathParts); $i++) {
            $contextType = $this->getElementType($contextElement); 
            $xPathPart = $xPathParts[$i];
            switch(substr($xPathPart, 0, 1)) {
            case '.':
                break;
            case '@':
                break;
            default:
                $contextElement = $this->xpath("./*[name()='xsd:sequence' or name()='xsd:all']/xsd:element[@name='".$xPathPart."' or @ref='".$xPathPart."']", $contextType)->item(0);
                $contextElement = $this->getRefElement($contextElement);
            }
        }
        return $contextElement;
    }

   
    //*************************************************************************
    // XML
    //************************************************************************/
    public function asXml($dataObject) 
    {
        $Document = new XMLDocument();
        $this->objectToXml($dataObject, $Document, $Document); 
        
        return $Document;
    }
    
    public function validate($dataObject) 
    {
        $Document = $this->asXml($dataObject);
        
        //////echo htmlspecialchars($this->Document->saveXML());
        echo '<pre><b>VALIDATING XML</b>';
        
        libxml_use_internal_errors(true);
        if($Document->schemaValidateSource($this->Schema->saveXML())) {
            // Return nothing
        } else {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                $display = "<br/>\n";
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $display .= "<b>Warning $error->code</b>: ";
                        break;
                    case LIBXML_ERR_ERROR:
                        $display .= "<b>Error $error->code</b>: ";
                        break;
                    case LIBXML_ERR_FATAL:
                        $display .= "<b>Fatal Error $error->code</b>: ";
                        break;
                }
                $display .= trim($error->message);
                if ($error->file) {
                    $display .=    " in <b>$error->file</b>";
                }
                $display .= " on line <b>$error->line </b> on column <b>$error->column </b>\n";
            }
            return $display;
            libxml_clear_errors();
        } 
        
    }
    
    private function childrenToXml($dataObject, $parentXml, $Document) 
    {
        //echo "<br/><b>Adding ".count($dataObject)." children elements to $parentName</b>";
        foreach($dataObject as $childObject) {
            if(!is_object($childObject)) Die("Non object value are forbidden");
            if($childObject->isDataObjectProperty) {
                //echo "<br/>Adding property element $childName => $childObject";
                $this->propertyToXml($childObject, $parentXml, $Document);
            } elseif($childObject->isDataObject) {
                //echo "<br/><b>Adding child object $childName</b>";
                $this->objectToXml($childObject, $parentXml, $Document);
            } elseif($childObject->isDataObjectArray) {
                //echo "<br/><b>Adding array of $childName</b>";
                $this->arrayToXml($childObject, $parentXml, $Document);
            }
        }
    }
    
    private function objectToXml($dataObject, $parentXml, $Document) 
    {
        $objectXml = $Document->createElement($dataObject->name);
        $parentXml->appendChild($objectXml);
        $this->childrenToXml($dataObject, $objectXml, $Document);
    }
    
    private function arrayToXml($dataObjectArray, $parentXml, $Document)
    {
        for($i=0; $i<count($dataObjectArray); $i++) {
            $childObject = $dataObjectArray[$i];
            //echo "<br/>Adding array item #$i";
            $this->objectToXml($childObject, $parentXml, $Document);
        }
    }
    
    private function propertyToXml($dataObjectProperty, $parentXml, $Document) 
    {
        $propertyXml = $Document->createElement($dataObjectProperty->name, $dataObjectProperty->value);
        $parentXml->appendChild($propertyXml);
    }
}