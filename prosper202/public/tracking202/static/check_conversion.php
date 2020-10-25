<?php
include_once(substr(dirname( __FILE__ ), 0,-19) . '/202-config/connect2.php');

header("Access-Control-Allow-Origin: *");
header('Cache-Control: no-cache');

if(null !== (getCookie202('t202defpix'))) {
    $defpixels= unserialize(base64_decode(getCookie202('t202defpix')));   
}else{
    $defpixels= []; 
}

if(isset($_REQUEST['t202subid'])){
    $mysql['click_id'] = $db->real_escape_string($_REQUEST['t202subid']);
    
    if (!in_array($mysql['click_id'], $defpixels)) {
        $notfound = true;
    }else{
        $payout = $memcache->get(md5($mysql['click_id'].'payout'));
        if($payout){
            echo $payout;
        }else{
             getPayout($mysql);
             echo $mysql['click_payout'];
        }
       header('HTTP/1.1 202 Moved Permanently');
        die();
    }
	if (is_numeric($mysql['click_id']) && $notfound) {
		$site_urls=" LEFT JOIN `202_clicks_site` AS 2cs ON (2c.click_id=2cs.click_id)
                 LEFT JOIN `202_site_urls` AS 2su ON (2cs.click_referer_site_url_id=2su.site_url_id) ";
		
		//get c1-c4 values etc
		        $cvar_sql ="
        SELECT 
            2cid.click_id,
            2c.user_id,
            2c.aff_campaign_id,
            2c.click_payout,
            2c.click_cpc,
            2c.click_lead,
            2c.click_time,
            2c.ppc_account_id,
            2pap.pixel_code,
            2pap.pixel_type_id,
            2c1.c1,
            2c2.c2,
            2c3.c3,
            2c4.c4,
            2kw.keyword,
            2g.gclid,
            2b.msclkid,
            2f.fbclid,
            2us.utm_source,
            2um.utm_medium,
            2uca.utm_campaign,
            2ut.utm_term,
            2uco.utm_content,
            2trc.click_cpa,
            2su.site_url_address,
            202_aff_campaigns.aff_campaign_payout
        FROM `202_clicks_tracking` AS 2cid
        LEFT JOIN `202_clicks_advance` AS 2ca USING (`click_id`)
        LEFT JOIN `202_google` AS 2g USING (`click_id`)
        LEFT JOIN `202_bing` AS 2b USING (`click_id`)
        LEFT JOIN `202_facebook` AS 2f USING (`click_id`)
        LEFT JOIN `202_clicks` AS 2c USING (`click_id`)
        LEFT JOIN 202_aff_campaigns ON (202_aff_campaigns.aff_campaign_id =  2c.aff_campaign_id) 
        LEFT JOIN 202_ppc_account_pixels 2pap USING (ppc_account_id)
        LEFT JOIN `202_tracking_c1` AS 2c1 USING (`c1_id`)
        LEFT JOIN `202_tracking_c2` AS 2c2 USING (`c2_id`)
        LEFT JOIN `202_tracking_c3` AS 2c3 USING (`c3_id`)
        LEFT JOIN `202_tracking_c4` AS 2c4 USING (`c4_id`)
        LEFT JOIN `202_utm_source` AS 2us ON (2g.`utm_source_id` = 2us.`utm_source_id` AND 2b.`utm_source_id` = 2us.`utm_source_id`)
        LEFT JOIN `202_utm_medium` AS 2um ON (2g.`utm_medium_id` = 2um.`utm_medium_id` AND 2b.`utm_medium_id` = 2um.`utm_medium_id` )
        LEFT JOIN `202_utm_campaign` AS 2uca ON (2g.`utm_campaign_id` = 2uca.`utm_campaign_id` AND 2b.`utm_campaign_id` = 2uca.`utm_campaign_id`)
        LEFT JOIN `202_utm_term` AS 2ut ON (2g.`utm_term_id` = 2ut.`utm_term_id` AND 2b.`utm_term_id` = 2ut.`utm_term_id`)
        LEFT JOIN `202_utm_content` AS 2uco ON (2g.`utm_content_id` = 2uco.`utm_content_id` AND 2b.`utm_content_id` = 2uco.`utm_content_id`)
        LEFT JOIN `202_keywords` AS 2kw ON (2ca.`keyword_id` = 2kw.`keyword_id`)
        LEFT JOIN `202_cpa_trackers` AS 2cpa USING (`click_id`)
        LEFT JOIN `202_trackers` AS 2trc ON (2cpa.`tracker_id_public` = 2trc.`tracker_id_public`)".$site_urls."
        WHERE 2c.`click_id` = {$mysql['click_id']}
        LIMIT 1";
		$cvar_sql_result = $db->query($cvar_sql);
		
		$cvar_sql_row = $cvar_sql_result->fetch_assoc();
				
		$mysql['original_click_payout'] = $db->real_escape_string($cvar_sql_row['click_payout']);
		$mysql['t202kw'] = $db->real_escape_string($cvar_sql_row['keyword']);
		$mysql['c1'] = $db->real_escape_string($cvar_sql_row['c1']);
		$mysql['c2'] = $db->real_escape_string($cvar_sql_row['c2']);
		$mysql['c3'] = $db->real_escape_string($cvar_sql_row['c3']);
		$mysql['c4'] = $db->real_escape_string($cvar_sql_row['c4']);
		$mysql['gclid'] = $db->real_escape_string($cvar_sql_row['gclid']);
		$mysql['msclkid'] = $db->real_escape_string($cvar_sql_row['msclkid']);
		$mysql['fbclid'] = $db->real_escape_string($cvar_sql_row['fbclid']);
		$mysql['utm_source'] = $db->real_escape_string($cvar_sql_row['utm_source']);
		$mysql['utm_medium'] = $db->real_escape_string($cvar_sql_row['utm_medium']);
		$mysql['utm_campaign'] = $db->real_escape_string($cvar_sql_row['utm_campaign']);
		$mysql['utm_term'] = $db->real_escape_string($cvar_sql_row['utm_term']);
		$mysql['utm_content'] = $db->real_escape_string($cvar_sql_row['utm_content']);
		$mysql['click_user_id'] = $db->real_escape_string($cvar_sql_row['user_id']);
		$mysql['campaign_id'] = $db->real_escape_string($cvar_sql_row['aff_campaign_id']);
        $mysql['payout'] = $db->real_escape_string($cvar_sql_row['click_payout']);
        $mysql['click_payout'] = $db->real_escape_string($cvar_sql_row['click_payout']);
        $mysql['click_payout_added'] = $db->real_escape_string($cvar_sql_row['aff_campaign_payout']);
		$mysql['cpc'] = $db->real_escape_string($cvar_sql_row['click_cpc']);
		$mysql['click_cpa'] = $db->real_escape_string($cvar_sql_row['click_cpa']);
		$mysql['click_lead'] = $db->real_escape_string($cvar_sql_row['click_lead']);
		$mysql['click_time'] = $db->real_escape_string($cvar_sql_row['click_time']);
        $mysql['referer'] = urlencode($db->real_escape_string($cvar_sql_row['site_url_address']));
        $mysql['pixel_code'] = stripslashes(($cvar_sql_row['pixel_code']));
        $mysql['pixel_type_id'] = $db->real_escape_string($cvar_sql_row['pixel_type_id']);
       
        
        if($mysql['pixel_code'] == '' ){
		
            header('HTTP/1.1 202 Moved Permanently');
            die();
        }
        
		if($cvar_sql_row['ppc_account_id'] == '0'){
			$mysql['ppc_account_id'] = '';
		}
		else{
			$mysql['ppc_account_id'] = $db->real_escape_string($cvar_sql_row['ppc_account_id']);
		}
		
			$tokens = getTokens($mysql);

            if(isset($mysql['click_lead']) && $mysql['click_lead']==1 && isset($mysql['pixel_code']) && $mysql['pixel_code'] != '' ){
		
                header('HTTP/1.1 202 Moved Permanently');
                
                
            
                if($_GET['show'] == 1){
                    if (!in_array($mysql['click_id'], $defpixels)) {
                        $defpixels[] = $mysql['click_id'];
                    }
                
                    //set the cookie for the PIXEL to fire, expire in 30 days
           $expire = 0;
           $expire_header = 0;
           $path = '/';
           $domain = $_SERVER['HTTP_HOST'];
           $secure = TRUE;
           $httponly = FALSE;
           
           //legacy cookies
           setcookie('t202defpix-legacy', base64_encode(serialize($defpixels)), $expire, '/', $domain);
 
          //samesite=none secure cookies
          if (PHP_VERSION_ID < 70300) {
              header('Set-Cookie: t202defpix='.base64_encode(serialize($defpixels)).';max-age='.$expire_header.';Path=/;Domain='.$domain.';SameSite=None; Secure');        
          }
          else {
              setcookie('t202defpix' , base64_encode(serialize($defpixels)), ['expires' => $expire,'path' => '/','domain' => $domain,'secure' => $secure,'httponly' => $httponly,'samesite' => 'None']);
          }

                    if($mysql['pixel_type_id'] == 5){
                        echo replaceTokens($mysql['pixel_code'],$tokens); 
                        die();               
                    }
                }

                echo $mysql['payout']; //this payout is for the js pixel
                if ($memcacheWorking) {
                    setCache(md5($mysql['click_id'].'payout'), $mysql['click_payout']);                                     
                } 
                die();
                
            }
		}
    
}
else{
    header('HTTP/1.1 202 Moved Permanently');
        die();
}

