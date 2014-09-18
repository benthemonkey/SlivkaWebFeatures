<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$out = $points_center->submitPointsForm($_POST);

if ($out) {
    echo json_encode(array("error" => null));
} else {
    echo json_encode(array("error" => "PDO Commit", "step" => "7"));
}
