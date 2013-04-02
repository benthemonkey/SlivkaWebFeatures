<?php
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$tmps = $points_center->getPoints();
echo json_encode($tmps);

?>