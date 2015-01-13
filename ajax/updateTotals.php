<?php
require_once "./PointsCenter.php";
$points_center = new \Slivka\PointsCenter();

$totals = $points_center->updateTotals();

echo json_encode($totals);
