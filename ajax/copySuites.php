<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new \Slivka\PointsCenter();

$response = $points_center->copySuites();

echo json_encode($response);
