<?php
require_once "./DatabasePDO.php";
class PointsCenter
{
    private static $quarter = "Spring 2013";
    private static $p2p_days = array("Tue","Thu");

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

            $slivkans = array("full_name"=>$full_name,"nu_email"=>$nu_email,"committee"=>$committee);
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
            $nicknames = array(nickname=>$nickname,aka=>$aka);
            
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

    public static function getEvents ()
    {
        self::initializeConnection();
        $events = array();
        try {
            $statement = self::$dbConn->prepare(
            "SELECT * FROM events WHERE quarter=':quarter' ORDER BY date");
            $statement->bindValue(":quarter", $quarter);
            $statement->execute();
            $events = $statement->fetchAll();

            
        }
    }
    
    /**
     * @return Book 
     */
    public static function findById ($id)
    {
        self::initializeConnection();
        $book = null;
        try {
            $statement = self::$dbConn->prepare(
            "SELECT  * from book WHERE id = :id");
            $statement->bindValue(":id", $id);
            $statement->execute();
            $statement->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
            $book = $statement->fetch();
        } catch (PDOException $e) {
            echo "Error!: " . $e->getMessage();
            die();
        }
        return $book;
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