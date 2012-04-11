<?php
/*
*    Copyright 2008,2009,2010 Maarch
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
* @brief  Contains the LinkController Class
*
*
* @file
* @author Arnaud Veber
* @date $date$
* @version $Revision$
* @ingroup core
*/

//Loads the require class
try {
    require_once('core/class/class_db.php');
    require_once('core/class/class_history.php');
} catch (Exception $e) {
    echo $e->getMessage() . ' // ';
}

class LinkController
{
    private $previousId = ' ';
    private $level = 0;

    public function formatMap($arrayToFormat, $sens)
    {
        $this->level++;
        $core = new core_tools();
        $core->test_user();
        $return = '';

        foreach ($arrayToFormat as $key => $value) {
            $infos = $this->getDocInfos($key, $_SESSION['current_basket']['coll_id']);
            $return .= '<div id="ged_'.$key.'" class="linkDiv">';
                $return .= '<table>';
                    $return .= '<tr>';
                        $return .= '<td>';
                            $return .= '<a href="index.php?display=true&dir=indexing_searching&page=view_resource_controler&id=' . $key . '" target="_blank">';
                                $return .= '<img src="static.php?filename=picto_dld.gif">';
                            $return .= '</a>';
                        $return .= '</td>';
                        $return .= '<td>';
                            $return .= '<a href="index.php?page=details&dir=indexing_searching&id='.$key.'">';
                                $return .=  '<b>'.$key.'</b>' ;
                            $return .= '</a>';
                        $return .= '</td>';
                        $return .= '<td class="barreLinks" width="2">';
                        $return .= '</td>';
                        $return .= '<td align="center">';
                            $return .= $_SESSION['mail_categories'][$infos['category_id']];
                        $return .= '</td>';
                        $return .= '<td class="barreLinks" width="2">';
                        $return .= '</td>';
                        $return .= '<td align="center">';
                                $date = explode('-', substr($infos['doc_date'], 0, 10));
                                $return .= $date[2].' '.$date[1].' '.$date[0];
                        $return .= '</td>';
                        $return .= '<td class="barreLinks" width="2">';
                        $return .= '</td>';
                        $return .= '<td align="center">';
                            $return .= '<a href="index.php?display=true&dir=indexing_searching&page=view_resource_controler&id=' . $key . '" target="_blank">';
                                $return .= $infos['subject'];
                            $return .= '</a>';
                        $return .= '</td>';
                        $return .= '<td class="barreLinks" width="2">';
                        $return .= '</td>';
                        $return .= '<td align="center">';
                                $return .= $infos['entity_label'].' ('.$infos['destination'].')';
                        $return .= '</td>';
                        $return .= '<td class="barreLinks" width="2">';
                        $return .= '</td>';
                        $return .= '<td align="center">';
                                $status = $this->getStatus($infos['status']);
                                $return .= $status;
                        $return .= '</td>';
                        if ($core->test_service('add_links', 'apps', false) && $this->level <= 1) {
                            if ($sens = 'asc') {
                                $delParent = $key;
                                $delChild = $_SESSION['doc_id'];
                            } else {
                                $delParent = $_SESSION['doc_id'];
                                $delChild = $key;
                            }
                            $return .= '<td class="barreLinks" width="2">';
                            $return .= '</td>';
                            $return .= '<td align="center">';
                                $return .= '<span onclick="';
                                  $return .= 'addLinks(';
                                    $return .= '\''.$_SESSION['config']['businessappurl'].'index.php?page=add_links&display=true\', ';
                                    $return .= '\''.$delChild.'\' ,';
                                    $return .= '\''.$delParent.'\' ,';
                                    $return .= '\'del\'';
                                  $return .= ');';
                                $return .= '">';
                                    $return .= '<img src="static.php?filename=picto_delete.gif" />';
                                $return .= '</span>';
                            $return .= '</td>';
                        }
                    $return .= '</tr>';
                $return .= '</table>';
                if (is_array($value)) {
                    $return .= $this->formatMap($value, $sens);
                }
            $return .= '</div>';
        }

        $this->level--;
        return $return;
    }

    public function getMap($parentId, $collection, $sens)
    {
        if (!empty($parentId) && !empty($collection)) {
            if ($sens == 'asc') {
                $links = $this->getLinksAsc($parentId, $collection);
            } else {
                $links = $this->getLinksDesc($parentId, $collection);
            }
            $linksArray = explode('||', $links);

            for ($i=0; $i<count($linksArray); $i++) {
                if ($linksArray[$i] != '' ) {
                    if (!preg_match("/".' ' . $linksArray[$i] . ' '."/", $this->previousId)) {
                        $this->previousId .= $parentId . ' ';
                        //echo $this->previousId . '<br />';
                        $return[$linksArray[$i]] = $this->getMap($linksArray[$i], $collection, $sens);
                    }
                } else {
                    $return = 'last';
                }
            }
        }

        return $return;
    }

    private function getLinks($parentId, $collection)
    {
        $db = new dbquery;
        $db->connect();
        $query = "SELECT res_child FROM res_linked WHERE coll_id='" . $collection . "' AND res_parent=" . $parentId;
        $result = $db->query($query);
        if ($result) {
            $i = 0;
            $links = '';
            while ($row = pg_fetch_assoc($result)) {
                $links .= $row['res_child'].'||';
                $i++;
            }
            if ($i > 0) {
                $return = substr($links, 0, -2);
            }
        } else {
            $return = 'Problème lors de la requête : '.$query;
        }

        return $return;
    }

    private function getLinksDesc($parentId, $collection)
    {
        $db = new dbquery;
        $db->connect();
        $query = "SELECT res_child FROM res_linked WHERE coll_id='" . $collection . "' AND res_parent=" . $parentId;
        $result = $db->query($query);
        if ($result) {
            $i = 0;
            $links = '';
            while ($row = pg_fetch_assoc($result)) {
                $links .= $row['res_child'].'||';
                $i++;
            }
            if ($i > 0) {
                $return = substr($links, 0, -2);
            }
        } else {
            $return = 'Problème lors de la requête : '.$query;
        }

        return $return;
    }

    private function getLinksAsc($parentId, $collection)
    {
        $db = new dbquery;
        $db->connect();
        $query = "SELECT res_parent FROM res_linked WHERE coll_id='" . $collection . "' AND res_child=" . $parentId;
        $result = $db->query($query);
        if ($result) {
            $i = 0;
            $links = '';
            while ($row = pg_fetch_assoc($result)) {
                $links .= $row['res_parent'].'||';
                $i++;
            }
            if ($i > 0) {
                $return = substr($links, 0, -2);
            }
        } else {
            $return = 'Problème lors de la requête : '.$query;
        }

        return $return;
    }

    private function getDocInfos($id, $collection)
    {
        if ($collection = 'letterbox_coll') {
            $vue = 'res_view_letterbox';
        } else {
            $vue = '';
        }
        $db = new dbquery;
        $db->connect();
        $query = "SELECT * FROM ".$vue." WHERE res_id = ".$id;
        $result = $db->query($query);
        if ($result) {
            $i = 0;
            while ($row = pg_fetch_assoc($result)) {
                $return = $row;
                $i++;
            }
        }

        return $return;
    }

    public function getStatus($status)
    {
        $db = new dbquery;
        $db->connect();
        $query = "SELECT label_status FROM status WHERE id = '" . $status . "'";
        $result = $db->query($query);
        if ($result) {
            $i = 0;
            while ($row = pg_fetch_assoc($result)) {
                $return = $row['label_status'];
                $i++;
            }
        }

        return $return;
    }

    public function nbDirectLink($id, $collection, $sens)
    {
        $i = 0;
        $db = new dbquery;
        $db->connect();
        if ($sens == 'desc' || $sens == 'all') {
            $query = "SELECT res_child FROM res_linked WHERE coll_id='" . $collection . "' AND res_parent=" . $id;
            $result = $db->query($query);
            if ($result) {
                while ($row = pg_fetch_assoc($result)) {
                    $i++;
                }
            }
        }

        if ($sens == 'asc' || $sens == 'all') {
            $query = "SELECT res_parent FROM res_linked WHERE coll_id='" . $collection . "' AND res_child=" . $id;
            $result = $db->query($query);
            if ($result) {
                while ($row = pg_fetch_assoc($result)) {
                    $i++;
                }
            }
        }

        return $i;
    }
}
