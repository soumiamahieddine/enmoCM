<?php

define('ADD_DOCUMENT', 1);
define('CREATE_SERIE', 2);
define('CREATE_OTHER_AGREGATE', 4);
define('DATA_MODIFICATION', 8);
define('DELETE_DOCUMENT', 16);
define('DELETE_SERIE', 32);
define('DELETE_OTHER_AGREGATE', 64);
define('VIEW_LOG', 128);

$_ENV['security_bitmask'] = array();
array_push($_ENV['security_bitmask'], array('ID' => ADD_DOCUMENT, 'LABEL' => _ADD_DOCUMENT_LABEL));
array_push($_ENV['security_bitmask'], array('ID' => CREATE_SERIE, 'LABEL' => _CREATE_SERIE_LABEL));
array_push($_ENV['security_bitmask'], array('ID' => CREATE_OTHER_AGREGATE, 'LABEL' => _CREATE_OTHER_AGREGATE_LABEL));
array_push($_ENV['security_bitmask'], array('ID' => DATA_MODIFICATION, 'LABEL' => _DATA_MODIFICATION_LABEL));
array_push($_ENV['security_bitmask'], array('ID' => DELETE_DOCUMENT, 'LABEL' => _DELETE_DOCUMENT_LABEL));
array_push($_ENV['security_bitmask'], array('ID' => DELETE_SERIE, 'LABEL' => _DELETE_SERIE_LABEL));
array_push($_ENV['security_bitmask'], array('ID' => DELETE_OTHER_AGREGATE, 'LABEL' => _DELETE_OTHER_AGREGATE_LABEL));
array_push($_ENV['security_bitmask'], array('ID' => VIEW_LOG, 'LABEL' => _VIEW_LOG_LABEL));

function get_task_label($id_task, $tasks_array)
{
	for($i=0; $i<count($tasks_array); $i++)
	{
		if($tasks_array[$i]['ID'] == $id_task)
		{
			return $tasks_array[$i]['LABEL']; 
		}
	}
	return '';
}
?>
