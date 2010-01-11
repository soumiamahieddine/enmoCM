<?php 
//Arguments

if( !isset($argv) )
	exit(htmlentities("Ce script ne peut-etre appelé qu'en PHP CLI"));
	
else if( isset($argv) && count($argv) < 3)
	exit("Erreur de Syntaxe !\nLa syntaxe est $argv[0] <fichier de conf xml> <xml de sortie>");
	
else
{
	$ldap_conf_file = trim($argv[1]);
	$out_xml_file = trim($argv[2]);
}

//Extraction de /root/config dans le fichier de conf
$ldap_conf = new DomDocument();
try
{
	$ldap_conf->load($ldap_conf_file);
}
catch(Exception $e)
{ 
	exit("Impossible de charger le document : ".$ldap_conf_file."\n
		  Erreur : ".$e.getMessage."\n");
}
						  

$xp_ldap_conf = new domxpath($ldap_conf);

foreach($xp_ldap_conf->query("/root/config/*") as $cf)
	${$cf->nodeName} = $cf->nodeValue;

//Si une class custom est définie
if( file_exists(dirname($ldap_conf_file)."/../class/class_".$type_ldap.".php") )
	include(dirname($ldap_conf_file)."/../class/class_".$type_ldap.".php");

//Sinon si la class est définie pour le module	
else if( file_exists(dirname($ldap_conf_file)."/../../../../../modules/ldap/class/class_".$type_ldap.".php") )
	include(dirname($ldap_conf_file)."/../../../../../modules/ldap/class/class_".$type_ldap.".php");

//Sinon
else
	exit("Impossible de charger class_".$type_ldap.".php\n");

//**********************************//
//			LDAP CONNECTION	      	//
//**********************************//

//Try to create a new ldap instance
try
{
	$ad = new LDAP($domain,$login_admin,$pass,false);
}
catch(Exception $con_failure)
{
	exit("Impossible de se connecter à l'annuaire\n
		 Erreur : ".$con_failure->getMessage()."\n");
}

//**********************************//
//				MAPPING	         	//
//**********************************//


//User
foreach( $xp_ldap_conf->query("/root/mapping/user/@* | /root/mapping/user/* | /root/mapping/user/*/@*")  as $us)
	if( !empty($us->nodeValue) )
		$user_fields[$us->nodeName] = $us->nodeValue;

//Group
foreach( $xp_ldap_conf->query("/root/mapping/group/@* | /root/mapping/group/* | /root/mapping/group/*/@*")  as $gs)
	if( !empty($gs->nodeValue) )
		$group_fields[$gs->nodeName] = $gs->nodeValue;

//**********************************//
//			FILTER AND DNs     		//
//**********************************//

$i=0;
foreach( $xp_ldap_conf->query("/root/filter/dn/@id") as $dn)
{
	$dn_and_filter[$i][$dn->nodeName] = $dn->nodeValue;
	if(empty($dn_and_filter[$i][$dn->nodeName]))
		$dn_and_filter[$i][$dn->nodeName]= "DC=".str_replace(".",",DC=",$domain);
		
	$dn_and_filter[$i]['type'] = $xp_ldap_conf->query("/root/filter/dn[@id= '".$dn->nodeValue."']/@type")->item(0)->nodeValue;
	if(empty($dn_and_filter[$i]['type']))
		$dn_and_filter[$i]['type'] = "organization"; //Valeur par defaut
	
	$dn_and_filter[$i]['user'] = $xp_ldap_conf->query("/root/filter/dn[@id= '".$dn->nodeValue."']/user")->item(0)->nodeValue;
	if(empty($dn_and_filter[$i]['user']))
		$dn_and_filter[$i]['user'] = "(cn=*)"; //Valeur par defaut
	
	$dn_and_filter[$i]['group'] = $xp_ldap_conf->query("/root/filter/dn[@id='".$dn->nodeValue."']/group")->item(0)->nodeValue;
	if(empty($dn_and_filter[$i]['group']))
		$dn_and_filter[$i]['group'] = "(cn=*)"; //Valeur par defaut
	$i++;
}
unset($i);

//Aucun DN de défini : on prend tout l'annuaire en mode organization
if(count($dn_and_filter) < 1)
{
	$dn_and_filter[0]['id'] = "DC=".str_replace(".",",DC=",$domain);
	$dn_and_filter[0]['type'] = "organization";
	$dn_and_filter[0]['user'] = "(cn=*)";
	$dn_and_filter[0]['group'] = "(cn=*)";
}

//**********************************//
//				XML OUT	     		//
//**********************************//
$out_xml = new DomDocument('1.0', 'UTF-8');
$xp_out = new domxpath($out_xml);

$dns = $out_xml->createElement('dns');
$out_xml->appendChild($dns);

//**********************************//
//			XML FUNCTIONS 	 		//
//**********************************//

function createUserNode($parent,$user)
{
	global $out_xml, $ad, $user_fields, $group_fields;

	if($parent == null || count($user) < 1 )
		return null;

	$u_node = $out_xml->createElement("user");
	
	foreach($user as $k_fd => $v_fd)
	{
		if( $k_fd == "dn" || $k_fd == "ext_id")
			$u_node->setAttribute($k_fd,$v_fd);
		
		else if($k_fd == "memberof" && count($v_fd) > 0)
		{
			$mbof = $out_xml->createElement($k_fd);
						
			if( isset($user['role']) )
				$mbof->setAttribute("role",$user['role']);
			
			for($i=0;$i<count($v_fd);$i++)
			{
				$tmp_g_inf = groupInfo($v_fd[$i]);
				
				if( !empty( $tmp_g_inf ) )
				{
					$mbof->appendChild( createGroupNode($mbof,$tmp_g_inf) );
				}
			}
			
			if($mbof->hasChildNodes())
				$u_node->appendChild($mbof);
		}
		else if($k_fd == "memberof" && count($v_fd) < 1)
		{
			//Si l'utilisateur n'est membre d'aucun groupe : Rien à faire
		}
		else if($k_fd == 'role')
		{
			//Traité dans memberof
		}	
		else
			$u_node->appendChild($out_xml->createElement($k_fd,$v_fd));
	}
	
	return $u_node;
}

function createGroupNode($parent,$group)
{
	global $out_xml, $ad, $user_fields, $group_fields;

	if($parent == null || count($group) < 1 )
		return null;
		
	$g_node = $out_xml->createElement("group");
	
	foreach($group as $k_fd => $v_fd)
	{
		if( $k_fd == "dn" || $k_fd == "ext_id" )
			$g_node->setAttribute($k_fd,$v_fd);
		
		else if($k_fd == "memberof" && count($v_fd) > 0)
		{
			$mbof = $out_xml->createElement($k_fd);
			
			for($i=0;$i<count($v_fd);$i++)
			{
				$tmp_g_inf = groupInfo($v_fd[$i]);
				
				if( !empty( $tmp_g_inf ) )
					$mbof->appendChild( createGroupNode($mbof,$tmp_g_inf) );
			}
			
			if($mbof->hasChildNodes())
				$g_node->appendChild($mbof);
		}
		else if($k_fd == "memberof" && count($v_fd) < 1)
		{
			//Si le groupe n'est membre d'aucun groupe : Rien à faire
		}
		else
			$g_node->appendChild($out_xml->createElement($k_fd,$v_fd));
	}
	
	return $g_node;
}

function groupInfo($group_dn)
{
	global $ad, $group_fields, $dn_and_filter;
	
	if(!empty($dn_and_filter))
	foreach($dn_and_filter as $dn_fil)
	{
		$tmp_g_inf = $ad->group_info($group_dn,array_values($group_fields),$dn_fil['id'],$dn_fil['group']);
			
		if( !empty($tmp_g_inf) )
		{
			$group_node = array();
			foreach($group_fields as $k_gf => $v_gf)
				$group_node[$k_gf] = $tmp_g_inf[$v_gf];
		
			return $group_node;
		}
	}
	return null;
}

//**********************************//
//				USER	     		//
//**********************************//

if(!empty($dn_and_filter))
foreach($dn_and_filter as $dn_fil)
{
	//Pour chaque DN on extrait les utilisateurs comme feuilles du DN

	$dn = $out_xml->createElement('dn');
	$dn->setAttribute("id",$dn_fil['id']);
	$dns->appendChild($dn);
	
	$list_users = array();
	$list_users = $ad->all_users(array_values($user_fields),$dn_fil['id'],$dn_fil['user']);
	
	$user_node = array();
	foreach($list_users as $user)
	{
		foreach($user_fields as $k_uf => $v_uf)
			$user_node[$k_uf] = $user[$v_uf];
		
		$dn->appendChild(createUserNode($dn,$user_node));
	}
	unset($user_node);
}



//**********************************//
//			TYPE OF GROUP	 		//
//**********************************//

$xp_out_xml = new domxpath($out_xml);

if(!empty($dn_and_filter))
foreach($dn_and_filter as $dn_fil)
{
	//Pour chaque groupe present dans chaque DN on met à jour les groupes du xml
	
	$group_users = $ad->all_groups(array_values($group_fields),$dn_fil['id'],$dn_fil['group']);
	
	if(!empty($group_users))
	foreach($group_users as $group)
	{
		$group_node = array();
		
		foreach($group_fields as $k_gf => $v_gf)
			$group_node[$k_gf] = $group[$v_gf];
	
		$update_type = $xp_out_xml->query("//group[@ext_id='".$group_node['ext_id']."']");
		
		foreach($update_type as $ut)
		{
			$ut->setAttribute("type",$dn_fil["type"]);
		}
		
		unset($group_node);
	}
}

//**********************************//
//			SAVE XML FILE     		//
//**********************************//
if(file_exists($out_xml_file))
{
	unlink(dirname($out_xml_file)."/".basename($out_xml_file));
	echo "Old file ".basename($out_xml_file)." deleted\n";
}

$out_xml->formatOutput = true;
$out_xml->normalize();
$out_xml->save($out_xml_file);

echo "File ".basename($out_xml_file)." created\n";
?>