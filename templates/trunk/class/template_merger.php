<?php

/*

datasource :
[dataname] 	=> datavalue

datavalue can be :
A scalar value 						([dataname] => value)
An object or an associative array	([dataname] => object) 
An indexed array of arrays

Insert the data : {#mergefield.dataname}
If the data is scalar, simple insert
If the data is object or associative array, insert as imploded with blank separator
If the data is array of arrays or array of objects, insert as imploded with <p> between each record and blanck between each value

Insert a data attribute : {#mergefield.dataname.attribute}
If the data is scalar, no insert.
If the data is object or associative array, insert the requested attribute as for a mergefield 

			=> array 
				[index] 
					[attribute] => value	{#loop.dataname} + list + item including {#mergefield.dataname}


*/


class template_merger extends dbquery {
	
	private $parameters;
	public $parser;
	
	function set($parm_name, $parm_value) {
		$this->parameters[$parm_name] = $parm_value;
	}
	
	function merge($template, $datasource, $style) {
	
		// Parse global template instructions
		//$parsed_template = $this->parse_template($this->template);
		//print_r($parsed_template);
		
		$parser = $this->parse_template($template);
		
		if(isset($GLOBALS['maarchDirectory'])) {
			$maarchDirectory = $GLOBALS['maarchDirectory'];
		} elseif(isset($_SESSION['maarchDirectory'])) {
			$maarchDirectory = $_SESSION['maarchDirectory'];
		}
		require_once($maarchDirectory 
			. 'core' 
			. DIRECTORY_SEPARATOR . 'class' 
			. DIRECTORY_SEPARATOR . 'class_core_tools.php'
			);
		$coreTools = new core_tools();
		$coreTools->load_lang($lang, $maarchDirectory, $maarchApps);
		
		$result = '';
		foreach($parser as $id => $part) {
			if($part['type'] == 'txt') {
				$result .= $part['string'];
			}
			if($part['type'] == 'ins') {
				//echo "\nProcessing instruction" . $part['string'];
				$content = substr($part['string'], 2, mb_strlen($part['string']) - 3);
				$args = explode('.',$content); 
				$cmd = $args[0];
				array_shift($args);
				
				switch($cmd) {
				case 'mergefield':
					// Path to requested value in datasource
					//echo "\nmerge field " .$part['string'];
					$dspath = $this->dspath($datasource, $args);
					$field = $this->show_text($dspath);			
					$result .= $field;
					break;
				
				case 'mergetable' :
					$dspath = $this->dspath($datasource, $args);
					$field = $this->show_table($dspath);			
					$result .= $field;
					break;
				
				case 'mergelist' :
					$dspath = $this->dspath($datasource, $args);
					$field = $this->show_list($dspath);			
					$result .= $field;
					break;
				
				case 'insertdatetime' :
					$format = $args[0];
					$fmt = $this->parameters[$format];
					$result .= date($fmt);
					break;
					
				case 'translate' :
					$constant = $args[0];
					$result .= @constant($constant);
					break;
			
				} // end switch $cmd
				
			} // end 'ins'
		}
		
		$header  = '<header>';
		$header .= '<style type="text/css">' . $style . '</style>';
		$header .= '</header>';
		
		$body 	 = '<body>' . $result . '</body>';
		
		$output = $header . $body;
		// debug
		//$output .= '<br/>Datasource : <pre>' . print_r($datasource,true) . '</pre>';
		//$output .= '<br/>Parameters : <pre>' . print_r($this->parameters,true) . '</pre>';
		return $output;
	}
	
	function parse_template($template) {
		
		preg_match_all('/\{#\w+(\.\w+)+\}/', $template, $matches, PREG_OFFSET_CAPTURE);
		$instrs = array();
		foreach($matches[0] as $id => $match) {
			$io = $match[1];
			$instrs[$io]['type'] = 'ins';
			$instrs[$io]['string'] = $match[0];
		}
		
		$to = 0;
		foreach($instrs as $io => $instr) {
			$string = substr($template, $to, $io - $to); 
			if($string) {
				$instrs[$to]['type'] = 'txt';
				$instrs[$to]['string'] = $string;
			}
			$to = $io + mb_strlen($instr['string']);
		}
		
		ksort($instrs);
		//print_r($instrs);
		return $instrs;	
	}
	
	function dspath($dspath, $args) {
		$dstype = $this->dstype($dspath);
		for($id=0; $id<count($args); $id++) {	
			$p = $args[$id];
			//echo "\nMoving from $debug of type $dstype to $p";
			switch($dstype) {
			case 'array' :
				$dspath = $dspath[$p];
				$debug .= "[$p]";
				$dstype = $this->dstype($dspath);
				//echo "\nFound path $debug of type $dstype";
				break;
				
			case 'object' :
				$dspath = $dspath->$p;
				$debug .= "->$p";
				$dstype = $this->dstype($dspath);
				//echo "\nFound path $debug of type $dstype";
				break;
			
			case 'scalar' :
				$dstype = $this->dstype($dspath);
				//echo "\nFound value at $debug = $dspath";
				break 2;
				
			case 'null':
			case 'resource' :
			case false :
			default :
				break 2;
			}
		}
		return $dspath;
	}
	
	
	function dstype($dspath) {
		if(is_scalar($dspath))  return 'scalar';
		if(is_array($dspath)) return 'array';
		if(is_object($dspath)) return 'object';
		if(is_resource($dspath)) return 'resource';
		return false;
	}	
	
	function show_text($data, $level=1) {	
		if (is_array($data) || is_object($data)) {  
			foreach($data as $key => $value) {
				$text .=  '<br/><b>' . str_repeat('&nbsp;', $level) . $key . ': </b>';
				$text .= $this->show_text($value, ($level + 1));
			}
			return $text;
		}  
		else { 
			return trim($data);
		}
	}
	
	function show_list($data) {	
		$list .= "<ul>";
		if (is_array($data) || is_object($data)) {  	
			foreach($data as $key => $value) {
				$list .=  '<li><b>' . $key . ': </b>';
				$list .= $this->show_text($value);
				$list .= "</li>";
			}
		}  
		else{ 
			$list .=  '<li>' . $this->show_text($data) . "</li>";
		}
		$list .= "</ul>";
		return $list;
	}
	
	function show_table($data, $level=1) {	
		$table = '<table>';
		// Multidimensional array : each value becomes a column
		if (is_array($data)
			&& (is_array($data[0]) || is_object($data[0]))) {  
				$table .= '<thead>';
				$table .=  '<tr><th>#</th>';
				foreach($data[0] as $key => $value) {
					$table .=  '<th>' . $key . '</th>';
				}
				$table .=  '</tr>';
				$table .= '</thead>';
		}
		$table .= '<tbody>';
		if(is_array($data[0]) || is_object($data[0])) {
			foreach($data as $key => $value) {
				$table .= '<tr>';
				$table .= '<td>' . $key . '</td>';
				if (is_array($value) || is_object($value)) { 
					foreach($value as $key2 => $value2) {
						$table .= '<td>' . $this->show_text($value2) . '</td>'; 
					}
				}
				else {                        
					$table .= '<td>' . $value . '</td>'; 
				}
				$table .= '</tr>';
			}
		} else {
			$table = '<tr>';
			$table .= '<td>' . $data . '</td>';
			$table .= '</tr>';
		}
		$table .= '</tbody>';
		$table .= '</table>';
		return $table;
	}
}


?>