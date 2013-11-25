<?php
require_once "./DatabasePDO.php";
include_once "./swift/swift_required.php";

class PointsCenter
{
	private static $qtr = 1303; # This is the only place quarter must be updated

	private static $dbConn = null;
	public function __construct ()
	{
		error_reporting(E_ALL & ~E_NOTICE);
		self::initializeConnection();
	}

	private static function initializeConnection ()
	{
		if (is_null(self::$dbConn)) {
			self::$dbConn = DatabasePDO::getInstance();
		}
	}

	public function setQuarter ($qtr)
	{
		self::$qtr = $qtr;
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
				"SELECT slivkans.first_name,slivkans.last_name,
					slivkans.year,slivkans.major,suites.suite,slivkans.photo
				FROM slivkans
				LEFT JOIN suites ON slivkans.nu_email=suites.nu_email AND suites.qtr=:qtr
				WHERE slivkans.qtr_joined <= :qtr AND (slivkans.qtr_final IS NULL OR slivkans.qtr_final >= :qtr)
				ORDER BY slivkans.first_name,slivkans.last_name");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$directory = $statement->fetchAll(PDO::FETCH_NUM);
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
				"SELECT CONCAT(slivkans.first_name, ' ', slivkans.last_name) AS full_name,
					slivkans.nu_email,slivkans.gender,slivkans.wildcard,committees.committee,slivkans.photo,suites.suite,slivkans.year
				FROM slivkans
				LEFT JOIN committees ON slivkans.nu_email=committees.nu_email AND committees.qtr=:qtr
				LEFT JOIN suites ON slivkans.nu_email=suites.nu_email AND suites.qtr=:qtr
				WHERE slivkans.qtr_joined <= :qtr AND (slivkans.qtr_final IS NULL OR slivkans.qtr_final >= :qtr)
				ORDER BY slivkans.first_name,slivkans.last_name");
			$statement->bindValue(":qtr", self::$qtr);
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

	public function getIMs ($team)
	{
		self::initializeConnection();
		$IMs = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT event_name
				FROM events
				WHERE qtr=:qtr AND type='im' AND event_name LIKE :team");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":team", "%".$team."%");
			$statement->execute();
			$IMs = $statement->fetchAll(PDO::FETCH_COLUMN,0);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $IMs;
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

	public function getSlivkanPointsByCommittee ($nu_email)
	{
		self::initializeConnection();
		$points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT committee, count(nu_email) AS count
				FROM points LEFT JOIN events
					ON points.event_name=events.event_name
					WHERE nu_email=:nu_email AND type!='im' AND points.qtr=:qtr
				GROUP BY events.committee");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":nu_email", $nu_email);
			$statement->execute();
			$points = $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $points;
	}

	public function getSlivkanIMPoints ($nu_email)
	{
		self::initializeConnection();
		$im_points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT events.description, count(points.nu_email) AS count
				FROM points LEFT JOIN events
					ON points.event_name=events.event_name
					WHERE points.nu_email=:nu_email AND events.type='im' AND events.qtr=:qtr
					GROUP BY nu_email, events.description");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":nu_email", $nu_email);
			$statement->execute();
			$im_points = $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $im_points;
	}

	public function getSlivkanBonusPoints ($nu_email)
	{
		self::initializeConnection();
		$helper_points = 0;
		try {
			$statement = self::$dbConn->prepare(
				"SELECT sum(helper)
				FROM (
					SELECT nu_email, helper
					FROM bonuspoints
					WHERE nu_email=:nu_email AND qtr=:qtr

					UNION ALL

					SELECT nu_email, count(nu_email) AS helper
					FROM helperpoints WHERE nu_email=:nu_email AND qtr=:qtr) AS h");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":nu_email", $nu_email);
			$statement->execute();
			$helper_points = $statement->fetch(PDO::FETCH_COLUMN);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		$bonus = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT *
				FROM bonuspoints
				WHERE nu_email=:nu_email AND qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":nu_email", $nu_email);
			$statement->execute();
			$bonus = $statement->fetch(PDO::FETCH_NAMED);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		if($bonus){
			$committee_points = $bonus['committee'];
			$position_points = $bonus['other1']+$bonus['other2']+$bonus['other3'];
		}else{
			$committee_points = 0;
			$position_points = 0;
		}


		return array("helper" => $helper_points, "committee" => $committee_points, "position" => $position_points);
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

	public function getPointsTable ($assoc = false)
	{
		$quarter_info = self::getQuarterInfo();
		$slivkans = self::getSlivkans();
		$events = self::getEvents($quarter_info['start_date'],$quarter_info['end_date']);
		$points = self::getPoints();
		$helperpoints = self::getHelperPoints();
		$committeeattendance = self::getCommitteeAttendance();
		$bonuspoints = self::getBonusPoints();

		$points_table = array(); #table that is slivkan count by event count + 6
		$years_suites = array(); #for stats later
		$im_points = array();

		#form points_table
		$events_count = count($events);
		$events_total_ind		= $events_count + 2;
		$helper_points_ind		= $events_count + 3;
		$im_points_ind			= $events_count + 4;
		$committee_points_ind	= $events_count + 5;
		$bonus_points_ind		= $events_count + 6;
		$total_ind				= $events_count + 7;

		for($s=0; $s < count($slivkans); $s++){
			$points_table[$slivkans[$s]['nu_email']] = array_merge(array($slivkans[$s]['full_name'], $slivkans[$s]['gender']), array_fill('1', $events_count+6, 0));
			$years_suites[$slivkans[$s]['nu_email']] = array('year' => $slivkans[$s]['year'], 'suite' => $slivkans[$s]['suite']);
		}

		for($e=0; $e < $events_count; $e++){
			$event_name = $events[$e]['event_name'];
			$is_im = $events[$e]['type'] == "im";

			foreach($points[$event_name] as $s){
				$points_table[$s][2+$e] = 1;

				if(!$is_im){
					$points_table[$s][$events_total_ind]++;
				}else{
					if(!array_key_exists($s,$im_points)){
						$im_points[$s] = array();
					}

					$im_points[$s][$events[$e]['description']]++;
				}
			}

			if(array_key_exists($event_name, $helperpoints)){
				foreach($helperpoints[$event_name] as $s){
					$points_table[$s][2+$e] += 0.1;
					$points_table[$s][$helper_points_ind]++;
				}
			}

			if(array_key_exists($event_name, $committeeattendance)){
				foreach($committeeattendance[$event_name] as $s){
					$points_table[$s][2+$e] += 0.2;
				}
			}
		}

		#handling IMs
		foreach(array_keys($im_points) as $s){
			$im_points_total = 0;

			foreach($im_points[$s] as $im){
				if($im >= 3){ $im_points_total += $im; }
			}
			if($im_points_total > 15){ $im_points_total = 15; }

			$points_table[$s][$im_points_ind] = $im_points_total;
		}

		foreach(array_keys($bonuspoints) as $s){
			$points_table[$s][$helper_points_ind] += $bonuspoints[$s]['helper']; #bonus helper points
			$points_table[$s][$committee_points_ind] = (int)$bonuspoints[$s]['committee'];
			$points_table[$s][$bonus_points_ind] =
				$bonuspoints[$s]['other1'] +
				$bonuspoints[$s]['other2'] +
				$bonuspoints[$s]['other3'];
		}

		# statistics holders:
		$counts_by_year = array();
		$totals_by_year = array();
		$counts_by_suite = array();
		$totals_by_suite = array();

		#run through whole points table to finish up
		foreach(array_keys($points_table) as $s){
			#handling helper points max
			if($points_table[$s][$helper_points_ind] > 5){ $points_table[$s][$helper_points_ind] = 5; }

			$points_table[$s][$total_ind] = array_sum(array_slice($points_table[$s], $events_total_ind, 5));

			$year = $years_suites[$s]['year'];
			$suite = $years_suites[$s]['suite'];

			$counts_by_year[$year]++;
			$totals_by_year[$year] += array_sum(array_slice($points_table[$s], $events_total_ind, 3));
			$counts_by_suite[$suite]++;
			$totals_by_suite[$suite] += array_sum(array_slice($points_table[$s], $events_total_ind, 3));
		}

		foreach($totals_by_year as $y => $total){
			$by_year[] = array($y, round($total / $counts_by_year[$y], 2));
		}

		foreach($totals_by_suite as $s => $total){
			$by_suite[] = array($s, round($total / $counts_by_suite[$s], 2));
		}

		if(!$assoc){
			$points_table = array_values($points_table);
		}

		return array('points_table' => $points_table, 'events' => $events, 'by_year' => $by_year, 'by_suite' => $by_suite);
	}

	public function getMultipliers ()
	{
		self::initializeConnection();
		$slivkans = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT CONCAT(first_name,' ',last_name) AS full_name,
					nu_email,gender,qtr_joined,qtrs_away,qtr_final
				FROM slivkans
				WHERE qtr_final IS NULL OR qtr_final >= :qtr
				ORDER BY first_name, last_name");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$slivkans = $statement->fetchAll(PDO::FETCH_ASSOC);

		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		$count = count($slivkans);

		for($s=0; $s<$count; $s++){
			$y_join = round($slivkans[$s]['qtr_joined'],-2);
			$q_join = $slivkans[$s]['qtr_joined'] - $y_join;

			$y_this = round(self::$qtr,-2);
			$q_this = self::$qtr - $y_this;

			$y_acc = ($y_this - $y_join) / 100;
			$q_acc = $q_this - $q_join;

			$q_total = $q_acc + 3 * $y_acc - $slivkans[$s]['qtrs_away'];

			$mult = 1 + 0.1 * $q_total;

			$slivkans[$s]['mult'] = $mult;
		}

		return $slivkans;
	}

	public function getRankings ()
	{
		$is_housing = true;
		# figure out how many qtrs to consider
		# if its spring, you're trying to get final housing rankings.
		$y_this = round(self::$qtr,-2);
		$q_this = self::$qtr - $y_this;

		if($q_this == 2 && !$is_housing){
			$qtrs = array(self::$qtr);
		}else if($q_this == 3){
			$qtrs = array(self::$qtr-1, self::$qtr);
		}else if($q_this == 1 || $is_housing){
			$qtrs = array($y_this-100+2, $y_this-100+3, $y_this+1);
		}else{
			echo "Error: qtr messed up. " . $q_this;
			die();
		}

		# spring 13, fall 13, winter 14 HOUSING spring 14

		$rankings = self::getMultipliers();
		$totals = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email,total
				FROM totals
				WHERE qtr IN (".implode(",",$qtrs).")
				ORDER BY qtr");
			$statement->execute();
			$totals = $statement->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		$mult_count = count($rankings);
		$qtrs_count = count($qtrs);
		for($i=0; $i<$mult_count; $i++){
			$sum = 0;
			for($j=0; $j<$qtrs_count; $j++){
				$t = (int) $totals[$rankings[$i]['nu_email']][$j] OR 0;
				$rankings[$i][$qtrs[$j]] = $t;
				$sum += $t;
			}

			$rankings[$i]['total'] = $sum;
			$rankings[$i]['total_w_mult'] = $sum * $rankings[$i]['mult'];
		}

		return array('rankings' => $rankings, 'qtrs' => $qtrs);
	}

	public function updateTotals ()
	{
		$points_table = self::getPointsTable(true);
		$points_table = $points_table['points_table'];

		$sql = "INSERT INTO totals (nu_email, total, qtr) VALUES ";

		$fills = array();
		$values = array();
		foreach($points_table as $s => $row){
			$fills[] = "(?,?,?)";
			$values = array_merge($values,array($s,array_pop($row),self::$qtr));
		}
		$sql .= implode(", ",$fills) . " ON DUPLICATE KEY UPDATE total=VALUES(total)";

		print_r($values);

		try {
			$statement = self::$dbConn->prepare($sql);
			$statement->execute($values);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return true;
	}

	public function getAbstentions ()
	{
		self::initializeConnection();
		$abstentions = array();

		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email
				FROM slivkans
				WHERE qtr_final=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$abstentions = $statement->fetchAll(PDO::FETCH_COLUMN);

		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $abstentions;
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
					SET attendees=attendees+1
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
				"SELECT CONCAT(slivkans.first_name, ' ', slivkans.last_name) AS full_name,
					courses.nu_email,courses.qtr
				FROM courses
				INNER JOIN slivkans ON courses.nu_email=slivkans.nu_email
				WHERE courses.courses LIKE :course AND slivkans.qtr_joined <= :qtr AND (slivkans.qtr_final IS NULL OR slivkans.qtr_final >= :qtr)");
			$statement->bindValue(":course", "%".$department." ".$number."%");
			$statement->bindValue(":qtr", self::$qtr);
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
