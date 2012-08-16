<?php

class folderCMIS
{
    var $strReturn = 'THE RESULT IS A FOLDER WITH ';
    
    public function entryMethod ($atomFileContent, $requestedResourceId)
    {
        return $this->strReturn . $requestedResourceId . ' ' . $atomFileContent;
    }
    
}

