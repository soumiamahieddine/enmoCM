<?php
class DataObjectSchema 
    extends DOMDocument 
{
    
    private $includes = array();
    private $xpath;
    
    public function loadSchema($XsdFile)
    {
        $this->load($XsdFile);
        $this->xpath = new DOMXPath($this);
        $this->processInclusions($this);
    }
    
    public function processInclusions($Schema) 
    {
        $Schema->registerNodeClass('DOMElement', 'SchemaElement');
        $Schema->Schema->formatOutput = true;
        $Schema->Schema->preserveWhiteSpace = true;
        
        $xpath = new DOMXPath($Schema);
        $schema = $xpath->query('/xsd:schema')->item(0);
        $includes = $xpath->query('./xsd:include', $schema);
        for($i=0; $i<$includes->length; $i++) {
            $include = $includes->item($i);
            $schemaLocation = $include->schemaLocation;
            if(!$this->isIncluded($schemaLocation)) {
                $includeSchema = new DataObjectSchema();
                $includeSchema->load($_SESSION['config']['corepath'] . $schemaLocation);
                $this->processInclusions($includeSchema);
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
    
    function isIncluded($schemaLocation)
    {
        if (in_array($schemaLocation, $this->includes)) {
            return true;
        }
    }
    
    private function xpath($query, $contextElement=false) 
    {
        if(!$contextElement) $contextElement = $this->documentElement;
        $result = @$this->xpath->query($query, $contextElement);
        if(!$result) {
            die("Schema Query error : " . $query);
        }
        return $result;
    }
    
    // Get all sources
    public function getSources()
    {
        $dasSources = array();
        $DSnodes = $this->xpath('//xsd:annotation/xsd:appinfo/das:source');
        for($i=0; $i<$DSnodes->length; $i++) {
            $dasSource = $DSnodes->item($i);
            if($dasSource->parentNode->parentNode->parentNode->tagName != 'xsd:schema') {
                $parentName = $dasSource->parentNode->parentNode->parentNode->name;
                $dasSource->name = $parentName;
            }
            $dasSources[] = $dasSource;
        }
        return $dasSources;
    }
   
    public function getSourceOptions($datasource, $driver)
    {
        $optionNodes = $this->xpath("./das:option[@driver='".$driver."']", $datasource);
        for($i=0; $i<$optionNodes->length; $i++) {
            $options[] = $optionNodes->item($i);
        }
        return $options;
    }
    
    // Get all types
    public function getDatatypes()
    {
        $datatypes = array();
        $DTnodes = $this->xpath('//xsd:simpleType');
        for($i=0; $i<$DTnodes->length; $i++) {
            $datatype = $DTnodes->item($i);
            if($datatype->parentNode->tagName != 'xsd:schema') {
                $parentName = $datatype->parentNode->name;
                $datatype->name = $parentName;
            }
            $datatypes[] = $datatype;
        }
        return $datatypes;
    }
    
    public function getDatatypeSqltype($datatype, $driver) 
    {
        $sqltypesNodes = $this->xpath("./xsd:annotation/xsd:appinfo/das:sqltype[@driver='".$driver."']", $datatype);
        if($sqltypesNodes->length > 0) {
            return $sqltypesNodes->item(0);
        }
    }
    
    // get all relations
    public function getRelations()
    {
        $relations = array();
        $Rnodes = $this->xpath('//xsd:annotation/xsd:appinfo/das:relation');
        for($i=0; $i<$Rnodes->length; $i++) {
            $relation = $Rnodes->item($i);
            if($relation->parentNode->parentNode->parentNode->tagName != 'xsd:schema') {
                $parentName = $relation->parentNode->parentNode->parentNode->name;
                $relation->name = $parentName;
            }
            $relations[] = $relation;
        }
        return $relations;
    }
    
    // Get all object elements at root
    public function getObjectSchemas() 
    {
        return $this->xpath('/xsd:schema/xsd:element');
    }
    
    //
    public function getSchemaElement($schemaPath)
    {
        //echo "<br/>getSchemaElement($schemaPath)";
        return $this->xpath($schemaPath)->item(0);
        
    }
    
    public function getObjectSchema($objectName)
    {
        $objectSchemas = $this->xpath("/xsd:schema/xsd:element[@name='".$objectName."']");
        
        if($objectSchemas->length == 0) die("<br/><b>Unable to find root element named $rootTypeName</b>");
        
        return $objectSchemas->item(0);
    }
     
    public function getDasKey($elementName)
    {
        $objectElement = $this->getRootElement($elementName);
        $keyColumnNames = $objectElement->{'das:key-columns'};
        return $keyColumnNames;
    }
    
 
    //*************************************************************************
    // OLD FUNCTIONS
    //*************************************************************************
    public function isEnclosedType($propertyType) 
    {
        $baseTypeName = $this->getBaseTypeName($propertyType);
        if(!in_array(
            $baseTypeName,
            array(
                'xsd:boolean',
                'xsd:double', 
                'xsd:decimal',
                    'xsd:integer',
                        'xsd:nonPositiveInteger',
                            'xsd:negativeInteger',
                        'xsd:long',
                            'xsd:int', 
                            'xsd:short', 
                            'xsd:byte',
                        'xsd:nonNegativeInteger',
                            'xsd:positiveInteger',
                            'xsd:unsignedLong',
                                'xsd:unsignedInt',
                                    'xsd:unsignedShort',
                                        'xsd:unsignedByte',
                'xsd:float',
                )
            )
        ) {
            return true;
        }
    }
    
    private function getBaseTypeName($propertyType) 
    {
        if(substr($propertyType->name, 0, 4) == 'xsd:') {
            $baseTypeName = $propertyType->name;
        } else {
            $typeContents = $this->xpath("./*[name()='xsd:restriction' or name()='xsd:list' or name()='xsd:union']", $propertyType)->item(0);
            if($typeContents->base && substr($typeContents->base, 0, 4) == 'xsd:') {
                $baseTypeName = $typeContents->base;
            } elseif($typeContents->{'das:baseType'} && substr($typeContents->{'das:baseType'}, 0, 4) == 'xsd:') {
                $baseTypeName = $typeContents->{'das:baseType'};
            } else {
                $baseType = $this->xpath("//xsd:simpleType[@name='".$typeContents->base."']")->item(0);
                $baseTypeName = $this->getBaseTypeName($baseType);
            }
        }
        return $baseTypeName;
    }
    
    private function xPathOnSchema($xPath, $contextElement) 
    {
        $xPathParts = explode('/', $xPath);
        for($i=0; $i<count($xPathParts); $i++) {
            $contextType = $this->schema->getElementType($contextElement); 
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

    
    
}

class SchemaElement extends DOMElement {
    
    private function xpath($query, $contextElement=false) 
    {
        $xpath = new DOMXpath($this->ownerDocument);
        if(!$contextElement) $contextElement = $this->ownerDocument->documentElement;
        return $xpath->query($query, $contextElement);
    }
    
    function __get($name) 
    {
        if($this->hasAttribute($name)) {
            return $this->getAttribute($name);
        }
    }
    
    function __set($name, $value) 
    {
        $this->setAttribute($name, $value);
    }
    
    public function getType() 
    {
        $xpath = new DOMXpath($this->ownerDocument);
        if($this->type) {
            $typeName = $this->type;
            if(substr($typeName, 0, 4) == 'xsd:') {           
                $elementType = $this->ownerDocument->createElement('xsd:simpleType');
                $elementType->name = $typeName;
            } else {
                $elementType = $this->xpath("//*[(name()='xsd:complexType' or name()='xsd:simpleType') and @name='".$typeName."']")->item(0);
            }
        } else { 
            $elementType = $this->xpath("./*[(name()='xsd:complexType' or name()='xsd:simpleType')]", $this)->item(0);
        }
        if(!$elementType) die("Unable to find type for element $this->name");
        return $elementType;
    }
    
    public function getChildElements()
    {
        $objectType = $this->getType();
        $childElements = $this->xpath("./*[name()='xsd:sequence' or name()='xsd:all']/xsd:element", $objectType);
        return $childElements;
    }
    
    public function isDataObjectArray() 
    {
        if($this->minOccurs > 1 
            || ($this->maxOccurs > 1 
                || $this->maxOccurs == 'unbounded')) {
            return true;
        }
    }
    
    public function getRootElement($elementName)
    {
        return $this->xpath("/xsd:schema/xsd:element[@name='".$elementName."']")->item(0);
    }
    
    public function getRefElement() 
    {
        if($this->ref) {
            $refObjectElement = $this->getRootElement($this->ref);
            if(!$refObjectElement) die ("Referenced element named " . $this->ref . " not found in schema");
            return $refObjectElement;
        } else {
            return $this;
        }
    }
    
    public function getSourceName()
    {
        if($this->{'das:source'}) {
            return $this->{'das:source'};
        } else {
            return $this->name;
        }
    }
}

?>