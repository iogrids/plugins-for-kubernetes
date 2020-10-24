<?php include_once(str_repeat("../", 2).'202-config/connect2.php'); 

$usedCachedRedirect = false;
if (!$db) $usedCachedRedirect = true;

//the mysql server is down, use the cached redirect
if ($usedCachedRedirect==true) {
    
    $_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
    $mysql['click_id_public'] = $_GET['pci']; 
}
else{
//use the database
$mysql['click_id_public'] = $db->real_escape_string($_GET['pci']);
if(isset($_GET['202vars'])){
$mysql['202vars'] = base64_decode($db->real_escape_string($_GET['202vars']));
}
if($mysql['click_id_public']=='' || !is_numeric($mysql['click_id_public'])){ //if no pci found look for it in past 48 hours
    $mysql['ip_address'] = $ip_address->address;
    $daysago = time() - 86400 * 2; // past 48 hours
    $subid_sql="SELECT MAX(click_id) as click_id,click_id_public,202_ips.ip_address,202_ips.ip_id
	FROM 202_clicks_record
	LEFT JOIN 202_clicks USING (click_id)
	LEFT JOIN 202_clicks_advance USING (click_id)
	LEFT JOIN 202_ips USING (ip_id)
	LEFT JOIN 202_ips_v6 AS 2i6 ON (2i6.ip_id=202_ips.ip_address)
	WHERE 202_ips.ip_id='".INDEXES::get_ip_id($db,$ip_address)."'
	AND 202_clicks.click_time >= '".$daysago."'
	 AND click_id_public != '0'";

    $result = $db->query($subid_sql);
    $row = $result->fetch_assoc();
    $mysql['click_id'] = $db->real_escape_string($row['click_id']);
}


}

if(isset($mysql['click_id_public']) && $mysql['click_id_public']!= ''){
$tracker_sql = "
	SELECT
		aff_campaign_name,
		site_url_address,
		user_pref_cloak_referer
	FROM
		202_clicks, 202_clicks_record, 202_clicks_site, 202_site_urls, 202_aff_campaigns,202_users_pref
	WHERE
		202_clicks.aff_campaign_id = 202_aff_campaigns.aff_campaign_id
		AND 202_users_pref.user_id = 1
		AND 202_clicks.click_id = 202_clicks_record.click_id
		AND 202_clicks_record.click_id_public='".$mysql['click_id_public']."'
		AND 202_clicks_record.click_id = 202_clicks_site.click_id 
		AND 202_clicks_site.click_redirect_site_url_id = 202_site_urls.site_url_id
";

$tracker_row = memcache_mysql_fetch_assoc($db, $tracker_sql);
$referrer = $tracker_row['user_pref_cloak_referer'];
}

if (!isset($tracker_row)) {
	$action_site_url = "/202-404.php";
	$redirect_site_url = "/202-404.php";
} else {
	$action_site_url = dirname($_SERVER['PHP_SELF'])."/cl2.php";
	//modify the redirect site url to go through another cloaked link
	$redirect_site_url = $tracker_row['site_url_address'];  
}

$html['aff_campaign_name'] = $tracker_row['aff_campaign_name']; 

if(isset($mysql['202vars'])&&$mysql['202vars']!=''){
	//remove & at the end of the string

	if(!parse_url ($redirect_site_url,PHP_URL_QUERY)){
		
		//if there is no query url the add a ? to thecVars but before doing that remove case where there may be a ? at the end of the url and nothing else
		$redirect_site_url = rtrim($redirect_site_url,'?');

		//remove the & from thecVars and put a ? in front of it

		$redirect_site_url .="?".$mysql['202vars'];

	}
	else {

		$redirect_site_url .="&".$mysql['202vars'];

	}}
	
	?>

<html>
	<head>
		<title><?php echo $html['aff_campaign_name']; ?></title>
		<meta name="robots" content="noindex">
		<meta name="referrer" content="<?php echo $referrer; ?>">
		<meta http-equiv="refresh" content="0; url=<?php echo $redirect_site_url; ?>">
	</head>
	<body>
	
		<form name="form1" id="form1" method="get" action="<?php echo $action_site_url; ?>">
			<input type="hidden" name="q" value="<?php echo $redirect_site_url; ?>"/>
			<input type="hidden" name="r" value="<?php echo $referrer; ?>"/>
		</form>
		<script type="text/javascript">
			document.form1.submit();
		</script>
		
		
		<div style="padding: 30px; text-align: center;">
			You are being automatically redirected to <?php echo $html['aff_campaign_name']; ?>.<br/><br/>
			Page Stuck? <a href="<?php echo $redirect_site_url; ?>">Click Here</a>.
		</div>
	</body> 
</html> 