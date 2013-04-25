<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();

$message = $points_center->submitPointsCorrectionForm($_GET,$key);

echo json_encode(array("message" => $message));

?>