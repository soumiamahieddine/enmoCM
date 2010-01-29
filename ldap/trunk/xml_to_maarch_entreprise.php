<?php

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


//CLASS_LOG
//Si une class custom est définie
if( file_exists(dirname($ldap_conf_file)."/../class/class_log.php") )
	include(dirname($ldap_conf_file)."/../class/class_log.php");

//Sinon si la class est définie pour le module	
else if( file_exists(dirname($ldap_conf_file)."/../../../../../modules/ldap/class/class_log.php") )
	include(dirname($ldap_conf_file)."/../../../../../modules/ldap/class/class_log.php");

//Sinon
else
	exit("Impossible de charger class_log.php\n");


//APPS CONFIG.XML
//Looking for the config.xml
if( @DOMDocument::load(dirname($ldap_conf_file)."/../../../apps/maarch_entreprise/xml/config.xml") )
{
	$config_xml = DOMDocument::load(dirname($ldap_conf_file)."/../../../apps/maarch_entreprise/xml/config.xml");
}
else if( @DOMDocument::load(dirname($ldap_conf_file)."/../../../../../apps/maarch_entreprise/xml/config.xml") ) 
{
	$config_xml = DOMDocument::load(dirname($ldap_conf_file)."/../../../../../apps/maarch_entreprise/xml/config.xml");
}
else
	exit("Unable to load the config.xml file");


//PHP File to include
$include_file = array("class_functions.php","class_db.php");
foreach($include_file as $if)
{
	if ( file_exists(dirname($ldap_conf_file)."/../../../core/class/".$if) )
	{
		include_once(dirname($ldap_conf_file)."/../../../core/class/".$if);
	}
	else if( file_exists(dirname($ldap_conf_file)."/../../../../../core/class/".$if) )
	{
		include_once(dirname($ldap_conf_file)."/../../../../../core/class/".$if);
	}
	else
		exit("Unable to load ".$if."\n");
	
}
 
//Create The Log
try
{
	$log = new log(dirname($ldap_conf_file).'/log.xml','root');
} 
catch(Exception $e){ exit($e->getMessage()."\n"); }

$log->start();
 
 
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
	//$log->add_fatal_error("Impossible de charger le document : ".$in_xml_file." Erreur : ".$e.getMessage);
	exit("Impossible de charger le document : ".$in_xml_file." Erreur : ".$e.getMessage);
}

$xp_in_xml = new domxpath($in_xml);


$old_in_xml = new DomDocument();

try
{
	@$old_in_xml->load(dirname($in_xml_file)."/old_".basename($in_xml_file));
	$log->add_notice("*** FOUND : ".dirname($in_xml_file)."/old_".basename($in_xml_file));
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
	//$log->add_fatal_error("Impossible de charger le document : ".$ldap_conf_file." Erreur : ".$e.getMessage);
	exit("Impossible de charger le document : ".$ldap_conf_file." Erreur : ".$e.getMessage);
}

$xp_ldap_conf = new domxpath($ldap_conf);

foreach($xp_ldap_conf->query("/root/config/*") as $cf)
	${$cf->nodeName} = $cf->nodeValue;

//Extraction du fichier de conf de la dernière execution
$old_ldap_conf = new DomDocument();
try
{
	@$old_ldap_conf->load(dirname($ldap_conf_file)."/old_".basename($ldap_conf_file));
	$log->add_notice("*** FOUND : ".dirname($ldap_conf_file)."/old_".basename($ldap_conf_file));
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
$_SESSION['config']['databaseserverport'] = $config_xml->getElementsByTagName("databaseserverport")->item(0)->nodeValue;
$_SESSION['config']['databasepassword'] = $config_xml->getElementsByTagName("databasepassword")->item(0)->nodeValue;
$_SESSION['config']['databasetype'] = $config_xml->getElementsByTagName("databasetype")->item(0)->nodeValue;
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
$db->query("create or replace function create_table_if_exists() returns void as
$$
begin
    if not exists(select * from information_schema.tables where table_name = 'ext_references') then
       CREATE TABLE ext_references
		(
		  reference_id character varying(32) NOT NULL DEFAULT '0'::character varying,
		  type character varying(32) DEFAULT NULL::character varying,
		  field character varying(32) DEFAULT NULL::character varying,
		  value character varying(32) DEFAULT NULL::character varying,
		  CONSTRAINT ext_references_pkey PRIMARY KEY (reference_id,type)
		);
	end if;
end;
$$
language 'plpgsql';
SELECT create_table_if_exists();");

	
//**********************************//
//			GROUPS  UPDATE   		//
//**********************************//
$log->add_notice("*** GROUPS  UPDATE ***");

//Prepare les champs pour l'update ou l'insert
//On enleve le champ group_id qui est traité par un increment ici
//On insert que les groupes de type "rights"
$db->query("SELECT column_name as field FROM information_schema.columns WHERE table_name ='usergroups'");
while($field = $db->fetch_object())
	if($field->field != "group_id")
		$lb_groups_fields[] = $field->field;

$update_groups_fields = array_values(array_uintersect($xml_group_fields,$lb_groups_fields,"strcasecmp"));

$xml_groups_id = array();
foreach($xp_in_xml->query("//group[@type = \"rights\"]/@ext_id") as $group_id)
	$xml_groups_id[] = $group_id->nodeValue;
	
$old_xml_groups_id = array();
foreach($old_xp_in_xml->query("//group[@type = \"rights\"]/@ext_id") as $old_group_id)
	$old_xml_groups_id[] = $old_group_id->nodeValue;

//On supprimer les doublons des groupes
$xml_groups_id = array_unique($xml_groups_id);
$old_xml_groups_id = array_unique($old_xml_groups_id);


//INSERT GROUPS
$insert_groups = array_values(array_diff($xml_groups_id,$old_xml_groups_id));
foreach($insert_groups as $ig)
{
	$db->query("SELECT value AS group_id FROM ext_references 
				WHERE reference_id = '".$ig."'
				AND ( field = 'group_id' OR field = 'entity_id')
				AND type = '".$type_ldap."'");
				
	if($group_id = $db->fetch_object()->group_id)
	{
		//Le groupe exise deja : on le supprime dans usergroups, on le maj dans ext_reference
		$db->query("DELETE FROM usergroups WHERE group_id = '".$group_id."'");
		$log->add_notice("DELETE FROM usergroups WHERE group_id = '".$group_id."'");
		
		$db->query("UPDATE ext_references SET value = '".$group_id."', field = 'group_id' 
					WHERE reference_id = '".$ig."'");
					
		$log->add_notice("UPDATE ext_references SET value = '".$group_id."', field = 'group_id' 
					WHERE reference_id = '".$ig."'");
	}
	else
	{
		//Il n'existe pas : on l'insert dans ext_reference
		
		//On insert un group_id = <group_prefix_ldap>{numero}
		
		$db->query("SELECT MAX(CAST(SUBSTRING(value,CHAR_LENGTH('".$group_prefix_ldap."')+1) as INTEGER )) as max_group_id
					FROM ext_references 
					WHERE (field = 'group_id' OR field = 'entity_id')
					AND type = '".$type_ldap."'");
		
		$max_group_id = $db->fetch_object()->max_group_id;
		
		if(!isset($max_group_id))
			$max_group_id = 0;
			
		$group_id = $group_prefix_ldap.($max_group_id + 1);
		
		$db->query("INSERT INTO ext_references (reference_id,field,value,type)
					SELECT '".$ig."','group_id','".$group_id."','".$type_ldap."'
					WHERE NOT EXISTS (SELECT reference_id FROM ext_references WHERE reference_id = '".$ig."' AND type = '".$type_ldap."');");
					
		$log->add_notice("INSERT INTO ext_references (reference_id,field,value,type)
					SELECT '".$ig."','group_id','".$group_id."','".$type_ldap."'
					WHERE NOT EXISTS (SELECT reference_id FROM ext_references WHERE reference_id = '".$ig."' AND type = '".$type_ldap."');");
		
	}
		
	$sql_insert = "INSERT INTO usergroups ( group_id, ".implode(",",$update_groups_fields)." ) SELECT '".$group_id."','";
	
	foreach($update_groups_fields as $ugf)
	{
		$sql_insert .= addslashes($xp_in_xml->query("//group[@ext_id=\"".$ig."\"]/".$ugf)->item(0)->nodeValue)."','";
	}
	
	$sql_insert = substr($sql_insert,0,-2);
	$sql_insert .= " WHERE NOT EXISTS (SELECT group_id FROM usergroups WHERE group_id = '".$group_id."');";
	
	$db->query($sql_insert);
	$log->add_notice($sql_insert);
}

//DELETE GROUPS
$delete_groups = array_values(array_diff($old_xml_groups_id,$xml_groups_id));
foreach($delete_groups as $dg)
{
	//Maj enabled N
	$sql_disabled = "UPDATE usergroups SET enabled = 'N' WHERE group_id IN
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($dg)."'
					AND field = 'group_id'
					AND type = '".$type_ldap."')";
	
	$db->query($sql_disabled);
	$log->add_notice($sql_disabled);
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
	$log->add_notice($sql_update);
	unset($sql_update);
}

//**********************************//
//			UPDATE SERVICES		    //
//**********************************//
$log->add_notice("*** UPDATE SERVICES ***");

//Les services sont les groupes qui ne sont pas de type "rights"
$update_services_fields = array("entity_label" => "group_desc",
								"short_label" => "group_desc");

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
	$db->query("SELECT value AS entity_id FROM ext_references 
				WHERE reference_id = '".$is."'
				AND ( field = 'entity_id' OR field = 'group_id' )
				AND type = '".$type_ldap."'");

	if($service_id = $db->fetch_object()->entity_id)
	{
		//Le service existe deja : on le supprime dans services
		$db->query("DELETE FROM entities WHERE entity_id = '".$service_id."'");
		$log->add_notice("DELETE FROM entities WHERE entity_id = '".$service_id."'");
		
		$db->query("UPDATE ext_references SET value = '".$service_id."', field = 'entity_id' 
					WHERE reference_id = '".$is."'");
					
		$log->add_notice("UPDATE ext_references SET value = '".$service_id."', field = 'entity_id' 
						  WHERE reference_id = '".$is."'");
		
	}
	else
	{
		//Il n'existe pas : on l'insert dans ext_reference
		
		//On insert un group_id = <group_prefix_ldap>{numero}
		
		$db->query("SELECT MAX(CAST(SUBSTRING(value,CHAR_LENGTH('".$group_prefix_ldap."')+1) as INTEGER )) as max_group_id
					FROM ext_references 
					WHERE (field = 'group_id' OR field = 'entity_id')
					AND type = '".$type_ldap."'");
		
		$max_group_id = $db->fetch_object()->max_group_id;
		
		if(!isset($max_group_id))
			$max_group_id = 0;
			
		$service_id = $group_prefix_ldap.($max_group_id + 1);
		
		$db->query("INSERT INTO ext_references (reference_id,field,value,type)
					SELECT '".$is."','entity_id','".$service_id."','".$type_ldap."'
					WHERE NOT EXISTS (SELECT reference_id FROM ext_references WHERE reference_id = '".$is."' AND type = '".$type_ldap."');");
					
		$log->add_notice("INSERT INTO ext_references (reference_id,field,value,type)
					SELECT '".$is."','entity_id','".$service_id."','".$type_ldap."'
					WHERE NOT EXISTS (SELECT reference_id FROM ext_references WHERE reference_id = '".$is."' AND type = '".$type_ldap."');");
					
		
	}
	
	$sql_insert = "INSERT INTO entities ( entity_id, ".implode(",",array_keys($update_services_fields)).",entity_type ) SELECT '".$service_id."','";
	
	foreach($update_services_fields as $k_usf => $d_usf)
	{
		$sql_insert .= addslashes(trim($xp_in_xml->query("//group[@ext_id=\"".$is."\"]/".$d_usf)->item(0)->nodeValue))."','";
	}
	
	//Ajout entity_type
	$sql_insert .= $entity_type."','";
	
	$sql_insert = substr($sql_insert,0,-2);
	$sql_insert .= " WHERE NOT EXISTS  (SELECT entity_id FROM entities WHERE entity_id = '".$service_id."');";
	
	$db->query($sql_insert);
	$log->add_notice($sql_insert);
	unset($sql_insert);
	
}

//DELETE SERVICES
$delete_services = array_values((array_diff($old_xml_services_id,$xml_services_id)));
foreach($delete_services as $ds)
{
	//Maj enabled N
	$sql_disabled = "UPDATE entities SET enabled = 'N' WHERE entity_id IN
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($ds)."'
					AND field = 'entity_id'
					AND type = '".$type_ldap."')";
	
	$db->query($sql_disabled);
	$log->add_notice($sql_disabled);
	unset($sql_disabled);
}

//UPDATE SERVICES
$update_services = array_values(array_intersect($xml_services_id,$old_xml_services_id));

foreach($update_services as $us)
{
	//Maj de service
	$sql_update = "UPDATE entities SET ENABLED = 'Y',";
	
	foreach($update_services_fields as $k_usf => $d_usf)
	{
		$sql_update .= $k_usf." = '".addslashes(trim($xp_in_xml->query("//group[@ext_id=\"".$us."\"]/".$d_usf)->item(0)->nodeValue))."', ";
	}
	
	$sql_update = substr($sql_update,0,-2)." WHERE entity_id IN 
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($us)."'
					AND field = 'entity_id'
					AND type = '".$type_ldap."')";
	
	$db->query($sql_update);
	$log->add_notice($sql_update);
	unset($sql_update);
}

//**********************************//
//			USERS UPDATE 		    //
//**********************************//
$log->add_notice("*** USERS UPDATE ***");

//Prepare les champs pour l'update ou l'insert dans users
$db->query("SELECT column_name as field FROM information_schema.columns WHERE table_name ='users'");
while($field = $db->fetch_object())
	$lb_users_fields[] = $field->field;

$update_users_fields = array_values(array_uintersect($xml_user_fields,$lb_users_fields,"strcasecmp"));

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
		$log->add_notice("DELETE FROM users WHERE user_id = '".$value."'");
		
		$db->query("DELETE FROM ext_references 
					WHERE reference_id = '".$iu."'
					AND field = 'user_id'
					AND type = '".$type_ldap."'");
		
		$log->add_notice("DELETE FROM ext_references 
					WHERE reference_id = '".$iu."'
					AND field = 'user_id'
					AND type = '".$type_ldap."'");
	}
		
	//Il n'existe pas : on l'insert dans ext_reference
	$db->query("INSERT INTO ext_references (reference_id,field,value,type)
				SELECT '".$iu."','user_id','".$xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue."','".$type_ldap."'
				WHERE NOT EXISTS (SELECT reference_id FROM ext_references WHERE reference_id = '".$iu."')");
				
	$log->add_notice("INSERT INTO ext_references (reference_id,field,value,type)
				SELECT '".$iu."','user_id','".$xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue."','".$type_ldap."'
				WHERE NOT EXISTS (SELECT reference_id FROM ext_references WHERE reference_id = '".$iu."')");
	
	if( $pass_is_login == 'true' )
	{
		$sql_insert = "INSERT INTO users ( change_password , password, ".implode(",",$update_users_fields)." ) SELECT 'N', md5('".$xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue."'),'";
	}
	else
	{
		$sql_insert = "INSERT INTO users ( change_password , password, ".implode(",",$update_users_fields)." ) SELECT 'Y',md5('maarch'),'";
	}
		
	
	foreach($update_users_fields as $uuf)
	{
		$sql_insert .= addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/".$uuf)->item(0)->nodeValue))."','";
	}
	
	$sql_insert = substr($sql_insert,0,-2);
	$sql_insert .= " WHERE NOT EXISTS (SELECT user_id FROM users WHERE user_id = '".$xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue."');";
	$db->query($sql_insert);
	$log->add_notice($sql_insert);
	unset($sql_insert);
	
}

//DELETE USERS
$delete_users = array_values(array_diff($old_xml_users_id,$xml_users_id));
foreach($delete_users as $du)
{
	//Maj status DEL
	$sql_disabled = "UPDATE users SET status = 'DEL' WHERE user_id IN
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($du)."'
					AND field = 'user_id'
					AND type = '".$type_ldap."')";
	
	$db->query($sql_disabled);
	$log->add_notice($sql_disabled);
	unset($sql_disabled);
}

//UPDATE USERS
$update_users = array_values(array_intersect($xml_users_id,$old_xml_users_id));

foreach($update_users as $uu)
{
	//Maj de user
	
	$sql_update = "UPDATE users SET status = 'OK', ";
	
	foreach($update_users_fields as $uuf)
	{
		$sql_update .= $uuf." = '".addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/".$uuf)->item(0)->nodeValue))."', ";
	}
	
	$sql_update = substr($sql_update,0,-2)." WHERE user_id IN 
					(SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($uu)."'
					AND field = 'user_id'
					AND type = '".$type_ldap."')";
	
	$db->query($sql_update);
	$log->add_notice($sql_update);
	unset($sql_update);
}

//**********************************//
// GROUPS / SERVICES USERS LINKS   	//
//**********************************//
$log->add_notice("*** GROUPS / SERVICES USERS LINKS ***");

//Prepare les champs pour l'update ou l'insert dans usergroup_content
$db->query("SELECT column_name as field FROM information_schema.columns WHERE table_name ='usergroup_content'");
while($field = $db->fetch_object())
	$lb_usergroup_content_fields[] = $field->field;

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
$log->add_notice("--- NEW USERS ---");

$insert_users = array_values(array_diff($xml_users_id,$old_xml_users_id));
foreach($insert_users as $iu)
{
	//Les groupes de type "organization" de niveau 1
	$primary_groups_group_id = array();
	foreach($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/memberof/group[@type =\"organization\"]/@ext_id") as $node_ext_id)
	{
		$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($node_ext_id->nodeValue)."'
				AND field = 'entity_id'
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
	
	//INSERT USER / RIGHTS GROUP(S) LINK(S)
	foreach($group_rights_group_id as $grgi)
	{
		$sql_insert_usergroup_content = "INSERT INTO usergroup_content (group_id, primary_group, ".implode(",",$update_usergroup_content_fields)." )
										 SELECT '".addslashes($grgi)."','N','";
		
		foreach($update_usergroup_content_fields as $uugf)
		{
			$sql_insert_usergroup_content .= addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/".$uugf)->item(0)->nodeValue))."','";
		}
		
		$sql_insert_usergroup_content = substr($sql_insert_usergroup_content,0,-2);
		$sql_insert_usergroup_content .= " WHERE NOT EXISTS (SELECT group_id FROM usergroup_content WHERE group_id = '".$grgi."' 
										   AND user_id = '".addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue))."')";
		$db->query($sql_insert_usergroup_content);
		$log->add_notice($sql_insert_usergroup_content);
		unset($sql_insert_usergroup_content);
	}
	
	//INSERT USER / ORGA GROUP(S) LINK(S)
	foreach($primary_groups_group_id as $pggi)
	{
		$sql_insert_users_entities = "INSERT INTO users_entities (entity_id, primary_entity, ".implode(",",$update_usergroup_content_fields)." )
									  SELECT '".addslashes($pggi)."','N','";
		
		foreach($update_usergroup_content_fields as $uugf)
		{
			$sql_insert_users_entities .= addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/".$uugf)->item(0)->nodeValue))."','";
		}
		
		$sql_insert_users_entities = substr($sql_insert_users_entities,0,-2);
		$sql_insert_users_entities .= " WHERE NOT EXISTS (SELECT entity_id FROM users_entities WHERE entity_id = '".$pggi."'
										AND user_id = '".addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue))."')";
		$db->query($sql_insert_users_entities);
		$log->add_notice($sql_insert_users_entities);
		unset($sql_insert_users_entities);
	}
	
	//INSERT PRIMARY GROUP
	if(isset($group_rights_group_id[0]))
	{
		$sql_insert_service = 
		"UPDATE usergroup_content SET primary_group = 'Y'
		WHERE user_id ='".addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue))."' 
		AND group_id = '".$group_rights_group_id[0]."' 
		AND NOT EXISTS (SELECT primary_group FROM usergroup_content WHERE user_id = '".addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue))."' 
		AND primary_group = 'Y')";
	
		$db->query($sql_insert_service);
		$log->add_notice($sql_insert_service);
	}
	
	//INSERT USER SERVICE
	if(isset($primary_groups_group_id[0]))
	{
		$sql_insert_service = 
		"UPDATE users_entities SET primary_entity = 'Y'
		WHERE user_id ='".addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue))."' 
		AND entity_id  = '".$primary_groups_group_id[0]."'
		AND NOT EXISTS (SELECT primary_entity FROM users_entities WHERE user_id = '".addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$iu."\"]/user_id")->item(0)->nodeValue))."' 
		AND primary_entity = 'Y')";
	
		$db->query($sql_insert_service);
		$log->add_notice($sql_insert_service);
	}	
}



//**********************************//
//  		UPDATE USERS			//
//**********************************//
$log->add_notice("--- UPDATE USERS ---");

$update_users = array_values(array_intersect($xml_users_id,$old_xml_users_id));
foreach($update_users as $uu)
{
	//**********************************//
	//GROUPS TYPE ORGANIZATION / RIGHTS //
	//**********************************//

	//UPDATE USER / GROUP(S) LINK(S)
	//On compare la liste des groupes de premier niveau à celle du xml de l'execution precedente
	
	$group_level_one = array();
	foreach($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/memberof/group[@type =\"organization\"]/@ext_id") as $glo)
		$group_level_one[] = $glo->nodeValue;
	
	$old_group_level_one = array();
	foreach($old_xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/memberof/group[@type =\"organization\"]/@ext_id") as $oglo)
		$old_group_level_one[] = $oglo->nodeValue;
		
	$group_rights = array();
	foreach($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]//group[@type=\"rights\"]/@ext_id") as $gr)
		$group_rights[] = $gr->nodeValue;
	
	$old_group_rights = array();
	foreach($old_xp_in_xml->query("//user[@ext_id=\"".$uu."\"]//group[@type=\"rights\"]/@ext_id") as $ogr)
		$old_group_rights[] = $ogr->nodeValue;
		
		
	//INSERT RIGHTS GROUPS
	$usergroup_link_insert = array_values(array_diff($group_rights,$old_group_rights));
	foreach($usergroup_link_insert as $uli)
	{
		$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($uli)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
		$uli_group_id = $db->fetch_object()->value;
		
		
		//Primary group or not
		$db->query("SELECT user_id FROM usergroup_content WHERE user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
					AND primary_group = 'Y'");
		
		if( $db->fetch_object()->user_id )
			$primary_group = 'N';
		else
			$primary_group = 'Y';
		
		$sql_insert_usergroup_content = "INSERT INTO usergroup_content (group_id, primary_group, ".implode(",",$update_usergroup_content_fields)." )
										SELECT '".addslashes($uli_group_id)."','".$primary_group."','";
		
		foreach($update_usergroup_content_fields as $uugf)
		{
			if($uugf == 'user_id')
				$sql_insert_usergroup_content .= addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue))."','";
			else
				$sql_insert_usergroup_content .= addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$uli->nodeValue."\"]/".$uugf)->item(0)->nodeValue))."','";
		}
		
		$sql_insert_usergroup_content = substr($sql_insert_usergroup_content,0,-2);
		$sql_insert_usergroup_content .= " WHERE NOT EXISTS (SELECT group_id FROM usergroup_content WHERE group_id = '".addslashes($uli_group_id)."'
										   AND user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."')";
										   
		$db->query($sql_insert_usergroup_content);
		$log->add_notice($sql_insert_usergroup_content);
		unset($sql_insert_usergroup_content);
	}
	
	//INSERT ORGANIZATION GROUPS
	$entities_link_insert = array_values(array_diff($group_level_one,$old_group_level_one));
	foreach($entities_link_insert as $eli)
	{
		$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($eli)."'
				AND field = 'entity_id'
				AND type = '".$type_ldap."'");
				
		$eli_group_id = $db->fetch_object()->value;
		
		//Primary entity or not
		$db->query("SELECT user_id FROM users_entities WHERE user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
					AND primary_entity = 'Y'");
		
		if( $db->fetch_object()->user_id )
			$primary_entity = 'N';
		else
			$primary_entity = 'Y';
			
		$sql_insert_users_entities = "INSERT INTO users_entities (entity_id,primary_entity,".implode(",",$update_usergroup_content_fields)." )
									  SELECT '".addslashes($eli_group_id)."','".$primary_entity."','";
		
		foreach($update_usergroup_content_fields as $uugf)
		{
			if($uugf == 'user_id')
				$sql_insert_users_entities .= addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue))."','";
			else
				$sql_insert_users_entities .= addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$eli->nodeValue."\"]/".$uugf)->item(0)->nodeValue))."','";
		}
		
		$sql_insert_users_entities = substr($sql_insert_users_entities,0,-2);
		$sql_insert_users_entities .= " WHERE NOT EXISTS (SELECT entity_id FROM users_entities WHERE entity_id = '".addslashes($eli_group_id)."'
										AND user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."')";
		
		$db->query($sql_insert_users_entities);
		$log->add_notice($sql_insert_users_entities);
		unset($sql_insert_users_entities);
	}
	
	//DELETE RIGHTS GROUPS
	$usergroup_link_delete = array_values(array_diff($old_group_rights,$group_rights));
	
	foreach($usergroup_link_delete as $uld)
	{
		$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($uld)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."'");
				
		$uld_group_id = $db->fetch_object()->value;
		
		$sql_delete_usergroup_content = "DELETE FROM usergroup_content 
										WHERE user_id = '".addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue))."'
										AND group_id = '".addslashes(trim($uld_group_id))."' ";
		
		$db->query($sql_delete_usergroup_content);
		$log->add_notice($sql_delete_usergroup_content);
		unset($sql_delete_usergroup_content);
	}
	
	//DELETE ORGANIZATION GROUPS
	$entities_link_delete = array_values(array_diff($old_group_level_one,$group_level_one));
	foreach($entities_link_delete as $eld)
	{
		$db->query("SELECT value FROM ext_references 
				WHERE reference_id = '".addslashes($eld)."'
				AND field = 'entity_id'
				AND type = '".$type_ldap."'");
				
		$eld_group_id = $db->fetch_object()->value;
		
		$sql_delete_entities = "DELETE FROM users_entities
								WHERE user_id = '".addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue))."'
								AND entity_id = '".addslashes(trim($eld_group_id))."' ";
		
		$db->query($sql_delete_entities);
		$log->add_notice($sql_delete_entities);
		unset($sql_delete_entities);
	}
	

	//UPDATE RIGHTS GROUPS
	$usergroup_link_update = array_values(array_intersect($old_group_rights,$group_rights));
	foreach($usergroup_link_update as $ulu)
	{
		//Maj des liens groupes de droit (cas ou le user_id change)
		$db->query("UPDATE usergroup_content
					SET user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
					WHERE user_id IN
					(SELECT value FROM ext_references 
						WHERE reference_id = '".addslashes($uu)."'
						AND field = 'user_id'
						AND type = '".$type_ldap."') AND group_id IN
					(SELECT value FROM ext_references 
						WHERE reference_id = '".addslashes($ulu)."'
						AND field = 'group_id'
						AND type = '".$type_ldap."')");
						
		$log->add_notice("UPDATE usergroup_content
					SET user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
					WHERE user_id IN
					(SELECT value FROM ext_references 
						WHERE reference_id = '".addslashes($uu)."'
						AND field = 'user_id'
						AND type = '".$type_ldap."') AND group_id IN
					(SELECT value FROM ext_references 
						WHERE reference_id = '".addslashes($ulu)."'
						AND field = 'group_id'
						AND type = '".$type_ldap."')");
	}
	
	
	//UPDATE ORGANIZATION GROUPS
	$entities_link_update = array_values(array_intersect($old_group_level_one,$group_level_one));
	foreach($entities_link_update as $elu)
	{
		//Maj des liens services (cas ou le user_id change)
		$db->query("UPDATE users_entities
					SET user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
					WHERE user_id IN
					(SELECT value FROM ext_references 
						WHERE reference_id = '".addslashes($uu)."'
						AND field = 'user_id'
						AND type = '".$type_ldap."') AND entity_id IN
					(SELECT value FROM ext_references 
						WHERE reference_id = '".addslashes($elu)."'
						AND field = 'entity_id'
						AND type = '".$type_ldap."')");
						
		$log->add_notice("UPDATE users_entities
					SET user_id = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
					WHERE user_id IN
					(SELECT value FROM ext_references 
						WHERE reference_id = '".addslashes($uu)."'
						AND field = 'user_id'
						AND type = '".$type_ldap."') AND entity_id IN
					(SELECT value FROM ext_references 
						WHERE reference_id = '".addslashes($elu)."'
						AND field = 'entity_id'
						AND type = '".$type_ldap."')");
	}
	
	//UPDATE EXT_REFERENCES (USER_ID COULD CHANGE)
	
	$db->query("UPDATE ext_references
				SET value = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
				WHERE reference_id = '".addslashes($uu)."'
				AND field = 'user_id'
				AND type = '".$type_ldap."'");
				
	$log->add_notice("UPDATE ext_references
				SET value = '".addslashes($xp_in_xml->query("//user[@ext_id=\"".$uu."\"]/user_id")->item(0)->nodeValue)."'
				WHERE reference_id = '".addslashes($uu)."'
				AND field = 'user_id'
				AND type = '".$type_ldap."'");
}

//***********************************//
//  		DELETE USERS	   	    //
//**********************************//
$log->add_notice("--- DELETE USERS ---");

$delete_users = array_values(array_diff($old_xml_users_id,$xml_users_id));
foreach($delete_users as $du)
{
	//DELETE RIGHTS GROUPS
	$sql_delete_usergroup_content = "DELETE FROM usergroup_content 
									WHERE user_id = '".addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$du."\"]/user_id")->item(0)->nodeValue))."'";
	
	$db->query($sql_delete_usergroup_content);
	$log->add_notice($sql_delete_usergroup_content);
	unset($sql_delete_usergroup_content);
	
	//DELETE ORGANIZATION GROUPS
	$sql_delete_entities = "DELETE FROM users_entities
							WHERE user_id = '".addslashes(trim($xp_in_xml->query("//user[@ext_id=\"".$du."\"]/user_id")->item(0)->nodeValue))."'";
	
	$db->query($sql_delete_entities);
	$log->add_notice($sql_delete_entities);
	unset($sql_delete_entities);
}

//**********************************//
// 		SECURITY UPDATE				//
//**********************************//
$log->add_notice("*** SECURITY UPDATE ***");

$dns = $xp_ldap_conf->query("//dn[@type=\"rights\"]/@id");
foreach($dns as $dn)
{
	//CONF XML Parameters
	foreach($xp_ldap_conf->query("//dn[@id=\"".$dn->nodeValue."\"]/security/*") as $s)
		$security[$s->nodeName] = $s->nodeValue;
		
	//CONF OLD XML Parameters
	foreach($old_xp_ldap_conf->query("//dn[@id=\"".$dn->nodeValue."\"]/security/*") as $s)
		$old_security[$s->nodeName] = $s->nodeValue;
}
	
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
	
	$sql_security_insert = "INSERT INTO security (group_id,coll_id,where_clause,can_insert,can_update,can_delete) 
							SELECT '".$gri_id."','".$security['coll_id']."','".addslashes($security['where_clause'])."','".$security['can_insert']."','".$security['can_update']."','".$security['can_delete']."'
							WHERE NOT EXISTS (SELECT group_id FROM security WHERE group_id = '".$gri_id."')";
	
	$db->query($sql_security_insert);
	$log->add_notice($sql_security_insert);
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
	
	$sql_security_delete = "DELETE FROM security WHERE group_id ='".$grd_id."'";
	$db->query($sql_security_delete);
	$log->add_notice($sql_security_delete);		
}

//UPDATE
$group_rights_update = array_values(array_intersect($group_rights,$old_group_rights));
foreach($group_rights_update as $gru)
{
	//On regarde l'etat actuel de security
	$db->query("SELECT group_id,coll_id,where_clause,can_insert,can_update,can_delete FROM security 
				WHERE group_id IN 
				(SELECT value FROM ext_references WHERE reference_id = '".addslashes($gru)."'
				AND field = 'group_id'
				AND type = '".$type_ldap."')");
	
	while( $sec = $db->fetch_object() )
	{
		if( trim($sec->coll_id) == trim($old_security['coll_id']) )
		{
			$security_update = "UPDATE security SET coll_id = '".$security['coll_id']."'";
		
			//On met à jour les champs si il n'y a pas eu de changement à la main
			if( $sec->where_clause ==  $old_security['where_clause'] )
				$security_update .= ", where_clause = '".addslashes($security['where_clause'])."'";
			
			if( $sec->can_insert ==  $old_security['can_insert'] )
				$security_update .= ", can_insert = '".$security['can_insert']."'";
			
			if( $sec->can_update ==  $old_security['can_update'] )
				$security_update .= ", can_update = '".$security['can_update']."'";
			
			if( $sec->can_delete == $old_security['can_delete'] )
				$security_update .= ", can_delete = '".$security['can_delete']."'";
			
			$security_update .= " WHERE coll_id = '".$sec->coll_id."' AND group_id = '".$sec->group_id."' ";
		}
	}
	
	if( isset($security_update) )
	{
		$db->query($security_update);
		$log->add_notice($security_update);
		unset($security_update);
	}
}


//GROUPS TYPE ORGANIZATION
$dns = $xp_ldap_conf->query("//dn[@type=\"organization\"]/@id");
foreach($dns as $dn)
{
	//GROUPS IN DN
	$group_orga = array();
	foreach($xp_in_xml->query("/dns/dn[@id=\"".$dn->nodeValue."\"]//group[@type=\"organization\"]/@ext_id") as $go)
		$group_orga[] = $go->nodeValue;
	$group_orga = array_values(array_unique($group_orga));
	
	//OLD GROUPS IN DN
	$old_group_orga = array();
	foreach($old_xp_in_xml->query("/dns/dn[@id=\"".$dn->nodeValue."\"]//group[@type=\"organization\"]/@ext_id") as $ogo)
		$old_group_orga[] = $ogo->nodeValue;
	$old_group_orga = array_values(array_unique($old_group_orga));
	
	//TREE CONSTRUCTION
	foreach($group_orga as $go)
	{
		$db->query("SELECT value FROM ext_references 
					WHERE reference_id = '".addslashes($go)."'
					AND field = 'entity_id'
					AND type = '".$type_ldap."'");
	
		$entity_id = $db->fetch_object()->value;
	
		$parent = $xp_in_xml->query("//group[@ext_id='".$go."'][1]/memberof/group[@type=\"organization\"]/@ext_id")->item(0)->nodeValue;
		$old_parent = $old_xp_in_xml->query("//group[@ext_id='".$go."'][1]/memberof/group[@type=\"organization\"]/@ext_id")->item(0)->nodeValue;
		
		if( $parent != $old_parent )
		{
			$db->query("SELECT value FROM ext_references 
						WHERE reference_id = '".addslashes($parent)."'
						AND field = 'entity_id'
						AND type = '".$type_ldap."'");
			
			$parent_entity_id = $db->fetch_object()->value;
		
			$db->query("UPDATE entities SET parent_entity_id = '".$parent_entity_id."' 
						WHERE entity_id = '".$entity_id."' ");
						
			$log->add_notice("UPDATE entities SET parent_entity_id = '".$parent_entity_id."' 
						WHERE entity_id = '".$entity_id."' ");		
		}
	}
}

//*****************************************************//
// CHANGE PARENT_ENTITY = NULL IN PARENT_ENTITY = ''   //
//*****************************************************//
$log->add_notice("CHANGE PARENT_ENTITY = NULL IN PARENT_ENTITY = ''");
$db->query("UPDATE entities SET parent_entity_id = '' WHERE parent_entity_id IS NULL");
$log->add_notice("UPDATE entities SET parent_entity_id = '' WHERE parent_entity_id IS NULL");
		
//**********************************//
// 			  RENAME XML			//
//**********************************//

if(file_exists(dirname($in_xml_file)."/old_".basename($in_xml_file)))
	unlink(dirname($in_xml_file)."/old_".basename($in_xml_file));
	
if(file_exists(dirname($ldap_conf_file)."/old_".basename($ldap_conf_file)))
	unlink(dirname($ldap_conf_file)."/old_".basename($ldap_conf_file));
	
copy(dirname($in_xml_file)."/".basename($in_xml_file),dirname($in_xml_file)."/old_".basename($in_xml_file));
copy(dirname($ldap_conf_file)."/".basename($ldap_conf_file),dirname($ldap_conf_file)."/old_".basename($ldap_conf_file));

$log->end();

$log->purge($purge_log);

?>