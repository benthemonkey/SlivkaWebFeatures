<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$department = $_GET['department'];
$course = $_GET['course'];

$result = mysql_query('SELECT * FROM courses WHERE Courses LIKE "%' . $department . ' ' . $course . '%" ORDER BY Name');

$current = array();
$past = array();

while($r = mysql_fetch_assoc($result)){
        if(strcmp($r['Quarter'],'Spring 2013') == 0){
            $current[] = $r['Name'];
        }else{
            $past[] = $r['Name'];
        }
}

$current = array_unique($current);
$past = array_unique($past);

$return = array(current => $current, past => $past);

echo json_encode($return);

mysql_close($con);

?>