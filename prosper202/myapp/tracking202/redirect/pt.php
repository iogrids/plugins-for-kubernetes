<?php
session_start();
#only allow numeric t202ids
$t202id = $_REQUEST['t202id']; 
if (!is_numeric($t202id)) die();

# check to see if mysql connection works, if not fail over to cached stored redirect urls
include_once(str_repeat("../", 2).'202-config/connect2.php'); 
include_once(str_repeat("../", 2).'202-config/class-dataengine-slim.php');

//see if it's a prefetch
$prefetch = is_prefetch();

//check if db is down and use cached redirect
processCacheRedirect();

//grab tracker data
$mysql['tracker_id_public'] = $db->real_escape_string($t202id);

$tracker_row = getTrackerDetailPT($mysql);

//// early gclid setting {
	$mysql['gclid']= $db->real_escape_string($_REQUEST['gclid']);
	$mysql['msclkid']= $db->real_escape_string($_REQUEST['msclkid']);

	getUTMParams($mysql);




$_SESSION['t202id']=$mysql['tracker_id_public'];
/**************end early gclid setting */
$gclidRow= getGclid($mysql);
if(isset($gclidRow)){
	$mysql['click_id'] = $db->real_escape_string($gclidRow['click_id']);
	$click_id = $mysql['click_id'];
	$mysql['click_time'] = $db->real_escape_string($gclidRow['click_time']);	
}
else{

//get the click id
	$mysql['click_time'] = time();
	$click_id = getClickId();
	$mysql['click_id'] = $click_id;
//insert gclid
	insertGclid($mysql);
}

$click_id_public = getClickIdPublic($click_id);
$mysql['click_id_public'] = $click_id_public; 

if($prefetch){
    //if this is  a prefetch request, send to the offer as quickly as possible
    $redirect_site_url = rotateTrackerUrl($db, $tracker_row);
    $redirect_site_url = replaceTrackerPlaceholders($db, $redirect_site_url,$click_id,$mysql);
    header('location: '.$redirect_site_url);
    die();    
}

if ($memcacheWorking) {  

	$url = $mysql['aff_campaign_url'];
	$tid = $t202id;

	$getKey = $memcache->get(md5('url_'.$tid.systemHash()));
	if($getKey === false){
		$setUrl = setCache(md5('url_'.$tid.systemHash()), $url);
	}
}
 

//set the timezone to the users timezone

date_default_timezone_set($mysql['user_timezone']);


if (!$tracker_row) { die(); }
    
// get publisher id
if (isset($_REQUEST['t202pubid'])) {
    $mysql['public_pub_id'] = $db->real_escape_string($_REQUEST['t202pubid']);
  
    $mysql['pub_id'] = getPublisher($mysql['public_pub_id']);
    if (isset($mysql['pub_id']) && $mysql['pub_id'] != '1') {
        $mysql['user_id'] = $mysql['pub_id'];
        
    }
}

//get keyword info
getKeyword($mysql);	  

//Get C1-C4 IDs
getCVars($mysql);

$mysql['gclid']= $db->real_escape_string($_REQUEST['gclid']);
$mysql['msclkid']= $db->real_escape_string($_REQUEST['msclkid']);

//get the utm params
getUTMParams($mysql);
   
$device_id = PLATFORMS::get_device_info($db,$detect,$_REQUEST['ua']);

if(isset($device_id['platform'])){
    $mysql['platform_id'] = $db->real_escape_string($device_id['platform']); 
}else{
    $mysql['platform_id'] = 0;
}

if(isset($device_id['browser'])){
    $mysql['browser_id'] = $db->real_escape_string($device_id['browser']);
}else{
    $mysql['browser_id'] = 0;
}

if(isset($device_id['device'])){
    $mysql['device_id'] = $db->real_escape_string($device_id['device']);
}else{
    $mysql['device_id'] = 0;
}

if ($device_id['type'] == '4') {
	$mysql['click_bot'] = '1';
} else {
	$mysql['click_bot'] = '0';
}
 
$mysql['click_in'] = 1;
$mysql['click_out'] = 1; 

$ip_id=INDEXES::get_ip_id($db,$ip_address);
$mysql['ip_id'] = $db->real_escape_string($ip_id);

//before we finish filter this click
$user_id = $tracker_row['user_id'];

//GEO Lookup
$GeoData = getGeoData($ip_address);

$country_id = INDEXES::get_country_id($db, $GeoData['country'], $GeoData['country_code']);
$mysql['country_id'] = $db->real_escape_string($country_id);
$mysql['country'] = $db->real_escape_string($GeoData['country']);

$region_id = INDEXES::get_region_id($db, $GeoData['region'], $mysql['country_id']);
$mysql['region_id'] = $db->real_escape_string($region_id);
$mysql['region'] = $db->real_escape_string($GeoData['city']);

$city_id = INDEXES::get_city_id($db, $GeoData['city'], $mysql['country_id']);
$mysql['city_id'] = $db->real_escape_string($city_id);
$mysql['city'] = $db->real_escape_string($GeoData['city']);

if ($tracker_row['maxmind_isp'] == '1') {
	$IspData = getIspData($ip_address);
	$isp_id = INDEXES::get_isp_id($db, $IspData);
	$mysql['isp_id'] = $db->real_escape_string($isp_id);
} else {
	$mysql['isp_id'] = '0';
}

if ($device_id['type'] == '4') {
	$mysql['click_filtered'] = '1';
} else {
	$click_filtered = FILTER::startFilter($db, $click_id,$mysql['ip_id'],$ip_address,$user_id);
	$mysql['click_filtered'] = $db->real_escape_string($click_filtered);
}

//because this is a simple landing page, set click_alp (which stands for click advanced landing page, equal to 0)
$mysql['click_alp'] = 0;

//set landing page type to direct link
$mysql['lp_type'] = "dl";

//ok we have the main data, now insert this row
insertClicks($mysql); 

// insert custom variables
insertClicksVariable($mysql, $tracker_row);

// insert msclkid and utm vars
insertMsclkid($mysql);

//now we have the click's advance data, now insert this row
insertClicksAdvance($mysql);   

//insert the tracking data
insertClicksTracking($mysql);
//now gather variables for the clicks record db
//lets determine if cloaking is on
if (($tracker_row['click_cloaking'] == 1) or //if tracker has overridden cloaking on                                                             
	(($tracker_row['click_cloaking'] == -1) and ($tracker_row['aff_campaign_cloaking'] == 1)) or
	((!isset($tracker_row['click_cloaking'])) and ($tracker_row['aff_campaign_cloaking'] == 1)) //if no tracker but but by default campaign has cloaking on
) {
	$cloaking_on = true;
	$mysql['click_cloaking'] = 1;

} 
/*
else { 
	$mysql['click_cloaking'] = 0;
	$mysql['click_id_public'] = 0; 
}
*/
//ok we have our click recorded table, now lets insert theses
insertClicksRecord($mysql);

getReferer($mysql);

//$outbound_site_url = 'http://'.$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$click_outbound_site_url_id = 0;//INDEXES::get_site_url_id($db, $outbound_site_url); 
$mysql['click_outbound_site_url_id'] = $db->real_escape_string($click_outbound_site_url_id); 

if ($cloaking_on == true) {
	$cloaking_site_url = 'https://'.$_SERVER['SERVER_NAME'] .dirname($_SERVER['PHP_SELF']). '/cl.php?pci=' . $click_id_public;      
}

//rotate the urls
if(isset($tracker_row['landing_page_url']) && isset($tracker_row['landing_page_url']) != '')
{
	$redirect_site_url	= $db->real_escape_string($tracker_row['landing_page_url']);
}
else
{
	$redirect_site_url = rotateTrackerUrl($db, $tracker_row);
}

$redirect_site_url = replaceTrackerPlaceholders($db, $redirect_site_url,$click_id,$mysql);

$click_redirect_site_url_id = 0;//INDEXES::get_site_url_id($db, $redirect_site_url); 
$mysql['click_redirect_site_url_id'] = $db->real_escape_string($click_redirect_site_url_id);

//insert this
insertClicksSite($mysql);
 
//set dirty hour
setDirtyHour($mysql);

if ($mysql['click_cpa'] != NULL) {
	$insert_sql = "INSERT INTO 202_cpa_trackers
				   SET         click_id='".$mysql['click_id']."',
							   tracker_id_public='".$mysql['tracker_id_public']."'";
	$insert_result = $db->query($insert_sql);
}

//set the cookie
setClickIdCookie($mysql['click_id'],$mysql['aff_campaign_id']);


//update impression pixel data
updateImpressionPixel($mysql);

//get and prep extra stuff for pre-pop or data passing
$urlvars = getPrePopVars($_REQUEST);

//now we've recorded, now lets redirect them

if ($cloaking_on == true) {
	//if cloaked, redirect them to the cloaked site. 
	header('location: '.setPrePopVars($urlvars,$cloaking_site_url,true));
} else {
	header('location: '.setPrePopVars($urlvars,$redirect_site_url,false));
} 

unset($mysql);
die();
?>