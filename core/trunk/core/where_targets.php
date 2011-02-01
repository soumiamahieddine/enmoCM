<?php
$core = new core_tools();
$core->load_lang();
$_ENV['targets'] = array();
$_ENV['targets']['DOC'] = _DOCS;

if($core->is_module_loaded('moreq'))
{
    $_ENV['targets']['CLASS'] = _CLASS_SCHEME;
}

?>
