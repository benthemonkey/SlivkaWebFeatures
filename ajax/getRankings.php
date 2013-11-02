<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$rankings = $points_center->getRankings();

echo json_encode($rankings);
?>