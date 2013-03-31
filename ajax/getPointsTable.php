<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$sql = "SELECT nu_email,full_name,committee FROM directory ORDER BY nu_email"; #  LIMIT 5 OFFSET 23
$result = mysql_query($sql) OR die("Error: " . mysql_error());

$slivkans = array();
while($r = mysql_fetch_array($result)){
	$slivkans[] = array(nu_email=>$r['nu_email'],full_name=>$r['full_name'],committee=>$r['committee']);
}

$sql = "SELECT * FROM events WHERE quarter='$quarter' ORDER BY date";
$result = mysql_query($sql) OR die("Error: " . mysql_error());

$events = array();
while($r = mysql_fetch_array($result)){
	$events[] = array(event_name=>$r['event_name'],date=>$r['date'],type=>$r['type'],attendees=>$r['attendees'],description=>$r['description']);
}


$sql = "SELECT * FROM points INNER JOIN events ON points.event_name=events.event_name WHERE events.quarter='$quarter' ORDER BY events.date";
$result = mysql_query($sql) OR die("Error: " . mysql_error());

$points = array();
while($r = mysql_fetch_array($result)){
	$points[$r['event_name']][] = $r['nu_email'];
}

$sql = "SELECT * FROM helperpoints INNER JOIN events ON helperpoints.event_name=events.event_name WHERE events.quarter='$quarter' ORDER BY events.date";
$result = mysql_query($sql) OR die("Error: " . mysql_error());

$helperpoints = array();
while($r = mysql_fetch_array($result)){
	$helperpoints[$r['event_name']][] = $r['nu_email'];
}

$sql = "SELECT * FROM committeeattendance INNER JOIN events ON committeeattendance.event_name=events.event_name WHERE events.quarter='$quarter' ORDER BY events.date";
$result = mysql_query($sql) OR die("Error: " . mysql_error());

$committeeattendance = array();
while($r = mysql_fetch_array($result)){
	$committeeattendance[$r['event_name']][] = $r['nu_email'];
}

$points_table = array(); #table that is slivkan count by event count + 3

foreach($slivkans as $s){
	$events_total = 0;
	$event_points = array();
	$helper_points = 0;
	$p2p_points = array();
	$im_points = array();

	foreach($events as $e){
		#echo json_encode($points[$e['event_name']]) . "<br/>";
		if(in_array($s['nu_email'], $points[$e['event_name']])){
			#echo $nu_email . " was at " . $e['event_name'] . "<br/>";
			$event_points_tmp = 1;
			if($e['type'] != "im" AND $e['type'] != "p2p"){ $events_total++; }
			if($e['type'] == "im"){ $im_points[$e['description']]++; }
		}else{
			$event_points_tmp = 0;
		}

		if($e['type'] == "p2p"){
			$date = explode("-",$e['date']);
			$day = date("D",mktime(0,0,0,$date[1],$date[2],$date[0]));
			if($event_points_tmp == 1){
				$p2p_points[$day][] = 1;
			}else{
				$p2p_points[$day][] = 0;
			}
		}

		if(in_array($s['nu_email'], $helperpoints[$e['event_name']])){
			$event_points_tmp .= "h";
			$helper_points++;
		}elseif(in_array($s['nu_email'], $committeeattendance[$e['event_name']])){
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
	if($s['committee'] == "Exec"){
		$bonus_points+= 40;
	}

	$events_total += $p2p_points_actual;
	$total = $events_total + $im_points_actual + $bonus_points;

	$points_table[$s['full_name']] = array_merge($event_points,array($p2p_points_actual,$events_total,$helper_points,$im_points_actual,0,$bonus_points,$total));
}

echo json_encode(array(points_table => $points_table, events => $events));

?>