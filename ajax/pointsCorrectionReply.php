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

$result = mysql_query($sql) OR die("Ahhh! Ben has failed! Tell him what the points correction reply should be. Please paste this too: " . mysql_error());
$result = mysql_fetch_array($result);
$nu_email = $result['nu_email'];
$event_name = mysql_real_escape_string($result['event_name']);

if($result['response'] == "0"){
	$sql = "UPDATE pointscorrection SET response='$code' WHERE message_key='$key'";

	mysql_query($sql) OR die("Ahhh! Ben has failed! Tell him what the points correction reply should be. Please paste this too: " . mysql_error());

	if($code == 1){
		$sql = "INSERT INTO points (nu_email,event_name) VALUES ('$nu_email','$event_name')";
		mysql_query($sql) OR die("Ahhh! Ben has failed! Tell him what the points correction reply should be. Please paste this too: " . mysql_error());

		$sql = "UPDATE events SET attendees = attendees+1 WHERE event_name='$event_name'";
		mysql_query($sql) OR die("Error: " . mysql_error());

		echo "Success! She/He was given a point for the event.";
		$html_snippet = "You were given a point for $event_name.";
	}elseif($code == 2){
		echo "Success! A point has not been given.";
		$html_snippet = "You were NOT given a point for $event_name. You can request an explanation through the VP.";
	}elseif($code == 3){
		echo "Success! The VP will consult another attendee of the event.";
		$html_snippet = "The points taker couldn't remember if you were at $event_name and additional inquiry will be made by the VP.";
	}

	#send email notification

	include_once "swift/swift_required.php";

	$html = "<h2>Slivka Points Correction Response Posted</h2>
	<h3>Automated Email</h3>
	<p style=\"padding: 10; width: 70%\">A points correction you submitted has received a response:</p>

	<p style=\"padding: 10; width: 70%\">$html_snippet</p>

	<p style=\"padding: 10; width: 70%\">If you received this email in error, please contact BenSRothman@gmail.com</p>";

	$text = "Slivka Points Correction Response Posted (Automated)";
	$subject = "Slivka Points Correction Response Posted (Automated)";
	$from = array("BenSRothman@gmail.com" =>"Ben Rothman");
	$to = array(
	 $nu_email . "@u.northwestern.edu" => $nu_email,
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
	 echo '<br/>Notification message successfully sent!';
	} else {
	 echo "There was an error: " . print_r($failures);
	}
}else{
	echo "You already responded to this request.";
}

mysql_close($con);

?>