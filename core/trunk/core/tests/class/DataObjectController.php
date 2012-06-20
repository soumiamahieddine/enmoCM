<?php

class dataObjectController extends DOMDocument
{

    private $Schema;
    private $includes;
    private $RootdataObject;
    private $Document;
    private $dataAccessService_Database;
    private $dataAccessService_XML;
    private $Queries;
    
    public function dataObjectController() 
    {
        require_once 'core/tests/class/Schema.php';
        require_once 'core/tests/class/XMLDocument.php';
        require_once 'core/tests/class/ArrayDataObject.php';
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
        $this->Schema->registerNodeClass('DOMElement', 'SchemaElement');
        $this->Schema->formatOutput = true;
        $this->Schema->preserveWhiteSpace = true;
        $this->processIncludes($this->Schema);

    }
    
    /**************************************************************************
    ** processIncludes
    **
    ** @description : 
    ** Process <include /> elements to import included xsd schema contents
    ** at the end of current schema in same namespace
    ** If the included schema contains include elements => recursive call
    ** Feeds the list of included schemas and avoids duplicate includes
    **
    ** @param (DOMDocument) $XSDDocument : Object to process 
    */
    private function processIncludes($Schema) 
    {
        $xpath = new DOMXPath($Schema);
        $schema = $xpath->query('/xsd:schema')->item(0);
        $includes = $xpath->query('./xsd:include', $schema);
        $nl = $includes->length;

        for($i=0; $i<$includes->length; $i++) {
            $include = $includes->item($i);
            $schemaLocation = $include->schemaLocation;
            if(!$this->includes || !in_array($schemaLocation, $this->includes)) {
                $includeSchema = new Schema();
                $includeSchema->load($_SESSION['config']['corepath'] . $schemaLocation);
                $includeSchema->registerNodeClass('DOMElement', 'SchemaElement');
                $includeSchema->Schema->formatOutput = true;
                $includeSchema->Schema->preserveWhiteSpace = true;
                
                $this->processIncludes($includeSchema);
                $includeXpath = new DOMXpath($includeSchema);
                $schemaContents = $includeXpath->query('/xsd:schema/*');
                for($j=0; $j<$schemaContents->length; $j++) {
                    $importNode = $schemaContents->item($j);
                    $importedNode = $Schema->importNode($importNode, true);
                    $Schema->documentElement->appendChild($importedNode);
                }
                $this->includes[] = $schemaLocation;
            }
            $schema->removeChild($include);
        }
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
    // SET / GET FUNCTIONS 
    //*************************************************************************
    
    public function setKey($elementName, $key) 
    {
        $objectElement = $this->getRootElement($elementName);
        $objectType = $this->getElementType($objectElement);
        $keyValues = explode(' ', $key);
        
        $keyExpressionParts = array();
        $DasType = $this->getDasType($objectElement);
        switch($DasType) {
        case 'database':
            $keyElementsList = $this->listKeyElements($objectElement, $objectType);
            for($i=0; $i<count($keyElementsList); $i++) {
                $keyElement = $keyElementsList[$i];
                $keyColumnName = $this->getColumnName($keyElement);
                $keyType = $this->getElementType($keyElement);
                $keyValue = $this->enclose($keyValues[$i], $keyType);
                $keyExpressionParts[] = $keyColumnName . " = " . $keyValue;
            }
            break;
        }
        if(count($keyExpressionParts) > 0) $this->Queries[$elementName]['keyExpression'] = implode(' and ', $keyExpressionParts);
    }
    
    public function getKey($elementName) 
    {
        $objectElement = $this->getRootElement($elementName);;
        $keyColumnNames = $objectElement->{'das:key-column'};
        return $keyColumnNames;
    }
     
    public function getIndex($elementName) 
    {
        $objectElement = $this->getRootElement($elementName);;
        $keyColumnNames = $objectElement->{'das:key-column'};
        return $keyColumnNames;
    }
     
    public function setOrder($elementName, $orderElements, $orderMode='ASC') 
    {
        $objectElement = $this->getRootElement($elementName);
        $objectType = $this->getElementType($objectElement);
        
        $DasType = $this->getDasType($objectElement);
        switch($DasType) {
        case 'database':
            $orderElementsArray = explode(' ', $orderElements);
            $orderExpressionParts = array();
            for($i=0; $i<count($orderElementsArray); $i++) {
                $orderElementName = $orderElementsArray[$i];
                $orderElement = $this->getTypeElementByName($objectType, $orderElementName);
                $orderElement = $this->getRefElement($orderElement);
                $orderColumn = $this->getColumnName($orderElement);
                $orderExpressionParts[] = $orderColumn;
            }
            break;
        }
        if(count($orderExpressionParts) > 0) {
            $this->Queries[$elementName]['orderByExpression'] = implode(', ', $orderExpressionParts) . " " . $orderMode;
        }
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
    // CREATE / LOAD DATA OBJECT
    //*************************************************************************
    /**************************************************************************
    ** loadRootDataObject
    **
    ** @description : 
    ** Loads the root object of DataObjectController 
    **
    ** @param (string) $rootTypeName : Name of a schema root element
    **
    ** @return (object) RootDataObject
    */
    public function loadRootDataObject($rootTypeName) 
    {
        //echo "<br/><b>loadRootDataObject($rootTypeName)</b>"; 
        //echo "<br/><br/><b>1 -> createDataObject($rootTypeName)</b>"; 
        $this->RootDataObject = $this->createDataObject($rootTypeName);
        
        //echo "<br/><br/><b>2 -> loadDataObject()</b>";
        $this->loadDataObject($this->RootDataObject, false);
        
        return $this->RootDataObject;
    }
    
    /**************************************************************************
    ** public createDataObject
    **
    ** @description : 
    ** Creates a new DataObject or ArrayDataObject from a root element name
    **  - DataObjectProperties
    **  - Children (instance of data objects or array data object)
    **
    ** @param (string) $rootTypeName : Name of a root element definition
    **
    ** @return (object) new DataObject / ArrayDataObject
    */
    public function createDataObject($rootTypeName) 
    {
        //echo "<br/><br/><b> createDataObject($rootTypeName)</b>"; 
        $objectElement = $this->getRootElement($rootTypeName);
        if(!$objectElement) die("<br/><b>Unable to find root element named $rootTypeName</b>");
        
        $dataObject = $this->instanciateDataObject($objectElement);
    
        return $dataObject;      
    }
    
    /**************************************************************************
    ** private instanciateDataObject
    **
    ** @description : 
    ** Creates a new instance of DataObject or ArrayDataObject including
    **  - DataObjectProperties
    **  - Children (instance of data objects or array data object)
    **
    ** @param (string) $objectElement : Online element definition
    **
    ** @return (object) new DataObject / ArrayDataObject
    */
    private function instanciateDataObject($objectElement)
    {
        if($objectElement->ref) {
            $refObjectElement = $this->getRootElement($objectElement->ref);
            if(!$refObjectElement) die ("Referenced element named '" . $objectElement->ref . "' not found in schema");
        } else {
            $refObjectElement = $objectElement;
        }
        //echo "<br/><br/><b>  instanciateDataObject() from element '$refObjectElement->name'</b>";
        $dataObject = new DataObject($refObjectElement);
        
        // Create Properties
        // ********************************************************************
        $objectType = $this->getElementType($refObjectElement);
        $childElements = $this->xpath("./*[name()='xsd:sequence' or name()='xsd:all']/xsd:element", $objectType);
        //echo "<br/>   Object has $childElements->length properties/children";
        for($i=0; $i<$childElements->length;$i++) {
            $childElement = $childElements->item($i);
            $refChildElement = $this->getRefElement($childElement);
            $childName = $refChildElement->name;
            $childType = $this->getElementType($refChildElement);
            if(!$childType) die("Unable to find data type for property " . $refChildElement->name);
            if ($childType->tagName == 'xsd:simpleType') {
                // DEFAULT and FIXED
                if($refChildElement->{'default'}) {
                    $childValue = $refChildElement->{'default'};
                }
                else if($refChildElement->fixed) {
                    $childValue = $refChildElement->fixed;
                } else {
                    $childValue = false;
                }
                //echo "<br/>    Adding property '$childName'";
                $dataObjectProperty = new DataObjectProperty($refChildElement, $childName, $childValue);
                $dataObject->$childName = $dataObjectProperty;
            }
            if ($childType->tagName == 'xsd:complexType') {
                //echo "<br/>    Adding child '$childName'";
                if($this->isArrayDataObject($childElement)) {
                    //echo " as ArrayDataObject";
                    $childDataObject = $this->instanciateArrayDataObject($childElement);
                } else {
                    //echo " as DataObject";
                    $childDataObject = $this->instanciateDataObject($childElement);
                }
                $dataObject->$childName = $childDataObject;
            }
        }
        
        return $dataObject;
    }
    
    private function instanciateArrayDataObject($objectElement) 
    { 
        if($objectElement->ref) {
            $refObjectElement = $this->getRootElement($objectElement->ref);
            if(!$refObjectElement) die ("Referenced element named '" . $objectElement->ref . "' not found in schema");
        } else {
            $refObjectElement = $objectElement;
        }
        //echo "<br/><br/><b>  instanciateArrayDataObject() from element '$refObjectElement->name'</b>";
        $arrayDataObject = new ArrayDataObject($refObjectElement);
        return $arrayDataObject;
    }
    
    /**************************************************************************
    ** public createDataObject
    **
    ** @description : 
    ** Creates a new DataObject or ArrayDataObject from a root element name
    **  - DataObjectProperties
    **  - Children (instance of data objects or array data object)
    **
    ** @param (string) $rootTypeName : Name of a root element definition
    **
    ** @return (object) new DataObject / ArrayDataObject
    */
    private function loadDataObject($dataObject) 
    {      
        // Load Data Access Service
        $objectElement = $dataObject->getSchemaElement();
        //echo "<br/><br/><b>loadDataObject() from schema element $objectElement->name</b>"; 
        $results = $this->getData($dataObject);
        $result = $results[0];
        //echo "<br/> Found " . count($result) . " properties for objects of array $typeName";
        if(count($result) > 0) {
            foreach($result as $propertyName => $propertyValue) {
                $dataObject->$propertyName = $propertyValue;
            }
        }

        $this->loadChildren($dataObject);
    }
    
    private function loadArrayDataObject($arrayDataObject)
    {
        // Load Data Access Service
        $objectElement = $arrayDataObject->getSchemaElement();
        //echo "<br/><br/><b>loadDataObject() from schema element $objectElement->name</b>"; 
        $results = $this->getData($arrayDataObject);
        for($i=0; $i<count($results); $i++) {
            $result = $results[$i];
            $dataObject = $this->instanciateDataObject($objectElement);
            //$arrayDataObject->append($dataObject);
            $arrayDataObject[1] = $dataObject;
            //echo "<br/> Found " . count($result) . " properties for objects of array $typeName";
            if(count($result) > 0) {
                foreach($result as $propertyName => $propertyValue) {
                    $dataObject->$propertyName = $propertyValue;
                }
            }
            $this->loadChildren($dataObject);
        }
    }
    
    private function loadProperties($dataObject)
    {
      
        /*
        switch($DasType) {
        case 'database':
                        // WHERE
            $whereClause = array();
            if(isset($this->Queries[$elementName]['keyExpression'])) {
                $whereClause[] = $this->Queries[$elementName]['keyExpression'];
            }
            ////echo "<br/><b>Get relation between $objectName and $parentName</b>";
            //$relationElements = $this->getRelationElements($objectElement);
            //$relationExpression = $this->makeRelationExpression($relationElements, $objectType, $parentObject);
            //if($relationExpression) $whereClause[] = $relationExpression;
            $whereExpression = $this->getWhereExpression($objectElement);
            if($whereExpression) $whereClause[] = $whereExpression;
            if(isset($this->Queries[$elementName]['queryExpressions'])) {
                for($i=0; $i<count($this->Queries[$elementName]['queryExpressions']); $i++) {
                    $whereClause[] = $this->Queries[$elementName]['queryExpressions'][$i];
                }
            }
            // ORDER
            $orderByClause = false;
            if(isset($this->Queries[$elementName]['orderByExpression'])) {
                $orderByClause = $this->Queries[$elementName]['orderByExpression'];
            }
            
            // Query
            $dbQuery  = "SELECT " . $selectExpression;
            $dbQuery .= " FROM " . $from;
            if(count($whereClause) > 0) $dbQuery .= " WHERE " . implode(' and ', $whereClause);
            if($orderByClause) $dbQuery .= " ORDER BY " . $orderByClause;
            
            //echo "<pre>" . $dbQuery . "</pre>";
            $db = new dbquery();
            $db->query($dbQuery);
            
            if(get_class($dataObject) == 'ArrayDataObject') {
                while($resultArray = $db->fetch_assoc()) {
                    $dataObject = new DataObject($objectElement);
                    $dataObject->append($dataObject);
                    //echo "<br/>Found " . count($resultArray) . " properties for objects of array $typeName";
                    if(count($resultArray) > 0) {
                        foreach($resultArray as $propertyName => $propertyValue) {
                            $dataObject->$propertyName = $propertyValue;
                        }
                    }
                }
            } else {
                $resultArray = $db->fetch_assoc();
                foreach($resultArray as $propertyName => $propertyValue) {
                    $dataObject->$propertyName = $propertyValue;
                }
            }
            break;
            
        case 'xml':
            $xml = new DOMDocument();
            $xml->load($_SESSION['config']['corepath'] . $objectElement->{'das:xml'});
            $xpath = new DOMXpath($xml);
            if($objectElement->{'das:xpath'}) {
                $query = $objectElement->{'das:xpath'};
            }
            $xmlContents = $xpath->query($query);
            for($i=0; $i<$xmlContents->length; $i++) {
                $xmlObject = $xmlContents->item($i);
                $propertyNodes = $xmlObject->childNodes;
                $propertiesArray = array();
                for($j=0; $j<$propertyNodes->length; $j++) {
                    $propertyNode = $propertyNodes->item($j);
                    if($propertyNode->nodeType == XML_TEXT_NODE) continue;
                    $propertiesArray[$propertyNode->tagName] = $propertyNode->nodeValue;
                }
                $propertiesArrays[] = $propertiesArray;
            }
            // TO DO : query only elements defined as properties and get child elements with their own datasource (prop elmt list)
            break;
        }*/
        
    }
    
    private function loadChildren($dataObject) 
    {
        //echo "<br/><br/><b>loadChildren() from dataObject</b>"; 
        $children = $dataObject->getChildren();
        foreach($dataObject->getChildren() as $childObject) {
            //echo "<br/> Loading child $childObject->typeName for $dataObject->typeName ";
            if($childObject->isDataObject) {
                $this->loadDataObject($childObject);
            }
            if($childObject->isArrayDataObject) {
                $this->loadArrayDataObject($childObject);
            }
        }
    }

    public function importDataObject($DataObject) 
    {
        $this->RootDataObject = $DataObject;
    }
    
    //*************************************************************************
    // SAVE DATA OBJECT
    //*************************************************************************
    public function saveDataObject($dataObject) 
    {
        $objectName = $dataObject->getTypeName();
        //////echo "<br/><br/><b>Saving element " . $objectName . "</b>";
        $objectElement = $this->getRootElement($objectName);
        
        $parentObject = $dataObject->getParentObject();
        $parentName = $parentObject->getTypeName();
        $parentElement = $this->getRootElement($parentName);
        //////echo "<br/>Element has a parent named " . $parentObjectName;

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
            ////echo "<br/>" . $dbquery; 
            $db = new dbquery();
            $db->query($dbquery);
            break;
        }
      
    }
    
    //*************************************************************************
    // SUB ROUTINES
    //*************************************************************************
    private function isArrayDataObject($inLineObjectElement) 
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
    private function getData($dataObject) 
    {
        $objectElement = $dataObject->getSchemaElement();
        //echo "<br/><br/><b>Loading DAS from element $objectElement->name</b>";
        $objectType = $this->getElementType($objectElement);

        $dasSource = $objectElement->{'das:source'};

        // Database, XML ?
        switch($dasSource) {
        case 'database':
            //echo "<br/> Found new data access service definition 'database'";
            $objectDas = new dataAccessService_Database();
            // Main source
            $dasTable = $objectDas->addTable($objectElement->name);
            $dasTable->addPrimaryKey(
                $objectElement->{'das:key-columns'}
            );
            
            // Relation with parent
            if($objectElement->{'das:parent-columns'} && $objectElement->{'das:child-columns'}) {
                $objectDas->addRelation(
                    'N/A',
                    $objectElement->name,
                    $objectElement->{'das:parent-columns'}, 
                    $objectElement->{'das:child-columns'}
                );
            }
            
            $childElements = $this->xpath("./*[name()='xsd:sequence' or name()='xsd:all']/xsd:element", $objectType);
            for($i=0; $i<$childElements->length;$i++) {
                $childElement = $childElements->item($i);
                $childElement = $this->getRefElement($childElement);
                $childName = $childElement->name;
                $childType = $this->getElementType($childElement);
                if(!$childType) die("Unable to find data type for property " . $childElement->name);
                if ($childType->tagName == 'xsd:simpleType') {
                    $dasColumn = $dasTable->addColumn($childName, $childType->name);
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
            return $objectDas->getData($dataObject);
            break;
            
        case 'xml':
            break;
        }
        
     
    }
    
    //*************************************************************************
    // SCHEMA FUNCTIONS
    //*************************************************************************
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
                ////echo "<br/>Remove element " . $this->getRefElement($propertyElement)->name . " from updated properties because it is a key";
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
        //////echo "<br/>Found keyref " . $keyrefElement->name . " on ref " . $keyrefElement->refer;
        // Get child key element
        //////echo "<br/>List child keys from " . $keyrefElement->name;
        $childKeyFieldsList = $this->getKeyFieldsList($keyrefElement);

        // Get Parent element def
        //////echo "<br/>List parent keys from " . $keyrefElement->refer;
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
            //////echo "<br/>Relation expression is " . implode(' and ', $relationExpressionParts);
            return implode(' and ', $relationExpressionParts);
        }
    }
    
    private function __getKeyFieldsList($keyElement) 
    {
        $selectorXpath = $this->xpath("./xsd:selector/@xpath", $keyElement)->item(0)->nodeValue;
        //////echo "<br/>Selector xPath is " . $selectorXpath;
        $fieldsXpath = $this->xpath("./xsd:field/@xpath", $keyElement);
        //////echo "<br/>Found " . $fieldsXpath->length . " key fields";
        $contextElement = $this->xpath("./parent::*", $keyElement)->item(0);
        $contextElement = $this->getRefElement($contextElement);
        //////echo "<br/>Context element is " . $contextElement->name;
        $selectorElement = $this->xPathOnSchema($selectorXpath, $contextElement);
        $selectorElement = $this->getRefElement($selectorElement);
        //////echo "<br/>Selected element is " . $selectorElement->name;
        for($i=0; $i<$fieldsXpath->length; $i++) {
            //////echo "<br/>Selecting field element with xpath " . $fieldsXpath->item($i)->nodeValue;
            $keyFieldElement = $this->xPathOnSchema($fieldsXpath->item($i)->nodeValue, $selectorElement);
            //////echo "<br/>Key field element is " . $keyFieldElement->name;
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

    private function getKeyExpression($keyElementsList, $dataObject) 
    {
        $keyExpressionParts = array();
        for($i=0; $i<count($keyElementsList); $i++) {
            $keyElement = $keyElementsList[$i];
            $keyColumnName = $this->getColumnName($keyElement);
            $keyType = $this->getElementType($keyElement);
            $keyName = $keyElement->name;
            $keyValue = $dataObject->{$keyName};
            $keyValue = $this->enclose($keyValue, $keyType);
            $keyExpressionParts[] = $keyColumnName . " = " . $keyValue;
        }
        if(count($keyExpressionParts) > 0) return implode (' and ', $keyExpressionParts);
    }
    
    private function listKeyElements($objectElement, $objectType) 
    {
        $keyElementsList = array();
        $keyColumnNamesArray = $objectElement->{'das:key-column'};
        $keyColumnNames = explode(' ', $keyColumnNamesArray);
        for($i=0; $i<count($keyColumnNames); $i++) {
            $keyColumnName = $keyColumnNames[$i];
            $keyElement = $this->getTypeElementByName($objectType, $keyColumnName);
            $keyElement = $this->getRefElement($keyElement);
            $keyElementsList[] = $keyElement;
        }
        return $keyElementsList;
    }
    
    private function getTypeElementByName($objectType, $elementName) 
    {
        return $this->xpath("./*[name()='xsd:sequence' or name()='all']/xsd:element[@das:column='".$elementName."'".
                " or @name='".$elementName."' or @ref='".$columnName."']", $objectType)->item(0);
    }
    
    private function enclose($value, $elementType) 
    {
        $baseTypeName = $this->getSimpleTypeBaseTypeName($elementType);
        if($this->isQuoted($baseTypeName)) {
            $value = "'" . $value . "'";
        } 
        return $value;
    }
    
    private function isQuoted($typeName) 
    {
        if(!in_array(
            $typeName,
            array(
                'boolean',
                'double', 
                'decimal',
                    'integer',
                        'nonPositiveInteger',
                            'negativeInteger',
                        'long',
                            'int', 
                            'short', 
                            'byte',
                        'nonNegativeInteger',
                            'positiveInteger',
                            'unsignedLong',
                                'unsignedInt',
                                    'unsignedShort',
                                        'unsignedByte',
                'float',
                )
            )
        ) {
            return true;
        }
    }
    
    //*************************************************************************
    // XML
    //************************************************************************/
    public function validate() 
    {
        $this->Document = new XMLDocument();
        //Object => create element
        $rootName = $this->RootDataObject->getTypeName();
        ////echo "<br/><br/><b>Adding root element $rootName</b>";
        $rootXml = $this->Document->createElement($rootName);
        $this->Document->appendChild($rootXml);
        $this->addChildXml($this->RootDataObject, $rootName, $rootXml);
        
        ////echo htmlspecialchars($this->Document->saveXML());
        ////echo '<pre><b>VALIDATING XML</b>';
        
        libxml_use_internal_errors(true);
        if($this->Document->schemaValidateSource($this->Schema->saveXML())) {
            return true;
            $dummy = array(
                'status' => '100',
                'messages' => array(
                    array('', '', '', '', ''),
                    ),
                );
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
    
    private function addChildXml($parentObject, $parentName, $parentXml) 
    {
        ////echo "<br/><b>Adding ".count($parentObject)." children elements to $parentName</b>";
        foreach($parentObject as $childName => $childContents) {
            if(is_scalar($childContents) || $childContents == false) {
                ////echo "<br/>Adding property element $childName => $childContents";
                $this->propertyToXml($childContents, $childName, $parentXml);
            } elseif(get_class($childContents) == 'DataObject') {
                ////echo "<br/><b>Adding child object $childName</b>";
                $this->objectToXml($childContents, $childName, $parentXml);
            } elseif(get_class($childContents) == 'ArrayDataObject') {
                ////echo "<br/><b>Adding array of $childName</b>";
                $this->arrayToXml($childContents, $childName, $parentXml);
            }
        }
    }
    
    private function objectToXml($dataObject, $objectName, $parentXml) 
    {
        if((is_scalar($dataObject) || $dataObject == false) 
            || get_class($dataObject) != 'DataObject') {
            ////echo "<br/><br/><b>Adding not well formed data object $objectName</b>";
            $this->notWellFormedToXml($dataObject, $objectName, $parentXml);
        } else {
            ////echo "<br/><br/><b>Adding object $objectName</b>";
            $objectXml = $this->Document->createElement($objectName);
            $parentXml->appendChild($objectXml);
            $this->addChildXml($dataObject, $objectName, $objectXml);
        }
    }
    
    private function arrayToXml($arrayDataObject, $arrayName, $parentXml)
    {
        for($i=0; $i<count($arrayDataObject); $i++) {
            $childObject = $arrayDataObject[$i];
            ////echo "<br/>Adding array item #$i";
            $this->objectToXml($childObject, $arrayName, $parentXml);
        }
    }
    
    private function propertyToXml($propertyValue, $propertyName, $parentXml) 
    {
        $propertyXml = $this->Document->createElement($propertyName, $propertyValue);
        $parentXml->appendChild($propertyXml);
    }
    
    private function notWellFormedToXml($childContents, $childName, $parentXml) 
    {
        if(is_scalar($childContents) || $childContents == false) {
            ////echo "<br/>Adding property element $childName => $childContents";
            $this->propertyToXml($childContents, $childName, $parentXml);
        } elseif(get_class($childContents) == 'DataObject') {
            ////echo "<br/><b>Adding child object $childName</b>";
            $this->objectToXml($childContents, $childName, $parentXml);
        } elseif(get_class($childContents) == 'ArrayDataObject') {
            ////echo "<br/><b>Adding array of $childName</b>";
            $this->arrayToXml($childContents, $childName, $parentXml);
        }
    
    }

}