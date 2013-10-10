<?php

require_once('core/core_tables.php');
require_once('core/class/class_db.php');
require_once('modules/notifications/lang/fr.php');
require_once('modules/notifications/notifications_tables_definition.php');
require_once('modules/notifications/class/notifications_controler.php');


//load Maarch session vars
$_SESSION['config']['app_id'] = $_SESSION['businessapps'][0]['appid'];
require_once('apps' 
	. DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . 'class' 
	. DIRECTORY_SEPARATOR . 'class_business_app_tools.php'
);
$businessAppTools = new business_app_tools();
$businessAppTools->build_business_app_config();

// Controler
$db = new dbquery();
$db->connect();
$rss_feeds = array();
/****************************************************************
* NOTIFICATION SUBSCRIPTIONS
*****************************************************************/
// Select notifications for RSS
$select = '*';
$tables = _NOTIF_RSS_STACK_TABLE_NAME . " rss "
	. " left join " . _NOTIF_EVENT_STACK_TABLE_NAME . " events "
	. " on rss_event_stack_sid = event_stack_sid "
	. " left join " . USERS_TABLE . " users "
	. " on users.user_id=events.user_id "
	. " left join " . _NOTIFICATIONS_TABLE_NAME . " notifs "
	. " on events.notification_sid = notifs.notification_sid ";
$where = "rss_user_id = '".$_REQUEST['user']."'";
	// Add date diff on exec_date VS current_date - 30

$query = $db->limit_select(0, 50, $select, $tables, $where);
$db->query($query);
while($item = $db->fetch_object()) {
    $rss_feed['guid'] = $item->rss_event_stack_sid;
    $rss_feed['title'] = $item->description;
    $rss_feed['link'] = $item->rss_event_url;
    $rss_feed['description'] = $item->event_info;
    $rss_feed['pubDate'] = $item->event_date;
	$rss_feed['user'] = $item->firstname . ' ' . $item->lastname;
    $rss_feeds[] = $rss_feed;
}

/****************************************************************
* GENERATE XML STREAM
*****************************************************************/
$myXML = new xmlWriter();
$myXML->openMemory();
$myXML->setIndent(true);
$myXML->startDocument('1.0', 'ISO-8859-1');
$myXML->startElement('rss');
$myXML->writeAttribute('version', '2.0');

    $myXML->startElement('channel');
		// Header
        $myXML->writeElement('title', 'Fils RSS Maarch Entreprise');
        $myXML->writeElement('link', $_SESSION['config']['businessappurl']);
        $myXML->writeElement('description', 'Fils RSS de notifications de l\'application Maarch Entreprise');
		
		$myXML->writeElement('pubDate', date('d/m/Y H:i:s'));	
        //$myXML->writeElement('lastBuildDate', date('d/m/Y H:i:s'));	
		//$myXML->writeElement('image', 'http://localhost/maarch_trunk/favicon.ico');	
		$myXML->writeElement('language', 'Fr');	
		//$myXML->writeElement('enclosure', 'some multimedia data');	
		
		for($i=0; $i < count($rss_feeds); $i++) {
            $rss_feed = $rss_feeds[$i];
            $myXML->startElement('item');
                $myXML->writeElement('title', $rss_feed['title']);
				$myXML->writeElement('link', $rss_feed['link']);
				$myXML->writeElement('pubDate', $rss_feed['pubDate']);
				$myXML->writeElement('description', 
					$rss_feed['description'] 
					. ' (' . $rss_feed['user'] 
					. ' - ' . $db->format_date($rss_feed['pubDate']) . ')');
				$myXML->writeElement('guid', $rss_feed['guid']);
				//$myXML->writeElement('guid', uniqid());
				
				$myXML->writeElement('author', $rss_feed['author']);
				$myXML->writeElement('category', $rss_feed['category']);
				$myXML->writeElement('comments', $rss_feed['comments']);
				// Attachments
				/*$myXML->startElement('enclosure');
					$myXML->writeAttribute('url', 'http://localhost/maarch_trunk/apps/maarch_entreprise/index.php?display=true&page=rss_enclosure&module=rss');
				$myXML->endElement();*/
            $myXML->endElement();	
        }
    $myXML->endElement();	
$myXML->endElement();

$rss_xml = $myXML->outputMemory();
/*
$xmlHdl = fopen($_SESSION['config']['corepath'] . 'modules' 
				. DIRECTORY_SEPARATOR . 'rss' 
				. DIRECTORY_SEPARATOR . 'feeds'
				. DIRECTORY_SEPARATOR . $_REQUEST['user'] . '.xml', 'w');
fwrite($xmlHdl, $rss_xml);
fclose($xmlHdl);
*/
echo $rss_xml;

?>
