<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new \Slivka\PointsCenter();

$quarter_table_properties = array_keys($points_center->getQuarterInfo());

if ($_POST['name'] != 'qtr' && in_array($_POST['name'], $quarter_table_properties)) {
    $status = $points_center->updateQuarterInfo($_POST['name'], $_POST['value']);
} else {
    $status = $points_center->updateConfig($_POST['name'], $_POST['value']);
}

echo $status;
