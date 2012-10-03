<?php
namespace maarch;
class Exception
	extends \Exception
{
	
    public $id;
	public $level;
	public $message;
    
	public function Exception($message, $code=false)
	{
		if(is_object($message) && get_class($message) == 'Message') {
            parent::__construct($message->message, $code);
            $this->id = $message->id;
            $this->level = $message->level;
        } else {
            parent::__construct($message, $code);
            $this->level = $level;
        }
	}
	
}