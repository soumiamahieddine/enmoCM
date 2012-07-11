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
        /*
        $this->registerNodeClass('DOMElement', 'SchemaElement');
        $sxe = simplexml_import_dom($this);
        $namespaces = $sxe->getNamespaces(true); 
        foreach ($namespaces as $prefix => $URI) {
            //$xpath->registerNamespace($prefix, $URI);
            $this->xpath->registerNamespace($prefix, $URI);
        }
        $this->xinclude();*/
        //echo htmlspecialchars($this->saveXML());
        
    }
    
    public function processInclusions($Schema) 
    {
        $Schema->registerNodeClass('DOMElement', 'SchemaElement');
        $Schema->Schema->formatOutput = true;
        $Schema->Schema->preserveWhiteSpace = true;
        
        $xpath = new DOMXPath($Schema);
        
        $sxe = simplexml_import_dom($Schema);
        $namespaces = $sxe->getNamespaces(true); 
        foreach ($namespaces as $prefix => $URI) {
            $xpath->registerNamespace($prefix, $URI);
            $this->xpath->registerNamespace($prefix, $URI);
        }
        
        $schemaNode = $xpath->query('/xsd:schema')->item(0);
        
        $includes = $xpath->query('./xsd:include', $schemaNode);
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
            $schemaNode->removeChild($include);
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
    
    public function getObjectSchema($objectName)
    {
        $objectSchemas = $this->xpath("/xsd:schema/xsd:element[@name='".$objectName."']");
        if($objectSchemas->length == 0) die("Unable to find root element named $objectName</b>");
        return $objectSchemas->item(0);
    }
     
    public function getDasKey($objectName)
    {
        $objectSchema = $this->getObjectSchema($objectName);
        $keyColumnNames = $objectSchema->{'das:key-columns'};
        return $keyColumnNames;
    }
 
    //OLD
    //*************************************************************************
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
    
    public function getColumnElements()
    {
        $typeElements = $this->xpath("./*[name()='xsd:sequence' or name()='xsd:all']/xsd:element", $this);
        $columnElements = array();
        for($i=0; $i<$typeElements->length; $i++) {
            $typeElement = $typeElements->item($i);
            $typeElement = $typeElement->getRefElement();
            $ElementType = $typeElement->getType();
            if($ElementType->tagName == 'xsd:simpleType') {
                $columnElements[] = $typeElement;
            }
        }
        return $columnElements;
    }
    
    public function getChildSchemas()
    {
        $objectType = $this->getType();
        $typeElements = $this->xpath("./*[name()='xsd:sequence' or name()='xsd:all']/xsd:element", $objectType);
        $childSchemas = array();
        for($i=0; $i<$typeElements->length; $i++) {
            $typeElement = $typeElements->item($i);
            $typeElement = $typeElement->getRefElement();
            $ElementType = $typeElement->getType();
            if($ElementType->tagName == 'xsd:complexType') {
                $childSchemas[] = $typeElement;
            }
        }
        return $childSchemas;
    }
    
    public function getSubElements()
    {
        $objectType = $this->getType();
        $typeElements = $this->xpath("./*[name()='xsd:sequence' or name()='xsd:all']/xsd:element", $objectType);
        $subElements = array();
        for($i=0; $i<$typeElements->length; $i++) {
            $typeElement = $typeElements->item($i);
            $typeElement = $typeElement->getRefElement();
            $subElements[] = $typeElement;
        }
        return $subElements;
    }
    
    public function getRefElement() 
    {
        if($this->ref) {
            $refObjectElement = $this->xpath("//xsd:element[@name='".$this->ref."']")->item(0);
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
    
    public function getTableName()
    {
        if($this->{'das:table'}) {
            return $this->{'das:table'};
        } else {
            return $this->name;
        }
    }
    
    public function getColumnName()
    {
        if($this->{'das:column'}) {
            return $this->{'das:column'};
        } else {
            return $this->name;
        }
    }
    
    public function getRelation() 
    {
        if($this->{'das:relation'}) {
            $relationNode = $this->xpath('//das:relation[@name="'.$this->{'das:relation'}.'"]');
            if($relationNode->length == 0) Die('Relation named ' . $this->{'das:relation'} . ' is not defined for element $this->name');
        } else {
            $relationNode = $this->xpath('./xsd:annotation/xsd:appinfo/das:relation', $this);
        }
        if($relationNode->length == 0) return false;
        return $relationNode->item(0);
    }
    
        // OLD
    //*************************************************************************
    private function getBaseTypeName() 
    {
        if(substr($this->name, 0, 4) == 'xsd:') {
            $baseTypeName = $this->name;
        } else {
            $typeContents = $this->xpath("./*[name()='xsd:restriction' or name()='xsd:list' or name()='xsd:union']", $this)->item(0);
            if($typeContents->base && substr($typeContents->base, 0, 4) == 'xsd:') {
                $baseTypeName = $typeContents->base;
            } elseif($typeContents->{'das:baseType'} && substr($typeContents->{'das:baseType'}, 0, 4) == 'xsd:') {
                $baseTypeName = $typeContents->{'das:baseType'};
            } else {
                $baseType = $this->xpath("//xsd:simpleType[@name='".$typeContents->base."']")->item(0);
                $baseTypeName = $baseType->getBaseTypeName();
            }
        }
        return $baseTypeName;
    }
    
    public function isEnclosedType() 
    {
        $baseTypeName = $this->getBaseTypeName();
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
}

?>