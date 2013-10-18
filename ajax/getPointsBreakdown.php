<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$points = $points_center->getSlivkanPoints($_GET['nu_email']);
$events = $points_center->getEvents($_GET['start'],$_GET['end']);

$attended = array();
$unattended = array();

foreach($events as $e){
	if(in_array($e['event_name'],$points)){
		$attended[] = $e;
	}else{
		$unattended[] = $e;
	}
}

$unattended_count = count($unattended); $unattended_types = array(); $unattended_committees = array();
for($i=0; $i<$unattended_count; $i++){
	$unattended_types[$unattended[$i]['type']] += 1;
	$unattended_committees[$unattended[$i]['committee']] += 1;
}

$attended_count = count($attended); $attended_types = array(); $attended_committees = array();
for($i=0; $i<$attended_count; $i++){
	$attended_types[$attended[$i]['type']] += 1;
	$attended_committees[$attended[$i]['committee']] += 1;
}

echo json_encode(array('attended'=>array('events'=>$attended,'types'=>$attended_types,'committees'=>$attended_committees),
	'unattended'=>array('events'=>$unattended,'types'=>$unattended_types,'committees'=>$unattended_committees)));

?>