<?php
class message
{
    public $level;
    public $code;
    public $text;
    
    function message($level, $code, $text)
    {
        $this->level = $level;
        $this->code = $code;
        $this->text = $text;
    }

}