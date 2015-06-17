<?php

require_once('core/core_tables.php');
require_once('core/class/class_db.php');

require_once('modules/rss/rss_tables_definition.php');
require_once('modules/rss/lang/fr.php');
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
$query = "select * from " . _NOTIF_RSS_STACK_TABLE_NAME . " rss "
	. " left join " . _NOTIF_EVENT_STACK_TABLE_NAME . " events "
	. " on rss_event_stack_sid = event_stack_sid "
	. " left join " . USERS_TABLE . " users "
	. " on users.user_id=events.user_id "
    . " where rss_user_id = '".$_REQUEST['user']."'";
	// Add date diff on exec_date VS current_date - 30
$db->query($query);

while($item = $db->fetch_object()) {
    $rss_feed['guid'] = $item->rss_event_stack_sid;
    $rss_feed['title'] = $item->event_info;
    $rss_feed['link'] = $item->rss_event_url;
    $rss_feed['description'] = $item->event_info;
    $rss_feed['pubDate'] = $item->event_date;
	$rss_feed['user'] = $item->firstname . ' ' . $item->lastname;
    $rss_feeds[] = $rss_feed;
}

/****************************************************************
* USER SUBSCRIPTIONS TO PAGES (detail, folder)
*****************************************************************/
// Select subscriptions
$query = "select * from " . RSS_SUBSCRIPTIONS_TABLE_NAME
    . " where rss_user_id = '".$_REQUEST['user']."'";
$db->query($query);

// Get excluded event_ids from config
$excl_event_ids = "' '";

// Get history events queries
$rss_subscriptions = array();
while($rss_subscription = $db->fetch_object()) {
    $rss_query_select = "h.*, u.firstname, u.lastname";
    $rss_query_table = HISTORY_TABLE . ' h left join users u on u.user_id=h.user_id' ;
    $rss_query_where = "table_name = '" . $rss_subscription->rss_table_name 
        . "' and record_id = '" . $rss_subscription->rss_record_id
        . "' and event_id not in (" . $excl_event_ids
        . ")";
    $rss_query_other = "order by event_date desc"; 
    $rss_subscription->query = $db->limit_select(0, 50, $rss_query_select, $rss_query_table, $rss_query_where, $rss_query_other);
    $rss_subscriptions[] = $rss_subscription;
}

// Get history events for user
foreach($rss_subscriptions as $rss_subscription) {
    $db->query($rss_subscription->query);
    while($item = $db->fetch_object()) {
        $rss_feed['guid'] = $item->id;
        $rss_feed['title'] = $item->info;
        $rss_feed['link'] = $rss_subscription->rss_url;
        $rss_feed['description'] = $item->info;
        $rss_feed['pubDate'] = $item->event_date;
		$rss_feed['user'] = $item->firstname . ' ' . $item->lastname;
        $rss_feeds[] = $rss_feed;
    }
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
$myXML->writeAttribute('xmlns:media', "http://search.yahoo.com/mrss/");

    $myXML->startElement('channel');
		// Header
        $myXML->writeElement('title', 'Maarch Entreprise RSS feeds');
        $myXML->writeElement('link', $_SESSION['config']['businessappurl']);
        $myXML->writeElement('description', _RSS_FEEDS_FOR_USER . ' ' . $_REQUEST['user']);
		
		$myXML->writeElement('pubDate', date('d/m/Y H:i:s'));	
        //$myXML->writeElement('lastBuildDate', date('d/m/Y H:i:s'));	
		$myXML->writeElement('image', $_SESSION['config']['businessappurl'] . 'static.php?filename=favicon.png');	
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