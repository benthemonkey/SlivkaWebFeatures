<?php
require_once "./PointsCenter.php";
$points_center = new \Slivka\PointsCenter();
$qtrs = $points_center->getQuarters();
$slivkans = $points_center->getSlivkans();
$fellows = $points_center->getFellows();
$quarter_info = $points_center->getQuarterInfo();
$im_teams = $quarter_info['im_teams'];

echo json_encode(array("qtrs"=>$qtrs,"slivkans"=>$slivkans,"fellows"=>$fellows,"im_teams"=>$im_teams));
