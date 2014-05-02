<?php

require_once('core/class/class_core_tools.php');
$Core_Tools = new core_tools;
$Core_Tools->load_lang();

$return = '';

if (isset($_REQUEST['res_id'])) {
    $status = 0;
    $return .= '<td>';
        $return .= '<div align="center">';
            $return .= '<table width="100%">';

                $db = new dbquery();
                $db->connect();
					  
				$query = "select c.firstname, c.lastname, c.society, c.address_num, c.address_street, c.address_complement, c.address_town, c.address_postal_code, c.address_country ";
						$query .= "from contacts c, contacts_res cres  ";
						$query .= "where cres.coll_id = 'letterbox_coll' AND cres.res_id = ".$_REQUEST['res_id']." AND cast (c.contact_id as varchar) = cres.contact_id";
						
                $db->query($query);

                $fetch = '';
                while ($res = $db->fetch_object()) {

                    $return .= '<tr>';
                        $return .= '<td style="background: transparent; border: 1px dashed rgb(200, 200, 200);">';
                            $return .= '<blockquote >';
                                $return .= '<div style="text-align: left; background-color: rgb(230, 230, 230); padding: 5px;">';
                                    $return .= $res->firstname . ' ' . $res->lastname . ' ' . $res->society;
									
									if(isset($res->address_num) && !empty($res->address_num)){
										$return .= ': ' . $res->address_num;
									}									
									if(isset($res->address_street) && !empty($res->address_street)){
										$return .= ', ' . $res->address_street;
									}									
									if(isset($res->address_complement) && !empty($res->address_complement)){
										$return .= ', ' . $res->address_complement;
									}									
									if(isset($res->address_postal_code) && !empty($res->address_postal_code)){
										$return .= ', ' . $res->address_postal_code;
									}									
									if(isset($res->address_town) && !empty($res->address_town)){
										$return .= ' ' . $res->address_town;
									}									
									if(isset($res->address_country) && !empty($res->address_country)){
										$return .= ', ' . $res->address_country;
									}
												
                                $return .= '</div>';
                                //$return .= '<br />';
                            $return .= '</blockquote>';
                        $return .= '</td>';
                    $return .= '</tr>';
                }
            $return .= '</table>';
            $return .= '<br />';
        $return .= '</div>';
    $return .= '</td>';
} else {
    $status = 1;
    $return .= '<td colspan="6" style="background-color: red;">';
        $return .= '<p style="padding: 10px; color: black;">';
            $return .= 'Erreur lors du chargement des notes';
        $return .= '</p>';
    $return .= '</td>';
}

echo "{status : " . $status . ", toShow : '" . addslashes($return) . "'}";
exit ();