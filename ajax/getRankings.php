<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$result = mysql_query("SELECT * FROM rankings JOIN directory ON rankings.nu_email=directory.nu_email WHERE rankings.gender='m' ORDER BY total_with_multiplier DESC");

$males = array();
$counter = 0;

while($r = mysql_fetch_array($result)){
	if($r['abstains'] != "1"){
		if($counter < 43){
			$above_cutoff = 1;
			$counter = $counter + 1;
		}else{
			$above_cutoff = 0;
		}
	}
	$males[] = array("full_name"=>$r['full_name'],"gender"=>$r['gender'],"spring_point_total"=>$r['spring_point_total'],"fall_point_total"=>$r['fall_point_total'],"winter_point_total"=>$r['winter_point_total'],"total"=>$r['total'],"multiplier"=>$r['multiplier'],"total_with_multiplier"=>$r['total_with_multiplier'],"above_cutoff"=>$above_cutoff,"abstains"=>$r['abstains']);
}

$result = mysql_query("SELECT * FROM rankings JOIN directory ON rankings.nu_email=directory.nu_email WHERE rankings.gender='f' ORDER BY total_with_multiplier DESC");

$females = array();
$counter = 0;

while($r = mysql_fetch_array($result)){
	if($r['abstains'] != "1"){
		if($counter < 39){
			$above_cutoff = 1;
			$counter = $counter + 1;
		}else{
			$above_cutoff = 0;
		}
	}
	$females[] = array("full_name"=>$r['full_name'],"gender"=>$r['gender'],"spring_point_total"=>$r['spring_point_total'],"fall_point_total"=>$r['fall_point_total'],"winter_point_total"=>$r['winter_point_total'],"total"=>$r['total'],"multiplier"=>$r['multiplier'],"total_with_multiplier"=>$r['total_with_multiplier'],"above_cutoff"=>$above_cutoff,"abstains"=>$r['abstains']);
}

echo json_encode(array("males"=>$males,"females"=>$females));

?>