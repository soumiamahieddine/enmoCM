<?php
	require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');
	$ent = new entity();
	$_SESSION['m_admin']['entities'] = array();
	$_SESSION['m_admin']['entities'] = $ent->getShortEntityTree($_SESSION['m_admin']['entities'] ,'all', '', array(), 'all' );

	if($_SESSION['origin'] == "basket_up" ||$_SESSION['origin'] == 'basket_add')
	{
		if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."entities".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."redirect_keywords.xml"))
		{
			$path = $_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."entities".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."redirect_keywords.xml";
		}
		else
		{
			$path = "modules".DIRECTORY_SEPARATOR."entities".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."redirect_keywords.xml";
		}
		$xml = simplexml_load_file($path);
		$_SESSION['m_admin']['redirect_keywords'] = array();
		$path_lang = "modules".DIRECTORY_SEPARATOR."entities".DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['config']['lang'].'.php';

		foreach($xml->keyword as $keyword)
		{
			$tmp = (string)$keyword->label;
			// the label of the page comes from the module basket languages files
			$tmp2 = $ent->retrieve_constant_lang($tmp, $path_lang);
			if($tmp2 <> false)
			{
				$desc =  $tmp2;
			}
			else
			{
				$desc = $tmp;
			}
			array_push($_SESSION['m_admin']['redirect_keywords'], array('ID' => (string) $keyword->id, 'LABEL' => $desc, 'KEYWORD' => true));
		}
		$_SESSION['m_admin']['entities'] = array_merge($_SESSION['m_admin']['redirect_keywords'],$_SESSION['m_admin']['entities']);
	}
?>
