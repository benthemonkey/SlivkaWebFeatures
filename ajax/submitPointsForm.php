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

$receipt = array("date"=>$date,"type"=>$type,"committee"=>$committee,"event_name"=>$event_name,
	"description"=>$description,"filled_by"=>$filled_by,"comments"=>$comments,"attendees"=>$attendees_list,
	"helper_points"=>$helper_points_list,"committee_members"=>$committee_members_list,"fellows"=>$fellows_list);

$sql = "INSERT INTO pointsform (date,type,committee,event_name,description,filled_by,comments,attendees,helper_points,committee_members,fellows) VALUES ('$date','$type','$committee','$event_name','$description','$filled_by','$comments','$attendees_list','$helper_points_list','$committee_members_list','$fellows_list')";

if (!mysql_query($sql)){
  	echo json_encode(array(receipt => $receipt,error => mysql_error(),step => "1"));
	die();
}

$event_name .= " " . $date;
$num_attendees = count($attendees);

$sql = "INSERT INTO events (event_name, date, committee, description, type, attendees) VALUES ('$event_name','$date','$committee','$description','$type','$num_attendees')";

if (!mysql_query($sql)){
	echo json_encode(array(receipt => $receipt,error => mysql_error(),step => "2"));
	die();
}

foreach($attendees as $s){
	if(in_array($s, $helper_points)){
		$points = 2;
	}else{
		$points = 1;
	}
	$sql = "INSERT INTO points (nu_email,event_name,points) VALUES ('$s','$event_name','$points')";
	if (!mysql_query($sql)){
	  	echo json_encode(array(receipt => $receipt,error => mysql_error(),step => "3"));
		die();
	}
}

foreach($committee_members as $s){
	$sql = "INSERT INTO committeeattendance (nu_email,event_name) VALUES ('$s','$event_name')";
	if (!mysql_query($sql)){
	  	echo json_encode(array(receipt => $receipt,error => mysql_error(),step => "4"));
		die();
	}
}

foreach($fellows as $s){
	$sql = "INSERT INTO fellowattendance (fellow,event_name) VALUES ('$s','$event_name')";
	if (!mysql_query($sql)){
	  	echo json_encode(array(receipt => $receipt,error => mysql_error(),step => "5"));
		die();
	}
}

echo json_encode(array(receipt => $receipt));

mysql_close($con);
?>