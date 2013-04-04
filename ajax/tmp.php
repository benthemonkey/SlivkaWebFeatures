<?php
require_once "./PointsCenter.php";
$points_center = new PointsCenter();
$tmps = $points_center->getEventsAttendedBySlivkan($_GET['nu_email'],$_GET['start'],$_GET['end']);
$tmp2 = $points_center->getEvents($_GET['start'],$_GET['end']);
echo json_encode($tmp2);

$unattended_inds = array_keys(array_diff($tmp2['event_name'],$tmps['event_name']));

$unattended = array();

foreach($unattended_inds as $ind){
	$unattended[] = array('event_name'=>$tmp2['event_name'][$ind],'type'=>$tmp2['type'][$ind],'committee'=>$tmp2['committee'][$ind]);
}
#echo json_encode($unattended);
?>