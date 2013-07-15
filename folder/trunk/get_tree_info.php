<?php
/*
*    Copyright 2008 - 2011 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Returns in a json structure all allowed first branches of a tree for
* the current user (Ajax)
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/
require_once "core/class/class_security.php";
require_once "core/class/class_core_tools.php";
require_once "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR
    . 'class_business_app_tools.php';
$appTools = new business_app_tools();
$core = new core_tools();
$core->load_lang();
$sec = new security();
$func = new functions();
$db = new dbquery();
$db->connect();
$dbTmp = new dbquery();
$dbTmp->connect();
$db1 = new dbquery();
$db1->connect();
$db2 = new dbquery();
$db2->connect();
$db3 = new dbquery();
$db3->connect();
$db4 = new dbquery();
$db4->connect();

$collId = $_SESSION['user']['collections'][0];
if ($_SESSION['user']['collections'][1] == 'letterbox_coll') {
    $collId = 'letterbox_coll';
}
$resView = $_SESSION['user']['security'][$collId]['DOC']['view'];
$whereClause = $sec->get_where_clause_from_coll_id($_SESSION['collection_id_choice']);
if (trim($whereClause) == "") {
	$whereClause = "1=1";
}

if (isset($_SESSION['nc']) && ! empty($_SESSION['nc'])) {
	$_REQUEST['num_folder'] = $_SESSION['nc']['num_folder'];
	$_REQUEST['name_folder'] = $_SESSION['nc']['name_folder'];
}
unset($_SESSION['nc']);
unset($_SESSION['chosen_num_folder']);
unset($_SESSION['chosen_name_folder']);
if (! empty($_REQUEST['project']) && empty($_REQUEST['market'])) {
	if (substr(
	    $_REQUEST['project'], strlen($_REQUEST['project']) - 1,
	    strlen($_REQUEST['project'])
	) == ")"
	) {
	    $folderSystemId = str_replace(
			')', '',
	        substr($_REQUEST['project'], strrpos($_REQUEST['project'], '(') + 1)
		);
	}
}
if (! empty($_REQUEST['market'])) {
	if (substr(
	    $_REQUEST['market'], strlen($_REQUEST['market']) - 1,
	    strlen($_REQUEST['market'])
	) == ")"
	) {
		$folderSystemId = str_replace(
			')', '',
		    substr($_REQUEST['market'], strrpos($_REQUEST['market'], '(') + 1)
		);
	}
}

if (isset($folderSystemId) && $folderSystemId <> '') {
	$_SESSION['chosen_name_folder'] = $folderSystemId;
	$dbTmp->query(
		"select distinct folder_id, folder_name, subject, folder_level, "
	    . "folders_system_id from " . $_SESSION['tablename']['fold_folders']
	    . " where folders_system_id = " . $folderSystemId
	);
	//$dbTmp->show();
	while ($resTmp = $dbTmp->fetch_object()) {
		if ($resTmp->folder_level == '1') {
			$db->query(
				"select distinct folder_id, folder_name, subject, folder_level,"
				. " folders_system_id from "
				. $_SESSION['tablename']['fold_folders'] . " where parent_id = "
				. $resTmp->folders_system_id . " or folders_system_id = "
				. $folderSystemId . " order by folder_name"
		    );

			$flagProject = true;
		} else {
			$db->query(
				"select distinct folder_id, folder_name, subject, folder_level,"
				. " folders_system_id, parent_id from "
				. $_SESSION['tablename']['fold_folders']
				. " where folders_system_id = " . $folderSystemId
				. " order by folder_name"
			);
		}
	}
	// $db->show();
}
$searchCustomerResults = array();
if (isset($_SESSION['chosen_name_folder'])
    && ! empty($_SESSION['chosen_name_folder'])
) {
    while ($res = $db->fetch_object()) {
		$actualCustomT1 = $res->folder_id;
		//echo '<br/>'.$actualCustomT1.'<br/>';
		//if (isset($flagProject) && $flagProject) {
			$dbTmp->query(
				"select folder_name, subject from "
			    . $_SESSION['tablename']['fold_folders']
			    . " where folders_system_id = " . $folderSystemId
			);
			$resTmp = $dbTmp->fetch_object();
			$idProject = $resTmp->folder_name;
			$labelProject = $resTmp->subject;
	/*	} else {
			$dbTmp->query(
				"select folder_name, subject from "
			    . $_SESSION['tablename']['fold_folders']
			    . " where folders_system_id = " . $res->parent_id . ""
			);
			$resTmp = $dbTmp->fetch_object();
			$idProject = $resTmp->folder_name;
			$labelProject = $resTmp->subject;
		}*/
		$db4->query(
			"select count(res_id) as cptresult from " . $resView
		    . " where folder_id = '" . $actualCustomT1 . "' and ("
		    . $whereClause . ")"
		);
		$rescpt4 = $db4->fetch_object();
		if ($rescpt4->cptresult > 150) {
			$error = "<br><br><p align='center' style='color:#FFC200;'>Passez SVP par la recherche avanc&eacute;e ou la recherche de dossiers, le nombre de documents demand&eacute; est trop important (".$rescpt4->cptresult.")</p>";
			break;
		}
		$folderLevels = array();
		$db1->query(
			"select distinct doctypes_first_level.doctypes_first_level_id, doctypes_first_level.doctypes_first_level_label, doctype_first_level_style from "
		    . $resView . ", doctypes_first_level where (" . $resView . ".doctypes_first_level_id = doctypes_first_level.doctypes_first_level_id) and (folder_id = '" . $actualCustomT1 . "' and ("
		    . $whereClause . ")) and doctypes_first_level.enabled = 'Y' order by doctypes_first_level_label asc"
		);
		//$db1->show();
		while ($res1 = $db1->fetch_object()) {
			$sLevel = array();
			$db2->query(
				"select distinct doctypes_second_level.doctypes_second_level_id, doctypes_second_level.doctypes_second_level_label, doctype_second_level_style from "
			    . $resView . ", doctypes_second_level where (" . $resView . ".doctypes_second_level_id = doctypes_second_level.doctypes_second_level_id) and ((" . $resView . ".doctypes_first_level_id = "
			    . $res1->doctypes_first_level_id . " and folder_id = '"
			    . $actualCustomT1 . "') and (" . $whereClause
			    . ")) and doctypes_second_level.enabled = 'Y' order by doctypes_second_level_label asc"
			);
			//$db2->show();
			//echo $res1->doctypes_first_level_label."<br>";
			while ($res2 = $db2->fetch_object()) {
				$doctypes = array();
				//echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$res2->doctypes_second_level_label."<br>";
				$db3->query(
					"select distinct type_id, type_label as description from "
				    . $resView . " where (doctypes_first_level_id = "
				    . $res1->doctypes_first_level_id
				    . " and doctypes_second_level_id = "
				    . $res2->doctypes_second_level_id . " and folder_id = '"
				    . $actualCustomT1 . "') and (" . $whereClause
				    . ") order by type_label asc"
				);
				//$db3->show();
				while ($res3 = $db3->fetch_object()) {
					$cptDoc = 0;
				/*	$db4->query(
						"select count(res_id) as total from " . $resView
						. " where (type_id = " . $res3->type_id
					    . " and folder_id = '" . $actualCustomT1 . "') and ("
					    . $whereClause . ")"
					);
					$res4 = $db4->fetch_object();
					if (isset($res4->total)) {
						$cptDoc = $res4->total;
					}

					array_push(
					    $doctypes,
					    array(
					    	'type_id' => $res3->type_id,
					    	'description' => $func->show_string($res3->description, true) . '('.$cptDoc.')',
					    )
					);*/
					$db4->query(
						"select res_id, doc_date, folder_name, identifier, subject from "
						. $resView." where (type_id = " . $res3->type_id
						. " and folder_id = '" . $actualCustomT1 . "') and ("
						. $whereClause . ") order by doc_date desc"
					);
					$results = array();
					while ($res4 = $db4->fetch_object()) {
						array_push(
							$results,
							array(
								'res_id' => $res4->res_id,
								'doc_date' => $res4->doc_date,
								'name_folder' => $res4->folder_name,
								'num_ref' => $res4->identifier,
								'subject' => $func->show_string($res4->subject, true),
							)
						);
						$cptDoc++;
					}
					if ($cptDoc == 0) {
						//array_push($doctypes, array('type_id' => $res3->type_id, 'description' => $func->show_string($res3->description), "results" => $results, "no_doc" => true ));
					} else {
						array_push(
							$doctypes,
							array(
								'type_id' => $res3->type_id,
								'description' => $func->show_string($res3->description, true) . ' ('.$cptDoc.')',
								"results" => $results,
								"no_doc" => false
							)
						);
					}
				}
 				if (isset($res2->doctype_second_level_style) && !empty($res2->doctype_second_level_style)) {
                    $dslStyle = $res2->doctype_second_level_style;
                } else {
                    $dslStyle = 'default_style';
                }
				array_push(
				    $sLevel,
				    array(
				    	'doctypes_second_level_id' => $res2->doctypes_second_level_id,
				    	'doctypes_second_level_label' => $func->show_string($res2->doctypes_second_level_label, true),
				    	'doctypes_second_level_class' => $dslStyle,
				    	'doctypes' => $doctypes,
				    )
				);
			}
			//$func->show_array($sLevel);
			if (isset($res1->doctype_first_level_style) && !empty($res1->doctype_first_level_style)) {
                    $dflStyle = $res1->doctype_first_level_style;
            } else {
                    $dflStyle = 'default_style';
            }
			array_push(
			    $folderLevels,
			    array(
			    	'doctypes_first_level_id' => $res1->doctypes_first_level_id,
			    	'doctypes_first_level_label' => $func->show_string($res1->doctypes_first_level_label, true),
			    	'doctypes_first_level_class' => $dflStyle,
			    	'second_level' => $sLevel
			    )
			);
		}
		array_push(
		    $searchCustomerResults,
		    array(
		    	'folder_id' => $res->folder_id,
		    	'folder_name' => $res->folder_name,
		    	'folder_subject' => $res->subject,
		    	'content' => $folderLevels,
		    )
		);
	}
}
$level = "";
$id = 'FolderTree';
$label = 'Consultation dossier';
try {
    $resStr = "{'treeId' : '" . $id . "', 'treeLabel' : '"
         . $label . "', 'img.path':'"
         . $_SESSION['config']['businessappurl'] . "tools/MaarchJS/src/img/', "
         . "'initial_structure' : [";
    for ($i = 0; $i < count($searchCustomerResults); $i ++) {
        $resStr .= "{'id' : 'folder::" . $searchCustomerResults[$i]['folder_id']
                . "', 'label' :'<b>" . addslashes(
                    $searchCustomerResults[$i]['folder_id']
                ) . "</b> <small>(" . addslashes($searchCustomerResults[$i]['folder_name'])
                . ")</small>', 'toolTip' : '"
                 . $searchCustomerResults[$i]['folder_id']
                 . "', 'classes' : ['default_style'], 'open' : true, children: [";
        for ($j = 0; $j < count($searchCustomerResults[$i]['content']); $j ++) {
            $resStr .= "{'id' : 'folder::" . $searchCustomerResults[$i]['folder_id']
                . "::dfl::" . addslashes(
                $searchCustomerResults[$i]['content'][$j]['doctypes_first_level_id']
                ) . "', 'label' :'" . addslashes(
                $searchCustomerResults[$i]['content'][$j]['doctypes_first_level_label']
                ) . "', 'toolTip' : '"
                . addslashes(
                $searchCustomerResults[$i]['content'][$j]['doctypes_first_level_label']
                ) . " (" . $searchCustomerResults[$i]['content'][$j]['doctypes_first_level_id']
                . ")', 'classes' : ['" .  $searchCustomerResults[$i]['content'][$j]['doctypes_first_level_class']
                . "'], 'open' : true, children : [";
            for ($k = 0; $k < count(
                $searchCustomerResults[$i]['content'][$j]['second_level']
            ); $k ++
            ) {
                $resStr .= "{'id' : 'folder::" . $searchCustomerResults[$i]['folder_id']
                . "::dfl::" . addslashes(
                $searchCustomerResults[$i]['content'][$j]['doctypes_first_level_id']
                ) . "::dsl::" . addslashes(
                    $searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes_second_level_id']
                    ) . "', 'label' :'" . addslashes(
                    $searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes_second_level_label']
                    ) . "', 'toolTip' : '"
                    . addslashes(
                    $searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes_second_level_label']
                    ) . " (" .  $searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes_second_level_id']
                    . ")', 'classes' : ['" .$searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes_second_level_class']
               		. "'], 'open' : true, children : [";
                for ($l = 0; $l < count(
                    $searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes']
                ); $l ++
                ) {

					    $resStr .= "{'id' : 'folder::" . $searchCustomerResults[$i]['folder_id']
                . "::dfl::" . addslashes(
                $searchCustomerResults[$i]['content'][$j]['doctypes_first_level_id']
                ) . "::dsl::" . addslashes(
                    $searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes_second_level_id']
                    ) . "::type::" . addslashes($searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['type_id'])
					        . "', 'label' :'" . addslashes(
                            $searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['description']
                            ) . "', 'toolTip' : '"
                            . addslashes(
                            $searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['description']
                            ) . " (" .$searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['type_id']
                            . ")', 'classes' : ['doctypes'],";

					$resStr .= 'children : [';
                	for ($m = 0; $m < count(
                		$searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results']
                	); $m ++
                	) {
                		 $resStr .= "{'id' : 'resid::" . addslashes($searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['res_id'])
					        . "', 'label' :'" . addslashes(
                            	$searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['subject']
                            ) . "', 'toolTip' : '"
                            . addslashes(
                            $searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['subject']
                            ) . " (" . $searchCustomerResults[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['results'][$m]['res_id']
                            . ")', 'classes' : ['documents']},";

                	}
					$resStr .= ']},';
                }
                $resStr .= "]},";
            }
            $resStr .= "]},";
        }
        $resStr .= "]},";
    }

    $resStr  = preg_replace("/]},]/", ']}]', $resStr);
    $resStr  = preg_replace("/,]/", ']', $resStr);
    $resStr  = preg_replace("/,$/", '', $resStr);

} catch (Exception $e) {
    echo "Impossible to get object id=$id // ";
}

$resStr  .= ']}';
header('Content-type: application/json');
echo $resStr;
