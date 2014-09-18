<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$points_table = $points_center->getPointsTable();

echo json_encode($points_table);
