<?php


class DB_Connect {
    private $conn;
    private $file;
    //constructor
    function __construct($file) {
        $this->file = $file;
        $this->connect();
    }
    public function connect() {
        writeToFile($this->file, "DB_Connect() :: connect()");
            //import database connection variables
        require_once "DB_Config.php";

        //create connection
        $this->conn = new mysqli(DB_HOST, USERNAME, PASSWORD, DATABASE);

        //Check Connection
        if(mysqli_connect_error()) {
            writeToFile($this->file, "DB_Connect() :: Connection failed: " .mysqli_connect_error());
        } else {
            writeToFile($this->file, "DB_Connect() :: Connection successful");
        }
    }

    public function closeConn() {
        writeToFile($this->file, "DB_Connect() :: closeConn()");
        writeToFile($this->file, "DB_Connect() :: Closing Connection");
        if(isset($this->conn)) {
            mysqli_close($this->conn);
        }
        unset($this);
    }
    public function getConn() {
        return $this->conn;
    }
}
