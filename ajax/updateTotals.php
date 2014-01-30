<?php
require_once "./PointsCenter.php";
$points_center = new PointsCenter();

$totals = $points_center->updateTotals();

echo json_encode($totals);
?>