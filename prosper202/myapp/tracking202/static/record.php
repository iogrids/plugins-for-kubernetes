<?php
ob_start();

// let's find out if this is an advance or simple landing page, so we can include the appropriate script for each
$landing_page_id_public = $_GET['lpip'];

$mysql['landing_page_id_public'] = $db->real_escape_string($landing_page_id_public);

$tracker_sql = "SELECT  landing_page_type
				FROM      202_landing_pages
				WHERE   landing_page_id_public='" . $mysql['landing_page_id_public'] . "'";

$tracker_row = memcache_mysql_fetch_assoc($db, $tracker_sql);

if (! $tracker_row) {
    die();
}

//figure if alp or slp
$landing_page_id_public = $_GET['lpip'];
$mysql['landing_page_id_public'] = $db->real_escape_string($landing_page_id_public);
$mysql['landing_page_type'] = $db->real_escape_string($tracker_row['landing_page_type']);

//check if deferred pixel is on
$mysql['deferred_pixel'] = 0; //default off

if(isset($_GET['defpixel']) && $_GET['defpixel'] == 1){
    $mysql['deferred_pixel'] =1;
}
//lp specifc
if ($mysql['landing_page_type'] == 0) {        
    $mysql['click_alp'] = 0;
    $mysql['lp_type'] = "slp";
    $lp_filename ="record_simple.php";
    
    
} elseif ($mysql['landing_page_type'] == 1) {
    $mysql['click_alp'] = 1;
    $mysql['lp_type'] = "alp";
    $lp_filename ="record_adv.php";
}

//read the query string from the landing page url and merge into the lp js $_GET
if(isset($_GET['t202LpUrl']) && $_GET['t202LpUrl'] != ''){
    $QueryString202=parse_url($_GET['t202LpUrl'], PHP_URL_QUERY);
}else{
    $QueryString202=parse_url($_SERVER["HTTP_REFERER"], PHP_URL_QUERY);
}

parse_str($QueryString202, $newGET);
$_GET = array_merge($_GET,$newGET);

//get the gclid
if(isset($_GET['gclid'])){
    $mysql['gclid'] = $db->real_escape_string($_GET['gclid']);
}else{
    $mysql['gclid'] = '';
}

if(isset($_GET['msclkid'])){
    $mysql['msclkid'] = $db->real_escape_string($_GET['msclkid']);
}else{
    $mysql['msclkid'] = '';
}

//get the fbclid
if(isset($_GET['fbclid'])){
    $mysql['fbclid'] = $db->real_escape_string($_GET['fbclid']);
}else{
    $mysql['fbclid'] = '';
}

//if gclid is set then get the click id and set it then pull all the other data from stored data instead of setting it
if(isset($mysql['gclid']) && $mysql['gclid'] != ''){
    $gclidRow=getGclid($mysql);
    if(isset($gclidRow)){
        
        $mysql['gclid_found'] = true;
        $mysql['click_id'] = $gclidRow['click_id'];
        $mysql['click_id_public'] = $gclidRow['click_id_public'];
        $gclidData['pci'] = $db->real_escape_string($gclidRow['click_id_public']);
        $gclidData['c1'] = $db->real_escape_string($gclidRow['c1']);
        $gclidData['c2'] =$db->real_escape_string($gclidRow['c2']);
        $gclidData['c3'] = $db->real_escape_string($gclidRow['c3']);
        $gclidData['c4'] = $db->real_escape_string($gclidRow['c4']);
        $gclidData['t202kw'] = $db->real_escape_string($gclidRow['t202kw']);
        $gclidData['utm_campaign'] = $db->real_escape_string($gclidRow['utm_campaign']);
        $gclidData['utm_source'] = $db->real_escape_string($gclidRow['utm_source']);
        $gclidData['utm_medium'] = $db->real_escape_string($gclidRow['utm_medium']);
        $gclidData['utm_term'] = $db->real_escape_string($gclidRow['utm_term']);
        $gclidData['utm_content'] = $db->real_escape_string($gclidRow['utm_content']);
        session_start();
        $gclidData['t202id'] = $db->real_escape_string($_SESSION['t202id']);
        $click_id = $mysql['click_id'];
        $click_id_public = $mysql['click_id_public'];
        $pageData = json_encode($gclidData);
    }
}
?>

<?php


$tracker_sql = "SELECT 202_landing_pages.user_id,
						202_landing_pages.landing_page_id,
                        202_landing_pages.leave_behind_page_url, 
                        202_landing_pages.aff_campaign_id,
						202_aff_campaigns.aff_campaign_url,
						202_aff_campaigns.aff_campaign_url_2,
						202_aff_campaigns.aff_campaign_url_3,
						202_aff_campaigns.aff_campaign_url_4,
						202_aff_campaigns.aff_campaign_url_5,
						202_aff_campaigns.aff_campaign_payout,
                        202_aff_campaigns.aff_campaign_cloaking,";
 
 if($mysql['landing_page_type']!=1) //if it's slp add the WHERE
{
    $tracker_sql .= "   b202_fbpa_status,
                        b202_fbpa_dynamic_epv,
                        b202_fbpa_content_name,
                        b202_fbpa_content_type,
                        b202_fbpa_outbound_clicks,
                        content_type,				
                        event_type,";
}

$tracker_sql .= "
						202_aff_campaigns.aff_campaign_rotate
				FROM    202_landing_pages";

                if($mysql['landing_page_type']==1) //if it's alp add the 202_aff_campaigns table
{
    $tracker_sql .= ",202_aff_campaigns";

}				

if($mysql['landing_page_type']!=1) //if it's slp add the WHERE
{
    $tracker_sql .= "
    LEFT JOIN 202_aff_campaigns USING (`aff_campaign_id`)
    LEFT JOIN 202_bot202_facebook_pixel_assistant USING(`landing_page_id`)
    LEFT JOIN 202_bot202_facebook_pixel_click_events ON 202_bot202_facebook_pixel_click_events.event_type_id = b202_fbpa_content_type
    LEFT JOIN 202_bot202_facebook_pixel_content_type ON 202_bot202_facebook_pixel_content_type.content_type_id = b202_fbpa_content_type";


}
$tracker_sql .= "
WHERE   202_landing_pages.landing_page_id_public='" . $mysql['landing_page_id_public']. "'";             

$tracker_row = memcache_mysql_fetch_assoc($db, $tracker_sql);

$mysql['b202_fbpa_status'] = $db->real_escape_string($tracker_row['b202_fbpa_status']);
$mysql['b202_fbpa_dynamic_epv'] = $db->real_escape_string($tracker_row['b202_fbpa_dynamic_epv']);
$mysql['b202_fbpa_content_name'] = $db->real_escape_string($tracker_row['b202_fbpa_content_name']);
$mysql['content_type'] = $db->real_escape_string($tracker_row['content_type']);
$mysql['event_type'] = $db->real_escape_string($tracker_row['event_type']);

// set the timezone to the users timezone
$mysql['user_id'] = $db->real_escape_string($tracker_row['user_id']);
$user_sql = "SELECT 		user_timezone,
							user_keyword_searched_or_bidded,
                            user_pref_referer_data,
                            user_pref_dynamic_bid,
                            user_account_currency,
							maxmind_isp
			   FROM 		202_users
			   LEFT JOIN	202_users_pref USING (user_id)
			   WHERE 		202_users.user_id='" . $mysql['user_id'] . "'";
$user_row = memcache_mysql_fetch_assoc($db, $user_sql);
$mysql['user_pref_dynamic_bid'] = $db->real_escape_string($user_row['user_pref_dynamic_bid']);
$mysql['user_account_currency'] = $db->real_escape_string($user_row['user_account_currency']);
//now this sets it
date_default_timezone_set($user_row['user_timezone']);

if (! $tracker_row) { die(); }

if ( isset($_GET['t202id']) ) {
    
    // get publisher id
    if (isset($_GET['t202pubid'])) {
        $mysql['public_pub_id'] = $db->real_escape_string($_GET['t202pubid']);
    
        $mysql['pub_id'] = getPublisher($mysql['public_pub_id']);
        if (isset($mysql['pub_id']) && $mysql['pub_id'] != '1') {
            $mysql['user_id'] = $mysql['pub_id'];
    
        }
    }
    
    // grab tracker data if avaliable
    $mysql['tracker_id_public'] = $db->real_escape_string($_GET['t202id']);
    $tracker_sql2 = "SELECT 2tr.text_ad_id,
							2tr.ppc_account_id,
							2tr.click_cpc,
							2tr.click_cloaking,
							2cv.ppc_variable_ids,
							2cv.parameters
					FROM    202_trackers AS 2tr
					LEFT JOIN 202_ppc_accounts AS 2ppc USING (ppc_account_id)
					LEFT JOIN (SELECT ppc_network_id, GROUP_CONCAT(ppc_variable_id) AS ppc_variable_ids, GROUP_CONCAT(parameter) AS parameters FROM 202_ppc_network_variables GROUP BY ppc_network_id) AS 2cv USING (ppc_network_id)
					WHERE   2tr.tracker_id_public='" . $mysql['tracker_id_public'] . "'";
    $tracker_row2 = memcache_mysql_fetch_assoc($db, $tracker_sql2);

}
else{
    // use default traffic source account if set

  //  if($mysql['ppc_account_id']==''){
    $default_account_sql = "SELECT `ppc_account_id`,`ppc_network_id` from 202_ppc_accounts where ppc_account_default = 1";
    $default_account_row = memcache_mysql_fetch_assoc($db, $default_account_sql);
    $mysql['ppc_account_id'] = $db->real_escape_string($default_account_row['ppc_account_id']);
    $mysql['ppc_network_id'] = $db->real_escape_string($default_account_row['ppc_network_id']);

    if($mysql['ppc_account_id']!=''){
        $tracker_sql2 = "SELECT ppc_account_id,
                           2cv.ppc_variable_ids,
                           2cv.parameters
                        FROM  202_ppc_accounts AS 2ppc 
                        LEFT JOIN (SELECT ppc_network_id, GROUP_CONCAT(ppc_variable_id) AS ppc_variable_ids, GROUP_CONCAT(parameter) AS parameters FROM 202_ppc_network_variables GROUP BY ppc_network_id) AS 2cv USING (ppc_network_id)
                        WHERE   ppc_network_id='" . $mysql['ppc_network_id'] . "'";
                        $tracker_row2 = memcache_mysql_fetch_assoc($db, $tracker_sql2);
    }

    
}

if ($tracker_row2) {
    $tracker_row = array_merge($tracker_row, $tracker_row2);
}

// INSERT THIS CLICK BELOW, if this click doesn't already exisit

// get mysql variables
//$mysql['user_id'] = $db->real_escape_string($tracker_row['user_id']);
$mysql['aff_campaign_id'] = $db->real_escape_string($tracker_row['aff_campaign_id']);
if($tracker_row['ppc_account_id']==''){
	$default_account_sql = "SELECT `ppc_account_id` from 202_ppc_accounts where ppc_account_default = 1";
	$default_account_row = memcache_mysql_fetch_assoc($db, $default_account_sql);
    $mysql['ppc_account_id'] = $db->real_escape_string($default_account_row['ppc_account_id']);
}
else{
	$mysql['ppc_account_id'] = $db->real_escape_string($tracker_row['ppc_account_id']);
}


// set cpc use dynamic variable if set or the default if not
if (isset($_GET['t202b']) && $mysql['user_pref_dynamic_bid'] == '1') {
    $_GET['t202b'] = ltrim($_GET['t202b'], '$');
    if (is_numeric($_GET['t202b'])) {
        $bid = number_format($_GET['t202b'], 5, '.', '');
        $mysql['click_cpc'] = $db->real_escape_string($bid);
    } else {
        $mysql['click_cpc'] = $db->real_escape_string($tracker_row['click_cpc']);
    }
} else {
    
    
    if(isset($tracker_row['click_cpc'])){
        $mysql['click_cpc'] = $db->real_escape_string($tracker_row['click_cpc']);
    }else{
        $mysql['click_cpc'] = '';
    }
}

if(isset($tracker_row['aff_campaign_payout'])){
    $mysql['click_payout'] = $db->real_escape_string($tracker_row['aff_campaign_payout']); //slp
}else{
    $mysql['aff_campaign_payout'] = '';
}

$mysql['click_time'] = time(); //slp
if(isset($tracker_row['landing_page_id'])){
    $mysql['landing_page_id'] = $db->real_escape_string($tracker_row['landing_page_id']);
}else{
    $mysql['landing_page_id'] = '';
}

if(isset($tracker_row['text_ad_id'])){
    $mysql['text_ad_id'] = $db->real_escape_string($tracker_row['text_ad_id']);
}else{
    $mysql['text_ad_id'] = '';
}


/* ok, if $_GET['OVRAW'] that is a yahoo keyword, if on the REFER, there is a $_GET['q], that is a GOOGLE keyword... */
// so this is going to check the REFERER URL, for a ?q=, which is the ACUTAL KEYWORD searched.
if(isset($_GET['referer'])){
    $referer_url_parsed = @parse_url($_GET['referer']);
    
    if(isset($referer_url_parsed['query'])){
        $referer_url_query = $referer_url_parsed['query'];
        @parse_str($referer_url_query, $referer_query);
    }
    
}else{
    $referer_query = '';
}

switch ($user_row['user_keyword_searched_or_bidded']) {
    
    case "bidded":
        
        // try to get the bidded keyword first
        if ($_GET['OVKEY']) { // if this is a Y! keyword
            $keyword = $db->real_escape_string($_GET['OVKEY']);
        } elseif ($_GET['t202kw']) {
            $keyword = $db->real_escape_string($_GET['t202kw']);
        } elseif ($_GET['target_passthrough']) { // if this is a mediatraffic! keyword
            $keyword = $db->real_escape_string($_GET['target_passthrough']);
        } else { // if this is a zango, or more keyword
            $keyword = $db->real_escape_string($_GET['keyword']);
        }
        break;
    
    case "searched":
        
        // try to get the searched keyword
        if (isset($referer_query['q'])) {
            $keyword = $db->real_escape_string($referer_query['q']);
        } elseif (isset($_GET['OVRAW'])) { // if this is a Y! keyword
            $keyword = $db->real_escape_string($_GET['OVRAW']);
        } elseif (isset($_GET['target_passthrough'])) { // if this is a mediatraffic! keyword
            $keyword = $db->real_escape_string($_GET['target_passthrough']);
        } elseif (isset($_GET['keyword'])) { // if this is a zango, or more keyword
            $keyword = $db->real_escape_string($_GET['keyword']);
        } elseif (isset($_GET['search_word'])) { // if this is a eniro, or more keyword
            $keyword = $db->real_escape_string($_GET['search_word']);
        } elseif (isset($_GET['query'])) { // if this is a naver, or more keyword
            $keyword = $db->real_escape_string($_GET['query']);
        } elseif (isset($_GET['encquery'])) { // if this is a aol, or more keyword
            $keyword = $db->real_escape_string($_GET['encquery']);
        } elseif (isset($_GET['terms'])) { // if this is a about.com, or more keyword
            $keyword = $db->real_escape_string($_GET['terms']);
        } elseif (isset($_GET['rdata'])) { // if this is a viola, or more keyword
            $keyword = $db->real_escape_string($_GET['rdata']);
        } elseif (isset($_GET['qs'])) { // if this is a virgilio, or more keyword
            $keyword = $db->real_escape_string($_GET['qs']);
        } elseif (isset($_GET['wd'])) { // if this is a baidu, or more keyword
            $keyword = $db->real_escape_string($_GET['wd']);
        } elseif (isset($_GET['text'])) { // if this is a yandex, or more keyword
            $keyword = $db->real_escape_string($_GET['text']);
        } elseif (isset($_GET['szukaj'])) { // if this is a wp.pl, or more keyword
            $keyword = $db->real_escape_string($_GET['szukaj']);
        } elseif (isset($_GET['qt'])) { // if this is a O*net, or more keyword
            $keyword = $db->real_escape_string($_GET['qt']);
        } elseif (isset($_GET['k'])) { // if this is a yam, or more keyword
            $keyword = $db->real_escape_string($_GET['k']);
        } elseif (isset($_GET['words'])) { // if this is a Rambler, or more keyword
            $keyword = $db->real_escape_string($_GET['words']);
        } elseif (isset($_GET['t202kw'])) { // if this is a p202 keyword
            $keyword = $db->real_escape_string($_GET['t202kw']);
        }
        else {
            $keyword = '';
        }
        break;
}

if (substr($keyword, 0, 8) == 't202var_') {
    $t202var = substr($keyword, strpos($keyword, "_") + 1);
    
    if (isset($_GET[$t202var])) {
        $keyword = $_GET[$t202var];
    }
}

$keyword = str_replace('%20', ' ', $keyword);
$keyword = utf8_decode($keyword);
$keyword_id = INDEXES::get_keyword_id($db, $keyword);
$mysql['keyword_id'] = $db->real_escape_string($keyword_id);
$mysql['keyword'] = $db->real_escape_string($keyword);

if(isset($_GET['c1'])){
    $c1 = $db->real_escape_string($_GET['c1']);
}else{
    $c1 = '';
}
$c1 = str_replace('%20', ' ', $c1);
$c1_id = INDEXES::get_c1_id($db, $c1);

$mysql['c1_id'] = $db->real_escape_string($c1_id);
$mysql['c1'] = $c1;

if(isset($_GET['c2'])){
    $c2 = $db->real_escape_string($_GET['c2']);
}else{
    $c2 = '';
}
$c2 = str_replace('%20', ' ', $c2);
$c2_id = INDEXES::get_c2_id($db, $c2);
$mysql['c2_id'] = $db->real_escape_string($c2_id);
$mysql['c2'] = $c2;

if(isset($_GET['c3'])){
    $c3 = $db->real_escape_string($_GET['c3']);
}else{
    $c3 = '';
}
$c3 = str_replace('%20', ' ', $c3);
$c3_id = INDEXES::get_c3_id($db, $c3);
$mysql['c3_id'] = $db->real_escape_string($c3_id);
$mysql['c3'] = $c3;

if(isset($_GET['c4'])){
    $c4 = $db->real_escape_string($_GET['c4']);
}else{
    $c4 = '';
}
$c4 = str_replace('%20', ' ', $c4);
$c4_id = INDEXES::get_c4_id($db, $c4);
$mysql['c4_id'] = $db->real_escape_string($c4_id);
$mysql['c4'] = $c4;

// utm_source
if(isset($_GET['utm_source'])){
    $utm_source = $db->real_escape_string($_GET['utm_source']);
}else{
    $utm_source = '';
}
if (isset($utm_source) && $utm_source != '') {
    $utm_source = str_replace('%20', ' ', $utm_source);
    $utm_source_id = INDEXES::get_utm_id($db, $utm_source, 'utm_source');
} else {
    $utm_source_id = 0;
}
$mysql['utm_source_id'] = $db->real_escape_string($utm_source_id);

// utm_medium
if(isset($_GET['utm_medium'])){
    $utm_medium = $db->real_escape_string($_GET['utm_medium']);
}else{
    $utm_medium = '';
}
if (isset($utm_medium) && $utm_medium != '') {
    $utm_medium = str_replace('%20', ' ', $utm_medium);
    $utm_medium_id = INDEXES::get_utm_id($db, $utm_medium, 'utm_medium');
} else {
    $utm_medium_id = 0;
}
$mysql['utm_medium_id'] = $db->real_escape_string($utm_medium_id);

// utm_campaign
if(isset($_GET['utm_campaign'])){
    $utm_campaign = $db->real_escape_string($_GET['utm_campaign']);
}else{
    $utm_campaign = '';
}
if (isset($utm_campaign) && $utm_campaign != '') {
    $utm_campaign = str_replace('%20', ' ', $utm_campaign);
    $utm_campaign_id = INDEXES::get_utm_id($db, $utm_campaign, 'utm_campaign');
} else {
    $utm_campaign_id = 0;
}
$mysql['utm_campaign_id'] = $db->real_escape_string($utm_campaign_id);

// utm_term
if(isset($_GET['utm_term'])){
    $utm_term = $db->real_escape_string($_GET['utm_term']);
}else{
    $utm_term = '';
}
if (isset($utm_term) && $utm_term != '') {
    $utm_term = str_replace('%20', ' ', $utm_term);
    $utm_term_id = INDEXES::get_utm_id($db, $utm_term, 'utm_term');
} else {
    $utm_term_id = 0;
}
$mysql['utm_term_id'] = $db->real_escape_string($utm_term_id);

// utm_content
if(isset($_GET['utm_content'])){
    $utm_content = $db->real_escape_string($_GET['utm_content']);
}else{
    $utm_content = '';
}
if (isset($utm_content) && $utm_content != '') {
    $utm_content = str_replace('%20', ' ', $utm_content);
    $utm_content_id = INDEXES::get_utm_id($db, $utm_content, 'utm_content');
} else {
    $utm_content_id = 0;
}
$mysql['utm_content_id'] = $db->real_escape_string($utm_content_id);

$ip_id = INDEXES::get_ip_id($db, $ip_address);
$mysql['ip_id'] = $db->real_escape_string($ip_id);

if(isset($_GET['ua'])){
    $mysql['ua'] = $db->real_escape_string($_GET['ua']);
}else{
    $mysql['ua'] = '';
}
$device_id = PLATFORMS::get_device_info($db, $detect, $mysql['ua']);

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
$mysql['click_out'] = 0;

// now lets get variables for clicks site

// if user wants to use t202ref from url variable use that first if it's not set try and get it from the ref url
if ($user_row['user_pref_referer_data'] == 't202ref') {
    if (isset($_GET['t202ref']) && $_GET['t202ref'] != '') { // check for t202ref value
        $mysql['t202ref'] = $db->real_escape_string($_GET['t202ref']);
        $click_referer_site_url_id = INDEXES::get_site_url_id($db, $mysql['t202ref']);
    } else { // if not found revert to what we usually do
        if ($referer_query['url']) {
            $click_referer_site_url_id = INDEXES::get_site_url_id($db, $referer_query['url']);
        } else {
            $click_referer_site_url_id = INDEXES::get_site_url_id($db, $_GET['referer']);
        }
    }
} else { // user wants the real referer first
         
    // now lets get variables for clicks site
         // so this is going to check the REFERER URL, for a ?url=, which is the ACUTAL URL, instead of the google content, pagead2.google....
    if (isset($referer_query['url'])) {
        $click_referer_site_url_id = INDEXES::get_site_url_id($db, $referer_query['url']);
    } else if(isset($_GET['referer'])) {
        $mysql['referer'] = $db->real_escape_string($_GET['referer']);
        $click_referer_site_url_id = INDEXES::get_site_url_id($db, $mysql['referer']);
    }else{
        $click_referer_site_url_id = INDEXES::get_site_url_id($db, '');
    }
}

$mysql['click_referer_site_url_id'] = $db->real_escape_string($click_referer_site_url_id);

// see if this click should be filtered
$user_id = $tracker_row['user_id'];

// GEO Lookup
$GeoData = getGeoData($ip_address);

$country_id = INDEXES::get_country_id($db, $GeoData['country'], $GeoData['country_code']);
$mysql['country_id'] = $db->real_escape_string($country_id);

$region_id = INDEXES::get_region_id($db, $GeoData['region'], $mysql['country_id']);
$mysql['region_id'] = $db->real_escape_string($region_id);

$city_id = INDEXES::get_city_id($db, $GeoData['city'], $mysql['country_id']);
$mysql['city_id'] = $db->real_escape_string($city_id);

if ($user_row['maxmind_isp'] == '1') {
    $IspData = getIspData($ip_address);
    $isp_id = INDEXES::get_isp_id($db, $IspData);
    $mysql['isp_id'] = $db->real_escape_string($isp_id);
} else {
    $mysql['isp_id'] = '0';
}

if ((null !== ( getCookie202('tracking202rlp_' . $landing_page_id_public) )) && isset($click_id)) {
	//check for existing subid in session cookie, if it's there use it and don't overwrite the subid
	$original_click_id = substr(getCookie202('tracking202rlp_' . $landing_page_id_public), 1, - 1);
	$original_click_id_public = getCookie202('tracking202rlp_' . $landing_page_id_public);
	$click_sql="Select click_time from 202_clicks where click_id=".$click_id." order by click_time limit 1";
    $click_row = memcache_mysql_fetch_assoc($db, $click_sql);
    $mysql['click_time']=$db->real_escape_string($click_row['click_time']);
} else {
	$original_click_id = '0';
    $original_click_id_public='0';
	$mysql['click_time'] = time();
}

// ok we have the main data, get click id
if(!isset($mysql['click_id']) && !isset($mysql['click_id_public'])){
    $click_id = getClickId();    
    $click_id_public = getClickIdPublic($click_id);

    $mysql['click_id'] = $click_id;
    $mysql['click_id_public'] = $db->real_escape_string($click_id_public);

}
if(null == ( getCookie202('tracking202rlp_' . $landing_page_id_public))){       
    setClickIdCookieForLp($click_id_public, $landing_page_id_public);
}

//filter the click if needed
if ($device_id['type'] == '4') {
    $mysql['click_filtered'] = '1';
} else {
    $click_filtered = FILTER::startFilter($db, $click_id, $ip_id, $ip_address, $user_id);
    $mysql['click_filtered'] = $db->real_escape_string($click_filtered);
}

if ((isset($tracker_row['click_cloaking']) && $tracker_row['click_cloaking'] == 1) || // if tracker has overrided cloaking on
((isset($tracker_row['click_cloaking']) && $tracker_row['click_cloaking'] == - 1) && ($tracker_row['aff_campaign_cloaking'] == 1)) || ((! isset($tracker_row['click_cloaking'])) && ($tracker_row['aff_campaign_cloaking'] == 1))) // if no tracker but but by default campaign has cloaking on
{
    $cloaking_on = true;
    $mysql['click_cloaking'] = 1;
    // if cloaking is on, add in a click_id_public, because we will be forwarding them to a cloaked /cl/xxxx link
} else {
    $cloaking_on = false;
    $mysql['click_cloaking'] = 0;
}
if(!isset($mysql['gclid_found']) || $mysql['gclid_found'] == false ){ 
//insert clicks    

insertClicks($mysql);

// insert custom variables
insertClicksVariable($mysql, $tracker_row);

// insert gclid and utm vars
insertGclid($mysql);

// insert msclkid and utm vars
insertMsclkid($mysql);

// insert fbclid and utm vars
insertFbclid($mysql);

// now we have the click's advance data, now insert this row
insertClicksAdvance($mysql);

// insert the tracking data
insertClicksTracking($mysql);

// ok we have our click recorded table, now lets insert theses
insertClicksRecord($mysql);
}


if(isset($_GET['t202LpUrl']) && $_GET['t202LpUrl'] != ''){
    $landing_site_url = $_GET['t202LpUrl'];
}else{
    $landing_site_url = $_SERVER['HTTP_REFERER'];
}

include_once (substr(dirname(__FILE__), 0, - 19) . '/tracking202/static/'.$lp_filename);
die();