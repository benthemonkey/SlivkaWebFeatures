<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$date 				= $_GET['date'];
$type 				= $_GET['type'];
$committee 			= $_GET['committee'];
$event_name 		= mysql_real_escape_string($_GET['event_name']);
$description 		= mysql_real_escape_string($_GET['description']);
$filled_by 			= $_GET['filled_by'];
$comments 			= mysql_real_escape_string($_GET['comments']);
$attendees 			= $_GET['attendees'];
$helper_points 		= $_GET['helper_points'];
$committee_members 	= $_GET['committee_members'];
$fellows 			= $_GET['fellows'];

$attendees_list = implode($attendees,", ");
$helper_points_list = implode($helper_points,", ");
$committee_members_list = implode($committee_members,", ");
$fellows_list = implode($fellows,", ");

$receipt = '<h3>Receipt:</h3>';
$receipt .= '<p>Date: ' . $date . '</p>';
$receipt .= '<p>Type: ' . $type . '</p>';
$receipt .= '<p>Committee: ' . $committee . '</p>';
$receipt .= '<p>Event Name: ' . $event_name . '</p>';
$receipt .= '<p>Description: ' . $description . '</p>';
$receipt .= '<p>Filled By: ' . $filled_by . '</p>';
$receipt .= '<p>Comments: ' . $comments . '</p>';
$receipt .= '<p>Attendees: ' . $attendees_list . '</p>';
$receipt .= '<p>Helper Points: ' . $helper_points_list . '</p>';
$receipt .= '<p>Committee Members: ' . $committee_members_list . '</p>';
$receipt .= '<p>Fellows: ' . $fellows_list . '</p>';
$receipt .= '<p><b>If any error messages appear below, email the receipt to Ben Rothman.</b></p>';

echo $receipt;

echo "<p>Inserting points form text into points form table: ";
$sql = "INSERT INTO pointsform (date,type,committee,event_name,description,filled_by,comments,attendees,helper_points,committee_members,fellows) VALUES ('$date','$type','$committee','$event_name','$description','$filled_by','$comments','$attendees_list','$helper_points_list','$committee_members_list','$fellows_list')";

if (!mysql_query($sql)){
  die('Error: ' . mysql_error());
}
echo "SUCCESS!</p>";

$event_name .= " " . $date;
$num_attendees = count($attendees);

echo "<p>Inserting event into event table: ";
$sql = "INSERT INTO events (event_name, date, committee, description, type, attendees) VALUES ('$event_name','$date','$committee','$description','$type','$num_attendees')";

if (!mysql_query($sql)){
  die('Error: ' . mysql_error());
}
echo "SUCCESS!</p>";

echo "<p>Inserting attendees into points table: ";
foreach($attendees as $s){
	if(in_array($s, $helper_points)){
		$points = 2;
	}else{
		$points = 1;
	}
	$sql = "INSERT INTO points (nu_email,event_name,points) VALUES ('$s','$event_name','$points')";
	if (!mysql_query($sql)){
	  die('Error: ' . mysql_error());
	}
}
echo "SUCCESS!</p>";

echo "<p>Inserting committee members into committeeattendance table: ";
foreach($committee_members as $s){
	$sql = "INSERT INTO committeeattendance (nu_email,event_name) VALUES ('$s','$event_name')";
	if (!mysql_query($sql)){
	  die('Error: ' . mysql_error());
	}
}
echo "SUCCESS!</p>";

echo "<p>Inserting fellows into fellowattendance table: ";
foreach($fellows as $s){
	$sql = "INSERT INTO fellowattendance (fellow,event_name) VALUES ('$s','$event_name')";
	if (!mysql_query($sql)){
	  die('Error: ' . mysql_error());
	}
}
echo "SUCCESS!</p>";

mysql_close($con);
?>