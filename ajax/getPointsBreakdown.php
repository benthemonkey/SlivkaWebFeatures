<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$points = $points_center->getSlivkanPoints($_GET['nu_email']);
$events = $points_center->getEvents(-1);
$counts = $points_center->getSlivkanPointsByCommittee($_GET['nu_email']);
$ims = $points_center->getSlivkanIMPoints($_GET['nu_email']);
$other = $points_center->getSlivkanBonusPoints($_GET['nu_email']);

$attended = array();
$unattended = array();

foreach($events as $e){
	if(in_array($e['event_name'],$points)){
		$attended[] = $e;
	}else{
		$unattended[] = $e;
	}
}

echo json_encode(array('events'=>array('counts'=>$counts, 'attended'=>$attended, 'unattended'=>$unattended),
	'ims'=>$ims, 'helper'=>$other['helper'], 'committee'=>$other['committee'], 'other'=>$other['other'], 'other_breakdown'=>$other['other_breakdown']));


?>