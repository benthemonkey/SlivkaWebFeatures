<?php
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$quarter_info = $points_center->getQuarterInfo();
$slivkans = $points_center->getSlivkans();
$nicknames = $points_center->getNicknames();
$fellows = $points_center->getFellows();

echo json_encode(array("slivkans"=>$slivkans,"nicknames"=>$nicknames,"fellows"=>$fellows,"quarter_info"=>$quarter_info));
?>