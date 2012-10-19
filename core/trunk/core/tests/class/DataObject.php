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
        $this->registerNodeClass('DOMComment', 'DataObjectComment');
        
        $this->xpath = new DOMXpath($this);
	}

    
    //*************************************************************************
    // DOM METHODS
    //************************************************************************* 
    public function xpath($query) 
    {
        return $this->xpath->query($query);
    }
    
    public function createProperty($name)
    {
        $propertyStrings[] = 'dataObjectProperty';
        $propertyStrings[] = 'name="' . $name . '"';
        $DataObjectProperty = $this->createComment(implode(" ", $propertyStrings));
        return $DataObjectProperty;
    }
    
    public function createDataObject($name)
    {
        $childStrings[] = 'dataObject';
        $childStrings[] = 'name="' . $name . '"';
        $DataObjectChild = $this->createComment(implode(" ", $childStrings));
        return $DataObjectChild;
    }
    
    public function createLog($operation, $level=DataObjectLog::INFO, $detail=false)
    {
        $messageStrings[] = 'dataObjectLog';
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
    
    public function getChildNodesByTagName($name=false)
    {
        if(!$name) $name = '*';
        $XPath = new DOMXPath($this);
        $nodes = $XPath->query(
            '/' . $name
        );
        return $nodes;
    }
    
    public function getCommentDataObjects()
    {
        $xpath = new DOMXPath($this);
        $dataObjects = 
            $xpath->query(
                "/comment()[starts-with(., 'dataObject ')]",
                $this
            );
        return $dataObjects;
    }
    
    public function getCommentDataObject($name) 
    {
        $dataObjects = $this->getCommentDataObjects();
        for($i=0; $i<$dataObjects->length; $i++) {
            $dataObject = $dataObjects->item($i);
            if($dataObject->getAttribute('name') == $name) {
                return $dataObject;
            }
        }
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

/*****************************************************************************
**                                                                          **
**                          DATA OBJECT ELEMENT                             **
**                                                                          **
*****************************************************************************/
class DataObjectElement
    extends DOMElement
    implements IteratorAggregate, ArrayAccess
{
   
    // DOM METHODS
    //*************************************************************************
    public function query($query, $contextElement=false) 
    {
        if(!$contextElement) $contextElement = $this;
        $xpath = new DOMXPath($this->ownerDocument);
        return $xpath->query($query, $contextElement);
    }
    
    // OBJECT METHODS
    //*************************************************************************  
    public function getName()
    {
        return $this->tagName;
    }
    
    public function getProperties()
    {
        $XPath = new DOMXPath($this->ownerDocument);
        $nodes = $XPath->query(
            './@*'
            . ' | '
            . './*',
            $this
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
    
    public function getContents()
    {
        $XPath = new DOMXPath($this->ownerDocument);
        $contents = $XPath->query(
            './@* | ./*', 
            $this
        );
        return $contents;
    
    }
    
    public function getAttributes()
    {
        $XPath = new DOMXPath($this->ownerDocument);
        $attributes = $XPath->query(
            './@*', 
            $this
        );
        return $attributes;
    }
    
    public function getElements()
    {
        $XPath = new DOMXPath($this->ownerDocument);
        $elements = $XPath->query(
            './*', 
            $this
        );
        return $elements;
    }
    
    public function getChildNodesByTagName($name=false)
    {
        if(!$name) $name = '*';
        $XPath = new DOMXPath($this->ownerDocument);
        $nodes = $XPath->query(
            './' . $name, 
            $this
        );
        return $nodes;
    }
    
    // MAGIC METHODS
    //*************************************************************************
    public function __set($name, $value) 
    {
        // Property storage is an attribute
        if($this->hasAttribute($name)) {
            $valueBefore = $this->getAttribute($name);
            if(is_null($value)) {
                //echo "<br/>1 - Property $name is attribute, old value was '$valueBefore', new value is null ==> remove attribute";
                $this->removeAttribute($name);
                $this->logUpdate($name, $valueBefore, 'null');
            } else if(
                (is_scalar($value) || !$value) 
                && $valueBefore != $value
            ) {
                //echo "<br/>2 - Property $name is attribute, old value was '$valueBefore', new value is string or false => set attribute";
                $this->setAttribute($name, $value);
                $this->logUpdate($name, $valueBefore, $value);
            }
            
            return;
        } 
        
        // Property storage is an existing element
        $XPath = new DOMXPath($this->ownerDocument);
        $propertyNodes = $XPath->query('./' . $name, $this);
        if($propertyNodes->length > 0) { 
            $propertyNode = $propertyNodes->item(0);
            $valueBefore = $propertyNode->nodeValue;
            if(is_null($value)) {
                //echo "<br/>3 - Property $name is element, old value was '$valueBefore', new value is null => comment element";
                $commentedProperty = 
                    $this->ownerDocument->createProperty($name);
                $this->replaceChild($commentedProperty, $propertyNode);
                $this->logUpdate($name, $valueBefore, 'null');
            } elseif(
                (is_scalar($value) || !$value) 
                && $valueBefore != $value
            ) {
                //echo "<br/>4 - Property $name is element, old value was '$valueBefore', new value is string or false => set element";
                $propertyNode->nodeValue = $value;
                $this->logUpdate($name, $valueBefore, $value);
            }
            
            return;
        }
        
        // Property storage is a commented element
        if($commentedProperty = $this->getCommentProperty($name)) { 
            if(is_null($value)) {
                //echo "<br/>5 - Property $name is a commented element, new value is null => no action";
            } elseif(is_scalar($value) || !$value) {
                //echo "<br/>6 - Property $name is a commented element, new value is string or false => set element";
                $propertyNode = 
                    $this->ownerDocument->createElement($name, $value);
                $this->replaceChild($propertyNode, $commentedProperty);
                $this->logUpdate($name, 'null', $value);
            }
            return;
        }
        
        // Property storage not found = add attribute
        if(is_scalar($value) || !$value) {
            //echo "<br/>7 - Property $name not found, new value is string or false, adding attribute";
            $this->setAttribute($name, $value);
            $this->logUpdate($name, 'null', $value);
            return;
        }
        
    }
    
    public function __get($name) 
    {
        if(!$name) return false;
        // Storage is an attribute
        if($this->hasAttribute($name)) {
            return (string)$this->getAttribute($name);
        }
        
        // Storage is an element
        $XPath = new DOMXPath($this->ownerDocument);
        $nodes = $XPath->query(
            './' . $name,
            $this
        );
        
        // Storage is a commentDataObject -> return array of instance
        if($commentDataObject = $this->getCommentDataObject($name)) {
            $nodesArray = array();
            for($i=0; $i<$nodes->length; $i++) {
                $nodesArray[] = $nodes->item($i);
            }
            return $nodesArray;
        }
        
        if($commentProperty = $this->getCommentProperty($name)) {
            return null;
        }
        
        // Storage is a property element -> return value
        if($nodes->length === 1 
            && $nodes->item(0)->getElementsByTagName('*')->length === 0
        ) {
            return (string)$nodes->item(0)->nodeValue;
        }
        
        if($nodes->length > 0 
            && $nodes->item(0)->getElementsByTagName('*')->length > 0
        ) {
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
            './' . $name, 
            $this
        );
        if($nodes->length > 0) return true;
    }
    
    public function __toString()
    {
        return $this->C14N();
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
    public function offsetSet($offset, $dataObject) 
    {
        if($dataObject->ownerDocument != $this->onwerDocument) {
            $dataObject = $this->ownerDocument->importNode($dataObject, true);
        } 
        $objectName = $dataObject->getName();
        $refDataObject = $this->getCommentDataObject($objectName);
        if(is_null($offset)) $offset = 999999;
        for($i=0; $i<$offset; $i++) {
            if($refDataObject->nextSibling 
                && $refDataObject->nextSibling->getName() == $objectName) {
                $refDataObject = $refDataObject->nextSibling;
            } else {
                break;
            }
        }
        $this->insertBefore(
            $dataObject, 
            $refDataObject->nextSibling
            );
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
    
    // DATA OBJECT COMMENT METHODS
    //*************************************************************************  
    public function getCommentProperties()
    {
        $xpath = new DOMXPath($this->ownerDocument);
        $properties = 
            $xpath->query(
                "./comment()[starts-with(., 'dataObjectProperty')]",
                $this
            );
        return $properties;
    }
    
    public function getCommentProperty($name) 
    {
        $properties = $this->getCommentProperties();
        for($i=0; $i<$properties->length; $i++) {
            $property = $properties->item($i);
            if($property->getAttribute('name') == $name) {
                return $property;
            }
        }
    }
    
    public function getCommentDataObjects()
    {
        $xpath = new DOMXPath($this->ownerDocument);
        $dataObjects = 
            $xpath->query(
                "./comment()[starts-with(., 'dataObject ')]",
                $this
            );
        return $dataObjects;
    }
    
    public function getCommentDataObject($name) 
    {
        $dataObjects = $this->getCommentDataObjects();
        for($i=0; $i<$dataObjects->length; $i++) {
            $dataObject = $dataObjects->item($i);
            if($dataObject->getAttribute('name') == $name) {
                return $dataObject;
            }
        }
    }
   
    // DATA OBJECT LOGS
    //*************************************************************************  
    public function logCreate()
    {
        $log = 
            $this->ownerDocument->createLog(
                DataObjectLog::CREATE, 
                DataObjectLog::INFO
            );
        $this->appendChild($log);
    }    
    
    public function logRead()
    {
        $this->clearLogs();
        $log = 
            $this->ownerDocument->createLog(
                DataObjectLog::READ, 
                DataObjectLog::INFO
            );
        $this->appendChild($log);
    }
    
    public function logDelete()
    {
        $log = 
            $this->ownerDocument->createLog(
                DataObjectLog::DELETE, 
                DataObjectLog::INFO
            );
        $this->appendChild($log);
    }
    
    public function logUpdate($name, $valueBefore, $valueAfter)
    {
        $messageDetail = 
            'name="' . $name 
            . '" value-before="' . $valueBefore 
            . '" value-after="' . $valueAfter . '"';
        $message = 
            $this->ownerDocument->createLog(
                DataObjectLog::UPDATE, 
                DataObjectLog::INFO, 
                $messageDetail
            );
        $this->appendChild($message);
    }
    
    public function logValidate($id, $message, $level)
    {
        $messageDetail = 'id="' . $id . '" message="' . $message . '"';
        $message = 
            $this->ownerDocument->createLog(
                DataObjectLog::VALIDATE, 
                $level, 
                $messageDetail
            );
        $this->appendChild($message);
    }
    
    public function getLogs()
    {
        $xpath = new DOMXPath($this->ownerDocument);
        $logs = 
            $xpath->query(
                "./comment()[starts-with(., 'dataObjectLog')]",
                $this
            );
        return $logs;
    }
    
    public function firstLog() 
    {
        $xpath = new DOMXPath($this->ownerDocument);
        $logs = 
            $xpath->query(
                "./comment()[starts-with(., 'dataObjectLog')]",
                $this
            );
        return $logs->item(0);
    }
    
    public function clearLogs()
    {
        $logs = $this->getLogs();
        for($i=0; $i<$logs->length; $i++) {
            $log = $logs->item($i);
            $this->removeChild($log);
        }
    }
    
    public function isCreated()
    {
        $firstLog = $this->firstLog();
        if($firstLog->getAttribute('operation') 
            == DataObjectLog::CREATE) return true;
    }
    
    public function isDeleted()
    {
        $logs = $this->getLogs();
        $l = $logs->length;
        for($i=$l-1; $i>=0; $i--) {
            $log = $logs->item($i);
            if($log->getAttribute('operation') == DataObjectLog::DELETE) {
                return true;
            }
        }
    }
    
    public function isRead()
    {
        $firstLog = $this->firstLog();
        if($firstLog->getAttribute('operation') == DataObjectLog::READ) {
            return true;
        }
    }
        
    public function getUpdatedProperties()
    {
        $updatedProperties = array();
        $logs = $this->getLogs();
        for($i=0; $i<$logs->length; $i++) {
            $log = $logs->item($i);
            if($log->getAttribute('operation') == DataObjectLog::UPDATE) {
                $updatedProperties[] = $log->getAttribute('name');
            }
        }
        return $updatedProperties;
    }
    
    public function getValidationErrors()
    {
        $validationErrors = array();
        $logs = $this->getLogs();
        for($i=0; $i<$logs->length; $i++) {
            $log = $logs->item($i);
            if($log->getAttribute('operation') == DataObjectLog::VALIDATE) {
                $validationErrors[] = 
                    new Message(
                        $log->getAttribute('id'),
                        $log->getAttribute('message'),
                        $log->getAttribute('level')
                    );
            }
        }
        return $validationErrors;
    }
    
    
    // XML INTERFACE
    //*************************************************************************
    public function asXML() 
    {  
        return $this->C14N(false,true);
    }
    
    public function show($withComments=true, $prettyAttrs=false, $returnValue=false)
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
        if($returnValue) {
            return $result;
        } else {
            echo "<pre>";
            echo htmlspecialchars($result);
            echo "</pre>";
        }
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

/*****************************************************************************
**                                                                          **
**                          DATA OBJECT ATTRIBUTE                           **
**                                                                          **
*****************************************************************************/
class DataObjectAttribute
    extends DOMAttr
{
    
    public function getName()
    {
        return $this->name;
    }
    
    public function __toString()
    {
        return (string)$this->nodeValue;
    }
    
}

/*****************************************************************************
**                                                                          **
**                          DATA OBJECT COMMENT                             **
**                                                                          **
*****************************************************************************/
class DataObjectComment
    extends DOMComment
{
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
    
    public function getName()
    {
        $contents = split(' ', $this);
        for($i=0; $i<count($contents); $i++) {
            $content = $contents[$i];
            if(mb_strlen(trim($content)) > 0) {
                return $content;
            }
        }
    
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

/*****************************************************************************
**                                                                          **
**                          DATA OBJECT LOG                                 **
**                                                                          **
*****************************************************************************/

class DataObjectLog 
    extends DataObjectComment
{
    const NONE      = 'NONE';
    const CREATE    = 'CREATE';
    const READ      = 'READ';
    const UPDATE    = 'UPDATE';
    const DELETE    = 'DELETE';
    const ENUMERATE = 'LIST';
    const VALIDATE  = 'VALIDATE';
    
    const INFO      = 0;
    const WARNING   = 1;
    const ERROR     = 2;
    const FATAL     = 3;

}

/*****************************************************************************
**                                                                          **
**                          DATA OBJECT LIST                                **
**                                                                          **
*****************************************************************************/
class DataObjectList
    implements IteratorAggregate, ArrayAccess, Countable
{
    
    private $storage = array();
    public $length;
    
    public function DataObjectList($nodeList=false)
    {
        if($nodeList) {
            $l = $nodeList->length;
            for($i=0; $i<$l; $i++) {
                $this->storage[$i] = $nodeList->item($i);
            } 
        }
        $this->length = count($this->storage);
    }
    
    public function show() 
    {
        $showArray = array();
        for($i=0; $i<count($this->storage); $i++) {
            $showArray[] = htmlspecialchars($this->storage[$i]->show(true, false, true));
        }
        echo "<pre>";
        print_r($showArray);
        echo "</pre>";
        
    }
    
    //*************************************************************************
    // MAGIC METHODS
    //*************************************************************************
    public function __get($name)
    {
        if($name == 'length') return count($this->storage);
    }
    
    
    //*************************************************************************
    // DOM NODELITS EMULATION
    //*************************************************************************    
    public function item($offset)
    {
        return $this->storage[$offset];
    }
    
    //*************************************************************************
    // DOM ELEMENT EMULATION
    //*************************************************************************    
    public function query($query)
    {
        if($firstObject = $this->storage[0]) {
            $dataObjectDocument = $firstObject->ownerDocument;
            $xpath = new DOMXPath($dataObjectDocument);
            return $xpath->query($query, $firstObject);
        } else {
            return false;
        }
    }
    
    //*************************************************************************
    // ITERATOR METHODS
    //*************************************************************************
    public function getIterator() {
        return new ArrayIterator($this->storage);
    }

    //*************************************************************************
    // COUNTABLE METHODS
    //*************************************************************************
    public function count()
    {
        return count($this->storage);
    } 
    
    //*************************************************************************
    // ARRAYACCESS METHODS
    //*************************************************************************
    public function offsetSet($offset, $value) 
    {
        if(!$offset) $offset = 0;
        $this->storage[$offset] = $value;
        $this->length = count($this->storage);
    }
    
    public function offsetExists($offset) 
    {
        return isset($this->storage[$offset]);
    }
    
    public function offsetUnset($offset) 
    {
        unset($this->storage[$offset]);
        $this->length = count($this->storage);
    }
    
    public function offsetGet($offset) 
    {
        return $this->storage[$offset];
    }


}