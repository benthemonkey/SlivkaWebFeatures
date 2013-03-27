<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

/*
Sudo code:
get all slivkans

for each slivkan's email:
	event points = house meetings + other
	for each week in quarter:
		if p2p1 or p2p2
			add one to P2P points

	im points = 
		im count < 3 ? 0
		im count > 15? 15
		else im points = im count
*/

$sql = "SELECT nu_email,full_name FROM directory ORDER BY nu_email"; # LIMIT 5 OFFSET 25
$result = mysql_query($sql) OR die("Error: " . mysql_error());

$slivkans = array();
while($r = mysql_fetch_array($result)){
	$slivkans[] = array(nu_email=>$r['nu_email'],full_name=>$r['full_name']);
}

$sql = "SELECT * FROM events WHERE quarter='$quarter' ORDER BY date DESC";
$result = mysql_query($sql) OR die("Error: " . mysql_error());

$events = array();
while($r = mysql_fetch_array($result)){
	$events[] = array(event_name=>$r['event_name'],type=>$r['type']);
}

#gunna straight up form the points table in hurr:

$sql = "SELECT * FROM points INNER JOIN events ON points.event_name=events.event_name WHERE quarter='$quarter' ORDER BY events.date DESC";

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
	$nu_email = $s['nu_email'];
	$event_points = array();
	$event_point_total = 0;
	$p2p_points = array();
	$im_point_total = 0;

	foreach($events as $e){
		#echo json_encode($points[$e['event_name']]) . "<br/>";
		if(in_array($nu_email, $points[$e['event_name']])){
			#echo $nu_email . " was at " . $e['event_name'] . "<br/>";
			$event_points[] = "1";
			if($r['type']=="im"){$im_point_total++;}
			elseif($r['type']=="p2p"){$p2p_points[] = 1;}
			else{$event_point_total++;}
		}else{
			$event_points[] = "0";
			if($r['type']=="p2p"){$p2p_points[] = 0;}
		}
	}

	/*#handle IMs
	$im_point_total = ($im_point_total > 15 ? 15 : $im_point_total);
	$im_point_total = ($im_point_total < 3 ? 0 : $im_point_total);

	#handle P2Ps
	$p2p_point_total = 0;
	for($i=0;$i<count($p2p_points);$i+=2){
		if($p2p_points[$i]==1 OR $p2p_points[$i+1]==1){$p2p_point_total++;}
	}

	$event_point_total += $im_point_total+$p2p_point_total;

	$event_points_totals[] = $event_point_total;
	$im_points_totals[] = $im_point_total;
	$p2p_points_totals[] = $p2p_point_total;*/
	$points_table[$s['full_name']] = $event_points;
}

echo json_encode(array(points_table=>$points_table));

?>