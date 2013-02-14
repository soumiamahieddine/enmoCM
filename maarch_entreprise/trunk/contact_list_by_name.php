<?php
/*
*    Copyright 2008,2009 Maarch
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
* @brief List of users for autocompletion
*
*
* @file
* @author  Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
$db = new dbquery();
$db->connect();
$listArray = array();
$db->query("select lastname, firstname, user_id from ".$_SESSION['tablename']['users']
	." where lower(lastname) like lower('%".$db->protect_string_db($_REQUEST['what'])."%') and enabled = 'Y' order by lastname, firstname");

while($line = $db->fetch_object())
{
	array_push($listArray, $db->show_string($line->lastname)." ".$db->show_string($line->firstname)." (user:".$line->user_id.")");
	//array_push($listArray, "user : [".$db->show_string($line->lastname)." ".$db->show_string($line->firstname))."]";
}
    $timestart=microtime(true);
   
    $args = explode(' ', $_REQUEST['what']);
    $args[] = $_REQUEST['Input'];
    $num_args = count($args);
    if($num_args == 0) return "<ul></ul>"; 
       
    $query = "SELECT result, SUM(confidence) AS score, count(1) as num FROM (";
    
    $subQuery = 
        "SELECT "
            . "(CASE is_corporate_person"
                . " WHEN 'Y' THEN society"
                . " WHEN 'N' THEN UPPER(lastname) || ' ' || firstname "
            . " END) || ' (contact:' || contact_id || ')' AS result, "
            . ' %d as confidence'
        . " FROM contacts"
        . " WHERE (user_id = '' OR user_id IS NULL OR user_id = '".$db->protect_string_db($_SESSION['user']['UserId'])."' ) "
            . " AND enabled = 'Y' "
            . " AND ("
                . " LOWER(lastname) LIKE LOWER('%s')"
                . " or LOWER(firstname) LIKE LOWER('%s')"
                . " or LOWER(society) LIKE LOWER('%s')"
            .")";
    
    $queryParts = array();
    foreach($args as $arg) {
        $arg = $db->protect_string_db($arg);
        # Full match of one given arg
        $expr = $arg;
        $conf = 100;
        $queryParts[] = sprintf($subQuery, $conf, $expr, $expr, $expr); 

        # Partial match (starts with)
        $expr = $arg . "%"; ;
        $conf = 34; # If found, partial match contains will also be so score is sum of both confidences, i.e. 67)
        $queryParts[] = sprintf($subQuery, $conf, $expr, $expr, $expr); 
      
        # Partial match (contains)
        $expr = "%" . $arg . "%";
        $conf = 33;
        $queryParts[] = sprintf($subQuery, $conf, $expr, $expr, $expr); 
    }
    $query .= implode (' UNION ALL ', $queryParts);
    $query .= ") as matches" 
        . " GROUP BY result "
        . " ORDER BY score DESC, result ASC";
    
    $db->query($query);
    $nb = $db->nb_result();
    $m = 30;
    if($nb >= $m) $l = $m;
    else $l = $nb;
    
    $timeend=microtime(true);
    $time = number_format(($timeend-$timestart), 3);

    $found = false;
    echo "<ul title='$l contacts found in " . $time."sec'>";
    for($i=0; $i<$l; $i++) {
        $res = $db->fetch_object();
        $score = round($res->score / $num_args);
        if($i%2==1) $color = 'LightYellow';
        else $color = 'white';
        echo "<li style='font-size: 8pt; background-color:$color;' title='confiance:".$score."%'>". $res->result ."</li>";
    }
    
    echo "</ul>";
    if($nb > $m)
        echo "<p align='left' style='background-color:LemonChiffon;' title=\"La liste n'a pas pu être affichée intégralement, veuillez compléter votre recherche.\" >...</p>";  
