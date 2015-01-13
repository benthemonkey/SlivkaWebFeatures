<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new \Slivka\PointsCenter();
$courses = $points_center->getCoursesInDept($_GET['department']);

echo json_encode($courses);
