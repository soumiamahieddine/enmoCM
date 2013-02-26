<?php
/*
*   Copyright 2008, 2013 Maarch
*
*   This file is part of Maarch Framework.
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
*   along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Script used by an Ajax autocompleter object to get the contacts data (from users or contacts)
*
* @file autocomplete_contacts.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/
require_once('core/class/class_request.php');

$req = new request();
$req->connect();

if (empty($_REQUEST['table'])) {
    exit();
}
$table = $_REQUEST['table'];

if ($table == 'users') {
    $select = array();
    $select[$_SESSION['tablename']['users']]= array('lastname', 'firstname', 'user_id');
    $where = " (lower(lastname) like lower('%".$req->protect_string_db($_REQUEST['Input'])."%') "
        ."or lower(firstname) like lower('%".$req->protect_string_db($_REQUEST['Input'])."%') "
        ."or user_id like '%".$req->protect_string_db($_REQUEST['Input'])."%') and (status = 'OK' or status = 'ABS') and enabled = 'Y'";
    $other = 'order by lastname, firstname';
    $res = $req->select($select, $where, $other, $_SESSION['config']['databasetype'], 11,false,"","","", false);
    echo "<ul>\n";
    for ($i=0; $i< min(count($res), 10)  ;$i++) {
        echo "<li>".$req->show_string($res[$i][0]['value']).', '.$req->show_string($res[$i][1]['value']).' ('.$res[$i][2]['value'].")</li>\n";
    }
    if (count($res) == 11) {
            echo "<li>...</li>\n";
    }
    echo "</ul>";
} elseif ($table == 'contacts') {
    $timestart=microtime(true);
   
   if (isset($_REQUEST['contact_type']) && $_REQUEST['contact_type'] <> '') {
       $contactTypeRequest = " AND contact_type = '" . $_REQUEST['contact_type'] . "'";
   }
   
    $args = explode(' ', $_REQUEST['Input']);
    $args[] = $_REQUEST['Input'];
    $num_args = count($args);
    if ($num_args == 0) return "<ul></ul>"; 
       
    $query = "SELECT result, SUM(confidence) AS score, count(1) AS num FROM (";
    
    $subQuery = 
        "SELECT "
            . "(CASE "
                . " WHEN is_corporate_person = 'Y' THEN society"
                . " WHEN is_corporate_person = 'N' THEN UPPER(lastname) || ' ' || firstname "
            . " END) || ' (' || contact_id || ')' AS result, "
            . " %d AS confidence"
        . " FROM contacts"
        . " WHERE (user_id = '' OR user_id IS NULL OR user_id = '".$req->protect_string_db($_SESSION['user']['UserId'])."' ) "
            . " AND enabled = 'Y' "
            . $contactTypeRequest
            . " AND ("
                . " LOWER(lastname) LIKE LOWER('%s')"
                . " OR LOWER(firstname) LIKE LOWER('%s')"
                . " OR LOWER(society) LIKE LOWER('%s')"
            .")";
    
    $queryParts = array();
    foreach($args as $arg) {
        $arg = $req->protect_string_db($arg);
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
    $query .= ") matches" 
        . " GROUP BY result "
        . " ORDER BY score DESC, result ASC";
    
    $req->query($query);
    $nb = $req->nb_result();
    
    $m = 30;
    if ($nb >= $m) $l = $m;
    else $l = $nb;
    
    $timeend=microtime(true);
    $time = number_format(($timeend-$timestart), 3);

    $found = false;
    echo "<ul id=\"autocomplete_contacts_ul\" title='$nb contacts'>";
    for ($i=0; $i<$l; $i++) {
        $res = $req->fetch_object();
        $score = round($res->score / $num_args);
        if ($i%2==1) $color = 'LightYellow';
        else $color = 'white';
        echo "<li style='font-size:8pt; background-color:$color;' title='confiance:".$score."%'>". $res->result ."</li>";
    }
    echo "</ul>";
    if ($nb > $m)
        echo "<p align='left' style='background-color:LemonChiffon;' title=\"La liste n'a pas pu être affichée intégralement, veuillez compléter votre recherche.\" >...</p>";
}
