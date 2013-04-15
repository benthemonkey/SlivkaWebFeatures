<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$slivkans = $points_center->getSlivkans();
$events = $points_center->getEvents();
$points = $points_center->getPoints();
$helperpoints = $points_center->getHelperPoints();
$committeeattendance = $points_center->getCommitteeAttendance();
$bonuspoints = $points_center->getBonusPoints();
$p2p_days = $points_center->getP2PDays();

$points_table = array(); #table that is slivkan count by event count + 6

for($s=0; $s < count($slivkans['full_name']); $s++){
	$events_total = 0;
	$event_points = array();
	$helper_points = 0;
	$p2p_points = array();
	$im_points = array();
	$nu_email = $slivkans['nu_email'][$s];

	for($e=0; $e < count($events['event_name']); $e++){
		$event_name = $events['event_name'][$e];

		if(in_array($nu_email, $points[$event_name])){
			$event_points_tmp = 1;
			if($events['type'][$e] != "im"){ 
				$events_total++; 
			}else{ 
				$im_points[$events['description'][$e]]++; 
			}
		}else{
			$event_points_tmp = 0;
		}

		if(in_array($nu_email, $helperpoints[$event_name])){
			$event_points_tmp .= "h";
			$helper_points++;
		}elseif(in_array($nu_email, $committeeattendance[$event_name])){
			#$event_points_tmp .= "c";										#####NOT NOTING COMMITTEES#######
		}

		$event_points[] = $event_points_tmp;
	}

	#handling helper points max
	if($helper_points > 5){
		$helper_points = 5;
	}

	#handling IMs:
	$im_points_actual = 0;
	foreach($im_points as $im){
		if($im >= 3){ $im_points_actual += $im; }
	}

	#handling bonus points:
	$bonus_points = 0;
	$committee_points = 0;
	if(array_key_exists($nu_email,$bonuspoints)){
		$bonus_points = $bonuspoints[$nu_email][0]['other1']+$bonuspoints[$nu_email][0]['other2']+$bonuspoints[$nu_email][0]['other3'];
		$committee_points = $bonuspoints[$nu_email][0]['committee'];
	}

	$total = $events_total + $helper_points + $im_points_actual + $bonus_points + $committee_points;

	$points_table[$slivkans['full_name'][$s]] = array_merge(
		array($slivkans['gender'][$s]),
		$event_points,
		array($events_total,$helper_points,$im_points_actual,$committee_points,$bonus_points,$total)
	);
}

echo json_encode(array(points_table => $points_table, events => $events));

?>