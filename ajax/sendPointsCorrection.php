<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$key = md5($_GET['event_name'] . $_GET['name']);

$message = $points_center->submitPointsCorrectionForm($_GET,$key);

echo json_encode(array("message" => $message));

?>