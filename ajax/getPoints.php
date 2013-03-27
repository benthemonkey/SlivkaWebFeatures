<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$start = mysql_real_escape_string($_GET['start']);
$end = mysql_real_escape_string($_GET['end']);

if($nu_email = mysql_real_escape_string($_GET['nu_email'])){

	$sql = "SELECT * FROM points INNER JOIN events ON points.event_name=events.event_name WHERE points.nu_email='$nu_email' AND events.date BETWEEN '$start' AND '$end' ORDER BY events.date DESC";

	if(!($result = mysql_query($sql))){
		die("Error: " . mysql_error());
	}

	$attended = array();
	$missed = array();

	while($r = mysql_fetch_array($result)){
		$attended[] = $r['event_name'];
	}

	$sql = "SELECT * FROM events WHERE date BETWEEN '$start' AND '$end' ORDER BY date DESC";

	if($result = mysql_query($sql)){
		while($r = mysql_fetch_array($result)){
			if(!in_array($r['event_name'], $attended)){
				$missed[] = $r['event_name'];
			}
		}
	}else{
		die("Error: " . mysql_error());
	}

	echo json_encode(array(attended => $attended,missed => $missed));
}

mysql_close($con);
?>