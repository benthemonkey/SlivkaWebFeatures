<?php
require_once __DIR__ . "/datastoreVars.php";
include_once __DIR__ . "/swift/swift_required.php";

class PointsCenter
{
	private static $qtr = 0;

	private static $dbConn = null;

	public function __construct ($qtr)
	{
		error_reporting(E_ALL & ~E_NOTICE);
		//ini_set('display_errors', '1');
		self::initializeConnection();

		if ($qtr) {
			self::$qtr = $qtr;
		} else {
			self::$qtr = $GLOBALS['QTR'];
		}
	}

	private static function initializeConnection ()
	{
		if (is_null(self::$dbConn)) {
            $dsn = $GLOBALS['DB_TYPE'] . ":host=" . $GLOBALS['DB_HOST'] . ";dbname=" . $GLOBALS['DB_NAME'];
            try {
                self::$dbConn = new PDO($dsn, $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                die();
            }

            self::$dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
	}

	public function getQuarter ()
	{
		return self::$qtr;
	}

	public function getQuarters ()
	{
		$quarters = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT qtr,quarter
				FROM quarters
				WHERE 1301<qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$quarters = $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $quarters;
	}

	public function getQuarterInfo ()
	{
		$quarter_info;
		try {
			$statement = self::$dbConn->prepare(
				"SELECT *
				FROM quarters
				WHERE qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$quarter_info = $statement->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		$quarter_info['im_teams'] = json_decode($quarter_info['im_teams']);
		return $quarter_info;
	}

	public function getDirectory ()
	{
		$directory = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT first_name,last_name,year,major,suite,photo
				FROM slivkans
				LEFT JOIN suites ON slivkans.nu_email=suites.nu_email AND suites.qtr=:qtr
				WHERE qtr_joined <= :qtr AND (qtr_final IS NULL OR qtr_final >= :qtr)
				ORDER BY first_name,last_name");
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
		$slivkans = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT CONCAT(first_name, ' ', last_name) AS full_name,
					slivkans.nu_email,gender,wildcard,committee,photo,suite,year
				FROM slivkans
				LEFT JOIN committees ON slivkans.nu_email=committees.nu_email AND committees.qtr=:qtr
				LEFT JOIN suites ON slivkans.nu_email=suites.nu_email AND suites.qtr=:qtr
				WHERE qtr_joined <= :qtr AND (qtr_final IS NULL OR qtr_final >= :qtr)
				ORDER BY first_name,last_name");
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
				WHERE qtr=:qtr AND date BETWEEN :start AND :end AND type<>'committee_only'
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

	public function getCommitteeEvents ($committee)
	{
		$events = array();

		try {
			$statement = self::$dbConn->prepare(
				"SELECT event_name, filled_by
				FROM events
				WHERE qtr=:qtr AND committee=:committee AND type<>'im'
				ORDER BY date, id");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":committee", $committee);
			$statement->execute();
			$events = $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $events;
	}

	public function getRecentEvents ($count = 20, $offset = 0)
	{
		$events = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT event_name,date,type,attendees,committee,description
				FROM events
				WHERE qtr=:qtr AND type<>'committee_only'
				ORDER BY date DESC, id DESC
				LIMIT :offset,:count");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":offset", $offset, PDO::PARAM_INT);
			$statement->bindValue(":count", $count, PDO::PARAM_INT);
			$statement->execute();
			$events = $statement->fetchAll(PDO::FETCH_NAMED);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return array_reverse($events);
	}

	public function getIMs ($team)
	{
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

	public function getCommitteePoints ()
	{
		$committee_points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT event_name,nu_email,points
				FROM committeepoints
				WHERE qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$committee_points = $statement->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $committee_points;
	}

	public function getCommitteeTotals ()
	{
		$committee_points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email,points
				FROM committees
				WHERE qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$committee_points = $statement->fetchAll(PDO::FETCH_KEY_PAIR);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $committee_points;
	}

	public function getCommitteeBonusPoints ($committee)
	{
		$committee_bonus_points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email,bonus
				FROM committees
				WHERE qtr=:qtr AND committee=:committee");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":committee", $committee);
			$statement->execute();
			$committee_bonus_points = $statement->fetchAll(PDO::FETCH_KEY_PAIR);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $committee_bonus_points;
	}

	public function getCommitteeAttendance ($committee)
	{
		$committee_attendance = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT p.event_name, p.nu_email FROM
					(SELECT event_name, committee, qtr
						FROM events
						WHERE qtr=:qtr AND committee=:committee AND type<>'im') AS e
				INNER Join committees AS c USING (committee, qtr)
				INNER JOIN points AS p USING (event_name, nu_email)");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":committee", $committee);
			$statement->execute();
			$committee_attendance = $statement->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $committee_attendance;
	}

	public function getEventTotals ()
	{
		$points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email, count(event_name) AS total
				FROM points INNER JOIN events USING (event_name, qtr)
				WHERE qtr=:qtr AND type NOT IN ('im','committee_only')
				GROUP BY nu_email");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$points = $statement->fetchAll(PDO::FETCH_KEY_PAIR);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		return $points;
	}

	public function getIMPoints ()
	{
		$im_points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email, LEAST(SUM(count),15) AS total
				FROM imcounts
				WHERE count>=3 AND qtr=:qtr
				GROUP BY nu_email");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$im_points = $statement->fetchAll(PDO::FETCH_KEY_PAIR);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $im_points;
	}

	public function getSlivkanPoints ($nu_email)
	{
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
		$points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT committee, count(nu_email) AS count
				FROM points
				INNER JOIN events USING (event_name,qtr)
				WHERE nu_email=:nu_email AND qtr=:qtr AND type NOT IN ('im','committee_only')
				GROUP BY committee");
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
		$im_points = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT sport, count
				FROM imcounts
				WHERE nu_email=:nu_email AND qtr=:qtr");
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
		$helper_points = 0;
		try {
			$statement = self::$dbConn->prepare(
				"SELECT count
				FROM helperpointcounts
				WHERE nu_email=:nu_email AND qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":nu_email", $nu_email);
			$statement->execute();
			$helper_points = $statement->fetch(PDO::FETCH_COLUMN);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		if(!$helper_points){ $helper_points = 0; }

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

		$committee_points = 0;
		try {
			$statement = self::$dbConn->prepare(
				"SELECT points
				FROM committees
				WHERE nu_email=:nu_email AND qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":nu_email", $nu_email);
			$statement->execute();
			$committee_points = $statement->fetch(PDO::FETCH_COLUMN);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		if($bonus){
			$helper_points += $bonus['helper'];
			$other_points = $bonus['other1']+$bonus['other2']+$bonus['other3'];
		}else{
			$other_points = 0;
		}

		$other_breakdown = array(
			array($bonus['other1_name'], $bonus['other1']),
			array($bonus['other2_name'], $bonus['other2']),
			array($bonus['other3_name'], $bonus['other3']));


		return array("helper" => $helper_points, "committee" => $committee_points, "other" => $other_points, "other_breakdown" => $other_breakdown);
	}

	public function getBonusPoints ()
	{
		$bonus_points = array();
		try {
			$statement = self::$dbConn->prepare( #using left + right join to mimic full outer join
				"SELECT nu_email,
					IFNULL(helperpointcounts.count,0)+IFNULL(bonuspoints.helper,0) AS helper,
					other1+other2+other3 AS other
				FROM bonuspoints
				LEFT JOIN helperpointcounts USING (nu_email,qtr)
					WHERE qtr=:qtr

				UNION

				SELECT nu_email,
					IFNULL(helperpointcounts.count,0)+IFNULL(bonuspoints.helper,0) AS helper,
					other1+other2+other3 AS other
				FROM bonuspoints
				RIGHT JOIN helperpointcounts USING (nu_email,qtr)
					WHERE qtr=:qtr");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$bonus_points = $statement->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		foreach(array_keys($bonus_points) AS $s){
			$bonus_points[$s] = $bonus_points[$s][0];
		}

		return $bonus_points;
	}

	public function getPointsTable ($showall = false)
	{
		$slivkans = self::getSlivkans();
		if($showall){
			$quarter_info = self::getQuarterInfo();
			$events = self::getEvents($quarter_info['start_date'],$quarter_info['end_date']);
		}else{
			$events = self::getRecentEvents();
		}
		$points = self::getPoints();
		$event_totals = self::getEventTotals();
		$im_points = self::getIMPoints();
		$bonus_points = self::getBonusPoints();

		$helper_points = self::getHelperPoints();
		$committee_points = self::getCommitteePoints();
		$committee_totals = self::getCommitteeTotals();


		$points_table = array(); #table that is slivkan count by event count + 6

		# statistics holders:
		$totals_by_year = array();
		$totals_by_suite = array();

		#form points_table
		$events_count = count($events);
		$total_ind = $events_count + 7;

		for($s=0; $s < count($slivkans); $s++){
			$nu_email = $slivkans[$s]['nu_email'];

			$committee_total = 0;
			if(array_key_exists($nu_email, $committee_totals)){
				$committee_total = (int) $committee_totals[$nu_email];
			}

			$subtotal = $event_totals[$nu_email] + $bonus_points[$nu_email]['helper'] + $im_points[$nu_email];
			$total = $subtotal + $committee_total + $bonus_points[$nu_email]['other'];

			$totals_by_year[$slivkans[$s]['year']][] = $subtotal;
			$totals_by_suite[$slivkans[$s]['suite']][] = $subtotal;

			$points_table[$nu_email] = array_merge(
				array($slivkans[$s]['full_name'], $slivkans[$s]['gender']),
				array_fill(0, $events_count, 0),
				array((int) $event_totals[$nu_email], (int) $bonus_points[$nu_email]['helper'],
					(int) $im_points[$nu_email], $committee_total,
					(int) $bonus_points[$nu_email]['other'], $total));
		}

		for($e=0; $e < $events_count; $e++){
			$event_name = $events[$e]['event_name'];
			$is_im = $events[$e]['type'] == "im";

			foreach($points[$event_name] as $s){
				$points_table[$s][2+$e] = 1;
			}

			if(!$is_im){
				if(array_key_exists($event_name, $helper_points)){
					foreach($helper_points[$event_name] as $s){
						$points_table[$s][2+$e] += 0.1;
					}
				}

				if(array_key_exists($event_name, $committee_points)){
					foreach($committee_points[$event_name] as $s){
						if($s['points'] > 0.0){
							$points_table[$s['nu_email']][2+$e] += 0.2;
						}
					}
				}
			}
		}

		foreach($totals_by_year as $y => $totals){
			$by_year[] = array($y, round(array_sum($totals)/count($totals), 2));
		}

		foreach($totals_by_suite as $s => $totals){
			$by_suite[] = array($s, round(array_sum($totals)/count($totals), 2));
		}

		return array('points_table' => array_values($points_table), 'events' => $events, 'by_year' => $by_year, 'by_suite' => $by_suite);
	}

	public function getCommitteePointsTable ($committee)
	{
		$slivkans = self::getCommittee($committee);
		$events = self::getCommitteeEvents($committee);
		$committee_points = self::getCommitteePoints();
		$committee_bonus_points = self::getCommitteeBonusPoints($committee);
		$committee_attendance = self::getCommitteeAttendance($committee);

		$committee_points_table = array();
		$event_names = array();

		$events_count = count($events);
		$total_ind = $events_count + 1;
		for($s=0; $s<count($slivkans); $s++){
			$nu_email = $slivkans[$s]['nu_email'];

			$committee_points_table[$nu_email] = array_fill(0, $events_count+2, array('points' => 0.0, 'filled_by' => false, 'attended' => false));
			$committee_points_table[$nu_email][$events_count]['points'] = (float) $committee_bonus_points[$nu_email];
			$committee_points_table[$nu_email][$total_ind]['points'] = (float) $committee_bonus_points[$nu_email]; # total column
		}

		for($e=0; $e<$events_count; $e++){
			$event_name = $events[$e]['event_name'];
			$filled_by = $events[$e]['filled_by'];

			$event_names[] = $event_name;

			if(array_key_exists($filled_by, $committee_points_table)){
				$committee_points_table[$filled_by][$e]['filled_by'] = true;
			}

			if(array_key_exists($event_name, $committee_attendance)){
				foreach($committee_attendance[$event_name] as $s){
					$committee_points_table[$s][$e]['attended'] = true;
				}
			}

			if(array_key_exists($event_name, $committee_points)){
				foreach($committee_points[$event_name] as $s){
					$committee_points_table[$s['nu_email']][$e]['points'] += $s['points'];
					$committee_points_table[$s['nu_email']][$total_ind]['points'] += $s['points'];
				}
			}
		}

		return array('points_table' => $committee_points_table, 'events' => $event_names);
	}

	public function getMultipliers ()
	{
		$slivkans = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT CONCAT(first_name,' ',last_name) AS full_name,
					nu_email,gender,qtr_joined,qtrs_away,qtr_final
				FROM slivkans
				WHERE qtr_final IS NULL OR qtr_final>=:qtr
				ORDER BY first_name, last_name");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();
			$slivkans = $statement->fetchAll(PDO::FETCH_ASSOC);

		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}
		$count = count($slivkans);
		$is_housing = $GLOBALS['IS_HOUSING'] == true;

		for($s=0; $s<$count; $s++){
			$y_join = round($slivkans[$s]['qtr_joined'],-2);
			$q_join = $slivkans[$s]['qtr_joined'] - $y_join;

			$y_this = round(self::$qtr,-2);
			$q_this = self::$qtr - $y_this;

			$y_acc = ($y_this - $y_join) / 100;
			$q_acc = $q_this - $q_join;

			$q_total = $q_acc + 3 * $y_acc - $slivkans[$s]['qtrs_away'];

			# give multiplier for current qtr if it isnt housing
			if (!$is_housing) {
				$q_total += 1;
			}

			$mult = 1 + 0.1 * $q_total;

			$slivkans[$s]['mult'] = $mult;
		}

		return $slivkans;
	}

	public function getRankings ()
	{
		$is_housing = $GLOBALS['IS_HOUSING'] == true;
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
		$abstentions = self::getAbstentions();
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

		$house_meetings;

		try {
			$statement = self::$dbConn->prepare(
				"SELECT count(type)
				FROM events
				WHERE qtr IN (".implode(",",$qtrs).") AND type='house_meeting'");
			$statement->execute();
			$house_meetings = $statement->fetch(PDO::FETCH_COLUMN);
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
			$rankings[$i]['abstains'] = in_array($rankings[$i]['nu_email'], $abstentions) || $rankings[$i]['total'] < $house_meetings;
		}

		return array('rankings' => $rankings, 'qtrs' => $qtrs, 'is_housing' => $is_housing);
	}

	public function updateTotals ()
	{
		$slivkans = self::getSlivkans();
		$event_totals = self::getEventTotals();
		$im_points = self::getIMPoints();
		$bonus_points = self::getBonusPoints();
		$committee_totals = self::getCommitteeTotals();

		try {
			$statement = self::$dbConn->prepare(
				"INSERT INTO totals (nu_email, total, qtr)
				VALUES (?,?,?)
				ON DUPLICATE KEY UPDATE total=VALUES(total)");

			for($s=0; $s < count($slivkans); $s++){
				$nu_email = $slivkans[$s]['nu_email'];
				$total = $event_totals[$nu_email] + $im_points[$nu_email];

				if(array_key_exists($nu_email, $committee_totals)){
					$total += $committee_totals[$nu_email];
				}

				if(array_key_exists($nu_email, $bonus_points)){
					$total += array_sum($bonus_points[$nu_email]);
				}

				$statement->execute(array($nu_email, $total, self::$qtr));
			}
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return true;
	}

	public function getAbstentions ()
	{
		$q = self::$qtr - round(self::$qtr,-2);

		#round up to closest "YY02"
		if ($q == 3) {
			$qtr_final = round(self::$qtr,-2) + 100 + 2;
		} else {
			$qtr_final = round(self::$qtr,-2) + 2;
		}

		$abstentions = array();

		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email
				FROM slivkans
				WHERE qtr_final>=:qtr AND qtr_final<=:qtr_final");
			$statement->bindValue(":qtr", self::$qtr);
			$statement->bindValue(":qtr_final", $qtr_final);
			$statement->execute();
			$abstentions = $statement->fetchAll(PDO::FETCH_COLUMN);

		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $abstentions;
	}

	public function getCommittee ($committee)
	{
		$slivkans = array();

		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email, points
				FROM committees
				WHERE committee=:committee AND qtr=:qtr");
			$statement->bindValue(":committee", $committee);
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();

			$slivkans = $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $slivkans;
	}

	public function updateCommittee ($slivkans, $committee, $points)
	{
		try {
			$statement = self::$dbConn->prepare(
				"DELETE FROM committees WHERE qtr=:qtr AND committee=:committee AND
				nu_email NOT IN ('".implode("','",$slivkans)."')");
			$statement->bindValue(':qtr', self::$qtr);
			$statement->bindValue(':committee', $committee);
			$statement->execute();

			$statement = self::$dbConn->prepare(
				"INSERT INTO committees (nu_email, committee, points, qtr) VALUES (?,?,?,?)
				ON DUPLICATE KEY UPDATE committee=VALUES(committee), points=VALUES(points)");

			for($s=0; $s < count($slivkans); $s++){
				$statement->execute(array($slivkans[$s], $committee, $points[$s], self::$qtr));
			}

		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return true;
	}

	public function getSuite ($suite)
	{
		$slivkans = array();

		try {
			$statement = self::$dbConn->prepare(
				"SELECT nu_email
				FROM suites
				WHERE suite=:suite AND qtr=:qtr");
			$statement->bindValue(":suite", $suite);
			$statement->bindValue(":qtr", self::$qtr);
			$statement->execute();

			$slivkans = $statement->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return $slivkans;
	}

	public function updateSuite ($slivkans, $suite)
	{
		try {
			$statement = self::$dbConn->prepare(
				"DELETE FROM suites WHERE qtr=:qtr AND suite=:suite AND
				nu_email NOT IN ('".implode("','",$slivkans)."')");
			$statement->bindValue(':qtr', self::$qtr);
			$statement->bindValue(':suite', $suite);
			$statement->execute();

			$statement = self::$dbConn->prepare(
				"INSERT INTO suites (nu_email, suite, qtr) VALUES (?,?,?)
				ON DUPLICATE KEY UPDATE suite=VALUES(suite)");

			for($s=0; $s < count($slivkans); $s++){
				$statement->execute(array($slivkans[$s], $suite, self::$qtr));
			}
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage();
			die();
		}

		return true;
	}

	public function submitCommitteePoint ($nu_email, $event_name, $points)
	{
		if($event_name == 'bonus'){
			try {
				$statement = self::$dbConn->prepare(
					"INSERT INTO committees (nu_email,bonus,qtr) VALUES (?,?,?)
					ON DUPLICATE KEY UPDATE bonus=VALUES(bonus)");

				$statement->execute(array($nu_email, $points, self::$qtr));
			} catch (PDOException $e) {
				echo "Error: " . $e->getMessage();
				die();
			}
		}else{
			try {
				$statement = self::$dbConn->prepare(
					"INSERT INTO committeepoints (nu_email,event_name,points,qtr) VALUES (?,?,?,?)
					ON DUPLICATE KEY UPDATE points=VALUES(points)");

				$statement->execute(array($nu_email, $event_name, $points, self::$qtr));
			} catch (PDOException $e) {
				echo "Error: " . $e->getMessage();
				die();
			}
		}

		return true;
	}

	public function submitHelperPoint ($nu_email, $event_name)
	{
		try {
			$statement = self::$dbConn->prepare(
				"INSERT INTO helperpoints (nu_email,event_name,qtr) VALUES (?,?,?)");

			$statement->execute(array($nu_email, $event_name, self::$qtr));
		} catch (PDOException $e) {
			echo "{\"status\": \"Error: " . $e->getMessage() . "\"}";
			die();
		}

		return true;
	}

	public function submitPointsForm ($get)
	{
		$real_event_name = $get['event_name'] . " " . $get['date'];

		if($get['fellows'] === NULL){ $get['fellows'] = array(""); }

		# Begin PDO Transaction
		self::$dbConn->beginTransaction();

		try {
			$statement = self::$dbConn->prepare(
				"INSERT INTO `pointsform` SET
				date=:date, type=:type, committee=:committee, event_name=:event_name, description=:description,
				filled_by=:filled_by, comments=:comments, attendees=:attendees, fellows=:fellows");
			$statement->bindValue(":date", $get['date']);
			$statement->bindValue(":type", $get['type']);
			$statement->bindValue(":committee", $get['committee']);
			$statement->bindValue(":event_name", $get['event_name']);
			$statement->bindValue(":description", $get['description']);
			$statement->bindValue(":filled_by", $get['filled_by']);
			$statement->bindValue(":comments", $get['comments']);
			$statement->bindValue(":attendees", implode(", ",$get['attendees']));
			$statement->bindValue(":fellows", implode(", ",$get['fellows']));

			$statement->execute();
		} catch (PDOException $e) {
			echo json_encode(array("error" => $e->getMessage(), "step" => "1"));
			self::$dbConn->rollBack();
			die();
		}

		try {
			$statement = self::$dbConn->prepare(
				"INSERT INTO events (event_name,date,qtr,filled_by,committee,description,type,attendees)
				VALUES (:event_name,:date,:qtr,:filled_by,:committee,:description,:type,:attendees)");
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
			self::$dbConn->rollBack();
			die();
		}

		try {
			$statement = self::$dbConn->prepare(
				"INSERT INTO points (nu_email,event_name,qtr)
				VALUES (?,?,?)");

			foreach($get['attendees'] as $a){
				$statement->execute(array($a,$real_event_name,self::$qtr));
			}
		} catch (PDOException $e) {
			echo json_encode(array("error" => $e->getMessage(), "step" => "3"));
			self::$dbConn->rollBack();
			die();
		}

		/*if ($get['helper_points'][0] != ""){
			try {
				$statement = self::$dbConn->prepare(
					"INSERT INTO helperpoints (nu_email, event_name, qtr)
					VALUES (?,?,?)");

				foreach($get['helper_points'] as $h){
					$statement->execute(array($h,$real_event_name,self::$qtr));
				}
			} catch (PDOException $e) {
				echo json_encode(array("error" => $e->getMessage(), "step" => "4"));
				self::$dbConn->rollBack();
				die();
			}
		}

		if ($get['committee_members'][0] != ""){
			try {
				$statement = self::$dbConn->prepare(
					"INSERT INTO committeepoints (nu_email, event_name, qtr)
					VALUES (?,?,?)");

				foreach($get['committee_members'] as $c){
					$statement->execute(array($c,$real_event_name,self::$qtr));
				}
			} catch (PDOException $e) {
				echo json_encode(array("error" => $e->getMessage(), "step" => "5"));
				self::$dbConn->rollBack();
				die();
			}
		}*/

		if ($get['fellows'][0] != ""){
			try {
				$statement = self::$dbConn->prepare(
					"INSERT INTO fellowattendance (full_name,event_name,qtr)
					VALUES (?,?,?)");

				foreach($get['fellows'] as $f){
					$statement->execute(array($f,$real_event_name,self::$qtr));
				}
			} catch (PDOException $e) {
				echo json_encode(array("error" => $e->getMessage(), "step" => "4"));
				self::$dbConn->rollBack();
				die();
			}
		}

		#email VP a notification?
		if($GLOBALS['VP_EMAIL_POINT_SUBMISSION_NOTIFICATIONS']){
			$html = "<table border=\"1\">";

			foreach($get as $key => $value){
				$html .= "<tr><td style=\"text-align:right;\">";

				if(is_array($value)){
					$html .=  $key . "</td><td>" . implode(", ", $value);
				}else{
					$html .= $key . "</td><td>" . $value;
				}

				$html .= "</td></tr>\n";
			}

			$html .= "</table>";

			self::sendEmail(null, "Points Submitted for " . $real_event_name, $html);
		}

		return self::$dbConn->commit();
	}

	public function submitPointsCorrectionForm($get,$key){

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

			$filled_by = $statement->fetch(PDO::FETCH_COLUMN);
		} catch (PDOException $e) {
			echo json_encode(array("message" => "Error: " . $e->getMessage()));
			die();
		}

		if($get['comments'] === NULL){ $get['comments'] = ""; }

		try {
			$statement = self::$dbConn->prepare(
				"INSERT INTO pointscorrection (message_key,nu_email,event_name,comments)
				VALUES (:message_key,:nu_email,:event_name,:comments)");
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
		<p style=\"padding: 10; width: 70%\">" . $get['name'] . "'s comment: " . strip_tags($get['comments']) . "</p>
		<ul>
			<li><a href=\"http://slivka.northwestern.edu/points/ajax/pointsCorrectionReply.php?key=$key&reply=$enc1\">" . $get['name'] . " was at " . $get['event_name'] . "</a></li>
			<li><a href=\"http://slivka.northwestern.edu/points/ajax/pointsCorrectionReply.php?key=$key&reply=$enc2\">" . $get['name'] . " was NOT at " . $get['event_name'] . "</a></li>
			<li><a href=\"http://slivka.northwestern.edu/points/ajax/pointsCorrectionReply.php?key=$key&reply=$enc3\">Not sure</a></li>
		</ul>

		<p style=\"padding: 10; width: 70%\">If you received this email in error, please contact " . $GLOBALS['VP_EMAIL'] . "</p>";

		return self::sendEmail($filled_by,"Points Correction for " . $get['event_name'] . " (Automated)", $html);
	}

	public function pointsCorrectionReply($get)
	{
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

			<p style=\"padding: 10; width: 70%\">If you received this email in error, please contact " . $GLOBALS['VP_EMAIL'] . "</p>";

		return self::sendEmail($result['nu_email'],"Points Correction Response Posted (Automated)",$html);
	}

	private function sendEmail($to_email,$subject,$body)
	{
		$from = array($GLOBALS['MAILBOT_EMAIL'] => "Slivka Points Center");

		if($to_email){
			$to = array(
				$to_email . "@u.northwestern.edu" => $to_email,
				$GLOBALS['VP_EMAIL_COPIES'] => $GLOBALS['VP_NAME'] . "'s Copy"
			);
		}else{
			$to = array($GLOBALS['VP_EMAIL'] => $GLOBALS['VP_NAME']);
		}

		$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
			->setUsername($GLOBALS['MAILBOT_EMAIL'])
			->setPassword($GLOBALS['MAILBOT_PASS']);

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
		$courses = array();
		try {
			$statement = self::$dbConn->prepare(
				"SELECT CONCAT(first_name, ' ', last_name) AS full_name,
					nu_email,qtr
				FROM courses
				INNER JOIN slivkans USING (nu_email)
				WHERE courses LIKE :course AND qtr_joined<=:qtr AND (qtr_final IS NULL OR qtr_final>=:qtr)");
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
		try {
			$statement = self::$dbConn->prepare(
				"INSERT INTO courses
				(nu_email,courses,qtr)
				VALUES (?,?,?)");
			$statement->execute(array($nu_email,$courses,$qtr));
		} catch (PDOException $e) {
			echo "Error: " . $e->getMessage() . "<br/>Tell the VP";
			die();
		}

		return true;
	}
}
