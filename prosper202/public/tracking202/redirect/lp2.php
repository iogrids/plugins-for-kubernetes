<?php 
 if ($_SERVER['REQUEST_METHOD'] === 'GET') { //output image if using image method
    header("content-type: image/gif");
    header('Content-Length: 43');
    header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
    header('Expires: Sun, 02 Feb 2002 02:02:00 GMT'); // Date in the past
    header("Pragma: no-cache");
    header('P3P: CP="Prosper202 does not have a P3P policy"');
    echo base64_decode("R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==");
} 

ob_start();
ignore_user_abort(true);
header('Transfer-Encoding:chunked');
ob_flush();
flush(); 
$vars=$_GET;

#only allow numeric lpip
if(isset($_REQUEST['lpip'])){
	$lpip = $_REQUEST['lpip'];
}else{
	die(); //or wait to figure out the lp being used
}



if (!is_numeric($lpip)) die();

# check to see if mysql connection works, if not fail over to cached stored redirect urls
include_once(str_repeat("../", 2).'202-config/connect2.php'); 
include_once(str_repeat("../", 2).'202-config/class-dataengine-slim.php');

if(isset($_REQUEST['landing_page_type'])){
	$mysql['landing_page_type'] = $db->real_escape_string($_REQUEST['landing_page_type']);
}

if(isset($_REQUEST['acip'])){
	$mysql['acip'] = $db->real_escape_string($_REQUEST['acip']);
}
if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
    $strProtocol = 'https';
} else {
    $strProtocol = 'http';
}

$mysql['landing_page_id_public'] = $db->real_escape_string($lpip);
$tracker_sql = "SELECT 202_landing_pages.user_id,
						202_landing_pages.landing_page_id,
						202_landing_pages.landing_page_id_public,
						202_aff_campaigns.aff_campaign_id,
						202_aff_campaigns.aff_campaign_rotate,
						202_aff_campaigns.aff_campaign_url,
						202_aff_campaigns.aff_campaign_url_2,
						202_aff_campaigns.aff_campaign_url_3,
						202_aff_campaigns.aff_campaign_url_4,
						202_aff_campaigns.aff_campaign_url_5,
						202_aff_campaigns.aff_campaign_payout,
						202_aff_campaigns.aff_campaign_cloaking
				FROM    202_landing_pages, 202_aff_campaigns
				WHERE   202_landing_pages.landing_page_id_public='" . $mysql['landing_page_id_public']. "'";

if($mysql['landing_page_type']!=1) //if it's slp add the WHERE
{
    $tracker_sql .= " AND     202_aff_campaigns.aff_campaign_id = 202_landing_pages.aff_campaign_id";
}elseif($mysql['landing_page_type'] == 1 && isset($mysql['acip'])) //if it's alp add the WHERE for acip
{
	$tracker_sql .= " AND     202_aff_campaigns.aff_campaign_id_public =". $mysql['acip'];
}

$tracker_row = memcache_mysql_fetch_assoc($db, $tracker_sql);

if (!$tracker_row) { die(); }

if ($memcacheWorking) {  

	$url = $tracker_row['aff_campaign_url']."&subid=p202";
	$tid = $lpip;

	$getKey = $memcache->get(md5('lp_'.$tid.systemHash()));
	if($getKey === false){
		$setUrl = setCache(md5('lp_'.$tid.systemHash()), $url, 0);
	}
}

//grab the GET variables from the LANDING PAGE
$landing_page_site_url_address_parsed = parse_url($_SERVER['HTTP_REFERER']);  
parse_str($landing_page_site_url_address_parsed['query'], $_GET);       

if ($_REQUEST['t202id']) { 
	//grab tracker data if avaliable
	$mysql['tracker_id_public'] = $db->real_escape_string($_GET['t202id']);

	$tracker_sql2 = "SELECT  text_ad_id,
							ppc_account_id,
							click_cpc,
							click_cloaking
					FROM    202_trackers
					WHERE   tracker_id_public='".$mysql['tracker_id_public']."'";   
	
	$tracker_row2 = memcache_mysql_fetch_assoc($db, $tracker_sql2);
	if ($tracker_row2) {
		$tracker_row = array_merge($tracker_row,$tracker_row2);
	}
}else{
	echo "no t202id";
}

//INSERT THIS CLICK BELOW, if this click doesn't already exisit

//get mysql variables 
$mysql['user_id'] = $db->real_escape_string($tracker_row['user_id']);
$mysql['aff_campaign_id'] = $db->real_escape_string($tracker_row['aff_campaign_id']);
$mysql['ppc_account_id'] = $db->real_escape_string($tracker_row['ppc_account_id']);
$mysql['click_cpc'] = $db->real_escape_string($tracker_row['click_cpc']);
$mysql['click_payout'] = $db->real_escape_string($tracker_row['aff_campaign_payout']);
$mysql['click_time'] = time();

$mysql['landing_page_id'] = $db->real_escape_string($tracker_row['landing_page_id']);
$mysql['text_ad_id'] = $db->real_escape_string($tracker_row['text_ad_id']);

//cloaking is auto on for raw links
	$mysql['click_cloaking'] = 1;

$redirect_site_url = rotateTrackerUrl($db, $tracker_row); 
$click_id = $_REQUEST['click_id'];
if($click_id=='' || !is_numeric($click_id)){
	if(null !== (getCookie202('tracking202subid_a_'.$tracker_row['aff_campaign_id']))) {
		$click_id = getCookie202('tracking202subid_a_'.$tracker_row['aff_campaign_id']);
		$mysql['click_id'] = $db->real_escape_string($row['click_id']);
	}else{
		$mysql['click_id'] = $db->real_escape_string($click_id);	
	}
       
}
else{
    $mysql['click_id'] = $db->real_escape_string($click_id);
}
//$mysql['click_id'] = $db->real_escape_string($click_id);
$mysql['click_out'] = 1;

if(isset($_REQUEST['click_outbound_site_url'])){
	$mysql['click_outbound_site_url_id'] = INDEXES::get_site_url_id($db, $_REQUEST['click_outbound_site_url']);
	if(isset($mysql['click_outbound_site_url_id'])){
		$sql="UPDATE  202_clicks_site
			 SET click_outbound_site_url_id='" . $mysql['click_outbound_site_url_id']. "' 
			WHERE `click_id`= ".$mysql['click_id'];
		$db->query($sql);
		unset($sql);	
		}
}

//update the campaign id for click outs
if(isset($mysql['acip']) && isset($mysql['click_id'])){
	
		$sql="UPDATE  202_clicks
			 SET aff_campaign_id='" . $mysql['aff_campaign_id']. "' 
			WHERE `click_id`= ".$mysql['click_id'];
		$db->query($sql);
		unset($sql);

}

$update_sql = "
	UPDATE
		202_clicks_record
	SET
		click_out='".$mysql['click_out']."',
		click_cloaking='".$mysql['click_cloaking']."'
	WHERE
		click_id='".$mysql['click_id']."'";

$click_result = $db->query($update_sql);

//set dirty hour
$de = new DataEngine();
$data=($de->setDirtyHour($mysql['click_id']));
die();