<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();

if($_GET['suite'] != ''){
	$status = $points_center->updateSuite($_GET['slivkans'], $_GET['suite']);
}else if($_GET['committee'] != ''){
	$status = $points_center->updateCommittee($_GET['slivkans'], $_GET['committee']);
}

echo $status;
?>