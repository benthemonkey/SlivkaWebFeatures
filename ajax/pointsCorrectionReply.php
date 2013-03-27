<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$key = mysql_real_escape_string($_GET['key']);
$reply = mysql_real_escape_string($_GET['reply']);

if($reply==md5('1')){ $code = 1; }
elseif($reply==md5('2')){ $code = 2;}
elseif($reply==md5('3')){ $code = 3;}
else{die("Error in decoding");}

$sql = "SELECT * FROM pointscorrection WHERE message_key='$key'";

if(!mysql_query($sql)){
	die("Something went horribly wrong. Just tell Ben what the correction should be.");
}

$result = mysql_query($sql);
$result = mysql_fetch_array($result);

if($result['response'] == "0"){
	$sql = "UPDATE pointscorrection SET response='$code' WHERE message_key='$key'";

	if(!mysql_query($sql)){
		die("Ahhh! Ben has failed! Tell him what the points correction reply should be.");
	}

	echo "Success! Your response has been recorded.";
}else{
	echo "You already responded to this request.";
}

mysql_close($con);

?>