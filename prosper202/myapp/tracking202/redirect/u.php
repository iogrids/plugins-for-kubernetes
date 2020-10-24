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
//header('Transfer-Encoding:chunked');
ob_flush();
flush();
$vars=$_REQUEST;
#only allow numeric t202ids

//if (!is_numeric($t202Subid)) die();

# check to see if mysql connection works, if not fail over to cached stored redirect urls
include_once(str_repeat("../", 2).'202-config/connect2.php'); 

if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
    $strProtocol = 'https';
} else {
    $strProtocol = 'http';
}

$_lGET = array_change_key_case($_REQUEST, CASE_LOWER); //make lowercase copy of get
$t202Subid = $_lGET['t202subid'];
$mysql['click_id'] = $db->real_escape_string($t202Subid);
$tracker_sql = "SELECT click_id
				FROM    202_clicks
				WHERE   click_id='".$mysql['click_id']."'";
				
$tracker_row = memcache_mysql_fetch_assoc($db, $tracker_sql);

if (!$tracker_row) { die(); }
//Get C1-C4 IDs
for ($i=1;$i<=4;$i++){
    $custom= "c".$i; //create dynamic variable
    $custom2= "t202c".$i; //create dynamic variable
    
    $custom_val=$db->real_escape_string($_lGET[$custom2]); // get the value

    if(isset($custom_val) && $custom_val !=''){ //if there's a value get an id
        $custom_val = str_replace('%20',' ',$custom_val);
        $custom_id = INDEXES::get_custom_var_id($db, $custom, $custom_val); //get the id
        $mysql[$custom.'_id']=$db->real_escape_string($custom_id); //save it
        $mysql[$custom]=$db->real_escape_string($custom_val); //save it
        $sql.=" `".$custom."_id`=".$mysql[$custom.'_id'].",";
    }
}

if(isset($sql)){
    
    $sql="UPDATE `202_clicks_tracking` SET".rtrim($sql,',')."  WHERE `click_id`= ".$mysql['click_id'];
    $db->query($sql);
}


//keywords
$keyword = $db->real_escape_string($_lGET['t202kw']);
if(isset($keyword) && $keyword != ''){
$keyword = str_replace('%20',' ',$keyword);
$keyword_id = INDEXES::get_keyword_id($db, $keyword);
$mysql['keyword_id'] = $db->real_escape_string($keyword_id);
$mysql['keyword'] = $db->real_escape_string($keyword);

$sql="UPDATE `202_clicks_advance` SET `keyword_id`='".$mysql['keyword_id']."' WHERE `click_id`= ".$mysql['click_id'];
$db->query($sql);

unset($sql);
}

//referer
$referer = $db->real_escape_string($_lGET['t202referrer']);
if(isset($referer) && $referer != ''){
    $mysql['click_referer_site_url_id'] = INDEXES::get_site_url_id($db, $referer);
    if(isset($mysql['click_referer_site_url_id'])){
    $sql="UPDATE  202_clicks_site
         SET click_referer_site_url_id='" . $mysql['click_referer_site_url_id']. "' 
        WHERE `click_id`= ".$mysql['click_id'];
    $db->query($sql);
    unset($sql);
    }
}

//t202id
$t202id = $db->real_escape_string($_lGET['t202id']);
if(isset($t202id) && $t202id != ''){
    $mysql['tracker_id_public'] = $t202id;
    $tracker_sql = "SELECT 2tr.text_ad_id,
							2tr.ppc_account_id,
							2tr.click_cpc,
                            2ac.aff_campaign_payout as click_payout
					FROM    202_trackers AS 2tr
					LEFT JOIN 202_ppc_accounts AS 2ppc USING (ppc_account_id)
                    LEFT JOIN 202_aff_campaigns AS 2ac USING (aff_campaign_id)
					LEFT JOIN (SELECT ppc_network_id, GROUP_CONCAT(ppc_variable_id) AS ppc_variable_ids, GROUP_CONCAT(parameter) AS parameters FROM 202_ppc_network_variables GROUP BY ppc_network_id) AS 2cv USING (ppc_network_id)
					WHERE   2tr.tracker_id_public='" . $mysql['tracker_id_public'] . "'";
    $tracker_row = memcache_mysql_fetch_assoc($db, $tracker_sql);

    if(!isset($tracker_row['click_payout']) || $tracker_row['click_payout']==''){
        $tracker_row['click_payout'] = '0';
    }
    if(isset($mysql['tracker_id_public'])){
        $sql="UPDATE 202_clicks
			  SET        
							ppc_account_id = '" . $tracker_row['ppc_account_id'] . "',
							click_cpc = '" . $tracker_row['click_cpc'] . "',
							click_payout = '" . $tracker_row['click_payout'] . "'
							WHERE   click_id='" . $mysql['click_id'] . "'";
        $db->query($sql);
        unset($sql);
    }
}




$utm_source = $db->real_escape_string($_lGET['t202utm_source']);
if(isset($utm_source) && $utm_source != '')
{
    $utm_source = str_replace('%20',' ',$utm_source);
    $utm_source_id = INDEXES::get_utm_id($db, $utm_source, 'utm_source');
    $mysql['utm_source_id']=$db->real_escape_string($utm_source_id);
    $mysql['utm_source']=$db->real_escape_string($utm_source);
    
    $sql="UPDATE 202_google
    SET        
          `utm_source_id`=".$mysql['utm_source_id']. "
          WHERE   click_id='" . $mysql['click_id'] . "'";
          $db->query($sql);
          if($db->affected_rows == 0){
              $sql="INSERT INTO 202_google
        SET        
          `gclid`='',
          `utm_source_id`=".$mysql['utm_source_id']. ",
          `click_id`=" . $mysql['click_id'];
          $db->query($sql);
          }
          unset($sql); 
}

//utm_medium
$utm_medium = $db->real_escape_string($_lGET['t202utm_medium']);
if(isset($utm_medium) && $utm_medium != '')
{
    $utm_medium = str_replace('%20',' ',$utm_medium);
    $utm_medium_id = INDEXES::get_utm_id($db, $utm_medium, 'utm_medium');
    $mysql['utm_medium_id']=$db->real_escape_string($utm_medium_id);
    $mysql['utm_medium']=$db->real_escape_string($utm_medium);

    $sql="UPDATE 202_google
    SET        
          `utm_medium_id`=".$mysql['utm_medium_id']. "
          WHERE   click_id='" . $mysql['click_id'] . "'";
          $db->query($sql);
          if($db->affected_rows == 0){
              $sql="INSERT INTO 202_google
        SET        
          `gclid`='',
          `utm_medium_id`=".$mysql['utm_medium_id']. ",
          `click_id`=" . $mysql['click_id'];
          $db->query($sql);
          }
          unset($sql); 
}

//utm_campaign
$utm_campaign = $db->real_escape_string($_lGET['t202utm_campaign']);
if(isset($utm_campaign) && $utm_campaign != '')
{
    $utm_campaign = str_replace('%20',' ',$utm_campaign);
    $utm_campaign_id = INDEXES::get_utm_id($db, $utm_campaign, 'utm_campaign');
    $mysql['utm_campaign_id']=$db->real_escape_string($utm_campaign_id);
    $mysql['utm_campaign']=$db->real_escape_string($utm_campaign);
   
    $sql="UPDATE 202_google
    SET        
          `utm_campaign_id`=".$mysql['utm_campaign_id']. "
          WHERE   click_id='" . $mysql['click_id'] . "'";
          $db->query($sql);
          if($db->affected_rows == 0){
              $sql="INSERT INTO 202_google
        SET        
          `gclid`='',
          `utm_campaign_id`=".$mysql['utm_campaign_id']. ",
          `click_id`=" . $mysql['click_id'];
          $db->query($sql);
          }
          unset($sql);  
}

//utm_term
$utm_term = $db->real_escape_string($_lGET['t202utm_term']);

if(isset($utm_term) && $utm_term != '')
{
    $utm_term = str_replace('%20',' ',$utm_term);
    $utm_term_id = INDEXES::get_utm_id($db, $utm_term, 'utm_term');
    $mysql['utm_term_id']=$db->real_escape_string($utm_term_id);
    $mysql['utm_term']=$db->real_escape_string($utm_term);

    $sql="UPDATE 202_google
            SET        
                  `utm_term_id`=".$mysql['utm_term_id']. "
                  WHERE   click_id='" . $mysql['click_id'] . "'";
                  $db->query($sql);
                  if($db->affected_rows == 0){
                      $sql="INSERT INTO 202_google
                SET        
                  `gclid`='',
                  `utm_term_id`=".$mysql['utm_term_id']. ",
                  `click_id`=" . $mysql['click_id'];
                  $db->query($sql);
                  }
                  unset($sql);
}

//utm_content
$utm_content = $db->real_escape_string($_lGET['t202utm_content']);
if(isset($utm_content) && $utm_content != '')
{
    $utm_content = str_replace('%20',' ',$utm_content);
    $utm_content_id = INDEXES::get_utm_id($db, $utm_content, 'utm_content');
    $mysql['utm_content_id']=$db->real_escape_string($utm_content_id);
    $mysql['utm_content']=$db->real_escape_string($utm_content);

    $sql="UPDATE 202_google
            SET        
                  `utm_content_id`=".$mysql['utm_content_id']. "
                  WHERE   click_id='" . $mysql['click_id'] . "'";
                  $db->query($sql);
                  if($db->affected_rows == 0){
                      $sql="INSERT INTO 202_google
                SET        
                  `gclid`='',
                  `utm_content_id`=".$mysql['utm_content_id']. ",
                  `click_id`=" . $mysql['click_id'];
                  $db->query($sql);
                  }
                  unset($sql);
}

if(isset($sql)){
    $db->query($sql);
}

//set dirty hour
    $dsql = " insert into 202_dataengine(user_id,
click_id,
click_time,
ppc_network_id,
ppc_account_id,
aff_network_id,
aff_campaign_id,
landing_page_id,
keyword_id,
utm_source_id,
utm_medium_id,
utm_campaign_id,
utm_term_id,
utm_content_id,
text_ad_id,
click_referer_site_url_id,
country_id,
region_id,
city_id,
isp_id,
browser_id,
device_id,
platform_id,
ip_id,
c1_id,
c2_id,
c3_id,
c4_id,
variable_set_id,
rotator_id,
rule_id,
rule_redirect_id,
click_lead,
click_filtered,
click_bot,
click_alp,
clicks,
click_out,
leads,
payout,
income,
cost)  
    SELECT 
2c.user_id,
2c.click_id,
2c.click_time,
2pn.ppc_network_id, 
2c.ppc_account_id,
2an.aff_network_id,
2ac.aff_campaign_id,
2c.landing_page_id,
2k.keyword_id,
2gg.utm_source_id,
2gg.utm_medium_id,
2gg.utm_campaign_id,
2gg.utm_term_id,
2gg.utm_content_id,
2ta.text_ad_id,
2cs.click_referer_site_url_id,
2cy.country_id,
2rg.region_id,
2ci.city_id,
2is.isp_id,
2b.browser_id,
2dm.device_id,
2p.platform_id,
2ca.ip_id,
2tc1.c1_id,
2tc2.c2_id,
2tc3.c3_id,
2tc4.c4_id,
2cv.variable_set_id,
2rc.rotator_id,
2rc.rule_id,
2rc.rule_redirect_id,
2c.`click_lead`,
2c.`click_filtered`,
2c.`click_bot`,
2c.`click_alp`, 
1 AS clicks, 
2cr.click_out AS click_out, 
2c.click_lead AS leads, 
2c.click_payout AS payout, 
IF (2c.click_lead>0,2c.click_payout,0) AS income, 
2c.click_cpc AS cost 
FROM 202_clicks AS 2c 
LEFT OUTER JOIN 202_clicks_record AS 2cr ON (2c.click_id = 2cr.click_id) 
LEFT OUTER JOIN 202_aff_campaigns AS 2ac ON (2c.aff_campaign_id = 2ac.aff_campaign_id) 
LEFT OUTER JOIN 202_clicks_advance AS 2ca ON (2c.click_id = 2ca.click_id) 
LEFT OUTER JOIN 202_browsers AS 2b ON (2ca.browser_id = 2b.browser_id) 
LEFT OUTER JOIN 202_platforms AS 2p ON (2ca.platform_id = 2p.platform_id) 
LEFT OUTER JOIN 202_aff_networks AS 2an ON (2ac.aff_network_id = 2an.aff_network_id) 
LEFT OUTER JOIN 202_ppc_accounts AS 2pa ON (2c.ppc_account_id = 2pa.ppc_account_id) 
LEFT OUTER JOIN 202_ppc_networks AS 2pn ON (2pa.ppc_network_id = 2pn.ppc_network_id)
LEFT OUTER JOIN 202_keywords AS 2k ON (2ca.keyword_id = 2k.keyword_id)
LEFT OUTER JOIN 202_google AS 2gg ON (2c.click_id = 2gg.click_id)
LEFT OUTER JOIN 202_landing_pages AS 2lp ON (2c.landing_page_id = 2lp.landing_page_id)
LEFT OUTER JOIN 202_text_ads AS 2ta ON (2ca.text_ad_id = 2ta.text_ad_id)
LEFT OUTER JOIN 202_clicks_site AS 2cs ON (2c.click_id = 2cs.click_id)
LEFT OUTER JOIN 202_clicks_tracking AS 2ct ON (2c.click_id = 2ct.click_id)
LEFT OUTER JOIN 202_site_urls AS 2suf ON (2cs.click_referer_site_url_id = 2suf.site_url_id) 
LEFT OUTER JOIN 202_locations_country AS 2cy ON (2ca.country_id = 2cy.country_id) 
LEFT OUTER JOIN 202_locations_region AS 2rg ON (2ca.region_id = 2rg.region_id) 
LEFT OUTER JOIN 202_locations_city AS 2ci ON (2ca.city_id = 2ci.city_id)
LEFT OUTER JOIN 202_locations_isp AS 2is ON (2ca.isp_id = 2is.isp_id) 
LEFT OUTER JOIN 202_device_models AS 2dm ON (2ca.device_id = 2dm.device_id)
LEFT OUTER JOIN 202_ips AS 2i ON (2ca.ip_id = 2i.ip_id)
LEFT OUTER JOIN 202_tracking_c1 AS 2tc1 ON (2ct.c1_id = 2tc1.c1_id) 
LEFT OUTER JOIN 202_tracking_c2 AS 2tc2 ON (2ct.c2_id = 2tc2.c2_id) 
LEFT OUTER JOIN 202_tracking_c3 AS 2tc3 ON (2ct.c3_id = 2tc3.c3_id) 
LEFT OUTER JOIN 202_tracking_c4 AS 2tc4 ON (2ct.c4_id = 2tc4.c4_id)
LEFT OUTER JOIN 202_clicks_variable AS 2cv ON (2c.click_id = 2cv.click_id)
LEFT OUTER JOIN 202_clicks_rotator AS 2rc ON (2c.click_id = 2rc.click_id)
WHERE 2c.click_id=" . $mysql['click_id']."
on duplicate key update
c1_id=values(c1_id),
c2_id=values(c2_id), 
c3_id=values(c3_id),
c4_id=values(c4_id),   
utm_source_id=values(utm_source_id),
utm_medium_id=values(utm_medium_id),
utm_campaign_id=values(utm_campaign_id),
utm_term_id=values(utm_term_id),
utm_content_id=values(utm_content_id),
click_lead=values(click_lead),
click_bot=values(click_bot),
click_out=values(click_out),
click_filtered=values(click_filtered),  
click_referer_site_url_id=values(click_referer_site_url_id), 
leads=values(leads),
payout=values(payout),
income=values(income),
cost=values(cost),
keyword_id=values(keyword_id),
rotator_id=values(rotator_id),
rule_id=values(rule_id),
rule_redirect_id=values(rule_redirect_id),
ppc_account_id=values(ppc_account_id),
aff_campaign_id=values(aff_campaign_id),
aff_network_id=values(aff_network_id)";

$result = $db->query($dsql);