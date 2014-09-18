<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$directory = $points_center->getDirectory();

echo json_encode($directory);
