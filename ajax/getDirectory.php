<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new \Slivka\PointsCenter();
$directory = $points_center->getDirectory($_POST['password']);

echo json_encode($directory);
