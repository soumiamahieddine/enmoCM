<?php

class Schema
	extends DOMDocument
{
	
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