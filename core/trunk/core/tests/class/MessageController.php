<?php

class MessageController
    extends DOMDocument
{
	
    private $logLevel;
    private $debug;
    private $xpath;
    
    public function __set($name, $value)
    {
        switch($name) {
            case 'logLevel' : $this->logLevel = $value;
            case 'debug' : $this->debug = $value;
        }
    }
    
    public function loadMessageFile($messageFile)
    {
        $customFilePath = 
            $_SESSION['config']['corepath'] . DIRECTORY_SEPARATOR 
            . 'custom' . DIRECTORY_SEPARATOR 
            . $messageFile;
            
        $relativeFilePath = 
            $_SESSION['config']['corepath'] . DIRECTORY_SEPARATOR 
            . $messageFile;
        
        if(is_file($customFilePath)) {
            $loadMessageFile = $customFilePath;
        } elseif(is_file($relativeFilePath)) {
            $loadMessageFile = $relativeFilePath;
        } elseif(is_file($messageFile)) {
            $loadMessageFile = $messageFile;
        } else {
            throw new maarch\Exception("Failed to load message definition file $messageFile");
        }
        
               
        if(!$this->documentElement) {
            $this->registerNodeClass('DOMElement', 'MessageDefinition');
            $definitions = $this->CreateElement('messageDefinitions');
            $this->appendChild($definitions);
            $this->xpath = new DOMXPath($this);
        } else {
            $definitions = $this->documentElement;
        }
        
        $MessageFileXml = new DOMDocument();
        $MessageFileXml->load($loadMessageFile);
        
        $xPath = new DOMXPath($MessageFileXml);
        
        $Messages = $xPath->query('/messages/message');
        for($i=0; $i<$Messages->length; $i++) {
           $importedMessage = $this->importNode($Messages->item($i), true);
           $definitions->appendChild($importedMessage);
        }
    
    }
    
    private function xpath($query, $contextElement=false) 
    {
        if(!$contextElement) $contextElement = $this->documentElement;
        return $this->xpath->query($query, $contextElement);
    }
    
    public function getMessageDefinition($id)
    {
        $definitions = $this->xpath("//message[@id='".$id."']");
        if($definitions->length === 0) return false;
        $definition = $definitions->item(0);
        return $definition;
    }
    
    public function getTexts(
        $idPrefix, 
        $lang = false
        )
    {
        $texts = array();
        $definitions = $this->xpath("//message[starts-with(@id, '".$idPrefix."')]");
        
        for($i=0; $i<$definitions->length; $i++) {
            $definition = $definitions->item($i);
            $text = $this->makeMessageText($definition, $lang);
            if(!$text) $text = $definition->id;
            $texts[$definition->id] = $text;
        }
        return $texts;
    }
    
    public function getMessageText(
        $id,
        $lang = false,
        $params = array()
        )
    {
        // Get message definition
        $definition = $this->getMessageDefinition($id);
        if(!$definition) return $id;
        
        $text = $this->makeMessageText(
            $definition,
            $lang,
            $params
        );
        return $text;
    }
    
    private function makeMessageText(
        $definition,
        $lang,
        $params = array()
        
        )
    {
        // Get message text in requested language
        if(!$lang) $lang = $_SESSION['config']['lang'];
        $texts = $this->xpath("./text[@lang='".$lang."']", $definition);
        
        // No text defined for language, return id
        if($texts->length === 0) {
            return $definition->id;
        }
        
        // Get template text
        $text = $texts->item(0)->nodeValue;
        // Merge params (if fail return template)
        $text = @vsprintf($text, $params);   
        
        return $text;
    }
    
    public function createMessage(
        $id,
        $lang = false,
        $params = array()
        )
    {
        // Get message definition
        $definition = $this->getMessageDefinition($id);
        
        // Make Text
        $text = $this->makeMessageText(
            $definition,
            $lang,
            $params           
        );

        // Create message object
        $message = new Message(
            $id, 
            $text,
            $definition->level
        );
        
        return $message;        
    }
    
}

class MessageDefinition 
    extends DOMelement
{
    
    function __get($name) 
    {
        if($this->hasAttribute($name)) {
            return $this->getAttribute($name);
        }
    }
    

}