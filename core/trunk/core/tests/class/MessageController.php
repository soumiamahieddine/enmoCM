<?php

class MessageController
    extends DOMDocument
{
	
    
    private $caller;
    private $logLevel;
    private $debug;
    private $xpath;
    
    public function __set($name, $value)
    {
        switch($name) {
            case 'caller' : $this->caller = $value;
            case 'logLevel' : $this->logLevel = $value;
            case 'debug' : $this->debug = $value;
        }
    }
    
    public function loadMessageFile($messageFile)
    {
        if(!$this->documentElement) {
            $this->registerNodeClass('DOMElement', 'MessageDefinition');
            $messageDefinitions = $this->CreateElement('messageDefinitions');
            $this->appendChild($messageDefinitions);
            $this->xpath = new DOMXPath($this);
        } else {
            $messageDefinitions = $this->documentElement;
        }
        
        $MessageFileXml = new DOMDocument();
        $MessageFileXml->load($messageFile);
        
        $xPath = new DOMXPath($MessageFileXml);
        
        $Messages = $xPath->query('/messages/message');
        for($i=0; $i<$Messages->length; $i++) {
           $importedMessage = $this->importNode($Messages->item($i), true);
           $messageDefinitions->appendChild($importedMessage);
        }
    
    }
    
    private function xpath($query, $contextElement=false) 
    {
        if(!$contextElement) $contextElement = $this->documentElement;
        return $this->xpath->query($query, $contextElement);
    }
    
    public function sendMessage(
        $messageId,
        $messageParams = array(),
        $messageLang = 'fr',                    
        $messageFunc = false
        )
    {
        // Get message definition
        $messageDefinitions = $this->xpath("//message[@id='".$messageId."']");
        if($messageDefinitions->length === 0) return false;
        $messageDefinition = $messageDefinitions->item(0);
        
        // Get message text in requested language
        $messageTexts = $this->xpath("./text[@lang='".$messageLang."']", $messageDefinition);
        if($messageTexts->length === 0) $messageText = $this->xpath("./text", $messageDefinition)->item(0)->nodeValue;
        $messageText = $messageTexts->item(0)->nodeValue;
        $messageText = vsprintf($messageText, $messageParams);
        
        // Create message object
        $message = new Message(
            $messageDefinition->level, 
            $messageDefinition->id, 
            $messageText,
            $this->caller,
            $messageFunc
            );
        
        $_SESSION['messages'][] = (array)$message;
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

class Message
{
    const INFO      = 0;
    const WARNING   = 1;
    const ERROR     = 2;
    const FATAL     = 3;
   
    public $timestamp;
    public $level;
    public $id;
    public $text;
    public $file;
    public $func;
    public $debug;
        
    function Message($level, $id, $text, $file, $func)
    {
        $this->timestamp = date('Y-m-d H-i-s.u');
        $this->level = $level;
        $this->id = $id;
        $this->text = $text;
        $this->file = $file;
        $this->func = $func;
    }

}