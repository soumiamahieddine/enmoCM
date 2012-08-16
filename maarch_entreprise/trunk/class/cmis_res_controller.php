<?php

class resCMIS
{
    var $strReturn = 'THE RESULT IS A RES WITH ';
    
    public function entryMethod ($atomFileContent, $requestedResourceId)
    {
        return $this->strReturn . $requestedResourceId . ' ' . $atomFileContent;
    }
    
}

