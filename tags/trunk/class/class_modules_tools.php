<?php
/**
*/



class tags
{
	public function build_modules_tables()
	{
		if (file_exists(
		    $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
		    . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . "modules"
		    . DIRECTORY_SEPARATOR . "tags" . DIRECTORY_SEPARATOR . "xml"
		    . DIRECTORY_SEPARATOR . "config.xml"
		)
		) {
			$path = $_SESSION['config']['corepath'] . 'custom'
			      . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
			      . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR
			      . "tags" . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
			      . "config.xml";
		} else {
			$path = "modules" . DIRECTORY_SEPARATOR . "tags"
			      . DIRECTORY_SEPARATOR . "xml" . DIRECTORY_SEPARATOR
			      . "config.xml";
		}
		$xmlconfig = simplexml_load_file($path);
	
		$hist = $xmlconfig->HISTORY;
		$_SESSION['history']['tagadd'] = (string) $hist->tagadd;
		$_SESSION['history']['tagup'] = (string) $hist->tagup;
		$_SESSION['history']['tagdel'] = (string) $hist->tagdel;
	}
}

