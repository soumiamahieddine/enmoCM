<?php
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_security.php");
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_manage_status.php");
require_once("apps".DIRECTORY_SEPARATOR.$_SESSION['config']['app_id'].DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_list_show.php");
require_once('modules'.DIRECTORY_SEPARATOR.'reports'.DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_graphics.php");
$core_tools = new core_tools();
$core_tools->load_lang();
$db = new dbquery();
$db->connect();
$db2 = new dbquery();
$db2->connect();
$db3 = new dbquery();
$db3->connect();
$req = new request();
$list = new list_show();
$graph = new graphics();
$sec = new security();

$entities_chosen=explode("#",$_POST['entities_chosen']);
$entities_chosen=join(",",$entities_chosen);

$status_obj = new manage_status();
$ind_coll = $sec->get_ind_collection('letterbox_coll');
$table = $_SESSION['collections'][$ind_coll]['table'];
$view = $_SESSION['collections'][$ind_coll]['view'];
$search_status = $status_obj->get_searchable_status();

//print_r($search_status);

$id_report = $_REQUEST['id_report'];
if(empty($id_report))
{
	?>
	<div class="error"><?php echo _REPORT.' '._UNKNOWN;?></div>
	<?php
	exit();
}

$report_type = $_REQUEST['report_type'];
if(empty($report_type))
{
	?>
	<div class="error"><?php echo _ERROR_REPORT_TYPE;?></div>
	<?php
	exit();
}

$period_type = $_REQUEST['period_type'];
if(empty($period_type))
{
	?>
	<div class="error"><?php echo _ERROR_PERIOD_TYPE;?></div>
	<?php
	exit();
}
$default_year = date('Y');
$where_date = '';
$date_title = '';

$str_status = '(';
	for($i=0;$i<count($search_status);$i++)
	{
		$str_status .= "'".$search_status[$i]['ID']."',";
	}
	$str_status = preg_replace('/,$/', ')', $str_status);
	$str_status2 = "('COU','END','NEW','RET','SIG','UNS','VAL','VIS','SMART','MAQUAL','BRSAS','XML','XML_SENT','WAIT_REPLY','BAP','DAV','AVD','APP')";

	if($period_type == 'period_year')
	{
		if(empty($_REQUEST['the_year']) || !isset($_REQUEST['the_year']))
		{
			?>
			<div class="error"><?php echo _YEAR.' '._MISSING;?></div>
			<?php
			exit();
		}
		if(	!preg_match('/^[1-2](0|9)[0-9][0-9]$/', $_REQUEST['the_year']))
		{
			?>
			<div class="error"><?php echo _YEAR.' '._WRONG_FORMAT;?></div>
			<?php
			exit();
		}
		
		$where_date = $req->extract_date('creation_date', 'year')." = '".$_REQUEST['the_year']."'";
		
		$date_title = _FOR_YEAR.' '.$_REQUEST['the_year'];
	}
	else if($period_type == 'period_month')
	{
		$arr_month = array('01','02','03','04','05','06','07','08','09','10','11','12');
		if(empty($_REQUEST['the_month']) || !isset($_REQUEST['the_month']))
		{
			?>
			<div class="error"><?php echo _MONTH.' '._MISSING;?></div>
			<?php
			exit();
		}
		if(	!in_array($_REQUEST['the_month'], $arr_month))
		{
			?>
			<div class="error"><?php echo _MONTH.' '._WRONG_FORMAT;?></div>
			<?php
			exit();
		}
		$where_date = $req->extract_date('creation_date', 'year')." = '".$default_year."' and ".$req->extract_date('creation_date', 'month')." = '".$_REQUEST['the_month']."'";
		$month = '';
		switch($_REQUEST['the_month'])
		{
			case '01':
			$month = _JANUARY;
			break;
			case '02':
			$month = _FEBRUARY;
			break;
			case '03':
			$month = _MARCH;
			break;
			case '04':
			$month = _APRIL;
			break;
			case '05':
			$month = _MAY;
			break;
			case '06':
			$month = _JUNE;
			break;
			case '07':
			$month = _JULY;
			break;
			case '08':
			$month = _AUGUST;
			break;
			case '09':
			$month = _SEPTEMBER;
			break;
			case '10':
			$month = _OCTOBER;
			break;
			case '11':
			$month = _NOVEMBER;
			case '12':
			$month = _DECEMBER;
			break;
			default:
			$month = '';
		}
		$date_title = _FOR_MONTH.' '.$month;
	}
	else if($period_type == 'custom_period')
	{
		if(isset($_REQUEST['date_start']) && $_REQUEST['date_start'] <> '')
		{
			$where_date  .= " AND ".$req->extract_date('creation_date')." > '".$db->format_date_db($_REQUEST['date_start'])."'";
			$date_title .= strtolower(_SINCE).' '.$_REQUEST['date_start'].' ';
		}

		if(isset($_REQUEST['date_fin']) && $_REQUEST['date_fin'] <> '')
		{
			$where_date  .= " AND ".$req->extract_date('creation_date')." < '".$db->format_date_db($_REQUEST['date_fin'])."'";
			$date_title.= strtolower(_FOR).' '.$_REQUEST['date_fin'].' ';
		}
		if(empty($where_date))
		{
			$where_date = $req->extract_date('creation_date', 'year')." = '".$default_year."'";
			$date_title = _FOR_YEAR.' '.$default_year;
		}
	}
	else
	{
		?>
		<div class="error"><?php echo _PERIOD.' '._MISSING;?></div>
		<?php
		exit();
	}

	$where_date = preg_replace('/^ AND/', '', $where_date);
//echo $where_date ;

	if($id_report == 'process_delay')
	{
		//$db->query("select type_id, description from ".$_SESSION['tablename']['doctypes']." where enabled = 'Y' order by description");
	
		if (!$_REQUEST['entities_chosen']){
	    $db->query("select type_id, description from ".$_SESSION['tablename']['doctypes']." where enabled = 'Y' order by description");
		}else{
		    $db->query("select type_id, description from ".$_SESSION['tablename']['doctypes']." where enabled = 'Y' and type_id IN (".$entities_chosen.") order by description");
		}
		//$db->show();



		$doctypes = array();
		
		while($res = $db->fetch_object())
		{
			array_push($doctypes, array('ID' => $res->type_id, 'LABEL' => $res->description));
		}

		if($report_type == 'graph')
		{
			$val_an = array();
			$_SESSION['labels1'] = array();
		}
		elseif($report_type == 'array')
		{
			$data = array();
		}
		$has_data = false;

		$totalDocTypes = count($doctypes);

		for($i=0; $i<count($doctypes);$i++)
		{
			//$db->query("SELECT doctypes_second_level_label,".$req->get_date_diff('closing_date', 'creation_date' )." AS delay FROM ".$view." WHERE  ".$where_date." AND closing_date is NOT NULL AND status in ".$str_status." and type_id = ".$doctypes[$i]['ID']."");
			$db->query("SELECT ".$view.".doctypes_second_level_label,".$req->get_date_diff($view.'.closing_date', $view.'.creation_date' )." AS delay from ".$view." inner join mlb_coll_ext on ".$view.".res_id = mlb_coll_ext.res_id WHERE  ".$where_date." AND ".$view.".closing_date is NOT NULL AND ".$view.".status not in ('DEL','BAD') and ".$view.".type_id = ".$doctypes[$i]['ID']."");
			//$db->show();


			$db2->query( "SELECT doctypes_second_level_label FROM doctypes INNER JOIN doctypes_second_level 
						  ON doctypes.doctypes_second_level_id = doctypes_second_level.doctypes_second_level_id
						  WHERE doctypes.type_id='". $doctypes[$i]['ID'] . "'");
			$res2 = $db2->fetch_object();

			if( $db->nb_result() > 0)
			{
				$tmp = 0;
				$nbDoc=0;
				while($res = $db->fetch_object())
				{
					if($res->delay <> ""){
						$tmp = $tmp + $res->delay;
						$nbDoc++;
					}
				}
				if ($nbDoc == 0) $nbDoc = 1;
				if($report_type == 'graph')
				{
					array_push($val_an, (string)$tmp / $nbDoc);
				}
				elseif($report_type == 'array')
				{
					array_push($data, array('SSCHEMISE' => $res2->doctypes_second_level_label, 'LABEL' => $db->show_string($doctypes[$i]['LABEL']), 'VALUE' => (string)round($tmp / $nbDoc,2)));
				}
				if($tmp / $nbDoc > 0)
				{
					$has_data = true;
				}
			}
			else
			{
				if($report_type == 'graph')
				{
					array_push($val_an, 0);
				}
				elseif($report_type == 'array')
				{
					array_push($data, array('SSCHEMISE' => $res2->doctypes_second_level_label, 'LABEL' => $db->show_string($doctypes[$i]['LABEL']), 'VALUE' => _UNDEFINED));
				}
			}
			if($report_type == 'graph')
			{
				array_push($_SESSION['labels1'], utf8_decode($db->show_string($doctypes[$i]['LABEL'])));
			}
		}

		if($report_type == 'graph')
		{
			$largeur=50*$totalDocTypes;
			if ($totalDocTypes<20){
				$largeur=1000;
			}
			
			$title = _PROCESS_DELAY_GENERIC_EVALUATION_REPORT_BY_TYPE.' '.$date_title ;
			$src1 = $_SESSION['config']['businessappurl']."index.php?display=true&module=reports&page=graphs&type=histo&largeur=$largeur&hauteur=600&marge_bas=300&title=".$title."&labelY="._N_DAYS;
			for($i=0;$i<count($_SESSION['labels1']);$i++)
			{
				//$src1 .= "&labels[]=".$_SESSION['labels1'][$i];
			}
			$_SESSION['GRAPH']['VALUES']='';
			for($i=0;$i<count($val_an);$i++)
			{
				$_SESSION['GRAPH']['VALUES'][$i]=$val_an[$i];
				//$src1 .= "&values[]=".$val_an[$i];
			}
		}
		elseif($report_type == 'array')
		{
			array_unshift($data, array('SSCHEMISE' => _SSCHEMISE, 'LABEL' => _DOCTYPE, 'VALUE' => _PROCESS_DELAY));

			// Tri du tableau $data
			foreach ($data as $key => $value) {
				$ssChemise[$key] = $value['SSCHEMISE'];
				$document[$key]  = $value['LABEL'];
			}
			array_multisort($ssChemise, SORT_ASC, $document, SORT_ASC, $data);
		}

		

		if ( $has_data)
		{
			if($report_type == 'graph')
			{
				echo "{label: ['".utf8_encode(str_replace(",", "','", addslashes(implode(",", $_SESSION['labels1']))))."'] ".
					", data: ['".utf8_encode(str_replace(",", "','", addslashes(implode(",", $_SESSION['GRAPH']['VALUES']))))."']".
					", title: '".addslashes($title)."'}";
				exit;
			}
			elseif($report_type == 'array')
			{
				$data2=urlencode(serialize($data));
				$form =	"<input type='button' class='button' value='Exporter les données' onclick='record_data(\"" . $_SESSION['config']['businessappurl']."index.php?display=true&dir=reports&page=record_data \",\"".$data2."\")' style='float:right;'/>";
				echo $form;

				$graph->show_stats_array($title, $data);
			}
		}
		else
		{
			echo '<br/><br/><div class="error">'._NO_DATA_MESSAGE.'</div>';
		}

	}
	if($id_report == 'process_delay_generic_evaluation')
	{	
		$data = "";
	//Gestion du graphique par année
		
	//récupération des libellés de mois
		$_SESSION['month'] = array();
		$_SESSION['month'][1] = _JANUARY;
		$_SESSION['month'][2] = _FEBRUARY;
		$_SESSION['month'][3] = _MARCH;
		$_SESSION['month'][4] = _APRIL;
		$_SESSION['month'][5] = _MAY;
		$_SESSION['month'][6] = _JUNE;
		$_SESSION['month'][7] = _JULY;
		$_SESSION['month'][8] = _AUGUST;
		$_SESSION['month'][9] = _SEPTEMBER;
		$_SESSION['month'][10] = _OCTOBER;
		$_SESSION['month'][11] = _NOVEMBER;
		$_SESSION['month'][12] = _DECEMBER;
		
		if($report_type == 'graph')
		{
			$val_an = array();
			$_SESSION['labels1'] = $_SESSION['month'];
		}
		elseif($report_type == 'array')
		{
			$data = array();
		}
		
	//Gestion en mode année
		if ($period_type == 'period_year')
		{
			for($i=1; $i<= 12; $i++)
			{
				if (!isset($where_date) || empty($where_date)) {
					$period = date("Y");
				} else {
					$period = substr($where_date, -5, -1);
				}
				
				//$db->query("SELECT ".$req->get_date_diff('closing_date', 'creation_date' )." FROM ".$view." WHERE status in ".$str_status." AND closing_date is NOT NULL AND date_part( 'month', creation_date)  = ".$i." and date_part( 'year', creation_date)  = ".$period." AND STATUS = 'END'");
				$db->query("SELECT ".$req->get_date_diff($view.'.closing_date', $view.'.creation_date')." from ".$view." inner join mlb_coll_ext on ".$view.".res_id = mlb_coll_ext.res_id WHERE ".$view.".status not in ('DEL','BAD') AND ".$view.".closing_date is NOT NULL AND date_part( 'month', ".$view.".creation_date)  = ".$i." and date_part( 'year', ".$view.".creation_date)  = ".$period);
				//$db->show();

				if( $db->nb_result() > 0)
				{
					
					$tmp = 0;
					$nbDoc = 0;
					while($elm = $db->fetch_array())
					{
						if ($elm[0] <> "") {
							$tmp = $tmp + $elm[0];
							$nbDoc++;
						}
					}
					if ($nbDoc == 0) $nbDoc = 1;
					if($report_type == 'graph')
					{
						array_push($val_an, (string)$tmp / $nbDoc);
					}
					elseif($report_type == 'array')
					{
						array_push($data, array('LABEL' => $_SESSION['month'][$i], 'VALUE' => (string)round($tmp / $nbDoc,2)));
					}
					if($tmp / $nbDoc > 0)
					{
						$has_data = true;
					}
				}
				else
				{
					if($report_type == 'graph')
					{
						array_push($val_an, 0);
					}
					elseif($report_type == 'tab')
					{
						array_push($data, array('LABEL' => $_SESSION['month'][$i], 'VALUE' => _UNDEFINED));
					}
				}
			}
			$title = _REPORTS_EVO_PROCESS.' '.$date_title ;
			if($report_type == 'graph')
			{
				$src1 = $_SESSION['config']['businessappurl']."index.php?display=true&module=reports&page=graphs&type=courbe&largeur=1000&hauteur=400&title=".$title."&labelX="._MONTH."&labelY="._N_DAYS;
				for($k=1;$k<=count($_SESSION['labels1']);$k++)
				{
					$src1 .= "&labels[]=".$_SESSION['labels1'][$k];
				}
				for($l=0;$l<count($val_an);$l++)
				{
					$src1 .= "&values[]=".$val_an[$l];
				}
			}
			elseif($report_type == 'array')
			{
				array_unshift($data, array('LABEL' => _MONTH, 'VALUE' => _PROCESS_DELAI_AVG));
			}
		}
	//Gestion du graphique par mois
		if ($period_type == 'period_month')
		{
			if($report_type == 'graph')
			{
				$val_mois = array();
			}
			elseif($report_type == 'graph')
			{
				$data = array();
			}
			
		//$max = date("t", $_REQUEST['the_month']);
			
			$mois = mktime( 0, 0, 0, $_REQUEST['the_month'], 1, date("Y") );
			$max = date("t",$mois);
			
			for($i=1; $i<= $max; $i++)
			{
				
				$db->query("SELECT ".$req->get_date_diff('closing_date', 'creation_date' )." FROM ".$view." WHERE status in ".$str_status." and date_part( 'month', creation_date)  = ".$_REQUEST['the_month']." and date_part( 'year', creation_date)  = ".date('Y')." and date_part( 'day', creation_date)  = ".$i." and ".$view.".closing_date is not null");
				
				if( $db->nb_result() > 0)
				{
					$tmp = 0;
					$nbDoc = 0;
					while($elm = $db->fetch_array())
					{
						if ($elm[0] <> "") {
							$tmp = $tmp + $elm[0];
							$nbDoc++;
						}
					}
					if ($nbDoc == 0) $nbDoc = 1;
					if($report_type == 'graph')
					{
						array_push($val_mois, (string) $tmp / $nbDoc);
					}
					elseif($report_type == 'array')
					{
						array_push($data, array('LABEL' => $i, 'VALUE' => (string) $tmp / $nbDoc));
					}
					$has_data = true;
				}
				else
				{
					if($report_type == 'graph')
					{
						array_push($val_mois, 0);
					}
					elseif($report_type == 'array')
					{
						array_push($data, array('LABEL' => $i, 'VALUE' => _UNDEFINED));
					}
				}
			} 
			
			$title2 = _REPORTS_EVO_PROCESS;
			if($report_type == 'graph')
			{
				
				$src2 = $_SESSION['config']['businessappurl']."index.php?display=true&module=reports&page=graphs&type=courbe&largeur=1000&hauteur=406&title=".$title2."&labelX="._DAYS."&labelY="._N_DAYS;
				
				$label_month = array();
				for($k=1;$k<=$max;$k++)
				{
					$src2 .= "&labels[]=".$k;
					$label_month[$k] = $k;
				}
				for($l=0;$l<count($val_mois);$l++)
				{
					$src2 .= "&values[]=".$val_mois[$l];
				}
				
			}
			elseif($report_type == 'array')
			{
				array_unshift($data, array('LABEL' => _DAYS, 'VALUE' => _PROCESS_DELAI_AVG));
				
			}
		}

		
		
		if ($period_type == 'period_year' && $has_data)
		{
			if($report_type == 'graph')
			{
				echo "{label: ['".html_entity_decode(str_replace(",", "','", addslashes(implode(",", $_SESSION['labels1']))))."'] ".
					", data: ['".utf8_encode(str_replace(",", "','", addslashes(implode(",", $val_an))))."']".
					", title: '".addslashes($title1)."'}";
				exit;
			}
				elseif($report_type  == 'array')
				{
					$data2=urlencode(serialize($data));
					$form =	"<input type='button' class='button' value='Exporter les données' onclick='record_data(\"" . $_SESSION['config']['businessappurl']."index.php?display=true&dir=reports&page=record_data \",\"".$data2."\")' style='float:right;'/>";
					echo $form;

					$graph->show_stats_array($title1, $data);
				}
		}
			elseif ($period_type == 'period_month' && $has_data)
			{
				if($report_type == 'graph')
				{
					// var_dump($val_mois);
					echo "{label: ['".utf8_encode(str_replace(",", "','", addslashes(implode(",", $label_month))))."'] ".
						", data: ['".utf8_encode(str_replace(",", "','", addslashes(implode(",", $val_mois))))."']".
						", title: '".addslashes($title2)."'}";
					exit;
				}
				elseif($report_type == 'array')
				{
					$data2=urlencode(serialize($data));
					$form =	"<input type='button' class='button' value='Exporter les données' onclick='record_data(\"" . $_SESSION['config']['businessappurl']."index.php?display=true&dir=reports&page=record_data \",\"".$data2."\")' style='float:right;'/>";
					echo $form;

					$graph->show_stats_array($title2, $data);
				}
			}
			else
			{
				echo '<br/><br/><div class="error">'._NO_DATA_MESSAGE.'</div>';
			}

			
		}
		else if($id_report == 'mail_typology')
		{
			$has_data = false;
			$title = _MAIL_TYPOLOGY_REPORT.' '.$date_title ;
			//$db->query("select distinct type_id, type_label from ".$view ." where status in ".$str_status." and ".$where_date." ORDER BY type_label ASC");
			//$db->query("select type_id, description from ".$_SESSION['tablename']['doctypes']." where enabled = 'Y' order by description");
			
			if (!$_REQUEST['entities_chosen']){
		    $db->query("select type_id, description from ".$_SESSION['tablename']['doctypes']." where enabled = 'Y' order by description");
			}else{
			    $db->query("select type_id, description from ".$_SESSION['tablename']['doctypes']." where enabled = 'Y' and type_id IN (".$entities_chosen.") order by description");
			}



			//$db->show();
			if($report_type == 'graph')
			{
				$vol_an = array();
				$vol_mois = array();
				$_SESSION['labels1'] = array();
			}
			elseif($report_type == 'array')
			{
				$data = array();

			}

			$totalCourrier=array();
			$totalEntities = count($entities);
			$z=0;
			while($line = $db->fetch_object())
			{
				//$db2->query("select count(*) as total from ".$view." where status in ".$str_status."  and ".$where_date." and type_id = ".$line->type_id."");
				$db2->query("select count(*) as total from ".$view." inner join mlb_coll_ext on ".$view.".res_id = mlb_coll_ext.res_id where ".$where_date." and type_id = ".$line->type_id." and ".$view.".status not in ('DEL','BAD')");
				$res = $db2->fetch_object();
				//$db2->show();

				$db3->query( "select doctypes_second_level_label from doctypes inner join doctypes_second_level 
						  on doctypes.doctypes_second_level_id = doctypes_second_level.doctypes_second_level_id
						  where doctypes.type_id='". $line->type_id . "'");
				$res3 = $db3->fetch_object();


				if($report_type == 'graph')
				{
					//array_push($_SESSION['labels1'], (string)utf8_decode($line->type_label));
					array_push($_SESSION['labels1'], (string)utf8_decode($line->description));
					array_push($vol_an, $res->total);
				}
				elseif($report_type == 'array')
				{
					array_push($data, array('SSCHEMISE' => $res3->doctypes_second_level_label, 'LABEL' =>$line->description, 'VALUE' => $res->total ));
					array_push($totalCourrier, $res->total);
				}

				if($res->total > 0)
				{
					$has_data = true;
				}
				$totalDocTypes=$z++;
			}

			if($report_type == 'array'){

				$totalCourriers=array_sum($totalCourrier);
				array_push($data, array('SSCHEMISE' => '_', 'LABEL' => 'Total :', 'VALUE' => $totalCourriers ));
			}

			if($report_type == 'graph')
			{
				$largeur=50*$totalDocTypes;

				if ($totalDocTypes<20){
					$largeur=1000;
				}

				$src1 = $_SESSION['config']['businessappurl']."index.php?display=true&module=reports&page=graphs&type=histo&largeur=$largeur&hauteur=600&marge_bas=300&title=".$title;
				$_SESSION['GRAPH']['VALUES']='';
				for($i=0;$i<count($vol_an);$i++)
				{
					$_SESSION['GRAPH']['VALUES'][$i]=$vol_an[$i];
					//$src1 .= "&values[]=".$vol_an[$i];
				}
			}
			elseif($report_type == 'array')
			{
				array_unshift($data, array('SSCHEMISE'=> _SSCHEMISE, 'LABEL' => _DOCTYPE, 'VALUE' => _NB_MAILS1));
				// Tri du tableau $data
				foreach ($data as $key => $value) {
					$ssChemise[$key] = $value['SSCHEMISE'];
					$document[$key]  = $value['LABEL'];
				}
				array_multisort($ssChemise, SORT_ASC, $document, SORT_ASC, $data);
			}
		
			if($has_data)
			{
				if($report_type == 'graph')
				{
					echo "{label: ['".utf8_encode(str_replace(",", "','", addslashes(implode(",", $_SESSION['labels1']))))."'] ".
						", data: ['".utf8_encode(str_replace(",", "','", addslashes(implode(",", $_SESSION['GRAPH']['VALUES']))))."']".
						", title: '".addslashes($title)."'}";
					exit;
				}
				elseif($report_type == 'array')
				{
					$data2=urlencode(serialize($data));
					$form =	"<input type='button' class='button' value='Exporter les données' onclick='record_data(\"" . $_SESSION['config']['businessappurl']."index.php?display=true&dir=reports&page=record_data \",\"".$data2."\")' style='float:right;'/>";
					echo $form;

					$graph->show_stats_array($title, $data);
				}
			}
			else
			{
				echo '<br/><br/><div class="error">'._NO_DATA_MESSAGE.'</div>';
			}
			exit();
		}
		else if($id_report == 'mail_vol_by_cat')
		{
			$has_data = false;
			$title = _MAIL_VOL_BY_CAT_REPORT.' '.$date_title ;
			if($report_type == 'graph')
			{
				$vol_an = array();
				$vol_mois = array();
				$_SESSION['labels1'] = array();
			}
			elseif($report_type == 'array')
			{
				$data = array();
			}

			$totalCourrier=array();
			$totalEntities = count($entities);


			foreach(array_keys($_SESSION['coll_categories']['letterbox_coll']) as $key)
			{
				if($key!='default_category'){
					$db->query("select count(*) as total from ".$view." inner join mlb_coll_ext on ".$view.".res_id = mlb_coll_ext.res_id where ".$view.".status not in ('DEL','BAD')  and ".$where_date." and ".$view.".category_id = '".$key."'");
					$res = $db->fetch_object();
					//$db->show();
					if($report_type == 'graph')
					{
						array_push($_SESSION['labels1'], utf8_decode($db->wash_html($_SESSION['coll_categories']['letterbox_coll'][$key], 'NO_ACCENT')));
						array_push($vol_an, $res->total);
					}
					elseif($report_type == 'array')
					{
						array_push($data, array('LABEL' => $_SESSION['coll_categories']['letterbox_coll'][$key], 'VALUE' => $res->total ));
						array_push($totalCourrier, $res->total);
					}

					if($res->total > 0)
					{
						$has_data = true;
					}
				}
			}

			if($report_type == 'array'){
				$totalCourriers=array_sum($totalCourrier);
				array_push($data, array('LABEL' => 'Total :', 'VALUE' => $totalCourriers ));
			}

			if($report_type == 'graph')
			{
				$largeur=50*$totalEntities;
				if ($totalEntities<5){
					$largeur=1000;
				}

				$src1 = $_SESSION['config']['businessappurl']."index.php?display=true&module=reports&page=graphs&type=histo&largeur=$largeur&hauteur=600&marge_bas=150&title=".$title;

				$_SESSION['GRAPH']['VALUES']='';
				for($i=0;$i<count($vol_an);$i++)
				{
					//$src1 .= "&values[]=".$vol_an[$i];
					$_SESSION['GRAPH']['VALUES'][$i]=$vol_an[$i];
				}
			}
			elseif($report_type == 'array')
			{
				array_unshift($data, array('LABEL' => _CATEGORY, 'VALUE' => _NB_MAILS1));
			}
	//echo $src1;
			if($has_data)
			{
				if($report_type == 'graph')
				{
					echo "{label: ['".utf8_encode(str_replace(",", "','", addslashes(implode(",", $_SESSION['labels1']))))."'] ".
						", data: ['".utf8_encode(str_replace(",", "','", addslashes(implode(",", $_SESSION['GRAPH']['VALUES']))))."']".
						", title: '".addslashes($title)."'}";
					exit;
				}
				elseif($report_type == 'array')
				{
					$data2=urlencode(serialize($data));
					$form =	"<input type='button' class='button' value='Exporter les données' onclick='record_data(\"" . $_SESSION['config']['businessappurl']."index.php?display=true&dir=reports&page=record_data \",\"".$data2."\")' style='float:right;'/>";
					echo $form;
					
					$graph->show_stats_array($title, $data);
				}
			}
			else
			{
				echo '<br/><br/><div class="error">'._NO_DATA_MESSAGE.'</div>';
			}
			exit();
		}
