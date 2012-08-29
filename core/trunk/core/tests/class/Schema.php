<?php

class Schema
	extends DOMDocument
{
	
    public $includedSchemaLocations = array();
    
    public function Schema()
    {     
        parent::__construct();
        $this->registerNodeClass('DOMNode', 'SchemaNode');
        $this->registerNodeClass('DOMElement', 'SchemaElement');
        $this->registerNodeClass('DOMAttr', 'SchemaAttribute');
    }
    
    public function loadXSD($schemaLocation, $rootSchema=false)
    {
        $this->load($schemaLocation);
        if(!$rootSchema) $rootSchema = $this;
        $this->processIncludes($this, $rootSchema);
    }
    
	public function processIncludes($schema, $rootSchema) 
    {
        $includes = $schema->getElementsByTagName('include');
        while($includes->length > 0) {
            $include = $includes->item(0);
            $includeSchemaLocation = $include->getAttribute('schemaLocation');
            if(!in_array(
                $includeSchemaLocation, 
                $rootSchema->includedSchemaLocations)
            ) {
                $this->includeXSD($schema, $includeSchemaLocation, $rootSchema);
                /*$includeSchema = new Schema();
                $includeSchema->loadXSD($schemaLocation, $rootSchema);
                $schemaContents = $includeSchema->documentElement->childNodes;
                for($j=0; $j<$schemaContents->length; $j++) {
                    $importNode = $schemaContents->item($j);
                    $importedNode = $schema->importNode($importNode, true);
                    $schema->documentElement->appendChild($importedNode);
                }
                $rootSchema->includedSchemaLocations[] = $schemaLocation;*/
            }
            $include->parentNode->removeChild($include);
        }
    }
    
    public function includeXSD($schema, $includeSchemaLocation, $rootSchema)
    {
        $includeSchema = new Schema();
        $includeSchema->loadXSD($includeSchemaLocation, $rootSchema);
        $schemaContents = $includeSchema->documentElement->childNodes;
        for($j=0; $j<$schemaContents->length; $j++) {
            $importNode = $schemaContents->item($j);
            $importedNode = $schema->importNode($importNode, true);
            $schema->documentElement->appendChild($importedNode);
        }
        $rootSchema->includedSchemaLocations[] = $includeSchemaLocation;
    }

}

class SchemaNode
    extends DOMNode
{

}


class SchemaElement
    extends DOMElement
{
    // On xsd:element / xsd:attribute
    public function hasDatasource()
    {
        if($this->hasAttribute('das:source')) return true;
    }
    
    public function getName()
    {
        return $this->getAttribute('name');
    }
    
    public function getTable()
    {
        if($this->hasAttribute('das:table')) {
            return $this->getAttribute('das:table');
        } else {
            return $this->getAttribute('name');
        }
    }
    
    public function getRightTable()
    {
        return $this->getAttribute('right-table');
    }
    
    public function getColumn()
    {
        if($this->hasAttribute('das:column')) {
            return $this->getAttribute('das:column');
        } else {
            return $this->getAttribute('name');
        }
    }
    
    public function isRequired()
    {
        if($this->tagName == 'xsd:attribute' 
            && $this->getAttribute('use') == "required")
        { 
            return true;
        }
        if($this->tagName == 'xsd:element' 
            && ( strtolower($this->getAttribute('nillable')) == "false"
                || !$this->hasAttribute('minOccurs') 
                || (integer)$this->getAttribute('minOccurs') > 0)
        ) {
            return true;
        }
    }
    
    public function getTypeName()
    {
        return $this->getAttribute('type');
    }
    
    public function getFilter()
    {
        return $this->getAttribute('das:filter');
    }
    
    public function getRef()
    {
        return $this->getAttribute('ref');
    }
    
    public function hasDefault()
    {
        if($this->hasAttribute('default')) return true;
    }
    
    public function hasFixed()
    {
        if($this->hasAttribute('fixed')) return true;
    }
    
    // On xsd:complexType / xsd:simpleType or das:foreign-key
    public function getEnclosure()
    {
        if($this->getAttribute('das:enclosed') == 'true' 
            || $this->getAttribute('enclosed') == 'true') 
        {
            return "'";
        } else {
            return "";
        }
    }
    
}

class SchemaAttribute
    extends DOMAttr
{

}
