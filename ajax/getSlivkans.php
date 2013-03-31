<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$sql = "SELECT * FROM directory WHERE qtr_final IS NULL ORDER BY first_name";

$result = mysql_query($sql) or die("Error: " . mysql_error());

$full_name = array();
$nu_email = array();
$committee = array();

while($row = mysql_fetch_array($result)){
    $full_name[] = $row['first_name'] . ' ' . $row['last_name'];
   	$nu_email[] = $row['nu_email'];
   	$committee[] = $row['committee'];
}
$slivkans = array("full_name"=>$full_name,"nu_email"=>$nu_email,"committee"=>$committee);

$sql = "SELECT * FROM nicknames INNER JOIN directory ON nicknames.nu_email=directory.nu_email";

$result = mysql_query($sql) or die("Error: " . mysql_error());

$nickname = array();
$aka = array();

while($row = mysql_fetch_array($result)){
	$nickname[] = $row['nickname'];
	$aka[] = $row['first_name'] . ' ' . $row['last_name'];
}
$nicknames = array("nickname"=>$nickname, "aka"=>$aka);

$sql = "SELECT * FROM fellows WHERE 1";
$result = mysql_query($sql) or die("Error: " . mysql_error());

$fellows = array();

while($row = mysql_fetch_array($result)){
	$fellows[] = $row['full_name'];
}

echo json_encode(array("slivkans"=>$slivkans,"nicknames"=>$nicknames,"fellows"=>$fellows));

mysql_close($con);
?>