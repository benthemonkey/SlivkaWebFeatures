<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$attended = $points_center->getEventsAttendedBySlivkan($_GET['nu_email'],$_GET['start'],$_GET['end']);
$events = $points_center->getEvents($_GET['start'],$_GET['end']);

$unattended_inds = array_keys(array_diff($events['event_name'],$attended['event_name']));

$unattended = array('event_name'=>array(),'type'=>array(),'committee'=>array());
$unattended_types = array();
$unattended_committees = array();

foreach($unattended_inds as $ind){
	$unattended['event_name'][] = $events['event_name'][$ind];
	$unattended['type'][] = $events['type'][$ind];
	$unattended['committee'][] = $events['committee'][$ind];

	$unattended_types[$events['type'][$ind]] += 1;
	$unattended_committees[$events['committee'][$ind]] += 1;
}

$attended_types = array(); $attended_committees = array();
for($i=0; $i<count($attended['type']); $i++){
	$attended_types[$attended['type'][$i]] += 1;
	$attended_committees[$attended['committee'][$i]] += 1;
}

echo json_encode(array('attended'=>array('events'=>$attended,'types'=>$attended_types,'committees'=>$attended_committees),
	'unattended'=>array('events'=>$unattended,'types'=>$unattended_types,'committees'=>$unattended_committees)));

?>