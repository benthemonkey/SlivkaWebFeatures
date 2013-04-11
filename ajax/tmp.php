<?php
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$tmps = $points_center->getBonusPoints();

$tmp2 = $tmps['SarahUttal2014'];
$total = $tmp2[0]['other1']+$tmp2[0]['other2'];
echo json_encode($total);

?>