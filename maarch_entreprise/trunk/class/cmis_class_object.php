<?php

abstract class objectCMIS
{
	
	protected $objectId  ;                 
	protected $createdBy  ;                
	protected $creationDate  ;             
	protected $lastModifiedBy  ;           
	protected $lastModificationDate  ;     
	protected $changeToken  ;              
	protected $localName;
	protected $localNamespace;
	protected $queryName;
	protected $displayName;
	protected $baseId;
	protected $parentId;
	protected $description ;               
	protected $creatable ;                 
	protected $fileable ;                  
	protected $queryable ;                 
	protected $controllablePolicy;         
	protected $controllableACL;            
	protected $fulltextIndexed;
	protected $includedInSupertypeQuery;
	
	protected $accessControlList;
	protected $properties;
	
	public static function getFeed($objects, $title=null){
		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->formatOutput = true;
	
		//TODO add xmlns
		$root = $doc->createElementNS('http://www.w3.org/2005/Atom', 'feed');
		$doc->appendChild($root);
	
		$eAuthor = $doc->createElement('author');
		$root->appendChild($eAuthor);
		$name = $_SESSION['user']['FirstName'].' '.$_SESSION['user']['LastName'];  //'name';
		$eName = $doc->createElement('name', $name);
		$eAuthor->appendChild($eName);
	
		 
		if(isset($title) && !empty($title)){
			$eTitle = $doc->createElement('title', $title);
			$root->appendChild($eTitle);
		}
	
		 
		$eNumItems = $doc->createElement('cmisra:numItems', count($objects));
		$root->appendChild($eNumItems);
	
		foreach($objects as $basket){
			$basket->getAtomXmlEntry($doc, $root);
		}
	
		return $doc;
	}
	
}