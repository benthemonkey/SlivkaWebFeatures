<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();

if($_GET['suite'] != ''){
	$slivkans = $points_center->getSuite($_GET['suite']);
}else if($_GET['committee'] != ''){
	$slivkans = $points_center->getCommittee($_GET['committee']);
}

echo json_encode($slivkans);
?>