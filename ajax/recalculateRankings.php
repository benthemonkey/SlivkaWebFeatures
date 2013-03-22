<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$result = mysql_query("SELECT * FROM rankings ORDER BY nu_email");

while($r = mysql_fetch_array($result)){
	if($r['spring_point_total']+$r['fall_point_total']+$r['winter_point_total'] != $r['total']){
		$newTotal = $r['spring_point_total']+$r['fall_point_total']+$r['winter_point_total'];
		$newTwM = $r['multiplier'] * $newTotal;

		$nu_email = $r['nu_email'];

		$sql = "UPDATE rankings SET total='$newTotal', total_with_multiplier='$newTwM' WHERE nu_email='$nu_email'";

		if(!mysql_query($sql)){
			die('Error: ' . mysql_error());
		}else{
			echo "<p>Updated " . $nu_email . "</p>";
		}
	}
}

mysql_close($con);
?>