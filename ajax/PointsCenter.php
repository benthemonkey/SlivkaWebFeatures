<?php
namespace Slivka;

use PDO;
use PDOException;

class PointsCenter
{
    private static $qtr = 0;
    private static $config = null;
    private static $dbConn = null;

    public function __construct()
    {
        error_reporting(E_ALL & ~E_NOTICE);
        //ini_set('display_errors', '1');
        self::initializeConnection();
        if (isset($_GET['qtr'])) {
            self::$qtr = $_GET['qtr'];
        }
    }

    private static function initializeConnection()
    {
        require_once __DIR__ . "/datastoreVars.php";
        if (is_null(self::$dbConn)) {
            $dsn = $DB_TYPE . ":host=" . $DB_HOST . ";dbname=" . $DB_NAME;
            try {
                self::$dbConn = new PDO($dsn, $DB_USER, $DB_PASS);
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                die();
            }

            self::$dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            self::loadConfig();
        }
    }

    private static function fetchAllQuery($query, $fetch = PDO::FETCH_ASSOC, $params = array())
    {
        $out = array();

        try {
            $statement = self::$dbConn->prepare($query);
            $statement->bindValue(":qtr", self::$qtr);

            foreach ($params as $var => $value) {
                $statement->bindValue($var, $value);
            }

            $statement->execute();
            $out = $statement->fetchAll($fetch);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        return $out;
    }

    // Loads the configuration settings from the database
    private static function loadConfig()
    {
        try {
            self::$config = self::fetchAllQuery("SELECT name,value FROM config WHERE 1", PDO::FETCH_KEY_PAIR);
            self::$qtr = (int) self::$config["qtr"];
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }
    }

    public function getConfig()
    {
        return self::$config;
    }

    public function updateConfig($name, $value)
    {
        try {
            $statement = self::$dbConn->prepare(
                "INSERT INTO config (name, value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE value=VALUES(value)"
            );
            $statement->execute(array($name, $value));
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        // If we're changing the quarter make sure a row exists in the `quarters` table
        if ($name == 'qtr') {
            $qtr = (int) $value;
            $quarter_info = self::getQuarterInfo($qtr);
            if (!$quarter_info) {
                $y = round($qtr, -2);
                $q = $qtr - $y;

                $year = 2000 + $y / 100;

                $season = '';
                switch ($q) {
                    case 1:
                        $season = 'Winter';
                        break;
                    case 2:
                        $season = 'Spring';
                        break;
                    default:
                        $season = 'Fall';
                }

                // get im teams from last year, same season
                $last_year_quarter_info = self::getQuarterInfo($qtr - 100);

                if ($last_year_quarter_info) {
                    $im_teams = json_encode($last_year_quarter_info['im_teams']);
                } else {
                    $im_teams = '';
                }

                try {
                    $statement = self::$dbConn->prepare(
                        "INSERT INTO quarters (qtr, quarter, im_teams)
                        VALUES (?, ?, ?)"
                    );
                    $statement->execute(array($value, $season . ' ' . $year, $im_teams));
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                    die();
                }
            }
        }

        return true;
    }

    public function getQuarter()
    {
        return self::$qtr;
    }

    public function getQuarters()
    {
        return self::fetchAllQuery("SELECT qtr,quarter FROM quarters WHERE 1301 < qtr ORDER BY qtr DESC");
    }

    public function getQuarterInfo($qtr = false)
    {
        if (!$qtr) {
            $qtr = self::$qtr;
        }

        $quarter_info;
        try {
            $statement = self::$dbConn->prepare(
                "SELECT *
                FROM quarters
                WHERE qtr=:qtr"
            );
            $statement->bindValue(":qtr", $qtr);
            $statement->execute();
            $quarter_info = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        if ($quarter_info) {
            $quarter_info['im_teams'] = json_decode($quarter_info['im_teams']);
        }

        return $quarter_info;
    }

    public function updateQuarterInfo($name, $value)
    {
        try {
            $statement = self::$dbConn->prepare(
                "UPDATE quarters
                SET ".$name."=:value
                WHERE qtr=:qtr"
            );
            $statement->bindValue(":value", $value);
            $statement->bindValue(":qtr", self::$qtr);
            $statement->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        return true;
    }

    public function getDirectory()
    {
        return self::fetchAllQuery(
            "SELECT first_name,last_name,year,major,suite,photo
                FROM slivkans
                LEFT JOIN suites ON slivkans.nu_email=suites.nu_email AND suites.qtr=:qtr
                WHERE qtr_joined <= :qtr AND (qtr_final IS NULL OR qtr_final >= :qtr)
                ORDER BY first_name,last_name",
            PDO::FETCH_NUM
        );
    }

    public function getSlivkans()
    {
        $absentSlivkans = self::fetchAllQuery('SELECT nu_email FROM absences WHERE qtr=:qtr', PDO::FETCH_COLUMN);

        $nicknames = self::fetchAllQuery("SELECT nu_email,nickname FROM nicknames", PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        $slivkans = self::fetchAllQuery(
            "SELECT CONCAT(first_name, ' ', last_name) AS full_name,
                slivkans.nu_email,gender,wildcard,committee,photo,suite,year
            FROM slivkans
            LEFT JOIN committees ON slivkans.nu_email=committees.nu_email AND committees.qtr=:qtr
            LEFT JOIN suites ON slivkans.nu_email=suites.nu_email AND suites.qtr=:qtr
            WHERE qtr_joined <= :qtr AND (qtr_final IS NULL OR qtr_final >= :qtr)
                AND slivkans.nu_email NOT IN('" . implode("','", $absentSlivkans) . "')
            ORDER BY first_name,last_name"
        );

        # Add tokens for typeahead.js
        $n = count($slivkans);
        for ($i=0; $i<$n; $i++) {
            $slivkans[$i]["tokens"] = explode(" ", $slivkans[$i]["full_name"]);

            if (array_key_exists($slivkans[$i]["nu_email"], $nicknames)) {
                $slivkans[$i]["tokens"] = array_merge($slivkans[$i]["tokens"], $nicknames[$slivkans[$i]["nu_email"]]);
            }
        }
        return $slivkans;
    }

    public function getFellows()
    {
        return self::fetchAllQuery(
            "SELECT full_name,position,about,photo
            FROM fellows
            WHERE qtr_final IS NULL"
        );
    }

    public function updateFellowPhoto($fellow, $photo)
    {
        try {
            $statement = self::$dbConn->prepare(
                "UPDATE fellows
                SET photo=:photo
                WHERE full_name=:full_name"
            );
            $statement->bindValue(":full_name", $fellow);
            $statement->bindValue(":photo", $photo);
            $statement->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        return true;
    }

    public function getEvents($count = 20, $offset = 0)
    {
        $events = array();
        try {
            $statement = self::$dbConn->prepare(
                "SELECT event_name,date,type,attendees,committee,description
                FROM events
                WHERE qtr=:qtr AND type<>'committee_only'
                ORDER BY date DESC, id DESC
                ".($count != -1 ? "LIMIT :offset,:count" : "")
            );
            $statement->bindValue(":qtr", self::$qtr);

            if ($count != -1) {
                $statement->bindValue(":offset", $offset, PDO::PARAM_INT);
                $statement->bindValue(":count", $count, PDO::PARAM_INT);
            }
            $statement->execute();
            $events = $statement->fetchAll(PDO::FETCH_NAMED);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        return array_reverse($events);
    }

    public function getRecentEvents()
    {
        $days_later = 14;
        $start = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d")-$days_later, date("Y")));

        return self::fetchAllQuery(
            "SELECT event_name,date,type,attendees,committee,description
                FROM events
                WHERE qtr=:qtr AND date>:start AND type<>'committee_only'
                ORDER BY date, id",
            PDO::FETCH_NAMED,
            array(":start" => $start)
        );
    }

    public function eventNameExists($event_name)
    {
        return count(self::fetchAllQuery(
            "SELECT event_name
                FROM events
                WHERE qtr=:qtr AND event_name=:event_name",
            PDO::FETCH_COLUMN,
            array(":event_name" => $event_name)
        )) > 0;
    }

    public function getCommitteeEvents($committee)
    {
        if ($committee == "Facilities") {
            return self::fetchAllQuery(
                "SELECT event_name,filled_by
                    FROM events
                    WHERE qtr=:qtr AND type<>'im' AND type<>'p2p'
                    ORDER BY date, id"
            );
        } else {
            return self::fetchAllQuery(
                "SELECT event_name,filled_by
                    FROM events
                    WHERE qtr=:qtr AND committee=:committee AND type<>'im'
                    ORDER BY date, id",
                PDO::FETCH_ASSOC,
                array(":committee" => $committee)
            );
        }
    }

    public function getIMs($team)
    {
        return self::fetchAllQuery(
            "SELECT event_name
                FROM events
                WHERE qtr=:qtr AND type='im' AND event_name LIKE :team",
            PDO::FETCH_COLUMN,
            array(":team" => "%".$team."%")
        );
    }

    public function getPoints()
    {
        return self::fetchAllQuery(
            "SELECT event_name,nu_email FROM points WHERE qtr=:qtr",
            PDO::FETCH_COLUMN| PDO::FETCH_GROUP
        );
    }

    public function getHelperPoints()
    {
        return self::fetchAllQuery(
            "SELECT event_name,nu_email FROM helperpoints WHERE qtr=:qtr",
            PDO::FETCH_COLUMN| PDO::FETCH_GROUP
        );
    }

    public function getCommitteePoints()
    {
        return self::fetchAllQuery(
            "SELECT event_name,nu_email,points,contributions,comments FROM committeepoints WHERE qtr=:qtr",
            PDO::FETCH_GROUP| PDO::FETCH_ASSOC
        );
    }

    public function getCommitteeTotals()
    {
        return self::fetchAllQuery("SELECT nu_email,points FROM committees WHERE qtr=:qtr", PDO::FETCH_KEY_PAIR);
    }

    public function getCommitteeBonusPoints($committee)
    {
        return self::fetchAllQuery(
            "SELECT nu_email,bonus,comments FROM committees WHERE qtr=:qtr AND committee=:committee",
            PDO::FETCH_ASSOC,
            array(":committee" => $committee)
        );
    }

    public function getCommitteeAttendance($committee)
    {
        if ($committee == "Facilities") {
            return self::fetchAllQuery(
                "SELECT event_name, nu_email FROM
                    committees INNER JOIN points USING (nu_email,qtr)
                    WHERE committee='Facilities' AND qtr=:qtr",
                PDO::FETCH_COLUMN| PDO::FETCH_GROUP
            );
        } else {
            return self::fetchAllQuery(
                "SELECT p.event_name, p.nu_email FROM
                    (SELECT event_name, committee, qtr
                        FROM events
                        WHERE qtr=:qtr AND committee=:committee AND type<>'im') AS e
                    INNER JOIN committees AS c USING (committee, qtr)
                    INNER JOIN points AS p USING (event_name, nu_email)",
                PDO::FETCH_COLUMN| PDO::FETCH_GROUP,
                array(":committee" => $committee)
            );
        }
    }

    public function getEventTotals()
    {
        return self::fetchAllQuery(
            "SELECT nu_email, count(event_name) AS total
                FROM points INNER JOIN events USING (event_name, qtr)
                WHERE qtr=:qtr AND type<>'im' AND type<>'committee_only'
                GROUP BY nu_email",
            PDO::FETCH_KEY_PAIR
        );
    }

    public function getIMPoints()
    {
        return self::fetchAllQuery(
            "SELECT nu_email, LEAST(SUM(count),15) AS total
                FROM imcounts
                WHERE count>=3 AND qtr=:qtr
                GROUP BY nu_email",
            PDO::FETCH_KEY_PAIR
        );
    }

    public function getSlivkanPoints($nu_email)
    {
        return self::fetchAllQuery(
            "SELECT event_name FROM points WHERE qtr=:qtr AND nu_email=:nu_email",
            PDO::FETCH_COLUMN,
            array(":nu_email" => $nu_email)
        );
    }

    public function getSlivkanPointsByCommittee($nu_email)
    {
        return self::fetchAllQuery(
            "SELECT committee, count(nu_email) AS count
                FROM points
                INNER JOIN events USING (event_name,qtr)
                WHERE nu_email=:nu_email AND qtr=:qtr AND type<>'im' AND type<>'committee_only'
                GROUP BY committee",
            PDO::FETCH_ASSOC,
            array(":nu_email" => $nu_email)
        );
    }

    public function getSlivkanIMPoints($nu_email)
    {
        return self::fetchAllQuery(
            "SELECT sport,count FROM imcounts WHERE nu_email=:nu_email AND qtr=:qtr",
            PDO::FETCH_ASSOC,
            array(":nu_email" => $nu_email)
        );
    }

    public function getSlivkanBonusPoints($nu_email)
    {
        $helper_points = 0;
        try {
            $statement = self::$dbConn->prepare(
                "SELECT count
                FROM helperpointcounts
                WHERE nu_email=:nu_email AND qtr=:qtr"
            );
            $statement->bindValue(":qtr", self::$qtr);
            $statement->bindValue(":nu_email", $nu_email);
            $statement->execute();
            $helper_points = $statement->fetch(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        if (!$helper_points) {
            $helper_points = 0;
        }

        $bonus = array();
        try {
            $statement = self::$dbConn->prepare(
                "SELECT *
                FROM bonuspoints
                WHERE nu_email=:nu_email AND qtr=:qtr"
            );
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
                WHERE nu_email=:nu_email AND qtr=:qtr"
            );
            $statement->bindValue(":qtr", self::$qtr);
            $statement->bindValue(":nu_email", $nu_email);
            $statement->execute();
            $committee_points = $statement->fetch(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        if ($bonus) {
            $helper_points += $bonus['helper'];
            $other_points = $bonus['other1']+$bonus['other2']+$bonus['other3'];
        } else {
            $other_points = 0;
        }

        $other_breakdown = array();

        if (!empty($bonus['other1_name'])) {
            $other_breakdown[] = array($bonus['other1_name'], $bonus['other1']);
        }
        if (!empty($bonus['other2_name'])) {
            $other_breakdown[] = array($bonus['other2_name'], $bonus['other2']);
        }
        if (!empty($bonus['other3_name'])) {
            $other_breakdown[] = array($bonus['other3_name'], $bonus['other3']);
        }

        return array(
            "helper" => $helper_points,
            "committee" => $committee_points | 0,
            "other" => $other_points,
            "other_breakdown" => $other_breakdown
        );
    }

    public function getBonusPoints()
    {
        $bonus_points = array();
        try {
            // using left + right join to mimic full outer join
            $statement = self::$dbConn->prepare(
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
                    WHERE qtr=:qtr"
            );
            $statement->bindValue(":qtr", self::$qtr);
            $statement->execute();
            $bonus_points = $statement->fetchAll(PDO::FETCH_GROUP| PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        foreach (array_keys($bonus_points) as $s) {
            $bonus_points[$s] = $bonus_points[$s][0];
        }

        return $bonus_points;
    }

    public function getPointsTable($showall = false)
    {
        $slivkans = self::getSlivkans();
        if ($showall) {
            $events = self::getEvents(-1);
        } else {
            $events = self::getEvents();
        }
        $points = self::getPoints();
        $event_totals = self::getEventTotals();
        $im_points = self::getIMPoints();
        $bonus_points = self::getBonusPoints();

        $helper_points = self::getHelperPoints();
        $committee_points = self::getCommitteePoints();
        $committee_totals = self::getCommitteeTotals();

        // table that is slivkan count by event count + 6
        $points_table = array();

        // statistics holders:
        $totals_by_year = array();
        $totals_by_suite = array();

        // form points_table
        $events_count = count($events);
        $total_ind = $events_count + 7;

        for ($s=0; $s < count($slivkans); $s++) {
            $nu_email = $slivkans[$s]['nu_email'];

            $slivkan_events = 0;
            $slivkan_committee = 0;
            $slivkan_helper = 0;
            $slivkan_im = 0;
            $slivkan_other = 0;

            if (array_key_exists($nu_email, $event_totals)) {
                $slivkan_events = (int) $event_totals[$nu_email];
            }

            if (array_key_exists($nu_email, $committee_totals)) {
                $slivkan_committee = (int) $committee_totals[$nu_email];
            }

            if (array_key_exists($nu_email, $bonus_points)) {
                $slivkan_helper = (int) $bonus_points[$nu_email]['helper'];
                $slivkan_other = (int) $bonus_points[$nu_email]['other'];
            }
            if (array_key_exists($nu_email, $im_points)) {
                $slivkan_im = (int) $im_points[$nu_email];
            }

            $subtotal = $slivkan_events + $slivkan_committee + $slivkan_helper + $slivkan_im;
            $total = $subtotal + $slivkan_other;

            $totals_by_year[$slivkans[$s]['year']][] = $subtotal;
            $totals_by_suite[$slivkans[$s]['suite']][] = $subtotal;

            $points_table[$nu_email] = array_merge(
                array($slivkans[$s]['full_name'], $slivkans[$s]['gender']),
                ($events_count > 0 ? array_fill(0, $events_count, 0) : array()),
                array($slivkan_events, $slivkan_helper, $slivkan_im, $slivkan_committee, $slivkan_other, $total)
            );
        }

        for ($e=0; $e < $events_count; $e++) {
            $event_name = $events[$e]['event_name'];
            $is_im = $events[$e]['type'] == "im";

            foreach ($points[$event_name] as $s) {
                $points_table[$s][2+$e] = 1;
            }

            if (!$is_im) {
                if (array_key_exists($event_name, $helper_points)) {
                    foreach ($helper_points[$event_name] as $s) {
                        $points_table[$s][2+$e] += 0.1;
                    }
                }

                if (array_key_exists($event_name, $committee_points)) {
                    foreach ($committee_points[$event_name] as $s) {
                        if ($s['points'] > 0.0) {
                            $points_table[$s['nu_email']][2+$e] += 0.2;
                        }
                    }
                }
            }
        }

        foreach ($totals_by_year as $y => $totals) {
            $by_year[] = array($y, round(array_sum($totals)/count($totals), 2));
        }

        foreach ($totals_by_suite as $s => $totals) {
            $by_suite[] = array($s, round(array_sum($totals)/count($totals), 2));
        }

        return array(
            'points_table' => array_values($points_table),
            'events' => $events,
            'by_year' => $by_year,
            'by_suite' => $by_suite
        );
    }

    public function getCommitteePointsTable($committee)
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
        for ($s = 0; $s < count($slivkans); $s++) {
            $nu_email = $slivkans[$s]['nu_email'];

            $committee_points_table[$nu_email] = array_fill(
                0,
                $events_count + 2,
                array(
                    'points' => 0.0,
                    'filled_by' => false,
                    'attended' => false,
                    'contributions' => '',
                    'comments' => ''
                )
            );
        }

        for ($i=0; $i<count($committee_bonus_points); $i++) {
            $nu_email = $committee_bonus_points[$i]['nu_email'];

            $committee_points_table[$nu_email][$events_count]['points'] = (float) $committee_bonus_points[$i]['bonus'];
            $committee_points_table[$nu_email][$events_count]['comments'] = $committee_bonus_points[$i]['comments'];
            # total column
            $committee_points_table[$nu_email][$total_ind]['points'] = (float) $committee_bonus_points[$i]['bonus'];
        }

        for ($e=0; $e<$events_count; $e++) {
            $event_name = $events[$e]['event_name'];
            $filled_by = $events[$e]['filled_by'];

            $event_names[] = $event_name;

            if (array_key_exists($filled_by, $committee_points_table)) {
                $committee_points_table[$filled_by][$e]['filled_by'] = true;
            }

            if (array_key_exists($event_name, $committee_attendance)) {
                foreach ($committee_attendance[$event_name] as $s) {
                    $committee_points_table[$s][$e]['attended'] = true;
                }
            }

            if (array_key_exists($event_name, $committee_points)) {
                foreach ($committee_points[$event_name] as $s) {
                    if (array_key_exists($s['nu_email'], $committee_points_table)) {
                        $committee_points_table[$s['nu_email']][$e]['points'] += $s['points'];
                        $committee_points_table[$s['nu_email']][$e]['contributions'] = $s['contributions'];
                        $committee_points_table[$s['nu_email']][$e]['comments'] = $s['comments'];
                        $committee_points_table[$s['nu_email']][$total_ind]['points'] += $s['points'];
                    }
                }
            }
        }

        return array('points_table' => $committee_points_table, 'events' => $event_names);
    }

    public function getMultipliers()
    {
        $slivkans = self::fetchAllQuery(
            "SELECT CONCAT(first_name,' ',last_name) AS full_name,
                    slivkans.nu_email,gender,qtr_joined,qtr_final,year,suite
                FROM slivkans
                LEFT JOIN suites ON slivkans.nu_email=suites.nu_email AND suites.qtr=:qtr
                WHERE qtr_final IS NULL OR qtr_final>=:qtr
                ORDER BY first_name, last_name"
        );

        $noShows = self::fetchAllQuery("SELECT nu_email, COUNT(nu_email) AS count FROM noshows", PDO::FETCH_KEY_PAIR);

        $absences = self::fetchAllQuery("SELECT nu_email, COUNT(nu_email) AS count from absences GROUP BY nu_email", PDO::FETCH_KEY_PAIR);

        $count = count($slivkans);
        $is_housing = self::$config['is_housing'] == 'true';

        for ($s=0; $s<$count; $s++) {
            $y_join = round($slivkans[$s]['qtr_joined'], -2);
            $q_join = $slivkans[$s]['qtr_joined'] - $y_join;

            $y_this = round(self::$qtr, -2);
            $q_this = self::$qtr - $y_this;

            $y_acc = ($y_this - $y_join) / 100;
            $q_acc = $q_this - $q_join;

            $q_total = $q_acc + 3 * $y_acc;

            // Subtract from multiplier all quarters spent away
            if (array_key_exists($slivkans[$s]['nu_email'], $absences)) {
                $q_total -= $absences[$slivkans];
            }

            // give multiplier for current qtr if it isnt housing
            if (!$is_housing) {
                $q_total += 1;
            }

            // figure out if they are a non res freshman
            // NOTE: If not completing a 4-year program, this code mistakes the person for being a sophomore
            if ($slivkans[$s]['suite'] == 'NonRes') {
                $y_grad = (int) $y_this / 100 + 3; // four years later, but three if its Fall
                if ($q_this == 3) {
                    $y_grad += 1;
                }

                if ($y_grad == $slivkans[$s]['year'] - 2000) {
                    $q_total += 1;
                }
            }

            $mult = 1 + 0.1 * $q_total;

            if (array_key_exists($slivkans[$s]['nu_email'], $noShows)) {
                $noShowCount = (int) $noShows[$slivkans[$s]['nu_email']];

                $mult -= 0.05 * $noShowCount;
            }

            $slivkans[$s]['mult'] = $mult;
        }

        return $slivkans;
    }

    public function getRankings()
    {
        $is_housing = self::$config['is_housing'] == 'true';
        // figure out how many qtrs to consider
        // if its spring, you're trying to get final housing rankings.
        $y_this = round(self::$qtr, -2);
        $q_this = self::$qtr - $y_this;

        if ($q_this == 2 && !$is_housing) {
            $qtrs = array(self::$qtr);
        } elseif ($q_this == 3) {
            $qtrs = array(self::$qtr-1, self::$qtr);
        } elseif ($q_this == 1 || $is_housing) {
            $qtrs = array($y_this-100+2, $y_this-100+3, $y_this+1);
        } else {
            echo "Error: qtr messed up. " . $q_this;
            die();
        }

        // spring 13, fall 13, winter 14 HOUSING spring 14
        $abstentions = self::getAbstentions();
        $rankings = self::getMultipliers();

        $totals = array();

        foreach ($qtrs as $qtr) {
            $totals[$qtr] = self::fetchAllQuery(
                "SELECT nu_email,total
                    FROM totals
                    WHERE qtr=".$qtr."
                    ORDER BY qtr",
                PDO::FETCH_KEY_PAIR
            );
        }

        $house_meetings = self::fetchAllQuery(
            "SELECT qtr,count(*) AS 'count'
            FROM events
            WHERE qtr IN (".implode(",", $qtrs).") AND type='house_meeting'
            GROUP BY qtr",
            PDO::FETCH_KEY_PAIR
        );

        $mult_count = count($rankings);
        $qtrs_count = count($qtrs);

        for ($i=0; $i<$mult_count; $i++) {
            $sum = 0;
            $minimum = 0;
            for ($j=0; $j<$qtrs_count; $j++) {
                if (array_key_exists($rankings[$i]['nu_email'], $totals[$qtrs[$j]])) {
                    $t = (int) $totals[$qtrs[$j]][$rankings[$i]['nu_email']];
                } else {
                    $t = 0;
                }

                $rankings[$i][$qtrs[$j]] = $t;
                $sum += $t;
            }

            switch ($qtrs_count) {
                case 1:
                    $minimum = (int)$house_meetings[$qtrs[0]];
                    break;
                case 2:
                    if ($rankings[$i]['qtr_joined'] != $qtrs[1]) {
                        $minimum = (int)$house_meetings[$qtrs[0]];
                    }

                    $minimum += (int)$house_meetings[$qtrs[1]];
                    break;
                case 3:
                    if ($rankings[$i]['qtr_joined'] != $qtrs[1] && $rankings[$i]['qtr_joined'] != $qtrs[2]) {
                        $minimum = (int)$house_meetings[$qtrs[0]];
                    }

                    if ($rankings[$i]['qtr_joined'] != $qtrs[2]) {
                        $minimum += (int)$house_meetings[$qtrs[1]];
                    }

                    $minimum += (int)$house_meetings[$qtrs[2]];
                    break;
            }

            $rankings[$i]['total'] = $sum;
            $rankings[$i]['total_w_mult'] = $sum * $rankings[$i]['mult'];
            $rankings[$i]['abstains'] = in_array($rankings[$i]['nu_email'], $abstentions) ||
                                            $rankings[$i]['total'] < $minimum;
        }

        return array('rankings' => $rankings, 'qtrs' => $qtrs, 'is_housing' => $is_housing);
    }

    public function updateTotals()
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
                ON DUPLICATE KEY UPDATE total=VALUES(total)"
            );

            for ($s = 0; $s < count($slivkans); $s++) {
                $nu_email = $slivkans[$s]['nu_email'];
                $total = $event_totals[$nu_email] + $im_points[$nu_email];

                if (array_key_exists($nu_email, $committee_totals)) {
                    $total += $committee_totals[$nu_email];
                }

                if (array_key_exists($nu_email, $bonus_points)) {
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

    public function getAbstentions()
    {
        $q = self::$qtr - round(self::$qtr, -2);

        #round up to closest "YY02"
        if ($q == 3) {
            $last_qtr_in_academic_year = round(self::$qtr, -2) + 100 + 2;
        } else {
            $last_qtr_in_academic_year = round(self::$qtr, -2) + 2;
        }

        return self::fetchAllQuery(
            "SELECT nu_email FROM slivkans WHERE qtr_final>=:qtr AND qtr_final<=:last_qtr_in_academic_year",
            PDO::FETCH_COLUMN,
            array(":last_qtr_in_academic_year" => $last_qtr_in_academic_year)
        );
    }

    public function updateSlivkanPhoto($nu_email, $photo)
    {
        try {
            $statement = self::$dbConn->prepare(
                "UPDATE slivkans
                SET photo=:photo
                WHERE nu_email=:nu_email"
            );
            $statement->bindValue(":nu_email", $nu_email);
            $statement->bindValue(":photo", $photo);
            $statement->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        return true;
    }

    public function getCommittee($committee)
    {
        $slivkans = array();

        try {
            $statement = self::$dbConn->prepare(
                "SELECT nu_email, points
                FROM committees
                WHERE committee=:committee AND qtr=:qtr"
            );
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

    public function updateCommittee($slivkans, $committee, $points)
    {
        try {
            $statement = self::$dbConn->prepare(
                "DELETE FROM committees WHERE qtr=:qtr AND committee=:committee AND
                nu_email NOT IN ('".implode("','", $slivkans)."')"
            );
            $statement->bindValue(':qtr', self::$qtr);
            $statement->bindValue(':committee', $committee);
            $statement->execute();

            $statement = self::$dbConn->prepare(
                "INSERT INTO committees (nu_email, committee, points, qtr) VALUES (?,?,?,?)
                ON DUPLICATE KEY UPDATE committee=VALUES(committee), points=VALUES(points)"
            );

            for ($s=0; $s < count($slivkans); $s++) {
                $statement->execute(array($slivkans[$s], $committee, $points[$s], self::$qtr));
            }

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        return true;
    }

    public function getSuite($suite)
    {
        $slivkans = array();

        try {
            $statement = self::$dbConn->prepare(
                "SELECT nu_email
                FROM suites
                WHERE suite=:suite AND qtr=:qtr"
            );
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

    public function updateSuite($slivkans, $suite)
    {
        try {
            $statement = self::$dbConn->prepare(
                "DELETE FROM suites WHERE qtr=:qtr AND suite=:suite AND
                nu_email NOT IN ('".implode("','", $slivkans)."')"
            );
            $statement->bindValue(':qtr', self::$qtr);
            $statement->bindValue(':suite', $suite);
            $statement->execute();

            $statement = self::$dbConn->prepare(
                "INSERT INTO suites (nu_email, suite, qtr) VALUES (?,?,?)
                ON DUPLICATE KEY UPDATE suite=VALUES(suite)"
            );

            for ($s=0; $s < count($slivkans); $s++) {
                $statement->execute(array($slivkans[$s], $suite, self::$qtr));
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        return true;
    }

    public function copySuites()
    {
        // figure out previous quarter
        $y = round(self::$qtr, -2);
        $q = self::$qtr - $y;
        if ($q == 1) {
            $qtrPrevious = ($y - 100) + 3;
        } else {
            $qtrPrevious = $y + ($q - 1);
        }

        try {
            $statement = self::$dbConn->prepare(
                "INSERT INTO suites (nu_email,suite,qtr)
                SELECT s.nu_email,s.suite,:qtr FROM suites AS s WHERE s.qtr=:qtrPrevious"
            );
            $statement->bindValue(':qtr', self::$qtr);
            $statement->bindValue(':qtrPrevious', $qtrPrevious);
            $statement->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        return true;
    }

    public function submitCommitteePoint($nu_email, $event_name, $points, $contributions, $comments)
    {
        if (($points == '0' || $points == '0.0') && $contributions == '' && $comments == '') {
            try {
                $statement = self::$dbConn->prepare(
                    "DELETE FROM committeepoints
                    WHERE nu_email=:nu_email AND event_name=:event_name AND qtr=:qtr"
                );
                $statement->bindValue(':nu_email', $nu_email);
                $statement->bindValue(':event_name', $event_name);
                $statement->bindValue(':qtr', self::$qtr);

                $statement->execute();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                die();
            }
        } elseif ($event_name == 'bonus') {
            try {
                $statement = self::$dbConn->prepare(
                    "UPDATE committees
                    SET bonus=:bonus, comments=:comments
                    WHERE nu_email=:nu_email AND qtr=:qtr"
                );
                $statement->bindValue(':bonus', $points);
                $statement->bindValue(':comments', strip_tags($comments));
                $statement->bindValue(':nu_email', $nu_email);
                $statement->bindValue(':qtr', self::$qtr);

                $statement->execute();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                die();
            }
        } else {
            try {
                $statement = self::$dbConn->prepare(
                    "INSERT INTO committeepoints (nu_email,event_name,points,contributions,comments,qtr)
                    VALUES (?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE points=VALUES(points), contributions=VALUES(contributions), comments=VALUES(comments)"
                );

                $statement->execute(array($nu_email, $event_name, $points, $contributions, strip_tags($comments), self::$qtr));
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                die();
            }
        }

        return true;
    }

    public function submitHelperPoint($full_name, $nu_email, $event_name, $comments)
    {
        try {
            $statement = self::$dbConn->prepare(
                "INSERT INTO helperpoints (nu_email,event_name,comments,qtr) VALUES (?,?,?,?)"
            );

            $statement->execute(array($nu_email, $event_name, $comments, self::$qtr));
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        self::sendEmail(false, "Helper Point given to " . $full_name . " for " . $event_name, "Reason: " . $comments);

        return true;
    }

    public function submitNoShow($full_name, $nu_email, $date, $comments)
    {
        try {
            $statement = self::$dbConn->prepare(
                "INSERT INTO noshows (nu_email,date,comments,qtr) VALUES (?,?,?,?)"
            );

            $statement->execute(array($nu_email, $date, $comments, self::$qtr));
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        self::sendEmail(false, "No-show submitted for " . $full_name . " on " . $date, "Reason: " . $comments);

        return true;
    }

    public function submitPointsForm($form_data)
    {
        $real_event_name = $form_data['event_name'] . " " . $form_data['date'];

        if ($form_data['committee_members'] === null) {
            $form_data['committee_members'] = array("");
        }
        if ($form_data['fellows'] === null) {
            $form_data['fellows'] = array("");
        }

        # Begin PDO Transaction
        self::$dbConn->beginTransaction();

        try {
            $statement = self::$dbConn->prepare(
                "INSERT INTO pointsform SET date=:date, type=:type, committee=:committee, event_name=:event_name,
                description=:description, filled_by=:filled_by, comments=:comments, attendees=:attendees,
                committee_members=:committee_members, fellows=:fellows"
            );
            $statement->bindValue(":date", $form_data['date']);
            $statement->bindValue(":type", $form_data['type']);
            $statement->bindValue(":committee", $form_data['committee']);
            $statement->bindValue(":event_name", $form_data['event_name']);
            $statement->bindValue(":description", $form_data['description']);
            $statement->bindValue(":filled_by", $form_data['filled_by']);
            $statement->bindValue(":comments", $form_data['comments']);
            $statement->bindValue(":attendees", implode(", ", $form_data['attendees']));
            $statement->bindValue(":committee_members", implode(", ", $form_data['committee_members']));
            $statement->bindValue(":fellows", implode(", ", $form_data['fellows']));

            $statement->execute();
        } catch (PDOException $e) {
            echo json_encode(array("error" => $e->getMessage(), "step" => "1"));
            self::$dbConn->rollBack();
            die();
        }

        try {
            $statement = self::$dbConn->prepare(
                "INSERT INTO events (event_name,date,qtr,filled_by,committee,description,type,attendees)
                VALUES (:event_name,:date,:qtr,:filled_by,:committee,:description,:type,:attendees)"
            );
            $statement->bindValue(":event_name", $real_event_name);
            $statement->bindValue(":date", $form_data['date']);
            $statement->bindValue(":qtr", self::$qtr);
            $statement->bindValue(":filled_by", $form_data['filled_by']);
            $statement->bindValue(":committee", $form_data['committee']);
            $statement->bindValue(":description", $form_data['description']);
            $statement->bindValue(":type", $form_data['type']);
            $statement->bindValue(":attendees", count($form_data['attendees']));

            $statement->execute();
        } catch (PDOException $e) {
            echo json_encode(array("error" => $e->getMessage(), "step" => "2"));
            self::$dbConn->rollBack();
            die();
        }

        try {
            $statement = self::$dbConn->prepare(
                "INSERT INTO points (nu_email,event_name,qtr)
                VALUES (?,?,?)"
            );

            foreach ($form_data['attendees'] as $a) {
                $statement->execute(array($a,$real_event_name,self::$qtr));
            }
        } catch (PDOException $e) {
            echo json_encode(array("error" => $e->getMessage(), "step" => "3"));
            self::$dbConn->rollBack();
            die();
        }

        /*if ($form_data['helper_points'][0] != "") {
            try {
                $statement = self::$dbConn->prepare(
                    "INSERT INTO helperpoints (nu_email, event_name, qtr)
                    VALUES (?,?,?)");

                foreach ($form_data['helper_points'] as $h) {
                    $statement->execute(array($h,$real_event_name,self::$qtr));
                }
            } catch (PDOException $e) {
                echo json_encode(array("error" => $e->getMessage(), "step" => "4"));
                self::$dbConn->rollBack();
                die();
            }
        }*/

        /*if ($form_data['committee_members'][0] != "") {
            try {
                $statement = self::$dbConn->prepare(
                    "INSERT INTO committeepoints (nu_email, event_name, points, qtr)
                    VALUES (?,?,?,?)"
                );

                foreach ($form_data['committee_members'] as $c) {
                    $statement->execute(array($c,$real_event_name,$form_data['filled_by'] == $c ? 1.0 : 0.5,self::$qtr));
                }
            } catch (PDOException $e) {
                echo json_encode(array("error" => $e->getMessage(), "step" => "5"));
                self::$dbConn->rollBack();
                die();
            }
        }*/

        if ($form_data['fellows'][0] != "") {
            try {
                $statement = self::$dbConn->prepare(
                    "INSERT INTO fellowattendance (full_name,event_name,qtr)
                    VALUES (?,?,?)"
                );

                foreach ($form_data['fellows'] as $f) {
                    $statement->execute(array($f,$real_event_name,self::$qtr));
                }
            } catch (PDOException $e) {
                echo json_encode(array("error" => $e->getMessage(), "step" => "4"));
                self::$dbConn->rollBack();
                die();
            }
        }

        #email VP a notification?
        if (self::$config['vp_email_notifications'] == 'true') {
            $html = "<table border=\"1\">";

            foreach ($form_data as $key => $value) {
                $html .= "<tr><td style=\"text-align:right;\">" . $key . "</td><td>";

                if (is_array($value)) {
                    $html .= implode(", ", $value);
                } else {
                    $html .= $value;
                }

                $html .= "</td></tr>\n";
            }

            $html .= "</table>";

            self::sendEmail(false, "Points Submitted for " . $real_event_name, $html);
        }

        return self::$dbConn->commit();
    }

    public function submitPointsCorrectionForm($form_data, $key)
    {
        try {
            $statement = self::$dbConn->prepare(
                "SELECT *
                FROM points
                WHERE event_name=:event_name AND nu_email=:nu_email"
            );
            $statement->bindValue(":event_name", $form_data['event_name']);
            $statement->bindValue(":nu_email", $form_data['sender_email']);

            $statement->execute();
            if ($statement->rowCount() > 0) {
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
                WHERE event_name=:event_name"
            );
            $statement->bindValue(":event_name", $form_data['event_name']);
            $statement->execute();

            $filled_by = $statement->fetch(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            echo json_encode(array("message" => "Error: " . $e->getMessage()));
            die();
        }

        if ($form_data['comments'] === null) {
            $form_data['comments'] = "";
        }

        try {
            $statement = self::$dbConn->prepare(
                "INSERT INTO pointscorrection (message_key,nu_email,event_name,comments)
                VALUES (:message_key,:nu_email,:event_name,:comments)"
            );
            $statement->bindValue(":message_key", $key);
            $statement->bindValue(":nu_email", $form_data['sender_email']);
            $statement->bindValue(":event_name", $form_data['event_name']);
            $statement->bindValue(":comments", $form_data['comments']);

            $statement->execute();
        } catch (PDOException $e) {
            echo json_encode(array("message" => "Error: Maybe you are trying to submit the same correction twice."));
            die();
        }

        $enc1 = md5('1');
        $enc2 = md5('2');
        $enc3 = md5('3');

        $url = "http://slivka.northwestern.edu/points/ajax/pointsCorrectionReply.php";

        $html = "<h2>Slivka Points Correction</h2>
        <h3>Automated Email</h3>
        <p style=\"padding: 10; width: 70%\">" . $form_data['name'] . " has submitted a points correction for the
        event, " . $form_data['event_name'] . ", for which you took points. Please click one of the following links
        to respond to this request. Please do so within 2 days of receiving this email.</p>
        <p style=\"padding: 10; width: 70%\">" . $form_data['name'] . "'s comment: " . strip_tags($form_data['comments']) . "</p>
        <ul>
            <li><a href=\"$url?key=$key&reply=$enc1\">" . $form_data['name'] . " was at " . $form_data['event_name'] . "</a></li>
            <li><a href=\"$url?key=$key&reply=$enc2\">" . $form_data['name'] . " was NOT at " . $form_data['event_name'] . "</a></li>
            <li><a href=\"$url?key=$key&reply=$enc3\">Not sure</a></li>
        </ul>

        <p style=\"padding: 10; width: 70%\">If you received this email in error, please contact " . self::$config['vp_email'] . "</p>";

        return self::sendEmail($filled_by, "Points Correction for " . $form_data['event_name'] . " (Automated)", $html);
    }

    public function getOpenPointsCorrections()
    {
        return self::fetchAllQuery("SELECT nu_email,event_name FROM pointscorrection WHERE response=0");
    }

    public function pointsCorrectionReply($get)
    {
        if ($get['reply'] == md5('1')) {
            $code = 1;
        } elseif ($get['reply'] == md5('2')) {
            $code = 2;
        } elseif ($get['reply'] == md5('3')) {
            $code = 3;
        } else {
            die("Error in decoding");
        }

        try {
            $statement = self::$dbConn->prepare(
                "SELECT *
                FROM pointscorrection
                WHERE message_key=:key"
            );
            $statement->bindValue(":key", $get['key']);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        if ($result['response'] == "0") {
            try {
                $statement = self::$dbConn->prepare(
                    "UPDATE pointscorrection
                    SET response=:code
                    WHERE message_key=:key"
                );
                $statement->bindValue(":code", $code);
                $statement->bindValue(":key", $get['key']);
                $statement->execute();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                die();
            }

        } else {
            echo "You already responded to this request.";
            die();
        }

        if ($code == 1) {
            try {
                $statement = self::$dbConn->prepare(
                    "INSERT INTO points (nu_email,event_name,qtr)
                    VALUES (:nu_email,:event_name,:qtr)"
                );
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
        } elseif ($code == 2) {
            echo "Success! A point has NOT been given.";
            $html_snippet = "You were NOT given a point for " . $result['event_name'] . ". You can request an explanation through the VP.";
        } elseif ($code == 3) {
            echo "Success! The VP will consult another attendee of the event.";
            $html_snippet = "The points taker couldn't remember if you were at " . $result['event_name'] . " and additional inquiry will be made by the VP.";
        }

        $html = "<h2>Slivka Points Correction Response Posted</h2>
            <h3>Automated Email</h3>
            <p style=\"padding: 10; width: 70%\">A points correction you submitted has received a response:</p>

            <p style=\"padding: 10; width: 70%\">" . $html_snippet . "</p>

            <p style=\"padding: 10; width: 70%\"><a href=\"http://slivka.northwestern.edu/points/table.php\" target=\"_blank\">View Points</a></p>

            <p style=\"padding: 10; width: 70%\">If you received this email in error, please contact " . self::$config['vp_email'] . "</p>";

        return self::sendEmail($result['nu_email'], "Points Correction Response Posted (Automated)", $html);
    }

    private function sendEmail($to_email, $subject, $body)
    {
        include_once __DIR__ . "/swift/swift_required.php";

        $from = array(self::$config['mailbot_email'] => "Slivka Points Center");

        if ($to_email) {
            $to = array(
                $to_email . "@u.northwestern.edu" => $to_email,
                self::$config['vp_email_copies'] => self::$config['vp_name'] . "'s Copy"
            );
        } else {
            $to = array(self::$config['vp_email'] => self::$config['vp_name']);
        }

        try {
            $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
                ->setUsername(self::$config['mailbot_email'])
                ->setPassword(self::$config['mailbot_password']);

            $mailer = \Swift_Mailer::newInstance($transport);

            $message = new \Swift_Message($subject);
            $message->setFrom($from);
            $message->setBody($body, 'text/html');
            $message->setTo($to);
            $message->addPart($subject, 'text/plain');

            if ($recipients = $mailer->send($message, $failures)) {
                return "Message successfully sent!";
            } else {
                return "There was an error: " . print_r($failures);
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            die();
        }
    }

    public function getCoursesInDept($department)
    {
        $courses = array();
        try {
            $statement = self::$dbConn->prepare(
                "SELECT courses
                FROM courses
                WHERE courses LIKE :department"
            );
            $statement->bindValue(":department", "%".$department."%");
            $statement->execute();
            $courses = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }

        $return = array();

        foreach ($courses as $c) {
            $arr = explode($department, $c);

            foreach ($arr as $el) {
                if ($el[0] == ' ') {
                    $return[] = substr($el, 1, ($el[5] > 0 && $el[5] < 5 ? 5 : 3));
                }
            }
        }

        $return = array_unique($return);
        sort($return);

        return $return;
    }

    public function getCourseListing($department, $number)
    {
        return self::fetchAllQuery(
            "SELECT CONCAT(first_name, ' ', last_name) AS full_name,
                    nu_email,qtr
                FROM courses
                INNER JOIN slivkans USING (nu_email)
                WHERE courses LIKE :course AND qtr_joined<=:qtr AND (qtr_final IS NULL OR qtr_final>=:qtr)",
            PDO::FETCH_ASSOC,
            array(":course" => "%".$department." ".$number."%")
        );
    }

    public function submitCourseDatabaseEntryForm($nu_email, $courses, $qtr)
    {
        try {
            $statement = self::$dbConn->prepare(
                "INSERT INTO courses
                (nu_email,courses,qtr)
                VALUES (?,?,?)"
            );
            $statement->execute(array($nu_email,$courses,$qtr));
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage() . "<br/>Tell the VP";
            die();
        }

        return true;
    }
}
