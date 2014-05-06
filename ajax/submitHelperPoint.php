<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();

$status = $points_center->submitHelperPoint($_POST['nu_email'], $_POST['event_name']);

echo $status;
?>