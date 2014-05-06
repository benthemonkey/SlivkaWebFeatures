<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();

if($_POST['suite'] != ''){
	$status = $points_center->updateSuite($_POST['slivkans'], $_POST['suite']);
}else if($_POST['committee'] != ''){
	$status = $points_center->updateCommittee($_POST['slivkans'], $_POST['committee'], $_POST['points']);
}

echo $status;
?>