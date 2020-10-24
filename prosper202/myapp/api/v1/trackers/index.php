<?php
include_once(str_repeat("../", 3).'202-config.php');
include_once(str_repeat("../", 3).'202-config/connect2.php');
include_once(str_repeat("../", 1).'functions.php');

header('Content-Type: application/json');
$data = array();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$data = getTrackers($db, $_GET);
} else {
	$data = array('msg' => 'Not allowed request method', 'error' => true, 'status' => 405);
}

$fruits = array (
			
    "trackers" => $data
    
    );
array_walk_recursive($fruits, function(&$val) {
    $val = utf8_encode($val);
});


$json = str_replace('\\/', '/', json_encode($fruits));

print_r(pretty_json($json));
?>