<?php
$_ENV['targets'] = array();
$_ENV['targets']['DOC'] = _DOCS;

if(core_tools::is_module_loaded('moreq'))
{
	$_ENV['targets']['CLASS'] = _CLASS_SCHEME;
}

?>
