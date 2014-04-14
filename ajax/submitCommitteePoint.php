<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new PointsCenter();

$status = $points_center->submitCommitteePoint($_GET['nu_email'], $_GET['event_name'], $_GET['points']);

echo $status;
?>