<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();

$status = $points_center->submitNoShow($_POST['nu_email'], $_POST['date'], $_POST['comments']);

echo $status;
?>