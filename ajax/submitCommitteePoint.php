<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();

$status = $points_center->submitCommitteePoint(
	$_POST['nu_email'],
	$_POST['event_name'],
	$_POST['points'],
	$_POST['contributions'],
	$_POST['comments']
);

echo $status;
?>