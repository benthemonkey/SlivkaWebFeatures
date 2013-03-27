<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

if(mysql_real_escape_string($_GET['all'])){
	$startdate = date('Y-m-d',mktime(0,0,0,2,31,2013));
}else{
	$startdate = date('Y-m-d',mktime(0,0,0,date("m"),date("d")-14,date("Y")));
}


$result = mysql_query("SELECT * FROM events WHERE date>='$startdate' AND quarter='$quarter' ORDER BY date DESC");

$events = array();

while($row = mysql_fetch_array($result)){
	$events[] = $row['event_name'];
}

echo json_encode(array("event_names"=>$events));

mysql_close($con);
?>