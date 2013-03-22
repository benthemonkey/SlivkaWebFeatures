<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$result = mysql_query("SELECT * FROM events WHERE 1");

$events = array();

while($row = mysql_fetch_array($result)){
	$events[] = $row['event_name'];
}

echo json_encode(array("event_names"=>$events));

mysql_close($con);
?>