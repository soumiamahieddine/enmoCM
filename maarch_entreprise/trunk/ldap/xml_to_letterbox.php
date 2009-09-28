<?php
$debug_group=false;
$debug_service=false;
$debug_user=false;
$debug_user_group=false;
$debug_basket=false;
$debug_security = false;
$debug_redirect_service = false;
$debug_redirect_group = false;
$debug_admin_group = false;

//Baskets affectées par la redirection de group et de service
$group_basket_update = array(	"LateMailBasket" => "mail_process",
								"MyBasket" => "mail_process");
								
//Baskets qui ne tiennent pas en compte de la rediraction
$group_basket = array("CopyMailBasket" => "copy_mail",
					  "DepartmentBasket" => "auth_dep");



//WEB EXECUTION
if(!isset($argv))
{
	if(!( isset($_GET['conf']) && isset($_GET['infile']) ))
		exit("<p><b>Erreur de Syntaxe !</b><br>La syntaxe est 
		".$_SERVER['REQUEST_URI']."?conf=".htmlentities("<fichier de conf xml>")."&infile=".htmlentities("<fichier d'import xml>")."<p>");
	
	else
	{
		$ldap_conf_file = trim($_GET['conf']);
		$in_xml_file = trim($_GET['infile']);
	}
}
//CLI EXECUTION	
else
{
	if(!(count($argv) > 2  ))
		exit("Erreur de Syntaxe !\nLa syntaxe est $argv[0] <fichier de conf xml> <fichier d\'import xml>");
	else
	{
		$ldap_conf_file = trim($argv[1]);
		$in_xml_file = trim($argv[2]);
	}
}

//PHP File to include
$include_file = array("class_log.php","../class_functions.php","../class_db.php");
foreach($include_file as $if)
{
	if ( !@include_once($if))
	{
		if(!isset($argv))
			exit("<p><b>Erreur:</b><br>Unable to load ".$if."</p>");
		
		else
			exit("Unable to load ".$if."\n");
	}
}
 
//Create The Log
try
{
	$log = new log('log.xml','root');
} 
catch(Exception $e){ exit($e->getMessage()."\n"); }

$log->start(); 


//Looking for the config.xml
if(!@DOMDocument::load("../xml/config.xml"))
{
	$log->add_fatal_error("Unable to load the config.xml file");
	exit;
}	
else
	$config_xml = DOMDocument::load("../xml/config.xml");


//**********************************//
//			LOAD XML INFILE	    	//
//**********************************//

$in_xml = new DomDocument();

try
{
	$in_xml->load($in_xml_file);
}
catch(Exception $e)
{ 
	$log->add_fatal_error("Impossible de charger le document : ".$in_xml_file." Erreur : ".$e.getMessage);
	exit;
}

$xp_in_xml = new domxpath($in_xml);


$old_in_xml = new DomDocument();

try
{
	@$old_in_xml->load("old_".$in_xml_file);
}
catch(Exception $e){}

$old_xp_in_xml = new domxpath($old_in_xml);


//**********************************//
//				LOAD CONF  		    //
//**********************************//

//Extraction du fichier de conf
$ldap_conf = new DomDocument();
try
{
	$ldap_conf->load($ldap_conf_file);
}
catch(Exception $e)
{ 
	$log->add_fatal_error("Impossible de charger le document : ".$ldap_conf_file." Erreur : ".$e.getMessage);
	exit;
}

$xp_ldap_conf = new domxpath($ldap_conf);

foreach($xp_ldap_conf->query("/root/config/*") as $cf)
	${$cf->nodeName} = $cf->nodeValue;

//Extraction du fichier de conf de la dernière execution
$old_ldap_conf = new DomDocument();
try
{
	@$old_ldap_conf->load(dirname($ldap_conf_file)."/old_".basename($ldap_conf_file));
}
catch(Exception $e){}
						  
$old_xp_ldap_conf = new domxpath($old_ldap_conf);
$old_lost_users = $xp_ldap_conf->query("/root/config/lost_users")->item(0)->nodeValue;


///**********************************//
//	     	DATABASE CONNECTION  	//
//**********************************//

//Database Session Var connection for the class_db
$_SESSION['config']['databaseserver'] =  $config_xml->getElementsByTagName("databaseserver")->item(0)->nodeValue;
$_SESSION['config']['databaseuser'] = $config_xml->getElementsByTagName("databaseuser")->item(0)->nodeValue;
$_SESSION['config']['databasepassword'] = $config_xml->getElementsByTagName("databasepassword")->item(0)->nodeValue;
$_SESSION['config']['databasename'] = $config_xml->getElementsByTagName("databasename")->item(0)->nodeValue;
$_SESSION['config']['force_client_utf8'] = $config_xml->getElementsByTagName("force_client_utf8")->item(0)->nodeValue;

$db = new dbquery();
$db->connect();

//**********************************//
//				MAPPING	         	//
//**********************************//

//User
foreach( $xp_ldap_conf->query("/root/mapping/user/@* | /root/mapping/user/* | /root/mapping/user/*/@*")  as $us)
	if( !empty($us->nodeValue) && ( trim($us->nodeValue) != "") )
		$xml_user_fields[] = $us->nodeName;

//Group
foreach( $xp_ldap_conf->query("/root/mapping/group/@* | /root/mapping/group/* | /root/mapping/group/*/@*")  as $gs)
	if( !empty($gs->nodeValue) && ( trim($gs->nodeValue) != "") )
		$xml_group_fields[] = $gs->nodeName;
		

//**********************************//
//			EXT_REFERENCES      	//
//**********************************//

//Cree la table ext_references si elle n'existe pas
$db->query("CREATE TABLE IF NOT EXISTS `ext_references` (
  `reference_id` varchar(32) character set utf8 NOT NULL,
  `type` varchar(32) character set utf8 NOT NULL,
  `field` varchar(32) character set utf8 NOT NULL,
  `value` varchar(32) character set utf8 NOT NULL,
  PRIMARY KEY  (`reference_id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

	
//**********************************//
//			GROUPS  UPDATE   		//
//**********************************//

//Prepare les champs pour l'update ou l'insert
//On enleve le champ group_id qui est traité par un increment ici
$db->query("SHOW COLUMNS FROM usergroups");
while($field = $db->fetch_object())
	if($field->Field != "GROUP_ID")
		$lb_groups_fields[] = $field->Field;
	
$update_groups_fields = array_values(array_uintersect($xml_group_fields,$lb_groups_fields,"strcasecmp"));

$xml_groups_id = array();
foreach($xp_in_xml->query("//group/@ext_id") as $group_id)
	$xml_groups_id[] = $group_id->nodeValue;
	
$old_xml_groups_id = array();
foreach($old_xp_in_xml->query("//group/@ext_id") as $old_group_id)
	$old_xml_groups_id[] = $old_group_id->nodeValue;

//On supprimer les doublons des groupes
$xml_groups_id = array_unique($xml_groups_id);
$old_xml_groups_id = array_unique($old_xml_groups_id);


//INSERT GROUPS
$insert_groups = array_values(array_diff($xml_groups_id,$old_xml_groups_id));
foreach($insert_groups as $ig)
{
	$db->query("SELECT group_id FROM usergroups WHERE group_id IN
				(SELECT value FROM ext_references 
				WHERE reference_id = '".$ig."'
				AND field = 'group_id'
				AND type = '".$type_ldap."')");

	if($group_id = $db->fetch_object()->group_id)
	{
		//Le groupe exise deja : on le supprime dans usergroups, on le maj dans ext_reference
		$db->query("DELETE FROM usergroups WHERE group_id = '".$group_id."'");
		if($debug_group) echo "DELETE FROM usergroups WHERE user_id = '".$group_id."'<br>";
	}
	else
	{
		//Il n'existe pas : on l'insert dans ext_reference
		
		//On insert un group_id = <group_prefix_ldap>{numero}
		
		$db->query("SELECT MAX(CAST(SUBSTRING(value,CHAR_LENGTH('".$group_prefix_ldap."')+1) as UNSIGNED )) as max_group_id
					FROM ext_references 
					WHERE field = 'group_id'
					AND type = '".$type_ldap."'");
		
		$max_group_id = $db->fetch_object()->max_group_id;
		
		if(!isset($max_group_id))
			$max_group_id = 0;
			
		$group_id = $group_prefix_ldap.($max_group_id + 1);
		
		$db->query("INSERT IGNORE INTO ext_references (reference_id,field,value,type)
					VALUES ('".$ig."','group_id','".$group_id."','".$type_ldap."')");
		if($debug_group) 
			echo("INSERT IGNORE INTO ext_references (reference_id,field,value,type)
			VALUES ('".$ig."','group_id','".$group_id."','".$type_ldap."')<br>");
		
	}
		
	$sql_insert = "INSERT IGNORE INTO usergroups ( group_id, ".implode(",",$update_groups_fields)." ) VALUES ('".$group_id."','";
	
	foreach($update_groups_fields as $ugf)
	{
		$sql_insert .= addslashes($xp_in_xml->query("//group[@ext_id=\"".$ig."\"]/".$ugf)->item(0)->nodeValue)."','";
	}
	
	$sql_insert = substr($sql_insert,0,-2).")";
	$db->query($sql_insert);
	if($debug_group) echo $sql_insert."<br>";
	unset($sql_insert);
	
}

//DELETE GROUPS
$delete_groups = array_values(array_diff($old_xml_groups_id,$xml_groups_id));
foreach($delete_groups as $dg)
{
	//Maj enabled N
	$sql_disabled = "UPDATE IGNORE usergroups SET enabled = 'N' WHERE group_id IN
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($dg)."'
					AND field = 'group_id'
					AND type = '".$type_ldap."')";
	
	$db->query($sql_disabled);
	if($debug_group) echo $sql_disabled."<br>";
	unset($sql_disabled);
}

//UPDATE GROUPS
$update_groups = array_values(array_intersect($xml_groups_id,$old_xml_groups_id));

foreach($update_groups as $ug)
{
	//Maj de group
	$sql_update = "UPDATE usergroups SET Enabled = 'Y', ";
	
	foreach($update_groups_fields as $ugf)
	{
		$sql_update .= $ugf." = '".addslashes($xp_in_xml->query("//group[@ext_id=\"".$ug."\"]/".$ugf)->item(0)->nodeValue)."', ";
	}
	
	$sql_update = substr($sql_update,0,-2)." WHERE group_id IN 
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($ug)."'
					AND field = 'group_id'
					AND type = '".$type_ldap."')";
	
	$db->query($sql_update);
	if($debug_group) echo $sql_update."<br>";
	unset($sql_update);
}

//**********************************//
//			UPDATE SERVICES		    //
//**********************************//
//Les services sont identiques au groupes sauf que l'on importe pas les groupes de type "rights"
$update_services_fields = array("group_desc" => "SERVICE");

$xml_services_id = array();
foreach($xp_in_xml->query("//group[@type != \"rights\"]/@ext_id") as $service_id)
	$xml_services_id[] = $service_id->nodeValue;
	
$old_xml_services_id = array();
foreach($old_xp_in_xml->query("//group[@type != \"rights\"]/@ext_id") as $old_service_id)
	$old_xml_services_id[] = $old_service_id->nodeValue;

//On supprimer les doublons des services
$xml_services_id = array_unique($xml_services_id);
$old_xml_services_id = array_unique($old_xml_services_id);


//INSERT SERVICES
$insert_services = array_values(array_diff($xml_services_id,$old_xml_services_id));
foreach($insert_services as $is)
{
	$db->query("SELECT id FROM services WHERE id IN
				(SELECT value FROM ext_references 
				WHERE reference_id = '".$ig."'
				AND field = 'group_id'
				AND type = '".$type_ldap."')");

	if($service_id = $db->fetch_object()->id)
	{
		//Le service existe deja : on le supprime dans services
		$db->query("DELETE FROM services WHERE id = '".$service_id."'");
		if($debug_service) echo "DELETE FROM services WHERE id = '".$service_id."'<br>";
	}
	else
	{
		//On recupere le group_id du group qui correspond
		$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".$is."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
		$service_id = $db->fetch_object()->value;
	}
	
	$sql_insert = "INSERT IGNORE INTO services ( id, ".implode(",",$update_services_fields)." ) VALUES ('".$service_id."','";
	
	foreach($update_services_fields as $k_usf => $d_usf)
	{
		$sql_insert .= addslashes($xp_in_xml->query("//group[@ext_id=\"".$is."\"]/".$k_usf)->item(0)->nodeValue)."','";
	}
	
	$sql_insert = substr($sql_insert,0,-2).")";
	$db->query($sql_insert);
	if($debug_service) echo $sql_insert."<br>";
	unset($sql_insert);
	
}

//DELETE SERVICES
$delete_services = array_values((array_diff($old_xml_services_id,$xml_services_id)));
foreach($delete_services as $ds)
{
	//Maj enabled N
	$sql_disabled = "UPDATE IGNORE services SET enabled = 'N' WHERE id IN
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($ds)."'
					AND field = 'group_id'
					AND type = '".$type_ldap."')";
	
	$db->query($sql_disabled);
	if($debug_service) echo $sql_disabled."<br>";
	unset($sql_disabled);
}

//UPDATE SERVICES
$update_services = array_values(array_intersect($xml_services_id,$old_xml_services_id));

foreach($update_services as $us)
{
	//Maj de service
	$sql_update = "UPDATE services SET ENABLED = 'Y', ";
	
	foreach($update_services_fields as $k_usf => $d_usf)
	{
		$sql_update .= $d_usf." = '".addslashes($xp_in_xml->query("//group[@ext_id=\"".$us."\"]/".$k_usf)->item(0)->nodeValue)."', ";
	}
	
	$sql_update = substr($sql_update,0,-2)." WHERE id IN 
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($us)."'
					AND field = 'group_id'
					AND type = '".$type_ldap."')";
	
	$db->query($sql_update);
	if($debug_service) echo $sql_update."<br>";
	unset($sql_update);
}

//**********************************//
//			USERS UPDATE 		    //
//**********************************//

//Prepare les champs pour l'update ou l'insert dans users
$db->query("SHOW COLUMNS FROM users");
while($field = $db->fetch_object())
	$lb_users_fields[] = $field->Field;

$update_users_fields = array_values(array_uintersect($xml_user_fields,$lb_users_fields,"strcasecmp"));
if( $pass_is_login == 'true' )
	$update_users_fields[] = 'password';

	
//On importe tous les users
if($lost_users == "true")
{
	$xml_users_id = array();
	foreach($xp_in_xml->query("//user/@ext_id") as $user_id)
		$xml_users_id[] = $user_id->nodeValue;
}

//On importe que les users qui sont membres d'un groupe
else
{
	$xml_users_id = array();
	foreach($xp_in_xml->query("//user[memberof]/@ext_id") as $user_id)
		$xml_users_id[] = $user_id->nodeValue;
}

//IDEM pour l'execution precedente
if(isset($old_lost_users) && $old_lost_users == "true")
{
	$old_xml_users_id = array();
	foreach($old_xp_in_xml->query("//user/@ext_id") as $old_user_id)
		$old_xml_users_id[] = $old_user_id->nodeValue;
}

else
{
	$old_xml_users_id = array();
	foreach($old_xp_in_xml->query("//user[memberof]/@ext_id") as $old_user_id)
		$old_xml_users_id[] = $old_user_id->nodeValue;
}
	

//INSERT USERS
$insert_users = array_values(array_diff($xml_users_id,$old_xml_users_id));
foreach($insert_users as $iu)
{
	$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".$iu."'
				AND field = 'user_id'
				AND type = '".$type_ldap."'");

	if($value = $db->fetch_object()->value)
	{
		//L'utilisateur existait deja : on le supprime avant de l'inserer
		$db->query("DELETE FROM users WHERE user_id = '".$value."'");
		if($debug_user) echo "DELETE FROM users WHERE user_id = '".$value."'<br>";
		
		$db->query("DELETE FROM ext_references 
					WHERE reference_id = '".$iu."'
					AND field = 'user_id'
					AND type = '".$type_ldap."'");
		if($debug_user) 
			echo ("DELETE FROM ext_references 
					WHERE reference_id = '".$iu."'
					AND field = 'user_id'
					AND type = '".$type_ldap."'").'<br>';
	}
		
	//Il n'existe pas : on l'insert dans ext_reference
	$db->query("INSERT IGNORE INTO ext_references (reference_id,field,value,type)
				VALUES ('".$iu."','user_id','".$xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue."','".$type_ldap."')");
	if($debug_user) 
		echo("INSERT IGNORE INTO ext_references (reference_id,field,value,type)
		VALUES ('".$iu."','user_id','".$xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue."','".$type_ldap."')<br>");
	
	$sql_insert = "INSERT IGNORE INTO users ( change_password ,".implode(",",$update_users_fields)." ) VALUES ('NO','";
	
	foreach($update_users_fields as $uuf)
	{
		if($uuf == 'password')
			$sql_insert .= md5($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue)."','";
		else
			$sql_insert .= addslashes($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/".$uuf)->item(0)->nodeValue)."','";
	}
	
	$sql_insert = substr($sql_insert,0,-2).")";
	$db->query($sql_insert);
	if($debug_user) 
		echo $sql_insert."<br>";
	unset($sql_insert);
	
}

//DELETE USERS
$delete_users = array_values(array_diff($old_xml_users_id,$xml_users_id));
foreach($delete_users as $du)
{
	//Maj status DEL
	$sql_disabled = "UPDATE IGNORE users SET status = 'DEL' WHERE user_id IN
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($du)."'
					AND field = 'user_id'
					AND type = '".$type_ldap."')";
	
	$db->query($sql_disabled);
	if($debug_user) 
		echo $sql_disabled."<br>";
	unset($sql_disabled);
}

//UPDATE USERS
$update_users = array_values(array_intersect($xml_users_id,$old_xml_users_id));

foreach($update_users as $uu)
{
	//Maj de user
	
	$sql_update = "UPDATE IGNORE users SET status = 'OK', ";
	
	foreach($update_users_fields as $uuf)
	{
		if($uuf == 'password')
			$sql_update .= "password = '".md5($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."', ";
		else
			$sql_update .= $uuf." = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/".$uuf)->item(0)->nodeValue)."', ";
	}
	
	$sql_update = substr($sql_update,0,-2)." WHERE user_id IN 
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($uu)."'
					AND field = 'user_id'
					AND type = '".$type_ldap."')";
	
	$db->query($sql_update);
	if($debug_user)
		echo $sql_update."<br>";
	unset($sql_update);
	
	//Maj de ext_reference
	$db->query("UPDATE IGNORE ext_references
				SET value = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
				WHERE reference_id = '".addslashes($uu)."'
				AND field = 'user_id'
				AND type = '".$type_ldap."'");
}

//**********************************//
// GROUPS / SERVICES USERS LINKS   	//
//**********************************//

//Memorisation des resultats des algos pour augmenter les performances
$mem_group_up = array();
$mem_group_down = array();

function group_up($level,$xpath_xml,$ext_id)
{
	global $mem_group_up;
	
	$xml_uri = $xpath_xml->document->documentURI;
	
	//Si deja caculé alors on retourne le resultat
	if(isset($mem_group_up[$xml_uri][$ext_id][$level]))
		return $mem_group_up[$xml_uri][$ext_id][$level];
		
	$group_ext_id = array();
	$current_nodes = $xpath_xml->query("//group[@ext_id =\"".$ext_id."\"][@type=\"organization\"]");
	
	//Quelque soit le groupe selectionné dans l'arbre, il est membre des mêmes groupes
	$current_node = $current_nodes->item(0);
	
	if($level == 0)
	{
		$group_ext_id = array($ext_id);
	}	
	else
	{
		$find_nodes = false;
		
		//Monte dans l'arbre
		foreach($xpath_xml->query("memberof[1]/group[@type=\"organization\"]",$current_node) as $this_group)
		{
			$find_nodes = true;
		
			if($this_group->nodeName == "group")
			{
				$group_ext_id = array_merge(array($ext_id),array_merge($group_ext_id,group_up(($level - 1),$xpath_xml,$this_group->getAttribute("ext_id"))));
			}
			else
			{
				$group_ext_id = array_merge(array($ext_id),$group_ext_id);
			}
		}
		
		if(!$find_nodes)
			$group_ext_id = array_merge(array($ext_id),$group_ext_id);
	}
	
	//Stocke le resultat pour optimisation resultat
		$mem_group_up[$xml_uri][$ext_id][$level] = array_values(array_unique($group_ext_id));
	
	return $mem_group_up[$xml_uri][$ext_id][$level];
}

function group_down($level,$xpath_xml,$ext_id)
{
	global $mem_group_down;

	$xml_uri = $xpath_xml->document->documentURI;
	
	//Si deja caculé alors on retourne le resultat
	if(isset($mem_group_down[$xml_uri][$ext_id][$level]))
		return $mem_group_down[$xml_uri][$ext_id][$level];
		
	$group_ext_id = array();
	//Les groupes selectionnés ne sont n'ont pas tous pour membres les mêmes groupes et les mêmes users
	$current_nodes = $xpath_xml->query("//group[@ext_id =\"".$ext_id."\"][@type=\"organization\"]");
	
	if($level == 0)
	{
		$group_ext_id = array($ext_id);
	}
	
	else
	{
		$find_nodes = false;
	
		//Descend dans l'arbre
		foreach($current_nodes as $current_node)
		{
		
			$find_nodes = true;
			$this_group = $xpath_xml->query("parent::memberof/parent::group[@type=\"organization\"]",$current_node)->item(0);
			if($this_group->nodeName == "group")
				$group_ext_id = array_merge(array($this_group->getAttribute("ext_id")),array_merge($group_ext_id,group_down(($level - 1),$xpath_xml,$this_group->getAttribute("ext_id"))));
		}
	
		if(!$find_nodes)
			$group_ext_id = array_merge(array($ext_id),$group_ext_id);
	}
	
	//Stocke le resultat pour optimisation algo
		$mem_group_down[$xml_uri][$ext_id][$level] = array_values(array_unique($group_ext_id));
		
	return array_values(array_unique($group_ext_id));
}

function group_brothers($xpath_xml,$ext_id)
{
	$group_ext_id = array();

	foreach(group_up(1,$xpath_xml,$ext_id) as $parent)
	{
		$group_ext_id = array_diff(array_merge($group_ext_id,group_down(1,$xpath_xml,$parent)),$parent);
	}
	
	return array_values(array_unique($group_ext_id));
}

//Prepare les champs pour l'update ou l'insert dans usergroup_content
$db->query("SHOW COLUMNS FROM usergroup_content");
while($field = $db->fetch_object())
	$lb_usergroup_content_fields[] = $field->Field;

$update_usergroup_content_fields = array_values(array_uintersect($xml_user_fields,$lb_usergroup_content_fields,"strcasecmp"));

//On importe tous les users
if($lost_users == "true")
{
	$xml_users_id = array();
	foreach($xp_in_xml->query("//user/@ext_id") as $user_id)
		$xml_users_id[] = $user_id->nodeValue;
}

//On importe que les users qui sont membres d'un groupe
else
{
	$xml_users_id = array();
	foreach($xp_in_xml->query("//user[memberof]/@ext_id") as $user_id)
		$xml_users_id[] = $user_id->nodeValue;
}

//IDEM pour l'execution precedente
if(isset($old_lost_users) && $old_lost_users == "true")
{
	$old_xml_users_id = array();
	foreach($old_xp_in_xml->query("//user/@ext_id") as $old_user_id)
		$old_xml_users_id[] = $old_user_id->nodeValue;
}

else
{
	$old_xml_users_id = array();
	foreach($old_xp_in_xml->query("//user[memberof]/@ext_id") as $old_user_id)
		$old_xml_users_id[] = $old_user_id->nodeValue;
}
	
//**********************************//
//  			NEW USERS			//
//**********************************//

$insert_users = array_values(array_diff($xml_users_id,$old_xml_users_id));
foreach($insert_users as $iu)
{
	//Les groupes de type "organization" de premier niveau rencontrés en remontant dans l'arbre
	$primary_groups_group_id = array();
	foreach($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/memberof[1]/group[@type =\"organization\"]/@ext_id") as $node_ext_id)
	{
		$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($node_ext_id->nodeValue)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
		$primary_groups_group_id[] = $db->fetch_object()->value;
	}
	
	//Les groupes de type "rights"
	$group_rights_group_id = array();
	foreach($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]//group[@type=\"rights\"]/@ext_id") as $group_rights_ext_id)
	{
		$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($group_rights_ext_id->nodeValue)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
		$group_rights_group_id[] = $db->fetch_object()->value;
	}
	
	//INSERT USER / ORGA GROUP(S) LINK(S)
	foreach($primary_groups_group_id as $pggi )
	{
		$sql_insert_usergroup_content = "INSERT IGNORE INTO usergroup_content (group_id, Primary_group, ".implode(",",$update_usergroup_content_fields)." )
										VALUES ('".addslashes($pggi)."','N','";
		
		foreach($update_usergroup_content_fields as $uugf)
		{
			$sql_insert_usergroup_content .= addslashes($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/".$uugf)->item(0)->nodeValue)."','";
		}
		
		$sql_insert_usergroup_content = substr($sql_insert_usergroup_content,0,-2).")";
		$db->query($sql_insert_usergroup_content);
		if($debug_user_group) 
			echo $sql_insert_usergroup_content."<br>";
		unset($sql_insert_usergroup_content);
	}
	
	//INSERT USER / RIGHTS GROUP(S) LINK(S)
	foreach($group_rights_group_id as $grgi)
	{
		$sql_insert_usergroup_content = "INSERT IGNORE INTO usergroup_content (group_id, Primary_group, ".implode(",",$update_usergroup_content_fields)." )
										VALUES ('".addslashes($grgi)."','N','";
		
		foreach($update_usergroup_content_fields as $uugf)
		{
			$sql_insert_usergroup_content .= addslashes($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/".$uugf)->item(0)->nodeValue)."','";
		}
		
		$sql_insert_usergroup_content = substr($sql_insert_usergroup_content,0,-2).")";
		$db->query($sql_insert_usergroup_content);
		if($debug_user_group) 
			echo $sql_insert_usergroup_content."<br>";
		unset($sql_insert_usergroup_content);
	}
	
	//INSERT PRIMARY GROUP
	if(isset($primary_groups_group_id[0]))
	{
		$sql_insert_service = 
		"UPDATE usergroup_content SET PRIMARY_GROUP = 'Y'
		WHERE user_id ='".addslashes($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue)."' 
		AND group_id = '".$primary_groups_group_id[0]."'";
	
		$db->query($sql_insert_service);
		if($debug_user_group) 
			echo $sql_insert_service."<br>";
	}
	
	//INSERT USER SERVICE
	if(isset($primary_groups_group_id[0]))
	{
		$sql_insert_service = 
		"UPDATE users SET department = '".$primary_groups_group_id[0]."'
		WHERE user_id ='".addslashes($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue)."' ";
	
		$db->query($sql_insert_service);
		if($debug_user_group) 
			echo $sql_insert_service."<br>";
	}	
}

//**********************************//
//  		UPDATE USERS			//
//**********************************//

$update_users = array_values(array_intersect($xml_users_id,$old_xml_users_id));
foreach($update_users as $uu)
{
	//**********************************//
	//GROUPS TYPE ORGANIZATION / RIGHTS //
	//**********************************//

	//UPDATE USER / GROUP(S) LINK(S)
	//On compare la liste des groupes de premier niveau à celle du xml de l'execution precedente
	
	$group_level_one = array();
	foreach($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/memberof[1]/group[@type =\"organization\"]/@ext_id") as $glo)
		$group_level_one[] = $glo->nodeValue;
	
	$old_group_level_one = array();
	foreach($old_xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/memberof[1]/group[@type =\"organization\"]/@ext_id") as $oglo)
		$old_group_level_one[] = $oglo->nodeValue;
		
	$group_rights = array();
	foreach($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]//group[@type=\"rights\"]/@ext_id") as $gr)
		$group_rights[] = $gr->nodeValue;
	
	$old_group_rights = array();
	foreach($old_xp_in_xml->query("//user[@ext_id=\"".$uu."\"]//group[@type=\"rights\"]/@ext_id") as $ogr)
		$old_group_rights[] = $ogr->nodeValue;
		
	//INSERT GROUPS ORGANIZATION AND RIGHTS
	$user_group_link_insert = array_values(array_merge(array_diff($group_level_one,$old_group_level_one),array_diff($group_rights,$old_group_rights)));
	foreach($user_group_link_insert as $ugli)
	{
		$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($ugli->nodeValue)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
		$ugli_group_id = $db->fetch_object()->value;
	
		$sql_insert_usergroup_content = "INSERT IGNORE INTO usergroup_content (group_id, ".implode(",",$update_usergroup_content_fields)." )
										VALUES ('".addslashes($ugli_group_id)."','";
		
		foreach($update_usergroup_content_fields as $uugf)
		{
			if($uugf == 'user_id')
				$sql_insert_usergroup_content .= addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."','";
			else
				$sql_insert_usergroup_content .= addslashes($xp_in_xml->query("//user[@ext_id=\"".$ugli->nodeValue."\"]/".$uugf)->item(0)->nodeValue)."','";
		}
		
		$sql_insert_usergroup_content = substr($sql_insert_usergroup_content,0,-2).")";
		$db->query($sql_insert_usergroup_content);
		if($debug_user_group) 
			echo $sql_insert_usergroup_content."<br>";
		unset($sql_insert_usergroup_content);
	}
	
	//DELETE GROUPS ORGANIZATION AND RIGHTS
	$user_group_link_delete = array_values(array_merge(array_diff($old_group_level_one,$group_level_one),array_diff($old_group_rights,$group_rights)));
		
	foreach($user_group_link_delete as $ugld)
	{
		$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($ugld)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
		$ugld_group_id = $db->fetch_object()->value;
		
		$sql_delete_usergroup_content = "DELETE FROM usergroup_content 
										WHERE user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
										AND group_id = '".addslashes($ugld_group_id)."' ";
		if($debug_user_group) 
			echo $sql_delete_usergroup_content."<br>";
		unset($sql_delete_usergroup_content);
	}
	
	//UPDATE GROUPS ORGANIZATION AND RIGHTS
	//Update l'intersection
	$user_group_link_update = array_values(array_merge(array_intersect($old_group_level_one,$group_level_one),array_intersect($old_group_rights,$group_rights)));
	
	foreach($user_group_link_update as $uglu)
	{
		$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($uglu)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
		$uglu_group_id = $db->fetch_object()->value;
		
		$sql_update_usergroup_content = "UPDATE IGNORE usergroup_content SET group_id ='".$uglu_group_id."',";

		foreach($update_usergroup_content_fields as $uugf)
		{
			if($uugf == 'user_id')
				$sql_update_usergroup_content .= $uugf." = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."',";
			else
				$sql_update_usergroup_content .= $uugf." = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uglu."\"]/".$uugf)->item(0)->nodeValue)."',";
		}
		
		$sql_update_usergroup_content = substr($sql_update_usergroup_content,0,-1);
		$db->query($sql_update_usergroup_content);
		if($debug_user_group) 
			echo $sql_update_usergroup_content."<br>";
		unset($sql_update_usergroup_content);
	}
	
	//Impossible de ne pas avoir de groupe primaire dans letterbox ni de service
	
	/*
	//UPDATE PRIMARY GROUP
	
	$db->query("SELECT group_id FROM usergroup_content 
				WHERE user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
				AND Primary_group = 'Y'");
				
	if(!$db->fetch_object()->group_id)
	{
		$db->query("SELECT group_id FROM usergroup_content
					WHERE user_id ='".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
					AND group_id IN
					(SELECT value FROM ext_references 
					WHERE reference_id = '".$group_level_one[0]."'
					AND field = 'group_id'
					AND type = '".$type_ldap."')");
				
		$primary_group_id = $db->fetch_object()->group_id;
		
		if(!empty($primary_group_id))
		{
			$sql_update_usergroup_content = "UPDATE usergroup_content SET Primary_group ='Y' 
			WHERE user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."' 
			AND group_id = '".$primary_group_id.'"';
			
			$db->query($sql_update_usergroup_content);
			if($debug_user_group) 
				echo $sql_update_usergroup_content."<br>";
			unset($sql_update_usergroup_content);
		}
	}
	
	//UPDATE SERVICE
	$db->query("SELECT department FROM users
				WHERE user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'");
				
	if(empty($db->fetch_object()->department))
	{
		$db->query("SELECT group_id FROM usergroup_content
					WHERE user_id ='".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
					AND group_id IN
					(SELECT value FROM ext_references 
					WHERE reference_id = '".$group_level_one[0]."'
					AND field = 'group_id'
					AND type = '".$type_ldap."')");
				
		$primary_group_id = $db->fetch_object()->group_id;
		
		if(!empty($primary_group_id))
		{
			$sql_update_usergroup_content = "UPDATE users SET group_id = '".$primary_group_id."' 
			WHERE user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'";
			
			$db->query($sql_update_usergroup_content);
			if($debug_user) 
				echo $sql_update_users."<br>";
			unset($sql_update_users);
		}
	}
	*/	
}
//**********************************//
// 			BASKET INSERT			//
//**********************************//

//MyBasket
$db->query("INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
VALUES ('res_x','MyBasket','Mes courriers à traiter','Courriers à traiter','(status = ''NEW'' or status=''COU'') and DEST_USER = @user and is_folder = ''Y''','Y')");

if($debug_basket)
	echo "INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
	VALUES ('res_x','MyBasket','Mes courriers à traiter','Courriers à traiter','(status = ''NEW'' or status=''COU'') and DEST_USER = @user and is_folder = ''Y''','Y')<br>";

//LateMailBasket
$db->query("INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
VALUES ('res_x','LateMailBasket','Mes courriers en retard','Courriers en retards','(STATUS=''NEW'' or STATUS=''COU'') and DEST_USER = @user and now() > CUSTOM_D2 and is_folder = ''Y''','Y')");

if($debug_basket)
	echo "INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
	VALUES ('res_x','LateMailBasket','Mes courriers en retard','Courriers en retards','(STATUS=''NEW'' or STATUS=''COU'') and DEST_USER = @user and now() > CUSTOM_D2 and is_folder = ''Y''','Y')<br>";
	

//CopyMailBasket
$db->query("INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
	VALUES ('res_x','CopyMailBasket','Mes courriers en copie','Liste des courriers en copie','l.res_table = ''res_x'' and l.user_id = @user and l.res_id = r.res_id and l.sequence > 1 and ( r.status=''NEW'' or r.status=''COU'' or r.status=''WAI'') and is_folder = ''Y''', 'Y')");

if($debug_basket)
	echo "INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
	VALUES ('res_x','CopyMailBasket','Mes courriers en copie','Liste des courriers en copie','l.res_table = ''res_x'' and l.user_id = @user and l.res_id = r.res_id and l.sequence > 1 and ( r.status=''NEW'' or r.status=''COU'' or r.status=''WAI'') and is_folder = ''Y''', 'Y')<br>";
	

//DepartmentBasket
$db->query("INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
	VALUES ('res_x','DepartmentBasket','Services autorisés','Services autorisés','status <> ''DEL'' AND status <> ''REP'' and status <> ''VAL'' and status <> ''END'' and is_folder=''Y''','Y')");

if($debug_basket)
	echo "INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
	VALUES ('res_x','DepartmentBasket','Mes courriers en copie','Liste des courriers en copie','status <> ''DEL'' AND status <> ''REP'' and status <> ''VAL'' and status <> ''END'' and is_folder=''Y''','Y')<br>";
	

//OutMails
$db->query("INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
	VALUES ('res_x','OutMails', 'Mes Courriers sortant', 'Mes courriers sortant', 'IS_INGOING = ''N'' and AUTHOR = @user and is_folder = ''Y''','N')");

if($debug_basket)
	echo "INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
	VALUES ('res_x','OutMails', 'Mes Courriers sortant', 'Mes courriers sortant', 'IS_INGOING = ''N'' and AUTHOR = @user and is_folder = ''Y''','N')<br>";


//QualifBasket
$db->query("INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
	VALUES ('res_x','QualifBasket','Mes affaires à qualifier','Mes affaires à qualifier','(status = ''ATT'' or status =''RSV'') and destination = @my_entity ','N')");

if($debug_basket)
	echo "INSERT Ignore Into baskets (res_table,basket_id,basket_name,basket_desc,basket_clause,is_generic) 
	VALUES ('res_x','QualifBasket','Mes affaires à qualifier','Mes affaires à qualifier','(status = ''ATT'' or status =''RSV'') and destination = @my_entity ','N')<br>";	


//**********************************//
// GROUPBASKET / SECURITY UPDATE	//
//**********************************//

//GROUPS TYPE RIGHTS
$group_rights = array();
	foreach($xp_in_xml->query("//group[@type=\"rights\"]/@ext_id") as $gr)
		$group_rights[] = $gr->nodeValue;
$group_rights = array_values(array_unique($group_rights));
	
$old_group_rights = array();
	foreach($old_xp_in_xml->query("//group[@type=\"rights\"]/@ext_id") as $ogr)
		$old_group_rights[] = $ogr->nodeValue;
$old_group_rights = array_values(array_unique($old_group_rights));


$group_rights_insert = array_values(array_diff($group_rights,$old_group_rights));
//INSERT
foreach($group_rights_insert as $gri)
{
	//Security
	$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($gri)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
	
	$gri_id = $db->fetch_object()->value;
	
	$sql_security_insert = "INSERT IGNORE INTO security (group_id,res_table,where_clause) 
							VALUES ('".$gri_id."','res_x','(1=0)')";
	
	$db->query($sql_security_insert);
	if($debug_security)
		echo $sql_security_insert."<br>";
		
	//Groupbasket
	//Pas de redirection
}

$group_rights_delete = array_values(array_diff($old_group_rights,$group_rights));
//DELETE
foreach($group_rights_delete as $grd)
{
	$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($grd)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
	
	$grd_id = $db->fetch_object()->value;
	
	$sql_security_delete = "DELETE IGNORE FROM security WHERE group_id ='".$grd_id."'";
	$db->query($sql_security_delete);			
	if($debug_security)
		echo $sql_security_delete."<br>";
		
	//Groupbasket
	//Pas de redirection
}

$group_rights_update = array_values(array_intersect($group_rights,$old_group_rights));
//UPDATE
//foreach($group_rights_update as $gru)
{
	//On ne met rien à jour
}


//GROUPS TYPE ORGANIZATION
$dns = $xp_in_xml->query("//dns/dn/@id");

foreach($dns as $dn)
{
	//CONF XML Parameters
	foreach($xp_ldap_conf->query("//dn[@id=\"".$dn->nodeValue."\"]/security/*") as $s)
		$security[$s->nodeName] = $s->nodeValue;
		
	foreach($xp_ldap_conf->query("//dn[@id=\"".$dn->nodeValue."\"]/redirect_services/*") as $rs)
		$redirect_services[$rs->nodeName] = $rs->nodeValue;
		
	foreach($xp_ldap_conf->query("//dn[@id=\"".$dn->nodeValue."\"]/redirect_groups/*") as $rg)
		$redirect_groups[$rg->nodeName] = $rg->nodeValue;
		
	//CONF OLD XML Parameters
	foreach($old_xp_ldap_conf->query("//dn[@id=\"".$dn->nodeValue."\"]/security/*") as $s)
		$old_security[$s->nodeName] = $s->nodeValue;
		
	foreach($old_xp_ldap_conf->query("//dn[@id=\"".$dn->nodeValue."\"]/redirect_services/*") as $rs)
		$old_redirect_services[$rs->nodeName] = $rs->nodeValue;
		
	foreach($old_xp_ldap_conf->query("//dn[@id=\"".$dn->nodeValue."\"]/redirect_groups/*") as $rg)
		$old_redirect_groups[$rg->nodeName] = $rg->nodeValue;
		
	//GROUPS IN DN
	$group_orga = array();
	foreach($xp_in_xml->query("/dns/dn[@id=\"".$dn->nodeValue."\"]//group[@type=\"organization\"]/@ext_id") as $go)
		$group_orga[] = $go->nodeValue;
	$group_orga = array_values(array_unique($group_orga));
	
	//TREE CONSTRUCTION (Security, Redirect_services, Redirect_groups)
	
	foreach($group_orga as $go)
	{
		$tree_security[$go] = array();
		$tree_redirect_services[$go]= array();
		$tree_redirect_groups[$go]=array();
		
		$tree_security[$go] = array_values(array_unique(array_merge($tree_security[$go],group_up(intval($security['up']),$xp_in_xml,$go))));
		$tree_redirect_services[$go] = array_values(array_unique(array_merge($tree_redirect_services[$go],group_up(intval($redirect_services['up']),$xp_in_xml,$go))));
		$tree_redirect_groups[$go] = array_values(array_unique(array_merge($tree_redirect_groups[$go],group_up(intval($redirect_groups['up']),$xp_in_xml,$go))));
		
		$tree_security[$go] = array_values(array_unique(array_merge($tree_security[$go],group_down(intval($security['down']),$xp_in_xml,$go))));
		$tree_redirect_services[$go] = array_values(array_unique(array_merge($tree_redirect_services[$go],group_down(intval($redirect_services['down']),$xp_in_xml,$go))));
		$tree_redirect_groups[$go] = array_values(array_unique(array_merge($tree_redirect_groups[$go],group_down(intval($redirect_groups['down']),$xp_in_xml,$go))));
		
		if($security['brothers'] == 'true')
			$tree_security[$go] = array_values(array_unique(array_merge($tree_security[$go],group_brothers($xp_in_xml,$go))));
			
		if($redirect_services['brothers'] == 'true')
			$tree_redirect_services[$go] = array_values(array_unique(array_merge($tree_redirect_services[$go],group_brothers($xp_in_xml,$go))));
			
		if($redirect_groups['brothers'] == 'true')
			$tree_redirect_groups[$go] = array_values(array_unique(array_merge($tree_redirect_groups[$go],group_brothers($xp_in_xml,$go))));
	}
	
	//GROUPS IN DN
	$old_group_orga = array();
	foreach($old_xp_in_xml->query("/dns/dn[@id=\"".$dn->nodeValue."\"]//group[@type=\"organization\"]/@ext_id") as $ogo)
		$old_group_orga[] = $ogo->nodeValue;
	$old_group_orga = array_values(array_unique($old_group_orga));
	
	//TREE CONSTRUCTION (Security, Redirect_services, Redirect_groups)
	foreach($old_group_orga as $ogo)
	{
		$old_tree_security[$ogo] = array();
		$old_tree_redirect_services[$ogo]= array();
		$old_tree_redirect_groups[$ogo]=array();
		
		$old_tree_security[$ogo] = array_values(array_unique(array_merge($old_tree_security[$ogo],group_up(intval($old_security['up']),$old_xp_in_xml,$ogo))));
		$old_tree_redirect_services[$ogo] = array_values(array_unique(array_merge($old_tree_redirect_services[$ogo],group_up(intval($old_redirect_services['up']),$old_xp_in_xml,$ogo))));
		$old_tree_redirect_groups[$ogo] = array_values(array_unique(array_merge($old_tree_redirect_groups[$ogo],group_up(intval($old_redirect_groups['up']),$old_xp_in_xml,$ogo))));
		
		$old_tree_security[$ogo] = array_values(array_unique(array_merge($old_tree_security[$ogo],group_down(intval($old_security['down']),$old_xp_in_xml,$ogo))));
		$old_tree_redirect_services[$ogo] = array_values(array_unique(array_merge($old_tree_redirect_services[$ogo],group_down(intval($old_redirect_services['down']),$old_xp_in_xml,$ogo))));
		$old_tree_redirect_groups[$ogo] = array_values(array_unique(array_merge($old_tree_redirect_groups[$ogo],group_down(intval($old_redirect_groups['down']),$old_xp_in_xml,$ogo))));
		
		if($security['brothers'] == 'true')
			$old_tree_security[$ogo] = array_values(array_unique(array_merge($old_tree_security[$ogo],group_brothers($old_xp_in_xml,$ogo))));
			
		if($redirect_services['brothers'] == 'true')
			$old_tree_redirect_services[$ogo] = array_values(array_unique(array_merge($old_tree_redirect_services[$ogo],group_brothers($old_xp_in_xml,$ogo))));
			
		if($redirect_groups['brothers'] == 'true')
			$old_tree_redirect_groups[$ogo] = array_values(array_unique(array_merge($old_tree_redirect_groups[$ogo],group_brothers($old_xp_in_xml,$ogo))));
	}
}

//EACH GROUP UPDATE SECURITY AND GROUPBASKET
$group_ext_id = array();
foreach($xp_in_xml->query("//group[@type=\"organization\"]/@ext_id") as $group)
{
	$group_ext_id[] = $group->nodeValue;
}
$group_ext_id = array_values(array_unique($group_ext_id));


foreach($group_ext_id as $gei)
{
	//SECURITY
	if(isset($tree_security[$gei]))
	{
		//Identifiant reel du groupe
		$db->query("SELECT value FROM ext_references 
			WHERE reference_id = '".addslashes($gei)."'
			AND field = 'group_id'
			AND type = '".$type_ldap."'");
		
		$this_group =  $db->fetch_object()->value;
	
		//LIST SERVICE
		$services_list = array();
		
		//Les services present lors de la derniere execution
		foreach($tree_security[$gei] as $s)
		{
			$db->query("SELECT value FROM ext_references 
			WHERE reference_id = '".addslashes($s)."'
			AND field = 'group_id'
			AND type = '".$type_ldap."'");
			
			$services_list[] = $db->fetch_object()->value;
		}
		
		$old_services_list = array();
		
		//Les services presents lors de l'avant derniere execution
		if(isset($old_tree_security[$gei]))
		foreach($old_tree_security[$gei] as $s)
		{
			$db->query("SELECT value FROM ext_references 
			WHERE reference_id = '".addslashes($s)."'
			AND field = 'group_id'
			AND type = '".$type_ldap."'");
			
			$old_services_list[] = $db->fetch_object()->value;
		}
		
		//Clause precedente
		$db->query("SELECT where_clause FROM security WHERE group_id ='".$this_group."'");
	
		if($where_clause = $db->fetch_object()->where_clause)
		{
			$find_services = preg_replace("#\s#","",$where_clause);
			preg_match("#DESTINATIONIN\('(.*)'\)#" ,$find_services,$all_matches);
			
			$sql_services=array();
			
			foreach(explode("','",$all_matches[1]) as $am)
				$sql_services[] = $am;
				
			$final_services=array();
	
			//On enleve les services qui n'existent plus
			$final_services = array_unique(array_diff(array_unique(array_merge($sql_services,$services_list)),array_diff($old_services_list,$services_list)));
			
			$update_security = "UPDATE security SET where_clause = 
			'DESTINATION IN (''".implode("'',''",$final_services)."'')' 
			WHERE group_id = '".$this_group."'";
			
			if($debug_security)
				echo $update_security."<br>";
			
			$db->query($update_security);
		}
		else
		{
			$insert_security = "INSERT INTO security (group_id,res_table,where_clause) 
								VALUES ('".$this_group."','res_x','DESTINATION IN (''".implode("'',''",$services_list)."'')')";
			
			if($debug_security)
				echo $insert_security."<br>";
				
			$db->query($insert_security);
		}
	}
	
	//Liste des baskets affectés par cette mise à jour
	foreach($group_basket_update as $k_gbu => $d_gbu)
	{
		//REDIRECT SERVICES
		if(isset($tree_redirect_services[$gei]))
		{
			//Identifiant reel du groupe
			
			$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($gei)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
			
			$this_group =  $db->fetch_object()->value;
		
			//LIST SERVICE
			$services_list = array();
			
			//Les services present lors de la derniere execution
			foreach($tree_redirect_services[$gei] as $s)
			{
				$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($s)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
				$services_list[] = $db->fetch_object()->value;
			}
			
			$old_services_list = array();
			
			//Les services present lors de l'avant derniere execution
			if(isset($old_tree_redirect_services[$gei]))
			foreach($old_tree_redirect_services[$gei] as $s)
			{
				$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($s)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
				$old_services_list[] = $db->fetch_object()->value;
			}
			
			//Clause precedente
			$db->query("SELECT redirect_basketlist FROM groupbasket WHERE group_id = '".$this_group."' AND basket_id = '".$k_gbu."' ");
		
			if($where_clause = $db->fetch_object()->where_clause)
			{
				$find_services = preg_replace("#\s#","",$where_clause);
				preg_match("#'(.*)'#",$find_groups,$all_matches);
				
				$sql_services=array();
				
				foreach(explode("','",$all_matches[1]) as $am)
					$sql_services[] = $am;
					
				$final_services=array();
				
				//On enleve les services qui n'existent plus
				$final_services = array_unique(array_diff(array_unique(array_merge($sql_services,$services_list)),array_diff($old_services_list,$services_list)));
								
				$update_redirect_service = "UPDATE groupbasket SET redirect_basketlist = 
				'''".implode("'',''",$final_services)."'''
				WHERE group_id = '".$this_group."' AND basket_id = '".$gbu."'";
				
				if($debug_redirect_service)
					echo $update_redirect_service."<br>";
				
				$db->query($update_redirect_service);
			}
			else
			{
				$insert_redirect_service = "INSERT Ignore Into groupbasket (group_id,basket_id,redirect_basketlist,result_page)
									VALUES ('".$this_group."','".$k_gbu."','''".implode("'',''",$services_list)."''','".$d_gbu."')";
				
				if($debug_redirect_service)
					echo $insert_redirect_service."<br>";
					
				$db->query($insert_redirect_service);
				
				//Si la ligne existe pas de insert, donc UPDATE
				
				$update_redirect_service = "UPDATE groupbasket SET redirect_basketlist = 
				'''".implode("'',''",$services_list)."'''
				WHERE group_id = '".$this_group."' AND basket_id = '".$k_gbu."'";
				
				if($debug_redirect_service)
					echo $update_redirect_service."<br>";
				
				$db->query($update_redirect_service);
			}
		}
		
		//REDIRECT GROUPS
		
		if(isset($tree_redirect_groups[$gei]))
		{
			//Identifiant reel du groupe
			
			$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($gei)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
			
			$this_group =  $db->fetch_object()->value;
		
			//LIST SERVICE
			$services_list = array();
			
			//Les services present lors de la derniere execution
			foreach($tree_redirect_groups[$gei] as $s)
			{
				$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($s)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
				$services_list[] = $db->fetch_object()->value;
			}
			
			$old_services_list = array();
			
			if(isset($old_tree_redirect_groups[$gei]))
			//Les services present lors de l'avant derniere execution
			foreach($old_tree_redirect_groups[$gei] as $s)
			{
				$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($s)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
				$old_services_list[] = $db->fetch_object()->value;
			}
			
			//Clause precedente
			$db->query("SELECT redirect_grouplist FROM groupbasket WHERE group_id = '".$this_group."' AND basket_id = '".$k_gbu."' ");
		
			if($where_clause = $db->fetch_object()->where_clause)
			{
				$find_services = preg_replace("#\s#","",$where_clause);
				preg_match("#'(.*)'#",$find_groups,$all_matches);
				
				$sql_services=array();
				
				foreach(explode("','",$all_matches[1]) as $am)
					$sql_services[] = $am;
					
				$final_services=array();
				
				//On enleve les services qui n'existent plus
				$final_services = array_unique(array_diff(array_unique(array_merge($sql_services,$services_list)),array_diff($old_services_list,$services_list)));
				
				$update_redirect_group = "UPDATE groupbasket SET redirect_grouplist = 
				'''".implode("'',''",$final_services)."'''
				WHERE group_id = '".$this_group."' AND basket_id = '".$k_gbu."'";
				
				if($debug_redirect_group)
					echo $update_redirect_group."<br>";
				
				$db->query($update_redirect_group);
			}
			else
			{
				$insert_redirect_group = "INSERT Ignore Into groupbasket (group_id,basket_id,redirect_grouplist,result_page)
									VALUES ('".$this_group."','".$k_gbu."','''".implode("'',''",$services_list)."''','".$d_gbu."')";
				
				if($debug_redirect_group)
					echo $insert_redirect_group."<br>";
					
				$db->query($insert_redirect_group);
				
				//Si la ligne existe pas de insert, donc UPDATE
				
				$update_redirect_group = "UPDATE groupbasket SET redirect_grouplist = 
				'''".implode("'',''",$services_list)."'''
				WHERE group_id = '".$this_group."' AND basket_id = '".$k_gbu."'";
				
				if($debug_redirect_group)
					echo $update_redirect_group."<br>";
				
				$db->query($update_redirect_group);
			}
		}
	}
	foreach($group_basket as $k_gb => $d_gb)
	{
		//Identifiant reel du groupe
		$db->query("SELECT enabled, group_id FROM usergroups
					WHERE group_id IN
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($gei)."'
					AND field = 'group_id'
					AND type = '".$type_ldap."')");
			
		$enabled_group_id = $db->fetch_object();
		
		if($enabled_group_id->enabled == 'Y')
		{
			$db->query("INSERT Ignore Into groupbasket (group_id,basket_id,result_page)
						VALUES ('".$enabled_group_id->group_id."','".$k_gb."','".$d_gb."')");
						
			if($debug_basket)
				echo "INSERT Ignore Into groupbasket (group_id,basket_id,result_page)
					  VALUES ('".$enabled_group_id->group_id."','".$k_gb."','".$d_gb."')<br>";
		}
		else
		{
			$db->query("DELETE Ignore FROM groupbasket WHERE group_id ='".$enabled_group_id->group_id."' AND basket_id = '".$k_gb."' ");
			
			if($debug_basket)
				echo "DELETE Ignore FROM groupbasket WHERE group_id ='".$enabled_group_id->group_id."' AND basket_id = '".$k_gb."'<br>";
		}
	}
}

//Les CopyMailBasket ne peuvent pas rediriger

$db->query("UPDATE IGNORE groupbasket SET can_redirect ='N' WHERE basket_id = 'CopyMailBasket'");

if($debug_basket)
	echo "UPDATE IGNORE groupbasket SET can_redirect ='N' WHERE basket_id = 'CopyMailBasket'";


//**********************************//
// 		INSERT ADMIN RIGHTS			//
//**********************************//
//Note : L'admin doit appartenir aux dn mappés de l'AD
//On considere que le groupe primaire de l'admin est le groupe administrateur

$admin_group_ext_id = $xp_in_xml->query("//user[user_id = \"".$login_admin."\"]/memberof[1]/group[@type = \"rights\"][1]/@ext_id");

if(isset($admin_group_ext_id))
{
	$set_admin_group = "UPDATE usergroups 
						SET Administrator = 'Y', consult_group ='Y', view_relance = 'Y', view_stats = 'Y',
						modif_rights = 'Y', export = 'Y', delete_rights = 'Y', print_rights = 'Y', param ='Y'
						WHERE group_id IN 
							(SELECT value FROM ext_references 
							WHERE reference_id = '".addslashes($admin_group_ext_id->item(0)->nodeValue)."'
							AND field = 'group_id'
							AND type = '".$type_ldap."')";
							
	$db->query($set_admin_group);
	
	if($debug_admin_group)
		echo $set_admin_group."<br>";
}
else
{
	//Aucun admin il va falloir definir le groupe d'admin à la main
}

//**********************************//
// 			  RENAME XML			//
//**********************************//

if(file_exists(dirname($in_xml_file)."/old_".basename($in_xml_file)))
	unlink(dirname($in_xml_file)."/old_".basename($in_xml_file));
	
if(file_exists(dirname($ldap_conf_file)."/old_".basename($ldap_conf_file)))
	unlink(dirname($ldap_conf_file)."/old_".basename($ldap_conf_file));
	
copy(dirname($in_xml_file)."/".basename($in_xml_file),dirname($in_xml_file)."/old_".basename($in_xml_file));
copy(dirname($ldap_conf_file)."/".basename($ldap_conf_file),dirname($ldap_conf_file)."/old_".basename($ldap_conf_file));

?>