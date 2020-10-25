<?php

#only allow numeric t202ids
$t202id = $_GET['t202id'];
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
//Mailchimp Integration
//getMailchimp($mysql);
$tracker_row = getTrackerDetail($mysql);
//get the click id
$click_id = getClickId();
$mysql['click_id'] = $click_id;
if($prefetch){
    //if this is  a prefetch request, send to the offer as quickly as possible
    $redirect_site_url = rotateTrackerUrl($db, $tracker_row);
    $redirect_site_url = replaceTrackerPlaceholders($db, $redirect_site_url,$click_id,$mysql);
    header('location: '.$redirect_site_url);
    die();    
}
if ($memcacheWorking) {  
	$url = $tracker_row['aff_campaign_url'];
	$tid = $t202id;
	$getKey = $memcache->get(md5('url_'.$tid.systemHash()));
	if($getKey === false){
		$setUrl = setCache(md5('url_'.$tid.systemHash()), $url);
	}
}
//set the timezone to the users timezone
$mysql['user_id'] = $db->real_escape_string($tracker_row['user_id']);
//now this sets timezone
date_default_timezone_set($tracker_row['user_timezone']);
if (!$tracker_row) { die(); }
// get publisher id
if (isset($_GET['t202pubid'])) {
    $mysql['public_pub_id'] = $db->real_escape_string($_GET['t202pubid']);
    $mysql['pub_id'] = getPublisher($mysql['public_pub_id']);
    if (isset($mysql['pub_id']) && $mysql['pub_id'] != '1') {
        $mysql['user_id'] = $mysql['pub_id'];
    }
}
// get mysql variables
$mysql['aff_campaign_id'] = $db->real_escape_string($tracker_row['aff_campaign_id']);
$mysql['ppc_account_id'] = $db->real_escape_string($tracker_row['ppc_account_id']);
$mysql['user_pref_dynamic_bid'] = $db->real_escape_string($tracker_row['user_pref_dynamic_bid']);
// set cpc use dynamic variable if set or the default if not
if (isset ( $_GET ['t202b'] ) && $mysql['user_pref_dynamic_bid'] == '1') {
    $_GET ['t202b']=ltrim($_GET ['t202b'],'$');
    if(is_numeric ( $_GET ['t202b'] )){
        $bid = number_format ( $_GET ['t202b'], 5, '.', '' );
        $mysql ['click_cpc'] = $db->real_escape_string ( $bid );
    }
    else{
        $mysql ['click_cpc'] = $db->real_escape_string ( $tracker_row ['click_cpc'] );
    }
} else
    $mysql ['click_cpc'] = $db->real_escape_string ( $tracker_row ['click_cpc'] );
$mysql['click_cpa'] = $db->real_escape_string($tracker_row['click_cpa']);
$mysql['click_payout'] = $db->real_escape_string($tracker_row['aff_campaign_payout']);
$mysql['click_time'] = time();
$mysql['text_ad_id'] = $db->real_escape_string($tracker_row['text_ad_id']);
$mysql['user_keyword_searched_or_bidded'] = $db->real_escape_string($tracker_row['user_keyword_searched_or_bidded']);
// get c1-c4
getCVars($mysql);
//get keyword info
getKeyword($mysql);	  
$mysql['gclid']= $db->real_escape_string($_GET['gclid']);
$mysql['msclkid']= $db->real_escape_string($_GET['msclkid']);
//get the utm params
getUTMParams($mysql);
$device_id = PLATFORMS::get_device_info($db,$detect,$_GET['ua']);
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
if (isset($device_id['type']) && $device_id['type'] == '4') {
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
if (isset($device_id['type']) && $device_id['type'] == '4') {
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
// insert gclid and utm vars
insertGclid($mysql);
// insert msclkid and utm vars
insertMsclkid($mysql);
//now we have the click's advance data, now insert this row
insertClicksAdvance($mysql);

//insert the tracking data
insertClicksTracking($mysql);

//now gather variables for the clicks record db
//lets determine if cloaking is on
if (($tracker_row['click_cloaking'] == 1) or //if tracker has overrided cloaking on                                                             
	(($tracker_row['click_cloaking'] == -1) and ($tracker_row['aff_campaign_cloaking'] == 1)) or
	((!isset($tracker_row['click_cloaking'])) and ($tracker_row['aff_campaign_cloaking'] == 1)) //if no tracker but but by default campaign has cloaking on
) {
	$cloaking_on = true;
	$mysql['click_cloaking'] = 1;
	// 	if cloaking is on, add in a click_id_public, because we will be forwarding them to a cloaked /cl/xxxx link
						$click_id_public = getClickIdPublic($click_id);
	$mysql['click_id_public'] = $click_id_public;
}
else {
	$mysql['click_cloaking'] = 0;
	$mysql['click_id_public'] = 0;
}

//ok we have our click recorded table, now lets insert theses
insertClicksRecord($mysql);

// if user wants to use t202ref from url variable use that first if it's not set try and get it from the ref url
if ($tracker_row['user_pref_referer_data'] == 't202ref') {
    if (isset($_GET['t202ref']) && $_GET['t202ref'] != '') { //check for t202ref value
        $mysql['t202ref']= $db->real_escape_string($_GET['t202ref']);
        $click_referer_site_url_id = INDEXES::get_site_url_id($db, $mysql['t202ref']);
    } else { //if not found revert to what we usually do
        if ($referer_query['url']) {
            $click_referer_site_url_id = INDEXES::get_site_url_id($db, $referer_query['url']);
        } else {
            $click_referer_site_url_id = INDEXES::get_site_url_id($db, $_SERVER['HTTP_REFERER']);
        }
    }
} else { //user wants the real referer first
    // now lets get variables for clicks site
    // so this is going to check the REFERER URL, for a ?url=, which is the ACUTAL URL, instead of the google content, pagead2.google....
    if ($referer_query['url']) {
        $click_referer_site_url_id = INDEXES::get_site_url_id($db, $referer_query['url']);
    } else {
        $click_referer_site_url_id = INDEXES::get_site_url_id($db, $_SERVER['HTTP_REFERER']);
    }
}
$mysql['click_referer_site_url_id'] = $db->real_escape_string($click_referer_site_url_id); 
$outbound_site_url = getScheme().'://'.$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

$click_outbound_site_url_id = INDEXES::get_site_url_id($db, $outbound_site_url);
$mysql['click_outbound_site_url_id'] = $db->real_escape_string($click_outbound_site_url_id);

if ($cloaking_on == true) {
	$cloaking_site_url = getScheme().'://'.$_SERVER['SERVER_NAME'] .dirname($_SERVER['PHP_SELF']). '/cl.php?pci=' . $click_id_public;
}


//rotate the urls
$redirect_site_url = rotateTrackerUrl($db, $tracker_row);
$redirect_site_url = replaceTrackerPlaceholders($db, $redirect_site_url,$click_id,$mysql);


$click_redirect_site_url_id = INDEXES::get_site_url_id($db, $redirect_site_url);
$mysql['click_redirect_site_url_id'] = $db->real_escape_string($click_redirect_site_url_id);

//insert this
insertClicksSite($mysql);

if ($mysql['click_cpa'] != NULL) {
	$insert_sql = "INSERT INTO 202_cpa_trackers
				   SET         click_id='".$mysql['click_id']."',
							   tracker_id_public='".$mysql['tracker_id_public']."'";
	$insert_result = $db->query($insert_sql);
}

//set the cookie
setClickIdCookie($mysql['click_id'],$mysql['aff_campaign_id']);

//set dirty hour
setDirtyHour($mysql);

updateImpressionPixel($mysql);

//get and prep extra stuff for pre-pop or data passing
$urlvars = getPrePopVars($_GET);

unset($mysql);
//now we've recorded, now lets redirect them
if ($cloaking_on == true) {
	//if cloaked, redirect them to the cloaked site. 
	header('location: '.setPrePopVars($urlvars,$cloaking_site_url,true));
} else {
	header('location: '.setPrePopVars($urlvars,$redirect_site_url,false));
} 
die();