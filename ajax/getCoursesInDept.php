<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$department = $_GET['department'];

$result = mysql_query('SELECT * FROM courses WHERE Courses LIKE "%' . $department . '%"');

$return = array();

while($r = mysql_fetch_assoc($result)){
    $arr = explode($department,$r['Courses']);
    
    foreach($arr as $el){
        if($el[0] === ' '){
            $return[] =  substr($el,1,($el[5] > 0 && $el[5] < 5 ? 5 : 3));
        }
    }
}

$return = array_unique($return);
sort($return);

echo json_encode($return);

mysql_close($con);

?>