<?php

class Database
{
    private $connection;
    private static $instance;

//    private $host = "localhost";
//    private $username = "root";
//    private $password = "";
//    private $database = "eatsybitsy";

    private $host = "localhost";
    private $username = "yeloohr_myGuide";
    private $password = "myGuide2017";
    private $database = "yeloohr_myGuide";

    public static function getInstance()
    {
        if(!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->connection = new mysqli($this->host, $this->username,
            $this->password, $this->database);


        if(mysqli_connect_error()) {
            trigger_error("Failed to conencto to MySQL: " . mysqli_connect_error(),
                E_USER_ERROR);
        }
    }

    private function __clone() { }

    public function getConnection()
    {
        return $this->connection;
    }


}
?>