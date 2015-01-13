<?php
header('Content-type: text/html; charset=utf-8');
require_once "./PointsCenter.php";
$points_center = new \Slivka\PointsCenter();

$nu_email = $_POST['nu_email'];
$courses = $_POST['courses'];
$qtr = $_POST['qtr'];

$courses_filtered = array();

preg_match_all("/([A-Z_&]{2,9} \d{3}-\d-\d{2})/", $courses, $courses_filtered);

$courses_filtered = implode("; ", $courses_filtered[0]);

$result = $points_center->submitCourseDatabaseEntryForm($nu_email, $courses, $qtr);

if ($result) {
    echo "SUCCESS! Your Courses: " . $courses_filtered;
} else {
    echo "Something went wrong. Tell the VP.";
}
