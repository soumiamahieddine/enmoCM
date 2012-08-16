<?php

class DataObjectDocument
	extends DOMDocument
    implements ArrayAccess
{
    
    private $xpath;

    //*************************************************************************
    // CONSTRUCTOR
    //************************************************************************* 
    public function DataObjectDocument()
	{
		parent::__construct();
        $this->formatOutput = false;
        $this->preserveWhiteSpace = false;
        $this->registerNodeClass('DOMAttr', 'DataObjectAttribute');
        $this->registerNodeClass('DOMElement', 'DataObjectElement');
        $this->registerNodeClass('DOMComment', 'DataObjectLog');
        
        $this->xpath = new DOMXpath($this);
	}

    
    //*************************************************************************
    // DOM METHODS
    //************************************************************************* 
    public function xpath($query) 
    {
        return $this->xpath->query($query);
    }
    
    public function createDataObjectLog($operation, $level=DataObjectLog::INFO, $detail=false)
    {
        $messageStrings[] = 'log';
        $messageStrings[] = 'operation="' . $operation . '"';
        $messageStrings[] = 'level="' . (string)$level . '"';
        if($detail) $messageStrings[] = $detail;
        $DataObjectLog = $this->createComment(implode(" ", $messageStrings));
        return $DataObjectLog;
    }
    
    public function importDataObject($DOMNode) 
    {
        $dataObject = $this->importNode(
            $DOMNode, 
            true
        );
        return $dataObject;
    }
    
    public function getChildren($name=false)
    {
        if(!$name) $name = '*';
        $nodes = $this->xpath('/' . $name);
        for($i=0; $i<$nodes->length; $i++) {
            $node = $nodes->item($i);
            if($node->getElementsByTagName('*')->length > 0) {
                $childrenArray[] = $node;
            }
        }
        return $childrenArray;
    }
    
    //*************************************************************************
    // MAGIC METHODS
    //************************************************************************* 
    public function __get($name) 
    {
        // Element
        $nodes = $this->xpath('/' . $name);
        return array($nodes->item(0));
    }
    
    public function __set($name, $value) 
    {
        switch($name) {
        case '' :
            $this->appendChild($value);
            break;
        default:
            $resultNodes = $this->xpath('/'.$name);
            switch ((string)$resultNodes->length) {
            case '0' :
                $resultNode = $this->createElement($name, $value);
                $this->appendChild($resultNode);
                break;
            case '1' :
                $resultNode = $resultNodes->item(0);
                if((string)$resultNode->nodeValue == $value) {
                    return;
                }
                //$this->logChange(DataObjectChange::UPDATE, $name, (string)$resultNode->nodeValue, $value);
                $resultNode->nodeValue = $value;
                break;
            }
        }
    }
    
    //*************************************************************************
    // ITERATOR METHODS
    //*************************************************************************
    public function getIterator() {
        $childNodes = $this->documentElement->childNodes;
        $childArray = array();
        for($i=0; $i<$childNodes->length; $i++) {
            $childNode = $childNodes->item($i);
            $childName = $childNode->tagName;
            $childValue = $childNode->nodeValue;
            $childArray[$childName] = $childValue;
        } 
        return new ArrayIterator($childArray);
    }
    
    //*************************************************************************
    // ARRAYACCESS METHODS
    //*************************************************************************
    public function offsetSet($offset, $value) 
    {
        if($value->ownerDocument != $this) {
            $dataObject = $this->importNode($value, true);
        } else {
            $dataObject = $value;
        }
        $this->appendChild($dataObject);
    }
    
    public function offsetExists($offset) 
    {
      
    }
    
    public function offsetUnset($offset) 
    {
    }
    
    public function offsetGet($offset) 
    {

    }
    
}

//*****************************************************************************
// DATA OBJECT
//*****************************************************************************
class DataObjectElement
    extends DOMElement
    implements IteratorAggregate, ArrayAccess
{
   
    // DOM METHODS
    //*************************************************************************
    private function xpath($query) 
    {
        return $this->ownerDocument->xpath($query, $this);
    }
    
    // OBJECT METHODS
    //*************************************************************************  
    public function getProperties()
    {
        $nodes = $this->xpath(
            $this->getNodePath() . '/@*'
            . ' | '
            . $this->getNodePath(). '/*'
        );
        for($i=0; $i<$nodes->length; $i++) {
            $node = $nodes->item($i);
            $propertyName = $node->nodeName;
            $propertyValue = $node->nodeValue;
            switch($node->nodeType) {
            case XML_ATTRIBUTE_NODE:
                $propertiesArray[$propertyName] = $propertyValue;
                break;
            case XML_ELEMENT_NODE:
                if($node->getElementsByTagName('*')->length == 0) {
                    $propertiesArray[$propertyName] = $propertyValue;
                }
                break;
            }
        }
        return $propertiesArray;
    }
    
    public function getProperty($name)
    {
        $nodes = $this->xpath('./@' . $name . ' | ./' . $name);
        if($nodes->length > 0) {
            return $nodes->item($i)->nodeValue;
        }
    }
    
    public function getChildren($name=false)
    {
        if(!$name) $name = '*';
        $XPath = new DOMXPath($this->ownerDocument);
        $nodes = $XPath->query(
            './' . $name, $this
        );
        for($i=0; $i<$nodes->length; $i++) {
            $node = $nodes->item($i);
            if($node->getElementsByTagName('*')->length > 0) {
                $childrenArray[] = $node;
            }
        }
        return $childrenArray;
    }
    
    // MAGIC METHODS
    //*************************************************************************
    public function __set($name, $value) 
    {
        if(is_scalar($value) || !$value || is_null($value)) {
            // Attribute == property
            if($this->hasAttribute($name)) {          
                $valueBefore = $this->getAttribute($name);
                if($valueBefore != $value) {
                    $this->logUpdate($name, $valueBefore, $value);
                    $this->setAttribute($name, $value); 
                }
                return;
            }
            $XPath = new DOMXPath($this->ownerDocument);
            $propertyNodes = $XPath->query(
                './' . $name, $this
            );
            if($propertyNodes->length == 1 
                && $propertyNodes->item(0)->getElementsByTagName('*')->length == 0) {
                $propertyNode = $propertyNodes->item(0);
                $valueBefore = $propertyNode->nodeValue;
                if($valueBefore != $value) {
                    $this->logUpdate($name, $valueBefore, $value);
                    $propertyNode->nodeValue = $value;
                    
                }
            }
        } 
        if(is_object($value) && get_class($value) == 'DataObject') { 
            // Child Element
            $this->appendChild($value);
        }
    }
    
    public function __get($name) 
    {
        // Attribute == property
        if($this->hasAttribute($name)) {
            return (string)$this->getAttribute($name);
        }
        // Element 
        $XPath = new DOMXPath($this->ownerDocument);
        $nodes = $XPath->query(
            $this->getNodePath() . '/' . $name
        );
        
        if($nodes->length == 1 
            && $nodes->item(0)->getElementsByTagName('*')->length == 0) {
            return (string)$nodes->item(0)->nodeValue;  
        } 
        if($nodes->length > 0) {
            $nodesArray = array();
            for($i=0; $i<$nodes->length; $i++) {
                $nodesArray[] = $nodes->item($i);
            }
            return $nodesArray;
        }
    }
    
    public function __isset($name)
    {
        // Attribute == property
        if($this->hasAttribute($name)) {
            return true;
        }
        // Element 
        $XPath = new DOMXPath($this->ownerDocument);
        $nodes = $XPath->query(
            './' . $name, $this
        );
        if($nodes->length > 0) return true;
    }
    
    public function __toString()
    {
        return (string)$this->nodeValue;
    }
    
    // ITERATOR METHODS
    //*************************************************************************
    public function getIterator() {
        $returnArray = $this->asArray('*');
        return new ArrayIterator($returnArray);
    }
    
    private function asArray($name)
    {
        $returnArray = array();
        $nodes = $this->ownerDocument->xpath(
            $this->getNodePath() . '/@'. $name 
            . ' | ' 
            . $this->getNodePath() . '/'. $name 
        );
        for($i=0; $i<$nodes->length; $i++) {
            $node = $nodes->item($i);
            $returnArray[] = $node;
        } 
        return $returnArray;
    }
    
    // ARRAYACCESS METHODS
    //*************************************************************************
    public function offsetSet($offset, $value) 
    {
        if($value->ownerDocument != $this->onwerDocument) {
            $dataObject = $this->ownerDocument->importNode($value, true);
        } else {
            $dataObject = $value;
        }
        $this->appendChild($dataObject);
    }
    
    public function offsetExists($offset) 
    {
        
    }
    
    public function offsetUnset($offset) 
    {

    }
    
    public function offsetGet($offset) 
    {
        
    }
   
    // MESSAGES INTERFACE
    //*************************************************************************  
    public function logCreate()
    {
        $message = 
            $this->ownerDocument->createDataObjectLog(
                DataObjectLog::CREATE, 
                DataObjectLog::INFO
            );
        $this->appendChild($message);
    }    
    
    public function logRead()
    {
        $message = 
            $this->ownerDocument->createDataObjectLog(
                DataObjectLog::READ, 
                DataObjectLog::INFO
            );
        $this->appendChild($message);
    }
    
    public function logUpdate($name, $valueBefore, $valueAfter)
    {
        $messageDetail = 
            'name="' . $name 
            . '" value-before="' . $valueBefore 
            . '" value-after="' . $valueAfter . '"';
        $message = 
            $this->ownerDocument->createDataObjectLog(
                DataObjectLog::UPDATE, 
                DataObjectLog::INFO, 
                $messageDetail
            );
        $this->appendChild($message);
    }
    
    public function logValidate($level, $message)
    {
        $messageDetail = 'message="' . $message . '"';
        $message = 
            $this->ownerDocument->createDataObjectLog(
                DataObjectLog::VALIDATE, 
                $level, 
                $messageDetail
            );
        $this->appendChild($message);
    }
    
    public function firstLog()
    {
        $xpath = new DOMXPath($this->ownerDocument);
        $firstOperation = 
            $xpath->query(
                $this->getNodePath() 
                . "/comment()"
            )->item(0);
        return $firstOperation;
    }
    
    public function isCreated()
    {
        $firstLog = $this->firstLog();
        if($firstLog->getAttribute('operation') == DataObjectLog::CREATE) return true;
    }
    
    public function isUpdated()
    {
        $updateOperations = 
            $this->ownerDocument->xpath(
                $this->getNodePath() 
                . "/comment()[contains(., 'operation=\"".DataObjectLog::UPDATE."\"')]"
            );
        if($updateOperations->length > 0) return true;
    }
    
    public function getUpdatedProperties()
    {
        $updateOperations = 
            $this->ownerDocument->xpath(
                $this->getNodePath() 
                . "/comment()[contains(., 'operation=\"".DataObjectLog::UPDATE."\"')]"
            );
        $updatedProperties = array();
        for($i=0; $i<$updateOperations->length; $i++) {
            $updateOperation = $updateOperations->item($i);
            $updatedProperties[] = $updateOperation->getAttribute('name');
        }
        return $updatedProperties;
    }
    
    public function getValidationErrors()
    {
        $xpath = new DOMXPath($this->ownerDocument);
        $validateOperations = 
            $xpath->query(
                $this->getNodePath() 
                . "/comment()[contains(., 'operation=\"".DataObjectLog::VALIDATE."\"')]"
            );
        $validationErrors = array();
        for($i=0; $i<$validateOperations->length; $i++) {
            $validateOperation = $validateOperations->item($i);
            $validationErrors[] = 
                "[" . $validateOperation->getAttribute('level') . "] "
                . $validateOperation->getAttribute('message');
        }
        return $validationErrors;
    }
    
    
    // XML INTERFACE
    //*************************************************************************
    public function asXML() 
    {  
        return $this->C14N(false,true);
    }
    
    public function show($withComments=true, $prettyAttrs=false)
    {
        // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $this->C14N(false, $withComments));
        
        // now indent the tags
        $token      = strtok($xml, "\n");
        $result     = ''; // holds formatted version as it is built
        $pad        = 0; // initial indent
        $matches    = array(); // returns from preg_matches()
        
        // scan each line and adjust indent based on opening/closing tags
        while ($token !== false) {
            $comment = false;
            // 1. open and closing tags on same line - no change
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) { 
                $indent = 0;
            // 2. closing tag - outdent now
            } elseif (preg_match('/^<\/\w/', $token, $matches)) {
                $indent = 0;
                $pad--;
            // 3. opening tag - don't pad this one, only subsequent tags
            } elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) {
                $indent = 1;
            // 4. no indentation needed
            } else {
                $indent = 0; 
            }
            if($prettyAttrs) {
                if(preg_match('/<\!\-\-\s*\w[^>]*\s*\-\->/', $token, $matches)) {
                    // nothing
                } else {
                    $token = $this->showAttr($token, $pad);
                }
            }
            // pad the line with the required number of leading spaces
            $line    = str_pad($token, strlen($token)+($pad*4), ' ', STR_PAD_LEFT);
            $result .= $line . "\n"; // add to the cumulative result, with linefeed
            $token   = strtok("\n"); // get the next token
            $pad    += $indent; // update the pad size for subsequent lines    
        } 
        
        echo "<pre>";
        echo htmlspecialchars($result);
        echo "</pre>";
    }
    
    private function showAttr($token, $pad)
    {
        $return = '';
        $array = preg_split("/\s(\w+=)/", $token, -1, PREG_SPLIT_DELIM_CAPTURE);
        for($i=0; $i<count($array); $i++) {
            $item = $array[$i];
            if(preg_match('/^\w+=$/', $item)) {
                if($i>0 && $i<count($array)) $attrpad = $pad + 1;
                else $attrpad = $pad;
                $attrname = str_pad($item, strlen($item)+($attrpad*2), ' ', STR_PAD_LEFT);
                $return .= $attrname;
            } else {
                $return .= $item;
                if($i<count($array)-1) $return .= "\n";
            }
        }
        return $return;
    }
    
}


//*****************************************************************************
// DATA OBJECT ATTRIBUTE
//*****************************************************************************
class DataObjectAttribute
    extends DOMAttr
{

    public function __toString()
    {
        return (string)$this->nodeValue;
    }
    
}

//*****************************************************************************
// DATA OBJECT LOG
//*****************************************************************************
class DataObjectLog
    extends DOMComment
{
    const NONE      = 'NONE';
    const CREATE    = 'CREATE';
    const READ      = 'READ';
    const UPDATE    = 'UPDATE';
    const DELETE    = 'DELETE';
    const VALIDATE  = 'VALIDATE';
    
    const INFO      = 0;
    const WARNING   = 1;
    const ERROR     = 2;
    const FATAL     = 3;
    
    
    public function getAttribute($name) 
    {
        $attribute_array = array();
        // Match attribute-name attribute-value pairs.
        $hasAttributes = preg_match_all(
                '#([^\s=]+)\s*=\s*(\'[^<\']*\'|"[^<"]*")#',
                $this, $matches, PREG_SET_ORDER);
        if ($hasAttributes) {
            foreach ($matches as $attribute) {
                $attribute_array[$attribute[1]] =
                    substr($attribute[2], 1, -1);
            }
        }
        return $attribute_array[$name];
    }
    
    public function __get($name)
    {
        if($name == 'tagName') {
            $found = preg_match('#^\s*[\w_]+\s#', $this, $matches);
            return $matches[0];
        }
    }
    
    public function __toString()
    {
        return (string)$this->nodeValue;
    }
} 
