<?php
require_once "./DatabasePDO.php";
include_once "./swift/swift_required.php";

class PointsCenter
{
	private static $qtr = 1303;

	private static $dbConn = null;
	public function __construct ()
	{
		self::initializeConnection();
	}

	private static function initializeConnection ()
	{
		if (is_null(self::$dbConn)) {
			self::$dbConn = DatabasePDO::getInstance();
		}
	}

	public function getQuarterInfo ()
	{
		self::initializeConnection();
		$quarter_info;
		try {
			$statement = self::$dbConn->prepare(
				"SELECT *
				FROM quarters
				WHERE qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$quarter_info = $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		$quarter_info[0]['im_teams'] = json_decode($quarter_info[0]['im_teams']);
		return $quarter_info[0];
	}

	public function getDirectory ()
	{
		self::initializeConnection();
		$directory = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT first_name,last_name,year,major,suite,photo
				FROM directory
				WHERE qtr_final IS NULL
				ORDER BY first_name");
			$statement->execute();
			$directory = $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $directory;
	}

	public function getSlivkans ()
	{
		self::initializeConnection();
		$slivkans = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT full_name,nu_email,gender,wildcard,committee,photo
				FROM directory
				WHERE qtr_final IS NULL
				ORDER BY first_name");
			$statement->execute();
			$slivkans = $statement->fetchAll(PDO::FETCH_ASSOC);

		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		# Add tokens for typeahead.js
		$n = count($slivkans);
		for($i=0; $i<$n; $i++){
			$slivkans[$i]["tokens"] = explode(" ",$slivkans[$i]["full_name"]);
		}

		return $slivkans;
	}

	public function getAllSlivkans ()
	{
		self::initializeConnection();
		$slivkans = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email,full_name
				FROM directory
				ORDER BY first_name");
			$statement->execute();
			$slivkans = $statement->fetchAll();

		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		$return = array();

		foreach($slivkans as $s){
			$return[$s['nu_email']] = $s['full_name'];
		}

		return $return;
	}

	public function getNicknames ()
	{
		self::initializeConnection();
		$nicknames = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email,nickname
				FROM nicknames");
			$statement->execute();
			$nicknames = $statement->fetchAll(PDO::FETCH_NAMED);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $nicknames;
	}

	public function getFellows ()
	{
		self::initializeConnection();
		$fellows = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT full_name,photo
				FROM fellows
				WHERE qtr_final IS NULL");
			$statement->execute();
			$fellows = $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $fellows;
	}

	public function getEvents ($start,$end)
	{
		self::initializeConnection();
		$events = array();

		if(!$start){
			$start = date('Y-m-d',mktime(0,0,0,date("m"),date("d")-14,date("Y")));
		}
		if(!$end){
			$end = '2050-01-01';
		}

		try {
			$statement = self::$dbConn->prepare(
				"SELECT event_name,date,type,attendees,committee,description
				FROM events
				WHERE qtr=:qtr AND date BETWEEN :start AND :end
				ORDER BY date, id");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":start", $start);
			$statement->bindValue(":end", $end);
			$statement->execute();
			$events = $statement->fetchAll(PDO::FETCH_NAMED);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $events;
	}

	public function getPoints ()
	{
		self::initializeConnection();
		$points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT event_name,nu_email
				FROM points
				WHERE qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$points = $statement->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $points;
	}

	public function getHelperPoints ()
	{
		self::initializeConnection();
		$helper_points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT event_name,nu_email
				FROM helperpoints
				WHERE qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$helper_points = $statement->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $helper_points;
	}

	public function getCommitteeAttendance ()
	{
		self::initializeConnection();
		$committee_attendance = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT event_name,nu_email
				FROM committeeattendance
				WHERE qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$committee_attendance = $statement->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $committee_attendance;
	}

	public function getSlivkanPoints ($nu_email)
	{
		self::initializeConnection();
		$events = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT event_name
				FROM points
				WHERE qtr=:qtr AND nu_email=:nu_email");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":nu_email", $nu_email);
			$statement->execute();
			$events = $statement->fetchAll(PDO::FETCH_COLUMN);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $events;
	}

	public function getBonusPoints ()
	{
		self::initializeConnection();
		$bonus_points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email,helper,committee,other1_name,other1,other2_name,other2,other3_name,other3
				FROM bonuspoints
				WHERE qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$bonus_points = $statement->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		#fixing wierd extra nested array in return
		foreach(array_keys($bonus_points) as $b){
			$bonus_points[$b] = $bonus_points[$b][0];
		}

		return $bonus_points;
	}

	public function submitPointsForm ($get)
	{
		#write to log
		$f = fopen("log.txt","a");
		fwrite($f, "\n" . json_encode($get));
		fclose($f);

		$real_event_name = $get['event_name'] . " " . $get['date'];

		self::initializeConnection();
		if($get['helper_points'] === NULL){ $get['helper_points'] = array(""); }
		if($get['committee_members'] === NULL){ $get['committee_members'] = array(""); }
		if($get['fellows'] === NULL){ $get['fellows'] = array(""); }

		try {
			$statement = self::$dbConn->prepare(
				"INSERT INTO `pointsform` SET
				date=:date, type=:type, committee=:committee, event_name=:event_name, description=:description,
				filled_by=:filled_by, comments=:comments, attendees=:attendees, helper_points=:helper_points,
				committee_members=:committee_members, fellows=:fellows");
			$statement->bindValue(":date", $get['date']);
			$statement->bindValue(":type", $get['type']);
			$statement->bindValue(":committee", $get['committee']);
			$statement->bindValue(":event_name", $get['event_name']);
			$statement->bindValue(":description", $get['description']);
			$statement->bindValue(":filled_by", $get['filled_by']);
			$statement->bindValue(":comments", $get['comments']);
			$statement->bindValue(":attendees", implode(", ",$get['attendees']));
			$statement->bindValue(":helper_points", implode(", ",$get['helper_points']));
			$statement->bindValue(":committee_members", implode(", ",$get['committee_members']));
			$statement->bindValue(":fellows", implode(", ",$get['fellows']));

			$statement->execute();
		} catch (PDOException $e) {
			echo json_encode(array("error" => $e->getMessage(), "step" => "1"));
			die();
		}

		try {
			$statement = self::$dbConn->prepare(
				"INSERT INTO events SET event_name=:event_name, date=:date, qtr=:qtr,
				filled_by=:filled_by, committee=:committee, description=:description, type=:type, attendees=:attendees");
			$statement->bindValue(":event_name", $real_event_name);
			$statement->bindValue(":date", $get['date']);
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":filled_by", $get['filled_by']);
			$statement->bindValue(":committee", $get['committee']);
			$statement->bindValue(":description", $get['description']);
			$statement->bindValue(":type", $get['type']);
			$statement->bindValue(":attendees", count($get['attendees']));

			$statement->execute();
		} catch (PDOException $e) {
			echo json_encode(array("error" => $e->getMessage(), "step" => "2"));
			die();
		}

		$a_sql = "INSERT INTO points (nu_email, event_name, qtr) VALUES ";
		$a_fills = array();
		$a_values = array();
		foreach($get['attendees'] as $a){
			$a_fills[] = "(?,?,?)";
			$a_values = array_merge($a_values,array($a,$real_event_name,self::$qtr));
		}
		$a_sql .= implode(", ",$a_fills);

		try {
			$statement = self::$dbConn->prepare($a_sql);
			$statement->execute($a_values);
		} catch (PDOException $e) {
			echo json_encode(array("error" => $e->getMessage(), "step" => "3"));
			die();
		}

		if ($get['helper_points'][0] != ""){
			$h_sql = "INSERT INTO helperpoints (nu_email, event_name, qtr) VALUES ";
			$h_fills = array();
			$h_values = array();
			foreach($get['helper_points'] as $h){
				$h_fills[] = "(?,?,?)";
				$h_values = array_merge($h_values,array($h,$real_event_name,self::$qtr));
			}
			$h_sql .= implode(", ",$h_fills);

			try {
				$statement = self::$dbConn->prepare($h_sql);
				$statement->execute($h_values);
			} catch (PDOException $e) {
				echo json_encode(array("error" => $e->getMessage(), "step" => "4"));
				die();
			}
		}

		if ($get['committee_members'][0] != ""){
			$c_sql = "INSERT INTO committeeattendance (nu_email, event_name, qtr) VALUES ";
			$c_fills = array();
			$c_values = array();
			foreach($get['committee_members'] as $c){
				$c_fills[] = "(?,?,?)";
				$c_values = array_merge($c_values,array($c,$real_event_name,self::$qtr));
			}
			$c_sql .= implode(", ",$c_fills);

			try {
				$statement = self::$dbConn->prepare($c_sql);
				$statement->execute($c_values);
			} catch (PDOException $e) {
				echo json_encode(array("error" => $e->getMessage(), "step" => "5"));
				die();
			}
		}

		if ($get['fellows'][0] != ""){
			$f_sql = "INSERT INTO fellowattendance (full_name, event_name, qtr) VALUES ";
			$f_fills = array();
			$f_values = array();
			foreach($get['fellows'] as $f){
				$f_fills[] = "(?,?,?)";
				$f_values = array_merge($f_values,array($f,$real_event_name,self::$qtr));
			}
			$f_sql .= implode(", ",$f_fills);

			try {
				$statement = self::$dbConn->prepare($f_sql);
				$statement->execute($f_values);
			} catch (PDOException $e) {
				echo json_encode(array("error" => $e->getMessage(), "step" => "6"));
				die();
			}
		}

		return true;
	}

	public function submitPointsCorrectionForm($get,$key){
		self::initializeConnection();

		try {
			$statement = self::$dbConn->prepare(
				"SELECT *
				FROM points
				WHERE event_name=:event_name AND nu_email=:nu_email");
			$statement->bindValue(":event_name", $get['event_name']);
			$statement->bindValue(":nu_email", $get['sender_email']);

			$statement->execute();
			if($statement->rowCount() > 0){
				echo json_encode(array("message" => "You already have points for that event!"));
				die();
			}

		} catch (PDOException $e) {
			echo json_encode(array("message" => "Error: " . $e->getMessage()));
			die();
		}

		try {
			$statement = self::$dbConn->prepare(
				"SELECT filled_by
				FROM events
				WHERE event_name=:event_name");
			$statement->bindValue(":event_name", $get['event_name']);
			$statement->execute();

			$filled_by = $statement->fetchAll(PDO::FETCH_ASSOC);
			$filled_by = $filled_by[0]['filled_by'];
		} catch (PDOException $e) {
			echo json_encode(array("message" => "Error: " . $e->getMessage()));
			die();
		}

		if($get['comments'] === NULL){ $get['comments'] = ""; }

		try {
			$statement = self::$dbConn->prepare(
				"INSERT INTO pointscorrection SET
				message_key=:message_key, nu_email=:nu_email, event_name=:event_name, comments=:comments");
			$statement->bindValue(":message_key", $key);
			$statement->bindValue(":nu_email", $get['sender_email']);
			$statement->bindValue(":event_name", $get['event_name']);
			$statement->bindValue(":comments", $get['comments']);

			$statement->execute();
		} catch (PDOException $e) {
			echo json_encode(array("message" => "Error: Maybe you are trying to submit the same correction twice."));
			die();
		}

		$enc1 = md5('1');
		$enc2 = md5('2');
		$enc3 = md5('3');

		$html = "<h2>Slivka Points Correction</h2>
		<h3>Automated Email</h3>
		<p style=\"padding: 10; width: 70%\">" . $get['name'] . " has submitted a points correction for the
		event, " . $get['event_name'] . ", for which you took points. Please click one of the following links
		to respond to this request. Please do so within 2 days of receiving this email.</p>
		<p style=\"padding: 10; width: 70%\">" . $get['name'] . "'s comment: " . $get['comments'] . "</p>
		<ul>
			<li><a href=\"http://slivka.northwestern.edu/points/ajax/pointsCorrectionReply.php?key=$key&reply=$enc1\">" . $get['name'] . " was at " . $get['event_name'] . "</a></li><br/>
			<li><a href=\"http://slivka.northwestern.edu/points/ajax/pointsCorrectionReply.php?key=$key&reply=$enc2\">" . $get['name'] . " was NOT at " . $get['event_name'] . "</a></li><br/>
			<li><a href=\"http://slivka.northwestern.edu/points/ajax/pointsCorrectionReply.php?key=$key&reply=$enc3\">Not sure</a></li>
		</ul>

		<p style=\"padding: 10; width: 70%\">If you received this email in error, please contact BenSRothman@gmail.com</p>";

		return self::sendEmail($filled_by,"Slivka Points Correction (Automated)",$html);
	}

	public function pointsCorrectionReply($get)
	{
		self::initializeConnection();

		if($get['reply']==md5('1')){ $code = 1; }
		elseif($get['reply']==md5('2')){ $code = 2;}
		elseif($get['reply']==md5('3')){ $code = 3;}
		else{die("Error in decoding");}

		try {
			$statement = self::$dbConn->prepare(
				"SELECT *
				FROM pointscorrection
				WHERE message_key=:key");
			$statement->bindValue(":key", $get['key']);
			$statement->execute();
			$result = $statement->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		if($result['response'] == "0"){
			try {
				$statement = self::$dbConn->prepare(
					"UPDATE pointscorrection
					SET response=:code
					WHERE message_key=:key");
				$statement->bindValue(":code", $code);
				$statement->bindValue(":key", $get['key']);
				$statement->execute();
			} catch (PDOException $e) {
				echo "Error: " . $e->getMessage();
				die();
			}

		}else{
			echo "You already responded to this request.";
			die();
		}

		if($code == 1){
			try {
				$statement = self::$dbConn->prepare(
					"INSERT INTO points (nu_email,event_name,qtr)
					VALUES (:nu_email,:event_name,:qtr)");
				$statement->bindValue(":nu_email", $result['nu_email']);
				$statement->bindValue(":event_name", $result['event_name']);
				$statement->bindValue(":qtr", self::$qtr);
				$statement->execute();
			} catch (PDOException $e) {
				echo "Error: " . $e->getMessage();
				die();
			}

			try {
				$statement = self::$dbConn->prepare(
					"UPDATE events
					SET attendees = attendees+1
					WHERE event_name=:event_name");
				$statement->bindValue(":event_name", $result['event_name']);
				$statement->execute();
			} catch (PDOException $e) {
				echo "Error: " . $e->getMessage();
				die();
			}

			echo "Success! She/He was given a point for the event.";
			$html_snippet = "You were given a point for " . $result['event_name'] . ".";
		}elseif($code == 2){
			echo "Success! A point has NOT been given.";
			$html_snippet = "You were NOT given a point for " . $result['event_name'] . ". You can request an explanation through the VP.";
		}elseif($code == 3){
			echo "Success! The VP will consult another attendee of the event.";
			$html_snippet = "The points taker couldn't remember if you were at " . $result['event_name'] . " and additional inquiry will be made by the VP.";
		}

		$html = "<h2>Slivka Points Correction Response Posted</h2>
			<h3>Automated Email</h3>
			<p style=\"padding: 10; width: 70%\">A points correction you submitted has received a response:</p>

			<p style=\"padding: 10; width: 70%\">" . $html_snippet . "</p>

			<p style=\"padding: 10; width: 70%\"><a href=\"http://slivka.northwestern.edu/points/table.php\" target=\"_blank\">View Points</a></p>

			<p style=\"padding: 10; width: 70%\">If you received this email in error, please contact BenSRothman@gmail.com</p>";

		return self::sendEmail($result['nu_email'],"Slivka Points Correction Response Posted (Automated)",$html);
	}

	private function sendEmail($to_email,$subject,$body)
	{
		$from = array("BenSRothman@gmail.com" =>"Ben Rothman");

		$to = array(
			$to_email . "@u.northwestern.edu" => $to_email,
			'BenSRothman+mailbot@gmail.com' => 'Bens Copy'
		);

		$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
			->setUsername("bensrothman@gmail.com")
			->setPassword(DatabasePDO::$GMAIL_PASS);

		$mailer = Swift_Mailer::newInstance($transport);

		$message = new Swift_Message($subject);
		$message->setFrom($from);
		$message->setBody($body, 'text/html');
		$message->setTo($to);
		$message->addPart($subject, 'text/plain');

		if ($recipients = $mailer->send($message, $failures)){
			return "Message successfully sent!";
		} else {
			return "There was an error: " . print_r($failures);
		}
	}

	public function getCoursesInDept ($department)
	{
		self::initializeConnection();
		$courses = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT courses
				FROM courses
				WHERE courses LIKE :department");
			$statement->bindValue(":department", "%".$department."%");
			$statement->execute();
			$courses = $statement->fetchAll(PDO::FETCH_COLUMN,0);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		$return = array();

		foreach($courses as $c){
			$arr = explode($department,$c);

			foreach($arr as $el){
				if($el[0] == ' '){
					$return[] = substr($el,1,($el[5] > 0 && $el[5] < 5 ? 5 : 3));
				}
			}
		}

		$return = array_unique($return);
		sort($return);

		return $return;
	}

	public function getCourseListing ($department,$number)
	{
		self::initializeConnection();
		$courses = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email,qtr
				FROM courses
				WHERE courses LIKE :course");
			$statement->bindValue(":course", "%".$department." ".$number."%");
			$statement->execute();
			$courses = $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $courses;
	}

	public function submitCourseDatabaseEntryForm ($nu_email,$courses,$qtr)
	{
		self::initializeConnection();
		try {
			$statement = self::$dbConn->prepare(
				"INSERT INTO courses
				(nu_email, courses, qtr)
				VALUES (?,?,?)");
			$statement->execute(array($nu_email,$courses,$qtr));
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage() . "<br/>Tell Ben";
			die();
		}

		return true;
	}
}