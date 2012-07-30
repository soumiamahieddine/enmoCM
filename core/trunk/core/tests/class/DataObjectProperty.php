<?php

class DataObjectProperty
	extends DOMAttr
{

    public function __toString()
    {
        return (string)$this->value;
    }

}