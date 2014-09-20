<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new \Slivka\PointsCenter();
$quarter_info = $points_center->getQuarterInfo();

$listing = $points_center->getCourseListing($_GET['department'], $_GET['course']);
$past = array();
$current = array();

foreach ($listing as $item) {
    if ($item['qtr'] == $quarter_info['qtr']) {
        $current[] = $item['full_name'];
    } else {
        $past[] = $item['full_name'];
    }
}

$current = array_unique($current);
sort($current);
$past = array_unique($past);
sort($past);

echo json_encode(array("past"=>$past, "current"=>$current));
