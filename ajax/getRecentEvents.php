<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$events = $points_center->getRecentEvents();

echo json_encode($events);

?>