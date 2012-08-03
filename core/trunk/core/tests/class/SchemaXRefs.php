<?php

class SchemaXRefs
	extends DOMDocument
{
	private $xpath;
	
    public function SchemaXRefs()
    {
        parent::__construct();
        
        $XRefs = $this->createElement('XRefs');
        $this->appendChild($XRefs);
        
        $this->xpath = new DOMXpath($this);
        $this->xpath->registerNamespace('xsd', 'xsd');
    }
    
	public function xpath($query) 
    {
        return $this->xpath->query($query, $this->documentElement);
    }
    
    // Get root reference node
    public function getXRefNode($refNode)
    {
        $XRefs = $this->xpath('/XRefs/'.$refNode->tagName.'[@name="'.$refNode->getAttribute('name').'"]');
        if($XRefs->length == 0) {
            $XRefNode = $this->createElement($refNode->tagName);
            $XRefNode->setAttribute('name', $refNode->getAttribute('name'));
            $this->documentElement->appendChild($XRefNode);
        } else {
           $XRefNode = $XRefs->item(0);
        }
        return $XRefNode;
    }
    
    // Get xpath of a reference in schema
    public function getXRefPath($refNode, $reqName)
    {
        $XRefPath = $this->xpath('//'.$refNode->tagName.'[@name="'.$refNode->getAttribute('name').'"]/@'.$reqName);
        if($XRefPath->length == 0) {
            return null;  
        } else {
            return $XRefPath->item(0)->nodeValue;
        }
    }
    
    public function addXRefPath($refNode, $targetNode)
    {
        $XRefNode = $this->getXRefNode($refNode);
        $reqName = $targetNode->tagName;
        if($reqName == 'complexType' || $reqName == 'simpleType') $reqName = 'type';
        if($targetNode) $XRefNode->setAttribute($reqName, $targetNode->getNodePath());
    }
    
    // Get a string data
    public function getXRefData($refNode, $reqName)
    {
        $XData = $this->xpath('//'.$refNode->tagName.'[@name="'.$refNode->getAttribute('name').'"]/'.$reqName);
        if($XData->length == 0) {
            return null;  
        } else {
            return $XData->item(0)->nodeValue;
        }
    }
    
    public function addXRefData($refNode, $targetName, $targetData)
    {
        $XRefNode = $this->getXRefNode($refNode);
        $XData = $this->createElement($targetName, $targetData);
        $XRefNode->appendChild($XData);
    }
    
    // Get an xml node tree
    public function getXRefElement($refNode, $reqName)
    {
        $XElement = $this->xpath('//'.$refNode->tagName.'[@name="'.$refNode->getAttribute('name').'"]/'.$reqName.'/*');
        if($XElement->length == 0) {
            return null;  
        } else {
            return $XElement->item(0);
        }
    }
    
    public function addXRefElement($refNode, $targetElement)
    {
        $XRefNode = $this->getXRefNode($refNode);
        $XRefNode->appendChild($this->importNode($targetElement,true));
    }
    
    
}