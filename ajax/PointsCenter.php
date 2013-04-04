<?php
require_once "./DatabasePDO.php";
class PointsCenter
{
    private static $quarter = "Spring 2013";
    public static $p2p_days = array("Tue","Thu");

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

    public function getP2PDays ()
    {
        return self::$p2p_days;
    }
    
    /**
     * get slivkans name, nu_email and committee
     */
    public function getSlivkans ()
    {
        self::initializeConnection();
        $slivkans = array();
        try {
            $statement = self::$dbConn->prepare(
            "SELECT full_name,nu_email,committee FROM directory WHERE qtr_final IS NULL ORDER BY first_name");
            $statement->execute();
            $slivkans = $statement->fetchAll();

            $full_name = array();
            $nu_email = array();
            $committee = array();

            foreach($slivkans as $s){
                $full_name[] = $s['full_name'];
                $nu_email[]  = $s['nu_email'];
                $committee[] = $s['committee'];
            }

            $slivkans = array('full_name'=>$full_name,'nu_email'=>$nu_email,'committee'=>$committee);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }
        return $slivkans;
    }

    /**
     * get nickname-nu_email pairs
     */

    public function getNicknames ()
    {
        self::initializeConnection();
        $nicknames = array();
        try {
            $statement = self::$dbConn->prepare(
            "SELECT nicknames.nickname,directory.first_name,directory.last_name FROM nicknames INNER JOIN directory ON nicknames.nu_email=directory.nu_email WHERE directory.qtr_final IS NULL");
            $statement->execute();
            $nicknames = $statement->fetchAll();

            $nickname = array();
            $aka = array();

            foreach($nicknames as $n){
                $nickname[] = $n['nickname'];
                $aka[] = $n['first_name'] . ' ' . $n['last_name'];
            }
            $nicknames = array('nickname'=>$nickname,'aka'=>$aka);
            
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }
        return $nicknames;
    }

    /**
     * get names of fellows
     */

    public function getFellows ()
    {
        self::initializeConnection();
        $fellows = array();
        try {
            $statement = self::$dbConn->prepare(
            "SELECT full_name FROM fellows WHERE qtr_final IS NULL");
            $statement->execute();
            $fellows = $statement->fetchAll(PDO::FETCH_COLUMN,0);
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

        $sql = "SELECT * FROM events WHERE quarter=:quarter";
        if($start AND $end){
            $sql .= " AND date BETWEEN :start AND :end";
        }
        $sql .= " ORDER BY date";

        try {
            $statement = self::$dbConn->prepare(
            $sql);
            $statement->bindValue(":quarter", self::$quarter);
            if($start AND $end){
                $statement->bindValue(":start", $start, PDO::PARAM_STR);
                $statement->bindValue(":end", $end, PDO::PARAM_STR);
            }
            $statement->execute();
            $events = $statement->fetchAll(PDO::FETCH_NAMED);

            $event_name = array();
            $date = array();
            $type = array();
            $attendees = array();
            $committee = array();
            $description = array();

            foreach($events as $e){
                $event_name[]  = $e['event_name'];
                $date[]        = $e['date'];
                $type[]        = $e['type'];
                $attendees[]   = $e['attendees'];
                $committee[]   = $e['committee'];
                $description[] = $e['description'];
            }
            
            $events = array('event_name'=>$event_name,'date'=>$date,'type'=>$type,'attendees'=>$attendees,'committee'=>$committee,'description'=>$description);
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
            "SELECT points.event_name,points.nu_email FROM points INNER JOIN events ON points.event_name=events.event_name WHERE events.quarter=:quarter ORDER BY events.date");
            $statement->bindValue(":quarter", self::$quarter);
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
            "SELECT helperpoints.event_name,helperpoints.nu_email FROM helperpoints INNER JOIN events ON helperpoints.event_name=events.event_name WHERE events.quarter=:quarter ORDER BY events.date");
            $statement->bindValue(":quarter", self::$quarter);
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
            "SELECT committeeattendance.event_name,committeeattendance.nu_email FROM committeeattendance INNER JOIN events ON committeeattendance.event_name=events.event_name WHERE events.quarter=:quarter ORDER BY events.date");
            $statement->bindValue(":quarter", self::$quarter);
            $statement->execute();
            $committee_attendance = $statement->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }
        return $committee_attendance;
    }

    public function getEventsAttendedBySlivkan ($nu_email,$start,$end)
    {
        self::initializeConnection();
        $events = array();
        try {
            $statement = self::$dbConn->prepare(
            "SELECT events.type,events.event_name,events.committee FROM points INNER JOIN events ON points.event_name=events.event_name WHERE events.quarter=:quarter AND points.nu_email=:nu_email AND events.date BETWEEN :start AND :end ORDER BY events.date");
            $statement->bindValue(":quarter", self::$quarter);
            $statement->bindValue(":nu_email", $nu_email, PDO::PARAM_STR);
            $statement->bindValue(":start", $start, PDO::PARAM_STR);
            $statement->bindValue(":end", $end, PDO::PARAM_STR);
            $statement->execute();
            $events = $statement->fetchAll(PDO::FETCH_NAMED);

            $type = array();
            $event_name = array();
            $committee = array();

            foreach($events as $e){
                $type[] = $e['type'];
                $event_name[] = $e['event_name'];
                $committee[] = $e['committee'];
            }

            $events = array('type'=>$type,'event_name'=>$event_name,'committee'=>$committee);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }
        return $events;
    }
    
    /**
     * Save the Book to the DB.  If new book, it creates a record and grabs the id.
     * @return boolean
     */
    public function save ()
    {
        try {
            if (empty($this->id)) {
                $statement = self::$dbConn->prepare(
                "INSERT INTO book SET title = :title");
                $statement->bindValue(':title', $this->title);
            } else {
                $statement = self::$dbConn->prepare(
                "UPDATE book SET title = :title WHERE id = :id");
                $statement->bindValue(':id', $this->id);
                $statement->bindValue(':title', $this->title);
            }
            if ($statement->execute()) {
                if (empty($this->id)) {
                    $this->id = self::$dbConn->lastInsertId();
                }
                return true;
            }
        } catch (PDOException $e) {
            echo "Error!: " . $e->getMessage();
            die();
        }
        return false;
    }
}