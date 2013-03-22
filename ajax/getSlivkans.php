<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$result = mysql_query("SELECT * FROM directory ORDER BY first_name");

$full_name = array();
$nu_email = array();
$committee = array();

while($row = mysql_fetch_array($result)){
    $full_name[] = $row['first_name'] . ' ' . $row['last_name'];
   	$nu_email[] = $row['nu_email'];
   	$committee[] = $row['committee'];
}
$slivkans = array("full_name"=>$full_name,"nu_email"=>$nu_email,"committee"=>$committee);

$result = mysql_query("SELECT * FROM nicknames INNER JOIN directory ON nicknames.nu_email=directory.nu_email");

$nickname = array();
$aka = array();

while($row = mysql_fetch_array($result)){
	$nickname[] = $row['nickname'];
	$aka[] = $row['first_name'] . ' ' . $row['last_name'];
}
$nicknames = array("nickname"=>$nickname, "aka"=>$aka);

$result = mysql_query("SELECT * FROM fellows WHERE 1");

$fellows = array();

while($row = mysql_fetch_array($result)){
	$fellows[] = $row['full_name'];
}

echo json_encode(array("slivkans"=>$slivkans,"nicknames"=>$nicknames,"fellows"=>$fellows));

mysql_close($con);
?>