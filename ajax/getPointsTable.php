<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$slivkans = $points_center->getSlivkans();
$events = $points_center->getEvents();
$points = $points_center->getPoints();
$helperpoints = $points_center->getHelperPoints();
$committeeattendance = $points_center->getCommitteeAttendance();
$p2p_days = $points_center->getP2PDays();

$points_table = array(); #table that is slivkan count by event count + 3

for($s=0; $s < count($slivkans['full_name']); $s++){
	$events_total = 0;
	$event_points = array();
	$helper_points = 0;
	$p2p_points = array();
	$im_points = array();

	for($e=0; $e < count($events['event_name']); $e++){
		$event_name = $events['event_name'][$e];
		$event_type = $events['type'][$e];
		#echo json_encode($points[$e['event_name']]) . "<br/>";
		if(in_array($slivkans['nu_email'][$s], $points[$event_name])){
			#echo $nu_email . " was at " . $e['event_name'] . "<br/>";
			$event_points_tmp = 1;
			if($event_type != "im" AND $event_type != "p2p"){ $events_total++; }
			if($event_type == "im"){ $im_points[$events['description'][$e]]++; }
		}else{
			$event_points_tmp = 0;
		}

		if($event_type == "p2p"){
			$date = explode("-",$events['date'][$e]);
			$day = date("D",mktime(0,0,0,$date[1],$date[2],$date[0]));
			if($event_points_tmp == 1){
				$p2p_points[$day][] = 1;
			}else{
				$p2p_points[$day][] = 0;
			}
		}

		if(in_array($slivkans['nu_email'][$s], $helperpoints[$event_name])){
			$event_points_tmp .= "h";
			$helper_points++;
		}elseif(in_array($slivkans['nu_email'][$s], $committeeattendance[$event_name])){
			$event_points_tmp .= "c";
		}

		$event_points[] = $event_points_tmp;
	}

	#handling p2p points:

	$p2p_points_actual = 0;
	for($i = 0; $i < count($p2p_points[$p2p_days[0]]); $i++){
		if($p2p_points[$p2p_days[0]][$i] == 1 OR $p2p_points[$p2p_days[1]][$i] == 1){ $p2p_points_actual++; } 
	}

	#handling IMs:
	$im_points_actual = 0;
	foreach($im_points as $im){
		if($im >= 3){ $im_points_actual += $im; }
	}

	#handling bonus points:
	$bonus_points = 0;
	if($slivkans['committee'][$s] == "Exec"){
		$bonus_points+= 40;
	}

	$events_total += $p2p_points_actual;
	$total = $events_total + $im_points_actual + $bonus_points;

	$points_table[$slivkans['full_name'][$s]] = array_merge($event_points,array($p2p_points_actual,$events_total,$helper_points,$im_points_actual,0,$bonus_points,$total));
}

echo json_encode(array(points_table => $points_table, events => $events));

?>