<?php
include_once(substr(dirname( __FILE__ ), 0,-19) . '/202-config/connect.php');
include_once(substr(dirname( __FILE__ ), 0,-19) . '/202-config/class-dataengine-slim.php');
include_once(substr(dirname(__FILE__), 0, - 19).'/202-config/convlogs.php');

$click_sql = 'SELECT click_payout, click_lead, click_time, aff_campaign_id FROM 202_clicks WHERE click_id = {$sub_id}';
$click_cpa_sql = 'SELECT 202_cpa_trackers.tracker_id_public, 202_trackers.click_cpa FROM 202_cpa_trackers LEFT JOIN 202_trackers USING (tracker_id_public) WHERE click_id = {$click_id}';

$mysql['user_id'] = 1;
if (isset($_POST['tracking_id']) && !empty($_POST['tracking_id']) && is_numeric($_POST['tracking_id'])) {
          $mysql['click_id'] = $db->real_escape_string($_POST['tracking_id']);
          $mysql['conv_time'] = $db->real_escape_string($_POST['transaction_time']);
          
  		$transAmount = $db->real_escape_string($_POST['affiliate_commission_amount']);

				$click_result = $db->query(strtr($click_sql, array('{$sub_id}' => $mysql['click_id'])));
				if ($click_result->num_rows > 0) {
			        $click_row = $click_result->fetch_assoc();

			        $cpa_result = $db->query(strtr($click_cpa_sql, array('{$click_id}' => $mysql['click_id'])));
        			$cpa_row = $cpa_result->fetch_assoc();
        			if (!($cpa_result->num_rows < 0)) {
                        $mysql['click_time'] = $db->real_escape_string($click_row['click_time']);
                        $mysql['click_cpa'] = $db->real_escape_string($cpa_row['click_cpa']);
                        $mysql['campaign_id'] = $db->real_escape_string($click_row['aff_campaign_id']);
        			}

        			if ($click_row['click_lead']) {
                        $mysql['click_payout'] = $db->real_escape_string($click_row['click_payout'] + $transAmount);
                        $mysql['click_lead'] = intval($db->real_escape_string($click_row['click_lead'])) + 1;
				    } else {
                        $mysql['click_payout'] = $db->real_escape_string($transAmount);
                        $mysql['click_lead'] = 1;
				    }

				    if ($mysql['click_cpa']) {
				        $sql_set = "click_cpc='".$mysql['click_cpa']."', click_lead='".$mysql['click_cpa']."', click_filtered='0', click_payout='".$mysql['click_payout']."'";
				    } else {
				        $sql_set = "click_lead='".$mysql['click_lead']."', click_filtered='0', click_payout='".$mysql['click_payout']."'";
				    }

				    $click_sql = "
				        UPDATE
				            202_clicks 
				        SET
				            ".$sql_set."
				        WHERE
				            click_id='".$mysql['click_id']."'    
				        ";
				    $db->query($click_sql);

                    addConversionLog($mysql['click_id'], $mysql['txid'], $mysql['campaign_id'], $mysql['click_payout'], $mysql['user_id'], $mysql['click_time'], $ip_address, $_SERVER['HTTP_USER_AGENT'], $mysql['conv_time'], '4');

				    //set dirty hour
				    $de = new DataEngine();
				    $data=($de->setDirtyHour($mysql['click_id']));
			    }
    	
    
}

?>