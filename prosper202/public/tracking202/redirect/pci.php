<?php include_once(str_repeat("../", 2).'202-config/connect2.php'); 
include_once(str_repeat("../", 2).'202-config/class-dataengine-slim.php');

$mysql['click_id_public'] = $db->real_escape_string($_GET['pci']);

if(!isset($mysql['click_id_public']) || $mysql['click_id_public'] ==''){
    $mysql['user_id'] = 1;
    $mysql['ip_address'] = $db->real_escape_string($_SERVER['REMOTE_ADDR']);
    $daysago = time() - 86400; // 24 hours
    $click_sql1 = "	SELECT 	202_clicks.click_id,ppc_account_id,click_id_public 
					FROM 		202_clicks
					LEFT JOIN	202_clicks_advance USING (click_id)
					LEFT JOIN 	202_ips USING (ip_id)
                    LEFT JOIN	202_clicks_record USING (click_id)
					WHERE 	202_ips.ip_address='".$mysql['ip_address']."'
					AND		202_clicks.user_id='".$mysql['user_id']."'
					AND		202_clicks.click_time >= '".$daysago."'
					ORDER BY 	202_clicks.click_id DESC
					LIMIT 		1";

    $click_result1 = $db->query($click_sql1) or record_mysql_error($click_sql1);
    $click_row1 = $click_result1->fetch_assoc();
    $mysql['click_id'] = $db->real_escape_string($click_row1['click_id']);
    $click_id = $mysql['click_id'];
    $mysql['ppc_account_id'] = $db->real_escape_string($click_row1['ppc_account_id']);
    $mysql['click_id_public'] = $db->real_escape_string($click_row1['click_id_public']);
    $pci=$mysql['click_id_public'];

}

if(isset($mysql['click_id_public'])){
	$click_sql = "
	SELECT
		202_clicks.click_id,
		202_clicks.aff_campaign_id,
		click_cloaking,
		click_cloaking_site_url_id,
		click_redirect_site_url_id
	FROM
		202_clicks 
		LEFT JOIN 202_clicks_record USING (click_id) 
		LEFT JOIN 202_clicks_site   USING (click_id)
	WHERE
		click_id_public='".$mysql['click_id_public']."'
";
$click_row = memcache_mysql_fetch_assoc($db, $click_sql);
}else{
	die();
}


$click_id = $click_row['click_id'];
$aff_campaign_id = $click_row['aff_campaign_id'];
$mysql['click_id'] = $db->real_escape_string($click_id);
$mysql['aff_campaign_id'] = $db->real_escape_string($aff_campaign_id);
$mysql['click_out'] = '1';

$click_sql = "UPDATE    202_clicks_record
			  SET       click_out='".$mysql['click_out']."'
			  WHERE     click_id='".$mysql['click_id']."'";
$click_result = $db->query($click_sql) or record_mysql_error($db, $click_sql);


//see if cloaking was turned on
if ($click_row['click_cloaking'] == 1) { 
	$cloaking_on = true;
	$mysql['site_url_id'] = $db->real_escape_string($click_row['click_cloaking_site_url_id']);
	$site_url_sql = "SELECT site_url_address FROM 202_site_urls WHERE site_url_id='".$mysql['site_url_id']."' limit 1";
	$site_url_row = memcache_mysql_fetch_assoc($db, $site_url_sql);
	$cloaking_site_url = $site_url_row['site_url_address'];
} else {
	$cloaking_on = false;
	$mysql['site_url_id'] = $db->real_escape_string($click_row['click_redirect_site_url_id']);
	$site_url_sql = "SELECT site_url_address FROM 202_site_urls WHERE site_url_id='".$mysql['site_url_id']."' limit 1";
	$site_url_row = memcache_mysql_fetch_assoc($db, $site_url_sql);
	$redirect_site_url = $site_url_row['site_url_address'];  	
}

//set the cookie
setClickIdCookie($mysql['click_id'],$mysql['aff_campaign_id']);

//set dirty hour
$de = new DataEngine();
$data=($de->setDirtyHour($mysql['click_id']));
//now we've updated, lets redirect
if ($cloaking_on == true) {
	//if cloaked, redirect them to the cloaked site. 
	header ('location: '.$cloaking_site_url);    
} else {
	header ('location: '.$redirect_site_url);        
}

