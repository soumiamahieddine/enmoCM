<?php

class DataObjectController extends DOMDocument
{

    private $Schema;
    private $includes;
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
        $schema = $xpath->query('/xs:schema')->item(0);
        $includes = $xpath->query('./xs:include', $schema);
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
                $schemaContents = $includeXpath->query('/xs:schema/*');
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
        $rootElement = $this->xpath("/xs:schema/xs:element")->item(0);
        
        $objectName = $rootElement->name;
        
        $objectType = $this->getElementType($rootElement);
        
        if($rootElement->{'ds:table'}) {
            $dataSource = 'database';
        } elseif($rootElement->{'ds:xml'}) {
            $dataSource = 'xml';
        }
        $childElementsList = $this->listChildElements($objectType);
        
        $RootDataObject = new RootDataObject($objectName, $this);
        
        $this->loadChildObjects($childElementsList, $RootDataObject, $query);
        
        return $RootDataObject;
    }
    
    function loadDataObject($objectElement, $parentObject, $query=false) 
    {
        $isArray = $this->isArray($objectElement);
        
        /* Ref -> jump to referenced element
        ** Not allowed : @name, type, fixed, default, form, block, nillable 
        ** @ds:table, @ds:key-field */
        $objectElement = $this->getRefElement($objectElement);

        $objectName = $objectElement->name;
        
        $objectType = $this->getElementType($objectElement);
        
        if($objectElement->{'ds:table'}) {
            $dataSource = 'database';
        } elseif($objectElement->{'ds:xml'}) {
            $dataSource = 'xml';
        }
        
        $childElementsList = $this->listChildElements($objectType);
        
        $parentName = $parentObject->getTypeName();
        $parentElement = $this->getElementByName($parentName);
        $parentType = $this->getElementType($parentElement);
        
        switch($dataSource) {
        case 'xml':
            ////echo "<br/>Loading data from xml file ".$objectElement->{'ds:xml'};
            $xml = new DOMDocument();
            $xml->load($_SESSION['config']['corepath'] . $objectElement->{'ds:xml'});
            $xml->registerNodeClass('DOMElement', 'DataObject');
            $xpath = new DOMXpath($xml);
            if($objectElement->{'ds:xpath'}) {
                $query = $objectElement->{'ds:xpath'};
            }
            $xmlContents = $xpath->query($query);
            for($i=0; $i<$xmlContents->length; $i++) {
                $newDataObject = $this->Document->importNode($xmlContents->item($i),true);
                $parentObject->appendChild($newDataObject);
            }
            // TO DO : query only elements defined as properties and get child elements with their own datasource
            
            break;

        case 'database':
        default:
            // Select expression
            $selectExpression = $this->makeSelectExpression($objectType);
            
            // From
            $from = $this->getFromQuery($objectElement);
            
            // Where
            $whereClause = array();
            if($query) $whereClause[] = $query;
            //echo "<br/><b>Get relation between $objectName and $parentName</b>";
            $relationExpression = $this->makeRelationExpression($objectElement, $parentObject);
            if($relationExpression) $whereClause[] = $relationExpression;
            $where = $this->getWhereQuery($objectElement);
            if($where) $whereClause[] = $where;
                        
            $dbQuery  = "SELECT " . $selectExpression;
            $dbQuery .= " FROM " . $from;
            if(count($whereClause) > 0) $dbQuery .= " WHERE " . implode(' and ', $whereClause);

            //echo "<br/>" . $dbQuery;
            $db = new dbquery();
            $db->query($dbQuery);
            
            if($isArray) {
                $ArrayDataObject = new ArrayDataObject($parentObject);
                $parentObject->$objectName = $ArrayDataObject;
                
                while($arrayObject = $db->fetch_assoc()) {
                    $DataObject = new DataObject($objectName, $ArrayDataObject);
                    $ArrayDataObject[] = $DataObject;
                    
                    foreach($arrayObject as $propertyName => $propertyValue) {
                        $DataObject->$propertyName = $propertyValue;
                    }
                    $this->loadChildObjects($childElementsList, $DataObject);
                }
            } else {
                $DataObject = new DataObject($objectName, $parentObject);
                $parentObject->$objectName = $DataObject;
                
                $arrayObject = $db->fetch_assoc();
                
                foreach($arrayObject as $propertyName => $propertyValue) {
                    $DataObject->$propertyName = $propertyValue;
                }
                $this->loadChildObjects($childElementsList, $DataObject);
            }
            break;
            
        }   
    }
    
    function loadChildObjects($childElementsList, $DataObject, $query=false) 
    {
        for($j=0; $j<count($childElementsList); $j++) {
            $childElement = $childElementsList[$j];
            $refChildElement = $this->getRefElement($childElement);
            $childName = $refChildElement->name;
            ////echo "<br/>Loading child $childName";
            $this->loadDataObject($childElement, $DataObject, $query);
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
        //  ds:table not allowed
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
    
    function listChildElements($elementType) 
    {
        // Get children Elements (complexType)
        $childElements = $this->xpath("./*[name()='xs:sequence' or name()='xs:all']/xs:element", $elementType);
        for($i=0; $i<$childElements->length;$i++) {
            $childElement = $childElements->item($i);
            $refChildElement = $this->getRefElement($childElement);
            $childType = $this->getElementType($refChildElement);
            if(!$childType) die("Unable to find data type for element " . $refChildElement->name);
            if ($childType->tagName == 'xs:complexType') {
                $childElementList[] = $childElement;
            }
        }
        return $childElementList;
    }
    
    function makeSelectExpression($objectType) 
    {
        $selectExpressionParts = array();
        $childElements = $this->xpath("./*[name()='xs:sequence' or name()='xs:all']/xs:element", $objectType);
        for($i=0; $i<$childElements->length;$i++) {
            $childElement = $childElements->item($i);
            $childElement = $this->getRefElement($childElement);
            $childType = $this->getElementType($childElement);
            if(!$childType) die("Unable to find data type for element " . $childElement->name);
            if ($childType->tagName == 'xs:simpleType') {
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
        if($objectElement->{'ds:table'}) {
            return $objectElement->{'ds:table'};
        } else {
            return $objectElement->name;
        }
    }
    
    function getColumnName($element) 
    {
        if($element->{'ds:column'}) {
            $columnName = $element->{'ds:column'};
        } else {
            $columnName = $element->name;
        }
        return $columnName;
    }
    
    function getWhereQuery($element) 
    {
        $elementType = $this->getElementType($element);
        $whereElements = $this->xpath('./xs:annotation/xs:appinfo/ds:where', $element);
        for($i=0; $i<$whereElements->length; $i++) {
            $whereElement = $whereElements->item($i);
            $columnName = $whereElement->column;
            $columnElement = $this->xpath("./*[name()='xs:sequence' or name()='all']/xs:element[@column='".$columnName."'".
                " or @name='".$columnName."']", $elementType)->item(0);
            $whereType = $this->getElementType($columnElement);
            $whereValue = $this->enclose($whereElement->nodeValue, $whereType);
            $where[] = $columnName . " = " . $whereValue;

        }
        if(count($where) > 0) {
            return implode (' and ', $where);
        }
    }
    
    function makeRelationExpression($objectElement, $parentObject) 
    {
        $relationExpressionParts = array();
        $keyrefElement = $this->xpath("./xs:keyref", $objectElement)->item(0);
        if(!$keyrefElement) return false;
        //echo "<br/>Found keyref " . $keyrefElement->name . " on ref " . $keyrefElement->refer;
        // Get child key element
        //echo "<br/>List child keys from " . $keyrefElement->name;
        $childKeyFieldsList = $this->getKeyFieldsList($keyrefElement);

        // Get Parent element def
        //echo "<br/>List parent keys from " . $keyrefElement->refer;
        $referElement = $this->xpath("//xs:key[@name='".$keyrefElement->refer."']")->item(0);
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
    
    function getKeyFieldsList($keyElement) {
        $selectorXpath = $this->xpath("./xs:selector/@xpath", $keyElement)->item(0)->nodeValue;
        //echo "<br/>Selector xPath is " . $selectorXpath;
        $fieldsXpath = $this->xpath("./xs:field/@xpath", $keyElement);
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
    
    function xPathOnSchema($xPath, $contextElement) {
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
                $contextElement = $this->xpath("./*[name()='xs:sequence' or name()='xs:all']/xs:element[@name='".$xPathPart."' or @ref='".$xPathPart."']", $contextType)->item(0);
                $contextElement = $this->getRefElement($contextElement);
            }
        }
        return $contextElement;
    }

    function getRelationQuery($objectElement, $parentObject) 
    {
        $relationElement = $this->getRelationElement($objectElement);
        // make query where clause
        if($relationElement) {
            $parentKeyName = $relationElement->{'parent-key'};
            $parentKeyElement = $this->getElementByName($parentKeyName);
            $parentKeyType = $this->getElementType($parentKeyElement);
            $parentKeyValue = $this->enclose($parentObject->{$parentKeyName}, $parentKeyType);   
            return $relationElement->{'child-key'} . " = " . $parentKeyValue;
        }
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
        if($element->{'ds:key-column'}) {
            $keyColumnName = $element->{'ds:key-column'};
            $keyElement = $this->xpath("./*[name()='xs:sequence' or name()='all']/xs:element[@column='".$keyColumnName."'".
                " or @name='".$keyColumnName."']", $elementType)->item(0);
            $keyElementsList[] = $keyElement;
        } else {
            $keyRefs = $this->xpath('./xs:annotation/xs:appinfo/ds:key', $element);
            for($j=0; $j<$keyRefs->length; $j++) {
                $keyColumnName = $keyRefs->item($j)->column;
                $keyElement = $this->xpath("./*[name()='xs:sequence' or name()='all']/xs:element[@column='".$keyColumnName."'".
                    " or @name='".$keyColumnName."']", $elementType)->item(0);
                $keyElementsList[] = $keyElement;
            }
        }
  
        return $keyElementsList;
    }
    
    function getJoins($element) 
    {
        $joinNodes = $this->xpath('./xs:annotation/xs:appinfo/ds:join', $element);
        for($i=0; $i<$joinNodes->length; $i++) {
            $joinNode = $joinNodes->item($i);
            $joinType = $joinNode->{'join-type'};
            $joinTable = $joinNode->table;
            $joinArgs = $this->xpath('./ds:on', $joinNode);
            for($j=0; $j<$joinArgs->length; $j++) {
                $joinArg = $joinArgs->item($j);
                
            }
        }
    }
    
    function getElementByName($elementName) 
    {
        return $this->xpath("//xs:element[@name='".$elementName."']")->item(0);
    }
    
    function getElementType($element) 
    {
        if($element->type) {
            $typeName = $element->type;
            if(substr($typeName, 0, 3) == 'xs:') {
                $elementType = $this->Schema->createElement('xs:simpleType');
                $elementType->name = $typeName;
            } else {
                $elementType = $this->xpath("//*[(name()='xs:complexType' or name()='xs:simpleType') and @name='".$typeName."']")->item(0);
            }
        } else { 
            $elementType = $this->xpath("./*[(name()='xs:complexType' or name()='xs:simpleType')]", $element)->item(0);
        }

        return $elementType;
    }
    
    function getSimpleTypeBaseTypeName($simpleType) 
    {
        if(substr($simpleType->name, 0, 3) == 'xs:') {
            $simpleTypeBaseName = $simpleType->name;
        } else {
            $simpleTypeBase = $this->getSimpleTypeBaseType($simpleType);
            $simpleTypeBaseName = $simpleTypeBase->name;
        }
        return $simpleTypeBaseName;
    }
    
    function getSimpleTypeBaseType($simpleType) 
    {
        $typeContents = $this->xpath("./*[name()='xs:restriction' or name()='xs:list']", $simpleType)->item(0);
        $baseTypeName = $typeContents->base;
        if(substr($baseTypeName, 0, 3) == 'xs:') {
            $simpleTypeBase = $this->Schema->createElement('xs:simpleType');
            $simpleTypeBase->name = $baseTypeName;
        } else {
            $baseType = $this->xpath("//xs:simpleType[@name='".$baseTypeName."']")->item(0);
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
    function asXML() {
        return $this->Document->saveXML();
    }
    
    function validate() {
        $this->xml = new XMLDocument();
        //Object => create element
        $rootName = $rootDataObject->getTypeName();
        
        $this->toXml($rootName, $rootDataObject, $this->xml);
        
        //echo '<pre><b>VALIDATING XML</b>';
        libxml_use_internal_errors(true);
       
        if($this->Document->schemaValidateSource($this->Schema->saveXML())) {
            //echo "<br/>Valid<br/>";
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
                $display .= " on line <b>$error->line</b>\n";
            }
            //echo $display;
            libxml_clear_errors();
        }        
    }
    
}