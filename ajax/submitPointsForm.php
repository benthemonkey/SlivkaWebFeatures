<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$out = $points_center->submitPointsForm($_GET);

if($out){
	echo json_encode(array("error" => NULL));
}
?>