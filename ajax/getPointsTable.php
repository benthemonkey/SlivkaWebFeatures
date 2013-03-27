<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$sql = "SELECT nu_email,full_name FROM directory ORDER BY nu_email"; # LIMIT 5 OFFSET 25
$result = mysql_query($sql) OR die("Error: " . mysql_error());

$slivkans = array();
while($r = mysql_fetch_array($result)){
	$slivkans[] = array(nu_email=>$r['nu_email'],full_name=>$r['full_name']);
}

$sql = "SELECT * FROM events WHERE quarter='$quarter' ORDER BY date";
$result = mysql_query($sql) OR die("Error: " . mysql_error());

$events = array();
while($r = mysql_fetch_array($result)){
	$events[] = array(event_name=>$r['event_name'],type=>$r['type']);
}


$sql = "SELECT * FROM points INNER JOIN events ON points.event_name=events.event_name WHERE quarter='$quarter' ORDER BY events.date";
$result = mysql_query($sql) OR die("Error: " . mysql_error());

$points = array();
while($r = mysql_fetch_array($result)){
	$points[$r['event_name']][] = $r['nu_email'];
}

$points_table = array(); #table that is slivkan count x event count
$event_points_totals = array(); #array size slivkan count
$im_points_totals = array(); #array size slivkan count
$p2p_points_totals = array(); #array size slivkan count

foreach($slivkans as $s){
	$event_points = array();

	foreach($events as $e){
		#echo json_encode($points[$e['event_name']]) . "<br/>";
		if(in_array($s['nu_email'], $points[$e['event_name']])){
			#echo $nu_email . " was at " . $e['event_name'] . "<br/>";
			$event_points[] = "1";
		}else{
			$event_points[] = "0";
		}
	}

	$points_table[$s['full_name']] = $event_points;
}

echo json_encode(array(points_table => $points_table, events => $events, slivkans => $slivkans));

?>