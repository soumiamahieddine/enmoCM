<?php

require_once('core/class/class_core_tools.php');
$Core_Tools = new core_tools;
$Core_Tools->load_lang();

$return = '';

if (isset($_REQUEST['identifier'])) {
    $status = 0;
    $return .= '<td>';
        $return .= '<div align="center">';
            $return .= '<table width="100%">';

                $db = new dbquery();
                $db->connect();

                $query = "select ";
     $query .= "DISTINCT(notes.id), ";
     $query .= "user_id, ";
     $query .= "date_note, ";
     $query .= "note_text ";
    $query .= "from ";
     $query .= "notes "; 
    $query .= "left join "; 
     $query .= "note_entities "; 
    $query .= "on "; 
     $query .= "notes.id = note_entities.note_id ";
    $query .= "where ";
      // $query .= "tablename = 'res_letterbox' ";
     // $query .= "AND "; 
      $query .= "coll_id = '".$_SESSION['collection_id_choice']."' ";
     $query .= "AND ";
      $query .= "identifier = " . $_REQUEST['identifier'] . " ";
     $query .= "AND ";
      $query .= "( ";
        $query .= "( ";
          $query .= "item_id IN (";
          
           foreach($_SESSION['user']['entities'] as $entitiestmpnote) {
            $query .= "'" . $entitiestmpnote['ENTITY_ID'] . "', ";
           }
           $query = substr($query, 0, -2);
          
          $query .= ") ";
         $query .= "OR "; 
          $query .= "item_id IS NULL ";
        $query .= ") ";
       $query .= "OR ";
        $query .= "user_id = '" . $_SESSION['user']['UserId'] . "' ";
      $query .= ") ";

                $db->query($query);

                $fetch = '';
                while ($return_db = $db->fetch_object()) {
                    // get lastname and firstname for user_id
                    $db2 = new dbquery;
                    $db2->connect();
                    $db2->query("SELECT lastname, firstname FROM users WHERE user_id ='" . $return_db->user_id . "'");
                    while ($user_db = $db2->fetch_object()) {
                        $lastname = $user_db->lastname;
                        $firstname = $user_db->firstname;
                    }

                    $return .= '<tr>';
                        $return .= '<td style="background: transparent; border: 1px dashed rgb(200, 200, 200);">';
                            $return .= '<blockquote style="padding: 1px;">';
                                $return .= '<div style="text-align: right; background-color: rgb(230, 230, 230); padding: 5px;">';
                                    $return .= 'Par: ';
                                    $return .= $firstname . ' ' . $lastname;
                                    $return .= ', ';
                                    $return .= $return_db->date_note;
                                $return .= '</div>';
                                $return .= '<br />';
                                $return .= '<div>';
                                    $note_text = str_replace(array("\r", "\n"), array("<br />", "<br />"), $return_db->note_text);
                                    $return .= str_replace('<br /><br />', '<br />', $note_text);
                                $return .= '</div>';
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