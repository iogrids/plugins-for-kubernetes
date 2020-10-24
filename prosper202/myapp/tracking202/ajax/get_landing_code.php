<?php include_once(str_repeat("../", 2).'202-config/connect.php'); 

AUTH::require_user();

$slack = false;
$mysql['user_id'] = $db->real_escape_string($_SESSION['user_id']);
$mysql['user_own_id'] = $db->real_escape_string($_SESSION['user_own_id']);
$user_sql = "SELECT 2u.user_name as username, 2up.user_slack_incoming_webhook AS url FROM 202_users AS 2u INNER JOIN 202_users_pref AS 2up ON (2up.user_id = 1) WHERE 2u.user_id = '".$mysql['user_own_id']."'";
$user_results = $db->query($user_sql);
$user_row = $user_results->fetch_assoc();

if (!empty($user_row['url'])) 
	$slack = new Slack($user_row['url']);
	
//check variables
	if(empty($_POST['aff_network_id'])) { $error['aff_network_id'] = '<div class="error"><small><span class="fui-alert"></span> You have not selected an Category.</small></div>'; }
	if(empty($_POST['aff_campaign_id'])) { $error['aff_campaign_id'] = '<div class="error"><small><span class="fui-alert"></span>You have not selected a campaign.</small></div>'; }
	if(empty($_POST['method_of_promotion'])) { $error['method_of_promotion'] = '<div class="error"><small><span class="fui-alert"></span>You have to select your method of promoting for this campaign.</small></div>'; }
	
	echo $error['aff_network_id'] . $error['aff_campaign_id'] . $error['method_of_promotion'];
	
	if ($error) { die(); }  
	
//but we'll allow them to choose the following options, can make a tracker link without but they will be notified
	//if they do a landing page, make sure they have one
	if ($_POST['method_of_promotion'] == 'landingpage') { 
		if (empty($_POST['landing_page_id'])) {
			$error['landing_page_id'] = '<div class="error"><small><span class="fui-alert"></span>You have not selected a landing page to use.</small></div>'; 
		}
		
		echo $error['landing_page_id']; 
		if ($error['landing_page_id']) { die(); }    
	}

//echo error
	echo $error['text_ad_id'] . $error['ppc_network_id'] . $error['ppc_account_id'] . $error['cpc'] . $error['click_cloaking'] . $error['cloaking_url'];

//show tracking code

	$mysql['landing_page_id'] = $db->real_escape_string($_POST['landing_page_id']);
	$landing_page_sql = "SELECT * FROM 202_landing_pages LEFT JOIN 202_aff_campaigns USING (aff_campaign_id) LEFT JOIN 202_aff_networks USING (aff_network_id) WHERE landing_page_id='".$mysql['landing_page_id']."'";
	$landing_page_result = $db->query($landing_page_sql) or record_mysql_error($landing_page_sql);
	$landing_page_row = $landing_page_result->fetch_assoc();
	$html['landing_page_id_public'] = htmlentities($landing_page_row['landing_page_id_public']);
	$html['aff_campaign_url'] = htmlentities($landing_page_row['aff_campaign_url']);
	
	if ($slack)
		$slack->push('simple_landing_page_code_generated', array('name' => $landing_page_row['landing_page_nickname'], 'campaign' => $landing_page_row['aff_campaign_name'], 'network' => $landing_page_row['aff_network_name'], 'user' => $user_row['username']));

	$parsed_url = parse_url($landing_page_row['landing_page_url']);
	
	?><small><em><u>Make sure you test out all the links to make sure they work yourself before running them live.</u></em></small><?php 	

	if ($_POST['method_of_promotion'] == 'landingpage') {
    if($_POST['secure_type'] == '1'){
        $protocol = "https://";
    }
    else{
        $protocol = "https://";
	}
	
	if($_POST['deferred_pixel_status'] == '1'){
        $deferred_pixel_code = '&amp;defpixel=1';
    }
    else{
        $deferred_pixel_code = '';
	}
	
	$affiliate_link = $protocol . getTrackingDomain() . get_absolute_url().'tracking202/redirect/go.php?lpip=' . $html['landing_page_id_public'];
	$html['affiliate_link'] = htmlentities($affiliate_link);

	$javascript_code = '
    <script src="'.$protocol.getTrackingDomain() . get_absolute_url().'tracking202/static/landing.js?lpip=' . $html['landing_page_id_public'] . $deferred_pixel_code.'" id="t202js" async></script>';
	
	$html['javascript_code'] = htmlentities($javascript_code);
	printf('<br></br><small><strong>Inbound Javascript Landing Page Code:</strong></small><br/>
            <span class="infotext">This is the javascript code should be put right above your &#60;&#47;body&#62; tag on <u>only</u> the page(s) where your visitors will first arrive to.
			This code is not supposed to be placed on every single page on your website. For example this <u>is not</u> to be placed in a template file that is to be included on everyone of your pages.</span>
            <textarea class="form-control" rows="1" style="background-color: #f5f5f5; font-size: 12px;">%s</textarea>', $html['javascript_code']);

	printf('<br/><small><strong>Option 1: New Outbound Zero-Redirect PurLink&trade; Technology:</strong></small><br/>
			<span class="infotext">Use our new PurLink&trade; Technology to eliminate slowness and revenue loss caused by the extra redirects between regular outbound tracking links and your offer. The PurLink looks exactly like your step 3 link, but behind the scenes Prosper202 dynamically fills in all the tokens and subids when visitors land on your page.<br/>
            </span><br/>
            <textarea class="form-control" rows="1" style="background-color: #f5f5f5; font-size: 12px;">%s</textarea>', $html['aff_campaign_url']);
	
	
	printf('<br/><small><strong>Option 2: Landing Page: Outbound Redirect Link:</strong></small><br/>
			<span class="infotext">Our original outbound redirect option. Use this link if you don\'t want to manualy upload PHP code to your server<br/>
            </span><br/>
            <textarea class="form-control" rows="1" style="background-color: #f5f5f5; font-size: 12px;">%s</textarea>', $html['affiliate_link']);

	
	
	$affiliate_link = $protocol . getTrackingDomain() . get_absolute_url().'tracking202/redirect/lp.php?lpip=' . $html['landing_page_id_public'];
	$html['affiliate_link'] = htmlentities($affiliate_link);
	
	$outbound_php = '<?php
	
  // -------------------------------------------------------------------
  //
  // Tracking202 PHP Redirection, created on ' . date('D M, Y',time()) .'
  //
  // This PHP code is to be used for the following landing page.
  // ' . $landing_page_row['landing_page_url'] . '
  //
  // -------------------------------------------------------------------

  function getCookie202($cookieName){
    $cookieValue = null;
    $legacyCookie = $cookieName.\'-legacy\';
    // check new format
    if(isset($_COOKIE[$cookieName])){
        $cookieValue=$_COOKIE[$cookieName];
    }// if not found check legacy
    else{
        if(isset($_COOKIE[$legacyCookie])){
            $cookieValue=$_COOKIE[$legacyCookie];     
        }   
    }
  return $cookieValue;
}
  if(null !== (getCookie202(\'tracking202outbound\'))) {
	$tracking202outbound = getCookie202(\'tracking202outbound\');
  } else {
	$tracking202outbound = \''.$html['affiliate_link'].'&pci=\'.getCookie202(\'tracking202pci\');
  }
	
  header(\'location: \'.$tracking202outbound);
	
?>';
	$html['outbound_php'] = htmlentities($outbound_php);
	
	printf('<br/><small><strong>Option 3: Landing Page: Outbound PHP Redirect Code:</strong></small><br/>
			<span class="infotext">This is the php code so you can <u>cloak your link</u>.
            Instead of having your link be seen on your outgoing links on your landing page,
			you can have your outgoing links just go to another page on your site,
            which then redirects the visitor to your link<br/><br/>
            So for example, if you wanted to have yourdomain.com/redirect.php be your cloaked link,
            on redirect.php you would place our <u>outbound php redirect code</u>.
            When the visitor goes to redirect.php with our outbound php code installed,
            they simply get redirected out to your link.<br/><br/>
            You must have PHP installed on your server for this to work! </span><br/>
            <p><textarea class="form-control" rows="20" style="background-color: #f5f5f5; font-size: 12px;">%s</textarea></p>', $html['outbound_php']);
	
	$outbound_javascript = '
<!DOCTYPE html>
<html>
<head>
	<title>GO</title>
</head>
<body>

<!-- PLACE OTHER LANDING PAGE CLICK THROUGH CONVERSION TRACKING PIXELS HERE -->
	
<!-- NOW THE TRACKING202 REDIRECTS OUT -->
<script type="text/javascript">
if (readCookie(\'tracking202outbound\') != \'\') {
	window.location=readCookie(\'tracking202outbound\');
} else {
	window.location=\''.$protocol. getTrackingDomain() . get_absolute_url().'tracking202/redirect/lp.php?lpip=' . $html['landing_page_id_public'] .'\';
}

function readCookie(name) {
    var nameEQ = name + "=";
    var nameEQLegacy = name + "-legacy=";
	var ca = document.cookie.split(\';\');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==\' \') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return urldecode(c.substring(nameEQ.length,c.length));
        if (c.indexOf(nameEQLegacy) == 0) return urldecode(c.substring(nameEQLegacy.length,c.length));
	}
	return false;
}

function urldecode(url) {
	  return decodeURIComponent(url.replace(/\+/g, \' \'));
}      
</script>
</body>
</html>';
	
	$html['outbound_javascript'] = htmlentities($outbound_javascript);
	printf('<strong><small><br/>Option 4: Landing Page: Outbound Javascript Redirect Code:</strong></small><br/>
			<span class="infotext">This allows you to generate a javascript redirect instead of a PHP redirect. 
			This is useful when you want to use other services like google website optimizers
			 to track the click-through ratios on your landing pages. With the normal PHP redirect
			 you previously could not do this.  With the new Javascript Redirect, you can place
			 other javascript tags to fire before processing the javascript redirect.</span><br></br>
             <textarea class="form-control" rows="35" style="background-color: #f5f5f5; font-size: 12px;">%s</textarea>', $html['outbound_javascript']);

	getDynamicContentSegment();

} 
  ?>