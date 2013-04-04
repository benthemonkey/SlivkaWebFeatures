<?php
header('Content-type: text/html; charset=utf-8');

require_once('datastoreVars.php');

$con = mysql_connect($DB_SERV,$DB_USER,$DB_PASS);

if(!$con){
    die('Could not connect: ' . mysql_errror());
}

mysql_select_db($DB_NAME,$con);

$event_name = mysql_real_escape_string($_GET['event_name']);
$name = $_GET['name'];
$sender_email = $_GET['sender_email'];
$comment = mysql_real_escape_string($_GET['comments']);

$sql = "SELECT * FROM points WHERE event_name='$event_name' AND nu_email='$sender_email'";
if($result = mysql_query($sql)){
	if(mysql_num_rows($result) > 0){
		echo json_encode(array(message => "You already have points for that event!"));
		die();
	}
}else{
	echo json_encode(array(message => "Error on Ben\'s end. Let him know and paste this: " . mysql_error()));
	die();
}

$date = substr($event_name,-10,10);

date_default_timezone_set('UTC');
$today = date("Y-m-d H:i:s",mktime(date("H")-11,date("i"),date("s"),date("m"),date("d"),date("Y")));

$sql = "SELECT * FROM events WHERE event_name='$event_name' AND date='$date'";

if(!mysql_query($sql)){
	echo json_encode(array(message => "Error on Ben\'s end. Let him know and paste this: " . mysql_error()));
	die();
}

$result = mysql_query($sql);
$result = mysql_fetch_array($result);

if($result['timestamp'] > $today ){
	echo json_encode(array(message => "To reduce spam, you must wait 6 hours after the points were submitted before you can send a correction."));
	die();
}


$filled_by_email = $result['filled_by'];

$key = md5($event_name . $name);

$sql = "INSERT INTO pointscorrection (message_key,nu_email,event_name,comments) VALUES ('$key','$sender_email','$event_name','$comments')";

if(!mysql_query($sql)){
	echo json_encode(array(message => "Error: Maybe you are trying to submit the same points correction twice."));
	die();
}


$enc1 = md5('1');
$enc2 = md5('2');
$enc3 = md5('3');

$html = "<h2>Slivka Points Correction</h2>
<h3>Automated Email</h3>
<p style=\"padding: 10; width: 70%\">$name has submitted a points correction for the 
event, $event_name, for which you took points. Please click one of the following links 
to respond to this request. Please do so within 2 days of receiving this email.</p>
<p style=\"padding: 10; width: 70%\">$name's comment: $comment</p>
<ul>
    <li><a href=\"http://slivka.northwestern.edu/points/ajax/pointsCorrectionReply.php?key=$key&reply=$enc1\">$name was at $event_name</a></li><br/>
	<li><a href=\"http://slivka.northwestern.edu/points/ajax/pointsCorrectionReply.php?key=$key&reply=$enc2\">$name was NOT at $event_name</a></li><br/>
	<li><a href=\"http://slivka.northwestern.edu/points/ajax/pointsCorrectionReply.php?key=$key&reply=$enc3\">Not sure</a></li>
</ul>

<p style=\"padding: 10; width: 70%\">If you received this email in error, please contact BenSRothman@gmail.com</p>";

mysql_close($con);

include_once "swift/swift_required.php";

$text = "Slivka Points Correction (Automated)";
$subject = "Slivka Points Correction (Automated)";
$from = array("BenSRothman@gmail.com" =>"Ben Rothman");
$to = array(
 $filled_by_email . "@u.northwestern.edu" => $filled_by_email,
 'BenSRothman+mailbot@gmail.com' => 'Bens Copy'
);

$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
  ->setUsername("bensrothman@gmail.com")
  ->setPassword($GMAIL_PASS);

$mailer = Swift_Mailer::newInstance($transport);

$message = new Swift_Message($subject);
$message->setFrom($from);
$message->setBody($html, 'text/html');
$message->setTo($to);
$message->addPart($text, 'text/plain');

if ($recipients = $mailer->send($message, $failures))
{
 echo json_encode(array(message => 'Message successfully sent!'));
} else {
 echo json_encode(array(message => "There was an error: " . print_r($failures)));
}

?>