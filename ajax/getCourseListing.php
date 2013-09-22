<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$quarter_info = $points_center->getQuarterInfo();
$slivkans = $points_center->getAllSlivkans();

$quarter_info['im_teams'] = json_decode($quarter_info['im_teams']);

$listing = $points_center->getCourseListing($_GET['department'],$_GET['course']);
$past = array(); $current = array();

foreach($listing as $item){
	if($item['qtr'] == $quarter_info['qtr']){
		$current[] = $slivkans[$item['nu_email']];
	}else{
		$past[] = $slivkans[$item['nu_email']];
	}
}

$current = array_unique($current); sort($current);
$past = array_unique($past); sort($past);

echo json_encode(array("past"=>$past, "current"=>$current));
?>