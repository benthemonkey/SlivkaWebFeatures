<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new \Slivka\PointsCenter();
$eventNameExists = $points_center->eventNameExists($_GET['event_name']);

echo json_encode(array('eventNameExists' => $eventNameExists));
