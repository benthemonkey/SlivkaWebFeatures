<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$result = mysql_query("SELECT event_name,date,fellows FROM pointsform WHERE fellows!=''");

while($r = mysql_fetch_array($result)){
	$real_event_name = mysql_real_escape_string($r['event_name'].' '.$r['date']);
	$fellows = explode(', ',$r['fellows']);


	if($real_event_name == "P2P 2013-05-24"){
		$real_event_name = "P2P 2013-05-22";
	}
	if($real_event_name == "White Ultimate 1 2013-04-30"){ $real_event_name = "White Ultimate 2 2013-04-30"; }
	if($real_event_name == "Co-Rec Soccer 1 2013-05-15"){ $real_event_name = "Co-Rec Soccer 4 2013-05-15"; }
	if($f = "Arthur Shmidt"){ $f = "Art Shmidt"; }

	foreach($fellows as $f){
		$sql = "INSERT INTO fellowattendance (full_name,event_name,qtr) VALUES ('$f','$real_event_name',1302)";
		if(!mysql_query($sql)){
			$tmp = mysql_error();
			if(substr($tmp,0,9) != 'Duplicate'){
				echo "<p>".$tmp."</p>";
				echo $real_event_name;
			}
		}else{
			echo "<p>Added ".$f."</p>";
		}
	}
		/*$sql = "UPDATE rankings SET total='$newTotal', total_with_multiplier='$newTwM' WHERE nu_email='$nu_email'";

		if(!mysql_query($sql)){
			die('Error: ' . mysql_error());
		}else{
			echo "<p>Updated " . $nu_email . "</p>";
		}
	}*/
}

mysql_close($con);
?>