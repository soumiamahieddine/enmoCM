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
                      
                $query = "SELECT c.is_corporate_person, c.is_private, c.contact_firstname, c.contact_lastname, c.firstname, c.lastname, c.society, c.society_short, c.contact_purpose_label, c.address_num, c.address_street, c.address_complement, c.address_town, c.address_postal_code, c.address_country ";
                        $query .= "FROM view_contacts c, contacts_res cres  ";
                        $query .= "WHERE cres.coll_id = 'letterbox_coll' AND cres.res_id = ".$_REQUEST['res_id']." AND cast (c.contact_id as varchar) = cres.contact_id AND c.ca_id = cres.address_id";
                        
                $db->query($query);

                $fetch = '';
                while ($res = $db->fetch_object()) {

                    $return .= '<tr>';
                        $return .= '<td style="background: transparent; border: 0px dashed rgb(200, 200, 200);">';
                            
                                $return .= '<div style="text-align: left; background-color: rgb(230, 230, 230); padding: 3px; margin-left: 20px; margin-top: -6px;">';
                                    $return .= '(contact) ';

                                    if ($res->is_corporate_person == 'Y') {
                                        $return .= $res->society . ' ' ;
                                        if (!empty ($res->society_short)) {
                                            $return .= '('.$res->society_short.') ';
                                        }
                                    } else {
                                        $return = $res->contact_lastname . ' ' . $res->contact_firstname . ' ';
                                        if (!empty ($res->society)) {
                                            $return .= '(' .$res->society . ') ';
                                        }                        
                                    }
                                    if ($res->is_private == 'Y') {
                                        $return .= '('._CONFIDENTIAL_ADDRESS.')';
                                    } else {
                                        $return .= "- " . $res->contact_purpose_label." : ";
                                        if (!empty($res->lastname) || !empty($res->firstname)) {
                                            $return .= $res->lastname . ' ' . $res->firstname;
                                        }
                                        if (!empty($res->address_num) || !empty($res->address_street) || !empty($res->address_town) || !empty($res->address_postal_code)) {
                                            $return .= ', '.$res->address_num .' ' . $res->address_street .' ' . $res->address_postal_code .' ' . strtoupper($res->address_town);
                                        }         
                                    }
          
                                $return .= '</div>';
                            
                        $return .= '</td>';
                    $return .= '</tr>';
                }
                
                $db = new dbquery();
                $db->connect();
                      
                $query = "SELECT u.firstname, u.lastname, u.user_id ";
                        $query .= "FROM users u, contacts_res cres  ";
                        $query .= "WHERE cres.coll_id = 'letterbox_coll' AND cres.res_id = ".$_REQUEST['res_id']." AND cast (u.user_id as varchar) = cres.contact_id";
                        
                $db->query($query);

                $fetch = '';
                while ($res = $db->fetch_object()) {

                    $return .= '<tr>';
                        $return .= '<td style="background: transparent; border: 0px dashed rgb(200, 200, 200);">';
                            
                                $return .= '<div style="text-align: left; background-color: rgb(230, 230, 230); padding: 3px; margin-left: 20px; margin-top: -6px;">';
                                    $return .= ' (utilisateur) ' . $res->firstname . ' ' . $res->lastname;
                                                
                                $return .= '</div>';
                                //$return .= '<br />';
                            
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