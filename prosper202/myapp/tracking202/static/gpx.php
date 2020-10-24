<?php //write out a transparent 1x1 gif
header("content-type: image/gif"); 
header('Content-Length: 43');
header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: Sun, 02 Feb 2002 02:02:00 GMT'); // Date in the past
header("Pragma: no-cache");
header('P3P: CP="Prosper202 does not have a P3P policy"');
echo base64_decode("R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==");

include_once(substr(dirname( __FILE__ ), 0,-19) . '/202-config/connect2.php');
include_once(substr(dirname( __FILE__ ), 0,-19) . '/202-config/class-dataengine-slim.php');
include_once(substr(dirname( __FILE__ ), 0,-19) . '/202-config/convlogs.php');

//get the aff_camapaign_id
$mysql['user_id'] = 1;

if (isset($_GET['subid']) && !empty($_GET['subid']) && is_numeric($_GET['subid'])) {
	$mysql['click_id'] = $db->real_escape_string($_GET['subid']);
} else if (isset($_GET['cid']) && !empty($_GET['cid']) && is_numeric($_GET['cid']) && (null !== ( getCookie202('tracking202subid_a_' . $_GET['cid']) ))) {
	$mysql['click_id'] = $db->real_escape_string( getCookie202('tracking202subid_a_' . $_GET['cid']) );
} else if (null !== ( getCookie202('tracking202subid') )) {
	$mysql['click_id'] = $db->real_escape_string( getCookie202('tracking202subid') );
} else {
    $mysql['ip_address'] = $db->real_escape_string($_SERVER['REMOTE_ADDR']);
    $daysago = time() - 2592000; // 30 days ago
    $sql = "SELECT 202_clicks.click_id
				   FROM 202_clicks
				   LEFT JOIN 202_clicks_advance USING (click_id)
				   LEFT JOIN 202_ips USING (ip_id) 
				   WHERE 202_ips.ip_address='" . $mysql['ip_address'] . "'
				   AND 202_clicks.user_id='" . $mysql['user_id'] . "'  
				   AND 202_clicks.click_time >= '" . $daysago . "'
				   ORDER BY 202_clicks.click_id DESC LIMIT 1";
    $result = $db->query($sql);
    $row = $result->fetch_assoc();
            
    $mysql['click_id'] = $db->real_escape_string($row['click_id']);
}

//if this is a duplicate conversion and dedupe is on then stop processing
if(ignoreDuplicates()){
    header('HTTP/1.1 400 Not Found', true, 400);
    header('Content-Type: application/json');
    $response = array('error' => true, 'code' => 400, 'msg' => 'Duplicate Conversion Found');
    print_r(json_encode($response));
    die();
}

if (is_numeric($mysql['click_id'])) {

	$sql = "SELECT 
					202_cpa_trackers.tracker_id_public, 
					202_trackers.click_cpa, 
					2c.aff_campaign_id, 
					2c.click_lead, 
					2c.click_payout,
					2c.click_time,
					2c.click_lead,
					2up.user_account_currency,
					2ac.aff_campaign_currency,
					2ac.aff_campaign_payout,
					2ac.aff_campaign_foreign_payout
				FROM 202_clicks AS 2c
				LEFT JOIN `202_cpa_trackers` USING (click_id) 
				LEFT JOIN `202_trackers` USING (tracker_id_public)
				LEFT JOIN `202_aff_campaigns` as 2ac ON (2ac.aff_campaign_id =  2c.aff_campaign_id)  
				LEFT JOIN `202_users_pref` AS 2up ON (2c.`user_id` = 2up.`user_id`)
				WHERE click_id = '".$mysql['click_id']."'";

	$result = $db->query($sql);
	$row = $result->fetch_assoc();

	$mysql['user_account_currency'] = $db->real_escape_string($row['user_account_currency']);
	$mysql['aff_campaign_currency'] = $db->real_escape_string($row['aff_campaign_currency']);
	$mysql['aff_campaign_foreign_payout'] = $db->real_escape_string($row['aff_campaign_foreign_payout']);

	//update currency payout value
	updateForeignPayout($mysql);
	
	if (!$row['click_lead'] || (isset($_GET['t202txid'])) && !empty($_GET['t202txid'])) {

		$mysql['original_click_payout'] = $row['click_payout'];
		$mysql['campaign_id'] = $db->real_escape_string($row['aff_campaign_id']);
		$mysql['click_time'] = $db->real_escape_string($row['click_time']);
		$mysql['click_cpa'] = $db->real_escape_string($row['click_cpa']);

		if (isset($_GET['t202txid']) && !empty($_GET['t202txid'])) {
			$mysql['txid'] =  $db->real_escape_string($_GET['t202txid']);
		}

		if (isset($_GET['amount']) && is_numeric($_GET['amount'])) {
			$mysql['click_payout'] = $db->real_escape_string($_GET['amount']);
			$mysql['click_payout_added'] = $mysql['click_payout'];
		} else {
			$mysql['click_payout_added'] = $row['aff_campaign_payout'];
			$mysql['click_payout'] = $row['click_payout'];
		}

		if (!empty($mysql['txid'])) {
			if ($row['click_lead']) {
				$mysql['click_payout'] = $mysql['original_click_payout'] + $mysql['click_payout_added'];
			} else {
				$mysql['click_payout'] = $mysql['click_payout_added'];
			}
		}

		$mysql['click_lead'] = $row['click_lead'] + 1;

		if ($mysql['click_cpa']) {
			$sql_set = "click_cpc='".$mysql['click_cpa']."', click_lead='".$mysql['click_lead']."', click_filtered='0', click_payout='".$mysql['click_payout']."'";
		} else {
			$sql_set = "click_lead='".$mysql['click_lead']."', click_filtered='0', click_payout='".$mysql['click_payout']."'";
		}

		$sql = "SELECT * FROM 202_conversion_logs 
				WHERE click_id = '".$mysql['click_id']."' 
				AND transaction_id = '".$mysql['txid']."' AND deleted = 0";
		$result = $db->query($sql);	

		if ($result->num_rows == 0) {
			$click_sql = "
				UPDATE
					202_clicks 
				SET
					".$sql_set."
				WHERE
					click_id='".$mysql['click_id']."'";
			$db->query($click_sql);


			addConversionLog($mysql['click_id'], $mysql['txid'], $mysql['campaign_id'], $mysql['click_payout_added'], $mysql['user_id'], $mysql['click_time'], $ip_address, $_SERVER['HTTP_USER_AGENT'], null, '1');
		} else {
			$row = $result->fetch_assoc();
			$mysql['adjusted_payout'] = $mysql['original_click_payout'] - $row['click_payout'] + $mysql['click_payout_added'];

			$db->query("UPDATE 202_clicks SET click_payout='".$mysql['adjusted_payout']."' WHERE click_id = '".$mysql['click_id']."'");

			$click_sql = "
				UPDATE
					202_conversion_logs
				SET
					click_payout = '".$mysql['click_payout_added']."'
				WHERE
					click_id='".$mysql['click_id']."' AND transaction_id = '".$mysql['txid']."'";
			$db->query($click_sql);
		}

		//set dirty hour
		setDirtyHour($mysql);
		
	}
}


