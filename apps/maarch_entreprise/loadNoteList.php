<?php
/*
*
*    Copyright 2014-2015 Maarch
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
* @brief   load Notes in results list 
*
* @file
* @author <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/

require_once('core/class/class_core_tools.php');
require_once "modules" . DIRECTORY_SEPARATOR . "notes" . DIRECTORY_SEPARATOR
    . "class" . DIRECTORY_SEPARATOR
    . "class_modules_tools.php";
$Core_Tools = new core_tools;
$Core_Tools->load_lang();
$Core_Tools->test_user();

$return = '';

if (isset($_REQUEST['identifier'])) {
    $status = 0;
    $return .= '<td>';
        $return .= '<div align="center">';
            $return .= '<table width="97%%">';

        $db = new Database();

        $query = "SELECT ";
     $query .= "DISTINCT(notes.id), ";
     $query .= "user_id, ";
     $query .= "date_note, ";
     $query .= "note_text ";
    $query .= "FROM ";
     $query .= "notes "; 
    $query .= "left join "; 
     $query .= "note_entities "; 
    $query .= "on "; 
     $query .= "notes.id = note_entities.note_id ";
    $query .= "WHERE ";
      $query .= "coll_id = ? ";
      $arrayPDO = array($_SESSION['collection_id_choice']);
     $query .= "AND ";
      $query .= "identifier = ? ";
      $arrayPDO = array_merge($arrayPDO, array($_REQUEST['identifier']));
     $query .= "AND ";
      $query .= "( ";
        $query .= "( ";
          $query .= "item_id IN (";
          
           foreach($_SESSION['user']['entities'] as $entitiestmpnote) {
            $query .= "?, ";
            $arrayPDO = array_merge($arrayPDO, array($entitiestmpnote['ENTITY_ID']));
           }

            if ($_SESSION['user']['UserId'] == 'superadmin') {
                $query .= " null ";
            } else {
                $query = substr($query, 0, -2);
            }
          
          $query .= ") ";
         $query .= "OR "; 
          $query .= "item_id IS NULL ";
        $query .= ") ";
       $query .= "OR ";
        $query .= "user_id = '" . $_SESSION['user']['UserId'] . "' ";
      $query .= ") ";
      $query .= " order by date_note desc";

                $stmt = $db->query($query, $arrayPDO);

                $fetch = '';
                while ($return_db = $stmt->fetchObject()) {
                    // get lastname and firstname for user_id
                  //var_dump($return_db);
                    $stmt2 = $db->query("SELECT lastname, firstname FROM users WHERE user_id =?", array($return_db->user_id));
                    while ($user_db = $stmt2->fetchObject()) {
                        $lastname = $user_db->lastname;
                        $firstname = $user_db->firstname;
                    }
                    $stmt3 = $db->query("SELECT notes.id as id, identifier, user_id, note_text, item_id, entity_label FROM notes, note_entities, entities WHERE identifier = ? AND note_id = notes.id AND entities.entity_id = note_entities.item_id and notes.id = ?", array($_REQUEST['identifier'], $return_db->id));
                    $entity_label = '';
                    $Tabentity = [];
                    while ($entity = $stmt3->fetchObject()) {
                        $Tabentity[] = $entity->entity_label;
                        $item_id = $entity->id;
                        $entity_label = $entity->entity_label;
                        }
                    $return .= '<tr>';
                        $return .= '<td style="background: transparent; padding-left:30px; padding-right:30px; border: 1px dashed rgb(200, 200, 200);">';
                            // $return .= '<blockquote style="padding: 1px;">';
                                $return .= '<div style="text-align: right; background-color: rgb(230, 230, 230); padding: 2px;">';
                                    $allEntity = '';
                                      foreach ($Tabentity as $value){
                                        $allEntity .= $value." / ";
                                      } 
                                      $notes_tools = new notes();
                                      $noteEntities = $notes_tools->getNotesEntities($return_db->id);
                                      $tabEntityLabel = [];
                                      $tabEntityId = [];
                                      $allEntities = '';
                                      $allEntitiesId = '';

                                      foreach($noteEntities as $value){
                                          $tabEntityLabel[] = $value->entity_label;
                                          $tabEntityId[] = $value->entity_id;
                                      }

                                      if(!empty($tabEntityLabel)){
                                          $allEntities = implode(', ',$tabEntityLabel);
                                          $allEntitiesId = implode(', ',$tabEntityId);
                                      }

                                    $return .= functions::xssafe($firstname) . ' ' . functions::xssafe($lastname);
                                    $return .= ', ';
                                    $return .= functions::xssafe($Core_Tools->format_date_db($return_db->date_note));

                                    if($allEntities != ''){

                                      $return .= '<i title="'.$allEntities.'" style="cursor:pointer;width:35em;text-overflow: ellipsis;color:red;clear:both;white-space: nowrap;overflow: hidden;">';
                                      $return .= ' (note restreinte)';
                                      $return .= '</i>';

                                    }
                                    
                                $return .= '</div>';

                                // $return .= '<br />';
                                if($entity_label != ''){

                                    $return .= '<div style="padding-top:2px;padding-bottom:2px;color:red;">';
                                    $note_text = str_replace(array("\r", "\n"), array("<br />", "<br />"), functions::xssafe($return_db->note_text));
                                    $return .= str_replace('<br /><br />', '<br />', $note_text);
                                    $return .= '</div>';
                                    $return .= '<div style="padding-top:2px;padding-bottom:2px;">';
                                    $return .= '<div style="cursor:pointer;width:250px;text-overflow: ellipsis;color:red;clear:both;white-space: nowrap;overflow: hidden;"><i title="'.$allEntities.'" >'._RESTRICTED_SERVICES.$allEntitiesId.'</i></div>';
                                  $return .= '</div>';

                                  }else{

                                $return .= '<div style="padding-top:2px;padding-bottom:2px;">';
                                    $note_text = str_replace(array("\r", "\n"), array("<br />", "<br />"), functions::xssafe($return_db->note_text));
                                    $return .= str_replace('<br /><br />', '<br />', $note_text);
                                    $return .= '</div>';

                                  }


                            // $return .= '</blockquote>';
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