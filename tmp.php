<?php
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$slivkans = $points_center->getSlivkans();
$nicknames = $points_center->getNicknames();
$fellows = $points_center->getFellows();
echo json_encode($fellows)

?>