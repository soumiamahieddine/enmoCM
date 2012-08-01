<?php

class Schema
	extends DOMDocument
{
	
    public function Schema()
    {     
        parent::__construct();
        $this->registerNodeClass('DOMNode', 'SchemaNode');
        $this->registerNodeClass('DOMElement', 'SchemaElement');
        $this->registerNodeClass('DOMAttr', 'SchemaAttribute');
    }
    
    public function loadXSD($xsdFile)
    {
        $this->load($xsdFile);
        $this->processIncludes($this);
    }
    
	public function processIncludes($schema) 
    {
        $includes = $schema->getElementsByTagName('include');
        while($includes->length > 0) {
            $include = $includes->item(0);
            $schemaLocation = $include->getAttribute('schemaLocation');
            $includeSchema = new Schema();
            $includeSchema->loadXSD($schemaLocation);

            $schemaContents = $includeSchema->documentElement->childNodes;
            for($j=0; $j<$schemaContents->length; $j++) {
                $importNode = $schemaContents->item($j);
                $importedNode = $schema->importNode($importNode, true);
                $schema->documentElement->appendChild($importedNode);
            }

            $include->parentNode->removeChild($include);
        }
    }

}

class SchemaNode
    extends DOMNode
{

}


class SchemaElement
    extends DOMElement
{
    
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
    
    public function getColumn()
    {
        if($this->hasAttribute('das:column')) {
            return $this->getAttribute('das:column');
        } else {
            return $this->getAttribute('name');
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
