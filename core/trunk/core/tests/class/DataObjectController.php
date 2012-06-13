<?php

class DataObjectController extends DOMDocument
{

    private $Schema;
    private $includes;
    private $RootDataObject;
    private $Document;
    
    function DataObjectController() 
    {
        require_once 'core/tests/class/XSDDocument.php';
        require_once 'core/tests/class/XMLDocument.php';
        require_once 'core/tests/class/RootDataObject.php';
        require_once 'core/tests/class/DataObject.php';
        require_once 'core/tests/class/ArrayDataObject.php';
    }
    
    function loadSchema($xsdFile) 
    {
        $this->Schema = new XSDDocument();
        $this->Schema->load($xsdFile);
        $this->Schema->registerNodeClass('DOMElement', 'XSDElement');
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
    function processIncludes($XSDDocument) 
    {
        $xpath = new DOMXPath($XSDDocument);
        $schema = $xpath->query('/xsd:schema')->item(0);
        $includes = $xpath->query('./xsd:include', $schema);
        $nl = $includes->length;

        for($i=0; $i<$includes->length; $i++) {
            $include = $includes->item($i);
            $schemaLocation = $include->schemaLocation;
            if(!$this->includes || !in_array($schemaLocation, $this->includes)) {
                $includeXsd = new XSDDocument();
                $includeXsd->load($_SESSION['config']['corepath'] . $schemaLocation);
                $includeXsd->registerNodeClass('DOMElement', 'XSDElement');
                $includeXsd->Schema->formatOutput = true;
                $includeXsd->Schema->preserveWhiteSpace = true;
                
                $this->processIncludes($includeXsd);
                $includeXpath = new DOMXpath($includeXsd);
                $schemaContents = $includeXpath->query('/xsd:schema/*');
                for($j=0; $j<$schemaContents->length; $j++) {
                    $importNode = $schemaContents->item($j);
                    $importedNode = $XSDDocument->importNode($importNode, true);
                    $XSDDocument->documentElement->appendChild($importedNode);
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
    function xpath($query, $contextElement=false) 
    {
        if(!$contextElement) $contextElement = $this->Schema->documentElement;
        $xpath = new DOMXpath($this->Schema);
        return $xpath->query($query, $contextElement);
    }
    
    //*************************************************************************
    // DATA OBJECT FUNCTIONS
    //*************************************************************************
    function loadRootDataObject($query=false) 
    {
        $rootElement = $this->xpath("/xsd:schema/xsd:element")->item(0);
        $rootName = $rootElement->name;
        $rootType = $this->getElementType($rootElement);
   
        $this->RootDataObject = new DataObject($rootName);
        $childElementsList = $this->listChildElements($rootType);
        
        $this->loadChildObjects($childElementsList, $this->RootDataObject, $rootType, $query);
        
        return $this->RootDataObject;
    }
    
    function loadDataObject($objectElement, $parentObject, $parentType, $query=false) 
    {
        $isArray = $this->isArray($objectElement);
        
        /* Ref -> jump to referenced element
        ** Not allowed : @name, type, fixed, default, form, block, nillable 
        ** @das:table, @das:key-field */
        $objectElement = $this->getRefElement($objectElement);

        if($objectElement->{'das:table'}) {
            $dataSource = 'database';
        } elseif($objectElement->{'das:xml'}) {
            $dataSource = 'xml';
        }
        
        switch($dataSource) {
        case 'xml':
            $this->loadFromXml($objectElement, $parentObject, $parentType, $isArray, $query);
            // TO DO : query only elements defined as properties and get child elements with their own datasource
            break;

        case 'database':
        default:
            $this->loadFromDatabase($objectElement, $parentObject, $parentType, $isArray, $query);
            break;
        }   
    }
    
    function loadChildObjects($childElementsList, $parentObject, $parentType, $query=false) 
    {
        for($j=0; $j<count($childElementsList); $j++) {
            $childElement = $childElementsList[$j];
            $refChildElement = $this->getRefElement($childElement);
            $childName = $refChildElement->name;
            //echo "<br/>Loading child $childName";
            $this->loadDataObject($childElement, $parentObject, $parentType, $query);
            
        }
    }
    
    function saveDataObject($dataObject) 
    {
        $objectName = $dataObject->getTypeName();
        //echo "<br/><br/><b>Saving element " . $objectName . "</b>";
        
        $objectElement = $this->getElementByName($objectName);
        
        $parentObject = $dataObject->parentNode;
        $parentObjectName = $parentObject->getTypeName();
        $parentObjectElement = $this->getElementByName($parentObjectName);
        //echo "<br/>Element has a parent named " . $parentObjectName;

        // Ref
        // If specified type, fixed, default, form, block, nillable not allowed
        //  das:table not allowed
        //*********************************************************************
        $refObjectElement = $this->getRefElement($objectElement);
        
        $objectType = $this->getElementType($refObjectElement);
        
        
        // Key columns
        $keyElementsList = $this->listKeyElements($refObjectElement);
        //echo "<br/>Element has " . count($keyElementsList) . " key(s)";
              
        // properties columns
        $propertyElementsList = $this->listPropertyElements($objectType);
        //echo "<br/>Element has " . count($propertyElementsList) . " propertie(s)";
        
        // Update table
        $table = $this->getFromQuery($refObjectElement);
        
        for($j=0; $j<count($propertyElementsList); $j++) {
            $propertyElement = $propertyElementsList[$j];
            $propertyElement = $this->getRefElement($propertyElement);
            if(in_array($propertyElement, $keyElementsList, true)) {
                //echo "<br/>Remove element " . $this->getRefElement($propertyElement)->name . " from updated properties because it is a key";
                continue;
            }
            $updates[] = $this->getUpdateQuery($propertyElement, $dataObject); 
        } 
                
        // Where
        for($j=0; $j<count($keyElementsList); $j++) {
            $keyElement = $keyElementsList[$j];
            //$keyElement = $this->getRefElement($keyElement);
            $keys[] = $this->getKeyQuery($keyElement, $dataObject); 
        }
        if(count($keys) == 0) die("No primary key defined for element $objectName"); 
        
        // Query
        //*********************************************************************
        $dbquery = "UPDATE " . $table
            . " SET " . implode(', ', $updates)
            . " WHERE " . implode(', ', $keys);
        //echo "<br/>" . $dbquery; 
        $db = new dbquery();
        //$db->query($dbquery);
        
    }
    
    function loadFromDatabase($objectElement, $parentObject, $parentType, $isArray, $query=false) 
    {
        $objectName = $objectElement->name;
        $objectType = $this->getElementType($objectElement);
        
        $childElementsList = $this->listChildElements($objectType);
           
        $selectExpression = $this->makeSelectExpression($objectType);
        $from = $this->getFromQuery($objectElement);

        $whereClause = array();
        if($query) $whereClause[] = $query;
        //echo "<br/><b>Get relation between $objectName and $parentName</b>";
        $relationExpression = $this->makeRelationExpression($objectElement, $parentObject, $parentType);
        if($relationExpression) $whereClause[] = $relationExpression;
        $whereExpression = $this->getWhereExpression($objectElement);
        
        if($whereExpression) $whereClause[] = $whereExpression;
        
        // Query
        $dbQuery  = "SELECT " . $selectExpression;
        $dbQuery .= " FROM " . $from;
        if(count($whereClause) > 0) $dbQuery .= " WHERE " . implode(' and ', $whereClause);

        echo "<br/>" . $dbQuery;
        $db = new dbquery();
        $db->query($dbQuery);

        if($isArray) {
            $ArrayDataObject = new ArrayDataObject();
            $parentObject->$objectName = $ArrayDataObject;
            
            while($arrayObject = $db->fetch_assoc()) {
                $DataObject = new DataObject($objectName);
                $ArrayDataObject[] = $DataObject;
                
                foreach($arrayObject as $propertyName => $propertyValue) {
                    $DataObject->$propertyName = $propertyValue;
                }
                //echo "<br/>Load ". count($childElementsList). "child(ren) object(s) for $objectName #" . count($ArrayDataObject); 
                $this->loadChildObjects($childElementsList, $DataObject, $objectType);
            }
        } else {
            $DataObject = new DataObject($objectName);
            $parentObject->$objectName = $DataObject;
            
            $arrayObject = $db->fetch_assoc();
            
            foreach($arrayObject as $propertyName => $propertyValue) {
                $DataObject->$propertyName = $propertyValue;
            }
            $this->loadChildObjects($childElementsList, $DataObject, $objectType);
        }
    
    }
    
    function loadFromXml($objectElement, $parentObject, $parentType, $isArray, $query=false) 
    {
        $objectName = $objectElement->name;
        $objectType = $this->getElementType($objectElement);
        
        $childElementsList = $this->listChildElements($objectType);
        
        $xml = new DOMDocument();
        $xml->load($_SESSION['config']['corepath'] . $objectElement->{'das:xml'});
        $xpath = new DOMXpath($xml);
        if($objectElement->{'das:xpath'}) {
            $query = $objectElement->{'das:xpath'};
        }
        $xmlContents = $xpath->query($query);
        if($isArray) {
            $ArrayDataObject = new ArrayDataObject();
            $parentObject->$objectName = $ArrayDataObject;
            echo "<br/>Found " . $xmlContents->length . " elements";
            for($i=0; $i<$xmlContents->length; $i++) {
                $xmlObject = $xmlContents->item($i);
                $DataObject = new DataObject($objectName);
                $ArrayDataObject[] = $DataObject;
                $propertyNodes = $xmlObject->childNodes;
                echo "<br/>Found " . $propertyNodes->length . " properties";
                for($j=0; $j<$propertyNodes->length; $j++) {
                    $propertyNode = $propertyNodes->item($j);
                    if($propertyNode->nodeType == XML_TEXT_NODE) continue;
                    $propertyName = $propertyNode->tagName;
                    echo "<br/>Property " . $propertyName;
                    $propertyValue = $propertyNode->nodeValue;
                    $DataObject->$propertyName = $propertyValue;
                }
                $this->loadChildObjects($childElementsList, $DataObject, $objectType);
            }
        } else {
            $DataObject = new DataObject($objectName);
            $parentObject->$objectName = $DataObject;
            
            $xmlObject = $xmlContents->item(0);
            
            $propertyNodes = $xmlObject->childNodes;
            for($j=0; $j<$propertyNodes->length; $j++) {
                $propertyNode = $propertyNodes->item($j);
                if($propertyNode->nodeType == XML_TEXT_NODE) continue;
                $propertyName = $propertyNode->tagName;
                $propertyValue = $propertyNode->nodeValue;
                $DataObject->$propertyName = $propertyValue;
            }
            $this->loadChildObjects($childElementsList, $DataObject);
        }
    }
    
    //*************************************************************************
    // SUB ROUTINES
    //*************************************************************************
    function isArray($element) 
    {
        if($element->minOccurs > 1 || ($element->maxOccurs > 1 || $element->maxOccurs == 'unbounded')) {
            return true;
        }
    }
    
    function getRefElement($objectElement) 
    {
        if($objectElement->ref) {
            $refObjectElement = $this->getElementByName($objectElement->ref);
            if(!$refObjectElement) die ("Referenced element named " . $objectElement->ref . " not found in schema");
            return $refObjectElement;
        } else {
            return $objectElement;
        }
    }
    
    function listChildElements($objectType) 
    {
        // Get children Elements (complexType)
        $childElements = $this->xpath("./*[name()='xsd:sequence' or name()='xsd:all']/xsd:element", $objectType);
        for($i=0; $i<$childElements->length;$i++) {
            $childElement = $childElements->item($i);
            $refChildElement = $this->getRefElement($childElement);
            $childType = $this->getElementType($refChildElement);
            if(!$childType) die("Unable to find data type for child " . $refChildElement->name);
            if ($childType->tagName == 'xsd:complexType') {
                $childElementList[] = $childElement;
            }
        }
        return $childElementList;
    }
    
    function makeSelectExpression($objectType) 
    {
        $selectExpressionParts = array();
        $childElements = $this->xpath("./*[name()='xsd:sequence' or name()='xsd:all']/xsd:element", $objectType);
        for($i=0; $i<$childElements->length;$i++) {
            $childElement = $childElements->item($i);
            $childElement = $this->getRefElement($childElement);
            $childType = $this->getElementType($childElement);
            if(!$childType) die("Unable to find data type for property " . $childElement->name);
            if ($childType->tagName == 'xsd:simpleType') {
                $propertyName = $childElement->name;
                $propertyType = $this->getElementType($childElement);
                $columnName = $this->getColumnName($childElement);
                // DEFAULT and FIXED
                if($childElement->{'default'}) {
                    $defaultValue = $this->enclose($childElement->{'default'}, $propertyType);
                    $selectExpressionParts[] = "COALESCE(" . $columnName . ", " . $defaultValue . ") as " . $propertyName;
                }
                else if($childElement->fixed) {
                    $fixedValue = $this->enclose($childElement->fixed, $propertyType);
                    $selectExpressionParts[] = $fixedValue . " as " . $propertyName;
                } else {
                    $selectExpressionParts[] = $columnName . " as " . $propertyName;
                }
            }
        }
        if(count($selectExpressionParts) > 0) return implode(', ', $selectExpressionParts);
    }
    
    function getUpdateQuery($propertyElement, $dataObject) 
    {
        $propertyName = $propertyElement->name;
        $propertyType = $this->getElementType($propertyElement);
        $columnName = $this->getColumnName($propertyElement);
        $columnValue = $this->enclose($dataObject->{$propertyName}, $propertyType);
        // DEFAULT
        if($propertyElement->{'default'}) {
            $defaultValue = $this->enclose($propertyElement->{'default'}, $propertyType);
            return $columnName  . " = COALESCE(" . $columnValue . ", " . $defaultValue . ")";
        }
        else {
            return $columnName  . " = " . $columnValue;
        } 
    }   
    
    function getFromQuery($objectElement) 
    {
        if($objectElement->{'das:table'}) {
            return $objectElement->{'das:table'};
        } else {
            return $objectElement->name;
        }
    }
    
    function getColumnElement($columnName, $objectType) 
    {
        $columnElement = $this->xpath("./*[name()='xsd:sequence' or name()='all']/xsd:element[@das:column='".$columnName."'".
                " or @name='".$columnName."' or @ref='".$columnName."']", $objectType)->item(0);
        $columnElement = $this->getRefElement($columnElement);
        return $columnElement;
    }
    
    function getColumnName($element) 
    {
        if($element->{'das:column'}) {
            $columnName = $element->{'das:column'};
        } else {
            $columnName = $element->name;
        }
        return $columnName;
    }
        
    function getWhereExpression($objectElement) 
    {
        if($objectElement->{'das:query'}) {
            return $objectElement->{'das:query'};
        }
    }
    
    function makeRelationExpression($objectElement, $parentObject, $parentType)
    {
        $objectType = $this->getElementType($objectElement);
        $relationExpressionParts = array();
        $relationElements = $this->xpath("./xsd:annotation/xsd:appinfo/das:relation", $objectElement);
        for($i=0; $i<$relationElements->length; $i++) {
            $relationElement = $relationElements->item($i);
            $childKeyName = $relationElement->{'child-key'};
            $childKeyElement = $this->getColumnElement($childKeyName, $objectType);
            
            $childKeyType = $this->getElementType($childKeyElement);
            $childColumnName = $this->getColumnName($childKeyElement);
            
            $parentKeyName = $relationElement->{'parent-key'};
            $parentKeyElement = $this->getColumnElement($parentKeyName, $parentType);
            $parentKeyType = $this->getElementType($parentKeyElement);
            $parentKeyValue = $this->enclose($parentObject->{$parentKeyName}, $childKeyType);  
            $relationExpressionParts[] = $childColumnName . " = " . $parentKeyValue;
        }
        if(count($relationExpressionParts) > 0) return implode(' and ', $relationExpressionParts);
    }
    
    function makeForeignKeyExpression($objectElement, $parentObject) 
    {
        $relationExpressionParts = array();
        $keyrefElement = $this->xpath("./xsd:keyref", $objectElement)->item(0);
        if(!$keyrefElement) return false;
        //echo "<br/>Found keyref " . $keyrefElement->name . " on ref " . $keyrefElement->refer;
        // Get child key element
        //echo "<br/>List child keys from " . $keyrefElement->name;
        $childKeyFieldsList = $this->getKeyFieldsList($keyrefElement);

        // Get Parent element def
        //echo "<br/>List parent keys from " . $keyrefElement->refer;
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
            //echo "<br/>Relation expression is " . implode(' and ', $relationExpressionParts);
            return implode(' and ', $relationExpressionParts);
        }
    }
    
    function getKeyFieldsList($keyElement) 
    {
        $selectorXpath = $this->xpath("./xsd:selector/@xpath", $keyElement)->item(0)->nodeValue;
        //echo "<br/>Selector xPath is " . $selectorXpath;
        $fieldsXpath = $this->xpath("./xsd:field/@xpath", $keyElement);
        //echo "<br/>Found " . $fieldsXpath->length . " key fields";
        $contextElement = $this->xpath("./parent::*", $keyElement)->item(0);
        $contextElement = $this->getRefElement($contextElement);
        //echo "<br/>Context element is " . $contextElement->name;
        $selectorElement = $this->xPathOnSchema($selectorXpath, $contextElement);
        $selectorElement = $this->getRefElement($selectorElement);
        //echo "<br/>Selected element is " . $selectorElement->name;
        for($i=0; $i<$fieldsXpath->length; $i++) {
            //echo "<br/>Selecting field element with xpath " . $fieldsXpath->item($i)->nodeValue;
            $keyFieldElement = $this->xPathOnSchema($fieldsXpath->item($i)->nodeValue, $selectorElement);
            //echo "<br/>Key field element is " . $keyFieldElement->name;
            $keyFieldElements[] = $keyFieldElement;
        }
        return $keyFieldElements;
    }
    
    function xPathOnSchema($xPath, $contextElement) 
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

    function getKeyQuery($keyElement, $dataObject) 
    {
        $keyName = $keyElement->name;
        $keyType = $this->getElementType($keyElement);
        $keyValue = $this->enclose($dataObject->{$keyName}, $keyType);
        $columnName = $this->getColumnName($keyElement);
        return $columnName . " = " . $keyValue;
    }
    
    function listKeyElements($element) 
    {
        if($element->{'das:key-column'}) {
            $keyColumnName = $element->{'das:key-column'};
            $keyElement = $this->xpath("./*[name()='xsd:sequence' or name()='all']/xsd:element[@column='".$keyColumnName."'".
                " or @name='".$keyColumnName."']", $elementType)->item(0);
            $keyElementsList[] = $keyElement;
        } else {
            $keyRefs = $this->xpath('./xsd:annotation/xsd:appinfo/das:key', $element);
            for($j=0; $j<$keyRefs->length; $j++) {
                $keyColumnName = $keyRefs->item($j)->column;
                $keyElement = $this->xpath("./*[name()='xsd:sequence' or name()='all']/xsd:element[@column='".$keyColumnName."'".
                    " or @name='".$keyColumnName."']", $elementType)->item(0);
                $keyElementsList[] = $keyElement;
            }
        }
  
        return $keyElementsList;
    }
    
    function getJoins($element) 
    {
        $joinNodes = $this->xpath('./xsd:annotation/xsd:appinfo/das:join', $element);
        for($i=0; $i<$joinNodes->length; $i++) {
            $joinNode = $joinNodes->item($i);
            $joinType = $joinNode->{'join-type'};
            $joinTable = $joinNode->table;
            $joinArgs = $this->xpath('./das:on', $joinNode);
            for($j=0; $j<$joinArgs->length; $j++) {
                $joinArg = $joinArgs->item($j);
                
            }
        }
    }
    
    function getElementByName($elementName) 
    {
        return $this->xpath("//xsd:element[@name='".$elementName."']")->item(0);
    }
    
    function getElementType($element) 
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

        return $elementType;
    }
    
    function getSimpleTypeBaseTypeName($simpleType) 
    {
        if(substr($simpleType->name, 0, 4) == 'xsd:') {
            $simpleTypeBaseName = $simpleType->name;
        } else {
            $simpleTypeBase = $this->getSimpleTypeBaseType($simpleType);
            $simpleTypeBaseName = $simpleTypeBase->name;
        }
        return $simpleTypeBaseName;
    }
    
    function getSimpleTypeBaseType($simpleType) 
    {
        $typeContents = $this->xpath("./*[name()='xsd:restriction' or name()='xsd:list']", $simpleType)->item(0);
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
    
    function enclose($value, $elementType) 
    {
        $baseTypeName = $this->getSimpleTypeBaseTypeName($elementType);
        if($this->isQuoted($baseTypeName)) {
            $value = "'" . $value . "'";
        } 
        return $value;
    }
    
    function isQuoted($typeName) 
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
    
    /**************************************************************************
    ** XML
    **************************************************************************/
    function objectToXml($DataObject, $parentXml) 
    {
        $objectName = $DataObject->getTypeName();
        $objectXml = $this->Document->createElement($objectName);
        $parentXml->appendChild($objectXml);
        $this->addChildXml($DataObject, $objectXml);
    }
    
    function addChildXml($parentObject, $parentXml) 
    {
        foreach($parentObject as $childName => $childValue) {
            if(is_scalar($childValue)) {
                $propertyXml = $this->Document->createElement($childName, $childValue);
                $parentXml->appendChild($propertyXml);
            } elseif(get_class($childValue) == 'DataObject') {
                $this->objectToXml($childValue, $parentXml);
            } elseif(get_class($childValue) == 'ArrayDataObject') {
                for($i=0; $i<count($childValue); $i++) {
                    $childObject = $childValue[$i];
                    $this->objectToXml($childObject, $parentXml);
                }
            }
        }
    }
    
    function validate() 
    {
        $this->Document = new XMLDocument();
        //Object => create element
        $rootName = $this->RootDataObject->getTypeName();
        $rootXml = $this->Document->createElement($rootName);
        $this->Document->appendChild($rootXml);
        $this->addChildXml($this->RootDataObject, $rootXml);
        
        echo htmlspecialchars($this->Document->saveXML());
        //echo '<pre><b>VALIDATING XML</b>';
        
        $useLibxmlErrors = false;
        $catchExceptions = true;
        if($useLibxmlErrors) {
            libxml_use_internal_errors(true);
            if($this->Document->schemaValidateSource($this->Schema->saveXML())) {
                echo "<br/><b>Valid</b><br/>";
            } else {
                $this->libxmlErrorHandler();
            } 
        } else {
            if($catchExceptions) {
                try {
                    $this->Document->schemaValidateSource($this->Schema->saveXML());
                } catch (DOMException $e) {
                    print_r($e);
                }
            
            } else {
                set_error_handler('DataObjectController::DOMErrorHandler');
                if($this->Document->schemaValidateSource($this->Schema->saveXML())) {
                    echo "<br/><b>Valid</b><br/>";
                } 
                restore_error_handler();
            }
        }
    }
    
    function libxmlErrorHandler() {
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
        echo $display;
        libxml_clear_errors();
    }
    
    function DOMErrorHandler($errno, $errstr, $errfile, $errline)
    {
        echo "<br/>errno=$errno, errstr=$errstr, errfile=$errfile, errline=$errline";
    }
    
    function _DOMErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if ($errno==E_WARNING && (substr_count($errstr,"DOMDocument::schemaValidateSource()")>0))
        {
            throw new DOMException($errstr);
        }
        else {
            return false;
        }
    }
    
}