<?php
require_once "./DatabasePDO.php";
class PointsCenter
{
    private static $quarter = 1302;
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
    
    public function getSlivkans ()
    {
        self::initializeConnection();
        $slivkans = array();
        try {
            $statement = self::$dbConn->prepare(
            "SELECT full_name,nu_email,wildcard,committee 
            FROM directory 
            WHERE qtr_final IS NULL 
            ORDER BY first_name");
            $statement->execute();
            $slivkans = $statement->fetchAll();

            $full_name = array();
            $nu_email = array();
            $wildcard = array();
            $committee = array();

            foreach($slivkans as $s){
                $full_name[] = $s['full_name'];
                $nu_email[]  = $s['nu_email'];
                $wildcard[]  = $s['wildcard'];
                $committee[] = $s['committee'];
            }

            $slivkans = array('full_name'=>$full_name,'nu_email'=>$nu_email,'wildcard'=>$wildcard,'committee'=>$committee);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }
        return $slivkans;
    }

    public function getNicknames ()
    {
        self::initializeConnection();
        $nicknames = array();
        try {
            $statement = self::$dbConn->prepare(
                "SELECT nicknames.nickname,directory.full_name 
                FROM nicknames INNER JOIN directory ON nicknames.nu_email=directory.nu_email");
            $statement->execute();
            $nicknames = $statement->fetchAll();

            $nickname = array();
            $full_name = array();

            foreach($nicknames as $n){
                $nickname[] = $n['nickname'];
                $full_name[] = $n['full_name'];
            }
            $nicknames = array('nickname'=>$nickname,'full_name'=>$full_name);
            
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
                "SELECT full_name 
                FROM fellows 
                WHERE qtr_final IS NULL");
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
                WHERE quarter=:quarter AND date BETWEEN :start AND :end
                ORDER BY date");
            $statement->bindValue(":quarter", self::$quarter);
            $statement->bindValue(":start", $start, PDO::PARAM_STR);
            $statement->bindValue(":end", $end, PDO::PARAM_STR);
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
                "SELECT event_name,nu_email 
                FROM points
                WHERE quarter=:quarter");
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
                "SELECT event_name,nu_email 
                FROM helperpoints
                WHERE quarter=:quarter");
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
                "SELECT event_name,nu_email 
                FROM committeeattendance
                WHERE quarter=:quarter");
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
                "SELECT events.type,events.event_name,events.committee 
                FROM points INNER JOIN events ON points.event_name=events.event_name 
                WHERE events.quarter=:quarter AND points.nu_email=:nu_email AND events.date BETWEEN :start AND :end 
                ORDER BY events.date");
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

    public function getBonusPoints ()
    {
        self::initializeConnection();
        $bonus_points = array();
        try {
            $statement = self::$dbConn->prepare(
                "SELECT nu_email,committee,other1_name,other1,other2_name,other2,other3_name,other3 
                FROM bonuspoints 
                WHERE quarter=:quarter");
            $statement->bindValue(":quarter", self::$quarter);
            $statement->execute();
            $bonus_points = $statement->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }
        return $bonus_points;
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