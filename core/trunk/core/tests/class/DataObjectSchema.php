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
        if(!$contextElement) $contextElement = $this->documentElement;
        return $this->xpath->query($query, $contextElement);
    }
    
    public function getDataSources()
    {
        $DSnodes = $this->xpath('/xsd:schema/xsd:annotation/xsd:appinfo/das:datasource');
        for($i=0; $i<$DSnodes->length; $i++) {
            $DS[] = $DSnodes->item($i);
        }
        return $DS;
    }
    
    public function getObjectSchemas() 
    {
        return $this->xpath('/xsd:schema/xsd:element');
    
    }
    
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
     
    public function getDasSource($schemaPath)
    {
        $objectElement = $this->getSchemaElement($schemaPath);
        $dasSource = $objectElement->{'das:source'};
        return $dasSource;
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
            $simpleTypeBase = $this->schema->createElement('xsd:simpleType');
            $simpleTypeBase->name = $baseTypeName;
        } else {
            $baseType = $this->xpath("//xsd:simpleType[@name='".$baseTypeName."']")->item(0);
            $simpleTypeBase = $this->getSimpleTypeBaseType($baseType);
        }
        return $simpleTypeBase;
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
        if(!$elementType) die("Unable to find type for element $element->name");
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
    
    public function getRelationElements()
    {
        if($this->{'das:relation'}) {
            $relationElements = $this->xpath("/xsd:schema/xsd:annotation/xsd:appinfo/das:relation[@name='".$this->{'das:relation'}."']");
        } else {
            $relationElements = $this->xpath("./xsd:annotation/xsd:appinfo/das:relation", $this);
        }
        return $relationElements;
    }
    
    public function getDasSource()
    {
        $dasSource = $objectElement->{'das:source'};
        return $dasSource;
    }
}

?>