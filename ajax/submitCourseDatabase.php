<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$name = $_POST['name'];
$courses = $_POST['courses'];
$quarter = $_POST['quarter'];

$courses_filtered = array();

preg_match_all("/([A-Z_]{4,9} \d{3}-\d-\d{2})/",$courses,$courses_filtered);

$courses_filtered = implode("; ",$courses_filtered[0]);

$result = mysql_query("INSERT INTO courses (Name, Courses, Quarter) VALUES ('" . $name . "','" . $courses_filtered . "','" . $quarter . "')");

if ($result)
echo "SUCCESS! Your Courses: " . $courses_filtered . "<br /><br /><a href=\"http://slivka.northwestern.edu/resources/course-database/\">Back to Course Directory</a>";
else
echo "Something went wrong. Tell Ben.";

mysql_close($con);
?>