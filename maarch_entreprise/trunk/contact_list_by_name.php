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
/*
$db->query("select is_corporate_person, society, lastname, firstname, contact_id from ".$_SESSION['tablename']['contacts']
	." where (lower(lastname) like lower('%".$db->protect_string_db($_REQUEST['what'])."%') "
	//." or lower(firstname) like lower('".$db->protect_string_db($_REQUEST['what'])."%') "
	." or lower(society) like lower('%".$db->protect_string_db($_REQUEST['what'])."%')) and enabled = 'Y' order by society, lastname, firstname");

//$db->show();
while($line = $db->fetch_object())
{
	if($line->is_corporate_person == "Y")
	{
		array_push($listArray, $db->show_string($line->society).", ".$db->show_string($line->lastname)." ".$db->show_string($line->firstname)." (contact:".$line->contact_id.")");
	}
	else
	{
		array_push($listArray, $db->show_string($line->society).", ".$db->show_string($line->lastname)." ".$db->show_string($line->firstname)." (contact:".$line->contact_id.")");
	}
}
echo "<ul>\n";
$authViewList = 0;
foreach($listArray as $what)
{
	if($authViewList >= 30)
	{
		$flagAuthView = true;
	}
	echo "<li>".$what."</li>\n";
	if(isset($flagAuthView) && $flagAuthView)
	{
		echo "<li>...</li>\n";
		break;
	}
		$authViewList++;
}
echo "</ul>";*/
    $timestart=microtime(true);
   
    $searchParts = explode(' ', $_REQUEST['what']);
    $nb_search = count($searchParts);
    if($nb_search == 0) return "<ul></ul>"; 
       
    $query = "SELECT result, COUNT(*) AS score FROM (";
    $queryParts = array();
    
    foreach($searchParts as $search) {
        $search = $db->protect_string_db($search);
        $queryParts[] .= "SELECT "
            . "(CASE is_corporate_person"
            . " WHEN 'Y' THEN society"
            . " WHEN 'N' THEN UPPER(lastname) || ' ' || firstname "
            . " END)"
            . " || '(contact:' || contact_id || ')' AS result, user_id, enabled"
            . " FROM contacts"
            . " WHERE ("
                . " LOWER(lastname) LIKE LOWER('%$search%')"
                . " or LOWER(firstname) LIKE LOWER('%$search%')"
                . " or LOWER(society) LIKE LOWER('%$search%')"
            .")";
        $queryParts[] .= "SELECT "
            . "(CASE is_corporate_person"
            . " WHEN 'Y' THEN society"
            . " WHEN 'N' THEN UPPER(lastname) || ' ' || firstname "
            . " END)"
            . " || '(contact:' || contact_id || ')' AS result, user_id, enabled"
            . " FROM contacts"
            . " WHERE ("
                . " LOWER(lastname) LIKE LOWER('%$search')"
                . " or LOWER(firstname) LIKE LOWER('%$search')"
                . " or LOWER(society) LIKE LOWER('%$search')"
            .")";
        $queryParts[] .= "SELECT "
            . "(CASE is_corporate_person"
            . " WHEN 'Y' THEN society"
            . " WHEN 'N' THEN UPPER(lastname) || ' ' || firstname "
            . " END)"
            . " || '(contact:' || contact_id || ')' AS result, user_id, enabled"
            . " FROM contacts"
            . " WHERE ("
                . " LOWER(lastname) = LOWER('$search')"
                . " or LOWER(firstname) = LOWER('$search')"
                . " or LOWER(society) = LOWER('$search')"
            .")";
    }
    $query .= implode (' UNION ALL ', $queryParts);
    $query .= ") as matches" 
        . " WHERE (user_id = '' OR user_id IS NULL OR user_id = '".$db->protect_string_db($_SESSION['user']['UserId'])."' ) "
        . " AND enabled = 'Y' "
        . " GROUP BY result "
        . " ORDER BY score DESC";
    
    $db->query($query);
    $nb = $db->nb_result();
    if($nb >= 30) $l = 30;
    else $l = $nb;
    
    $timeend=microtime(true);
    $time = number_format(($timeend-$timestart), 3);

    $found = false;
    echo "<ul title=".$time.">";
    foreach($listArray as $what) {
        echo "<li>". $what ."</li>";
    }
    for($i=0; $i<$l; $i++) {
        $res = $db->fetch_object();
        $score = round($res->score / $nb_search * 100 / 3);
        if($score == 100) $found = true;
        if($found == $score < 100) break;
        echo "<li title='confiance:".$score."%'>". $res->result ."</li>";
    }
    if($nb >= 30)
        echo "<li>...</li>";    
    echo "</ul>";
