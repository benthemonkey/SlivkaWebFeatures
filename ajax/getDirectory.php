<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$result = mysql_query("SELECT * FROM directory ORDER BY first_name");

$a = array();

while($row = mysql_fetch_array($result)){
    $a[] = array($row['first_name'], $row['last_name'], $row['year'], $row['major'], $row['suite'], $row['photo']);
}

echo json_encode($a);

?>