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
            . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR
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
            throw new maarch\Exception("Failed to load message definition file $messageFile in $customFilePath or $relativeFilePath");
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
        $result = $this->xpath->query($query, $contextElement);
        if($result) return $result;
        else {
            throw new maarch\Exception('XPath Error: ' . $query);
        }
    }
    
    public function getMessageDefinition($code)
    {
        try {
            $definitions = $this->xpath("//message[@code='".$code."']");
            if($definitions && $definitions->length === 0) return false;
            $definition = $definitions->item(0);
            return $definition;
        } catch (maarch\Exception $e) {
            throw $e;
        }
    }
    
    public function getTexts(
        $codePrefix, 
        $lang = false
        )
    {
        $texts = array();
        $definitions = $this->xpath("//message[starts-with(@code, '".$codePrefix."')]");
        
        for($i=0; $i<$definitions->length; $i++) {
            $definition = $definitions->item($i);
            $text = $this->makeMessageText($definition, $lang);
            if(!$text) $text = $definition->code;
            $texts[$definition->code] = $text;
        }
        return $texts;
    }
    
    public function getMessageText(
        $code,
        $lang = false,
        $params = array()
        )
    {
        // Get message definition
        try {
            $definition = $this->getMessageDefinition($code);
            if(!$definition) return $code;
            
            $text = $this->makeMessageText(
                $definition,
                $lang,
                $params
            );
            return $text;
        } catch (maarch\Exception $e) {
            throw $e;
        }
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
        
        // No text defined for language, return code
        if($texts->length === 0) {
            return $definition->code;
        }
        
        // Get template text
        $text = $texts->item(0)->nodeValue;
        // Merge params (if fail return template)
        $text = @vsprintf($text, $params);   
        
        return $text;
    }
    
    public function createMessage(
        $code,
        $lang = false,
        $params = array()
        )
    {
        // Get message definition
        $definition = $this->getMessageDefinition($code);
        
        // Make Text
        $text = $this->makeMessageText(
            $definition,
            $lang,
            $params           
        );

        // Create message object
        $message = new Message(
            $code, 
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