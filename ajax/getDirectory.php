<?php
header('Content-type: text/html; charset=utf-8');

require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$directory = $points_center->getDirectory();

$a = array();

foreach($directory as $row){
    $a[] = array($row['first_name'], $row['last_name'], $row['year'], $row['major'], $row['suite'], $row['photo']);
}

echo json_encode($a);
?>